<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Amu\Core\Models\GameModule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicPageController extends Controller
{
    public function home()
    {
        return $this->renderPageBySlug(SitePage::SLUG_HOME);
    }

    public function show(string $page)
    {
        $slug = match ($page) {
            'about-us' => SitePage::SLUG_ABOUT,
            'games' => SitePage::SLUG_GAMES,
            'membership' => SitePage::SLUG_MEMBERSHIP,
            'contact-us' => SitePage::SLUG_CONTACT,
            default => throw new ModelNotFoundException(),
        };

        return $this->renderPageBySlug($slug);
    }

    private function renderPageBySlug(string $slug)
    {
        $page = SitePage::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $navPages = SitePage::navigation()->get();
        $enabledGames = collect();

        if ($page->slug === SitePage::SLUG_GAMES) {
            $enabledGames = GameModule::query()
                ->where('enabled', true)
                ->orderBy('name')
                ->get();
        }

        return view('public.page', compact('page', 'navPages', 'enabledGames'));
    }
}
