<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Staff\DeactivateStaffMember;
use App\Actions\Staff\InviteStaffMember;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffMemberRequest;
use App\Http\Requests\Admin\UpdateStaffRoleRequest;
use App\Http\Requests\Admin\UpdateStaffSalaryRequest;
use App\Http\Resources\AdminStaffMemberResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Current staff, admins first, and the staff who have left, most recent
     * first.
     */
    public function index(): Response
    {
        return Inertia::render('admin/staff/Index', [
            'staff' => AdminStaffMemberResource::collection(
                User::query()->whereNull('deactivated_at')->orderByRaw('role = ? desc', [UserRole::Admin->value])->orderBy('name')->get()
            )->resolve(),
            'formerStaff' => AdminStaffMemberResource::collection(
                User::query()->whereNotNull('deactivated_at')->with('deactivatedBy:id,name')->latest('deactivated_at')->get()
            )->resolve(),
            'roles' => collect(UserRole::cases())->map(fn (UserRole $role): array => ['value' => $role->value, 'label' => $role->label()]),
        ]);
    }

    /**
     * Add a staff member and email them a link to set their password.
     */
    public function store(StoreStaffMemberRequest $request, InviteStaffMember $inviteStaffMember): RedirectResponse
    {
        $user = $inviteStaffMember->handle(
            $request->string('name')->trim()->toString(),
            $request->string('email')->trim()->toString(),
            UserRole::from($request->string('role')->toString()),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name was added and emailed a link to set their password.', ['name' => $user->name])]);

        return to_route('admin.staff.index');
    }

    /**
     * Change a staff member's permissions.
     */
    public function update(UpdateStaffRoleRequest $request, User $user): RedirectResponse
    {
        $user->role = UserRole::from($request->string('role')->toString());
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is now :role.', ['name' => $user->name, 'role' => $user->role->label()])]);

        return back();
    }

    /**
     * Set a staff member's current monthly salary, used for payroll from the
     * next approval. Pay already approved keeps the salary it was approved
     * with.
     */
    public function updateSalary(UpdateStaffSalaryRequest $request, User $user): RedirectResponse
    {
        $user->monthly_salary = $request->salaryInKobo();
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name\'s salary was saved.', ['name' => $user->name])]);

        return back();
    }

    /**
     * Email a staff member a new link to set their password, for an expired
     * invitation or a forgotten password.
     */
    public function sendPasswordLink(InviteStaffMember $inviteStaffMember, User $user): RedirectResponse
    {
        abort_unless($user->isActive(), 409, 'This account is deactivated. Reactivate it first.');

        $inviteStaffMember->sendInvitation($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('A link to set a password was emailed to :email.', ['email' => $user->email])]);

        return back();
    }

    /**
     * Deactivate the account of a staff member who has left. They are signed
     * out everywhere and can no longer sign in; their records are kept.
     */
    public function deactivate(Request $request, DeactivateStaffMember $deactivateStaffMember, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['account' => 'You cannot deactivate your own account. Ask another admin to do it.']);
        }

        abort_unless($user->isActive(), 409, 'This account is already deactivated.');

        $openRecords = $deactivateStaffMember->handle($user, $request->user());

        Inertia::flash('toast', $openRecords === 0
            ? ['type' => 'success', 'message' => __(':name can no longer sign in. Their records have been kept.', ['name' => $user->name])]
            : ['type' => 'warning', 'message' => trans_choice('{1} :name can no longer sign in. They left a clock-in or activity open: close it on their timesheet.|[2,*] :name can no longer sign in. They left :count records open: close them on their timesheet.', $openRecords, ['name' => $user->name])]);

        return back();
    }

    /**
     * Let a returning staff member sign in again.
     */
    public function reactivate(DeactivateStaffMember $deactivateStaffMember, User $user): RedirectResponse
    {
        $deactivateStaffMember->reactivate($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name can sign in again.', ['name' => $user->name])]);

        return back();
    }
}
