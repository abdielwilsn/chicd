<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('office_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('clocked_in_at');
            $table->decimal('clock_in_latitude', 10, 7);
            $table->decimal('clock_in_longitude', 10, 7);
            $table->unsignedInteger('clock_in_accuracy_metres')->nullable();
            $table->unsignedInteger('clock_in_distance_metres')->nullable();
            $table->boolean('clocked_in_within_geofence');

            $table->timestamp('clocked_out_at')->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('clock_out_accuracy_metres')->nullable();
            $table->unsignedInteger('clock_out_distance_metres')->nullable();
            $table->boolean('clocked_out_within_geofence')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'clocked_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_attendances');
    }
};
