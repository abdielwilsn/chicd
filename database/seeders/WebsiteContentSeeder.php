<?php

namespace Database\Seeders;

use App\Enums\OpportunityType;
use App\Enums\PostType;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    /**
     * Seed sample team members, opportunities and posts so the website's
     * content pages can be previewed locally. Programmes and gallery items
     * need real photographs, so add those through the admin area.
     */
    public function run(): void
    {
        TeamMember::factory()->leadership()->create(['position' => 'Executive Director', 'sort_order' => 1]);
        TeamMember::factory()->count(3)->create();

        Opportunity::factory()->create(['type' => OpportunityType::Volunteer, 'title' => 'Community Outreach Volunteer']);
        Opportunity::factory()->create(['type' => OpportunityType::Consultancy, 'title' => 'Baseline Survey Consultant']);

        Post::factory()->event()->create(['title' => 'Community Health Fair']);
        Post::factory()->create(['type' => PostType::Story]);
        Post::factory()->count(2)->create();
    }
}
