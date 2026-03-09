<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SitePageController extends Controller
{
    public function index()
    {
        $pages = SitePage::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(SitePage $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, SitePage $page)
    {
        $validated = $request->validate([
            'nav_label' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_body' => ['required', 'string', 'max:1200'],
            'body' => ['required', 'string', 'max:12000'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'cta_link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_published' => ['nullable', 'boolean'],
            'slug' => ['required', Rule::in([
                SitePage::SLUG_HOME,
                SitePage::SLUG_ABOUT,
                SitePage::SLUG_GAMES,
                SitePage::SLUG_MEMBERSHIP,
                SitePage::SLUG_CONTACT,
            ])],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $page->update($validated);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', "Page '{$page->title}' updated.");
    }
}
