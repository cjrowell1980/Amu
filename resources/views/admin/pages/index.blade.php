@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
    <h1>Website Pages</h1>

    <div class="card">
        <p style="color: #666; margin-bottom: 1rem;">
            Manage the public navigation and page copy for Home, About Us, Games, Membership, and Contact Us.
        </p>

        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Navigation</th>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr>
                        <td>{{ $page->sort_order }}</td>
                        <td>{{ $page->nav_label }}</td>
                        <td>{{ $page->title }}</td>
                        <td><code>{{ $page->slug }}</code></td>
                        <td>
                            <span class="badge {{ $page->is_published ? 'badge-green' : 'badge-gray' }}">
                                {{ $page->is_published ? 'Published' : 'Hidden' }}
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.pages.edit', $page) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
