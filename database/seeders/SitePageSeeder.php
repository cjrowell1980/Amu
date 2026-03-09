<?php

namespace Database\Seeders;

use App\Models\SitePage;
use Illuminate\Database\Seeder;

class SitePageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => SitePage::SLUG_HOME,
                'nav_label' => 'Home',
                'title' => 'Amu Home',
                'hero_title' => 'A platform core for modular multiplayer games.',
                'hero_body' => 'Amu handles identity, lobbies, sessions, realtime events, and operator workflows so new game modules can be introduced without rebuilding the platform.',
                'body' => "Amu is the service layer beneath the eventual player-facing experience.\n\nGuests can use this site to understand what the platform does today. Operators and moderators can sign in to manage rooms, sessions, games, and audit visibility.\n\nThis approach keeps the platform stable while game-specific experiences evolve independently.",
                'meta_description' => 'Amu is a multiplayer game platform core with auth, rooms, sessions, realtime events, and operator tooling.',
                'cta_label' => 'Admin Sign In',
                'cta_link' => '/login',
                'sort_order' => 10,
            ],
            [
                'slug' => SitePage::SLUG_ABOUT,
                'nav_label' => 'About Us',
                'title' => 'About Amu',
                'hero_title' => 'Designed as the operational backbone for multiplayer experiences.',
                'hero_body' => 'Amu was built to separate platform concerns from game-specific rules, making delivery, moderation, and future game expansion easier to manage.',
                'body' => "The platform focuses on the recurring parts of multiplayer products: authentication, room orchestration, session lifecycle, reconnect handling, and operator tooling.\n\nThat means teams can spend more energy on game design and less on rebuilding foundational infrastructure for every title.\n\nThe current web interface is intentionally practical. It exposes the management surface now and leaves room for a dedicated player application later.",
                'meta_description' => 'Learn how Amu separates platform infrastructure from game-specific modules.',
                'cta_label' => 'View Games',
                'cta_link' => '/games',
                'sort_order' => 20,
            ],
            [
                'slug' => SitePage::SLUG_GAMES,
                'nav_label' => 'Games',
                'title' => 'Games on Amu',
                'hero_title' => 'Game modules can arrive without changing the platform spine.',
                'hero_body' => 'Amu exposes a registry-driven path for introducing new game modules while keeping authentication, rooms, sessions, and operator tooling consistent.',
                'body' => "The games page can describe the current live catalog, upcoming launches, and which experiences are in beta or staff-only review.\n\nBecause Amu treats games as modules, the platform can evolve without turning every new title into an infrastructure rewrite.\n\nUse the admin area to keep this page current as the catalog changes.",
                'meta_description' => 'Review the current and upcoming game modules managed through Amu.',
                'cta_label' => 'Membership Options',
                'cta_link' => '/membership',
                'sort_order' => 30,
            ],
            [
                'slug' => SitePage::SLUG_MEMBERSHIP,
                'nav_label' => 'Membership',
                'title' => 'Membership',
                'hero_title' => 'Explain access tiers, benefits, and onboarding in one place.',
                'hero_body' => 'Use this page to describe membership plans, waitlists, early access rules, or any premium benefits attached to your platform rollout.',
                'body' => "Membership content is fully editable from the admin area so your messaging can change as launch phases change.\n\nYou can use this space for pricing, private beta access, support expectations, or eligibility requirements for special game modes.\n\nIf the public offer changes, the navigation and page content stay in sync from the same admin editor.",
                'meta_description' => 'Describe membership plans, access tiers, and early-access options for Amu.',
                'cta_label' => 'Contact Us',
                'cta_link' => '/contact-us',
                'sort_order' => 40,
            ],
            [
                'slug' => SitePage::SLUG_CONTACT,
                'nav_label' => 'Contact Us',
                'title' => 'Contact Us',
                'hero_title' => 'Make the next step obvious for players, partners, or operators.',
                'hero_body' => 'Use this page for support routes, partnership enquiries, moderation contact details, or community onboarding instructions.',
                'body' => "This page is intended for practical contact guidance.\n\nAdd support email addresses, Discord or community links, moderation escalation instructions, or a simple explanation of how to reach the team.\n\nBecause the content is editable in admin, non-developers can keep the contact details current without code changes.",
                'meta_description' => 'Find support, partnership, and operational contact information for Amu.',
                'cta_label' => 'Back to Home',
                'cta_link' => '/',
                'sort_order' => 50,
            ],
        ];

        foreach ($pages as $page) {
            SitePage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
