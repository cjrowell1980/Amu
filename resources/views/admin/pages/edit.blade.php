@extends('layouts.admin')

@section('title', 'Edit Page')

@section('content')
    <h1>Edit Page</h1>

    @if ($errors->any())
        <div class="alert" style="background: #fee2e2; color: #991b1b;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}">
            @csrf
            @method('PUT')

            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 1rem;">
                <div>
                    <label for="slug" style="display:block; font-weight:600; margin-bottom:0.4rem;">Page Key</label>
                    <input id="slug" name="slug" value="{{ old('slug', $page->slug) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
                </div>
                <div>
                    <label for="sort_order" style="display:block; font-weight:600; margin-bottom:0.4rem;">Navigation Order</label>
                    <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
                </div>
            </div>

            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 1rem;">
                <div>
                    <label for="nav_label" style="display:block; font-weight:600; margin-bottom:0.4rem;">Navigation Label</label>
                    <input id="nav_label" name="nav_label" value="{{ old('nav_label', $page->nav_label) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
                </div>
                <div>
                    <label for="title" style="display:block; font-weight:600; margin-bottom:0.4rem;">Browser Title</label>
                    <input id="title" name="title" value="{{ old('title', $page->title) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="hero_title" style="display:block; font-weight:600; margin-bottom:0.4rem;">Hero Title</label>
                <input id="hero_title" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="hero_body" style="display:block; font-weight:600; margin-bottom:0.4rem;">Hero Summary</label>
                <textarea id="hero_body" name="hero_body" rows="4" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">{{ old('hero_body', $page->hero_body) }}</textarea>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="body" style="display:block; font-weight:600; margin-bottom:0.4rem;">Page Content</label>
                <textarea id="body" name="body" rows="12" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">{{ old('body', $page->body) }}</textarea>
            </div>

            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 1rem;">
                <div>
                    <label for="cta_label" style="display:block; font-weight:600; margin-bottom:0.4rem;">CTA Label</label>
                    <input id="cta_label" name="cta_label" value="{{ old('cta_label', $page->cta_label) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
                </div>
                <div>
                    <label for="cta_link" style="display:block; font-weight:600; margin-bottom:0.4rem;">CTA Link</label>
                    <input id="cta_link" name="cta_link" value="{{ old('cta_link', $page->cta_link) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="meta_description" style="display:block; font-weight:600; margin-bottom:0.4rem;">Meta Description</label>
                <input id="meta_description" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" style="width:100%; padding:0.7rem; border:1px solid #d7d7d7; border-radius:6px;">
            </div>

            <label style="display:flex; align-items:center; gap:0.6rem; margin-bottom: 1.25rem;">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
                <span>Show this page in the public navigation</span>
            </label>

            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Save Page</button>
                <a class="btn" href="{{ route('admin.pages.index') }}" style="border:1px solid #ddd;">Back</a>
            </div>
        </form>
    </div>
@endsection
