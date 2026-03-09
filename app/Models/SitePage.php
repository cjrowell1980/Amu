<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasFactory;

    public const SLUG_HOME = 'home';
    public const SLUG_ABOUT = 'about';
    public const SLUG_GAMES = 'games';
    public const SLUG_MEMBERSHIP = 'membership';
    public const SLUG_CONTACT = 'contact';

    protected $fillable = [
        'slug',
        'nav_label',
        'title',
        'hero_title',
        'hero_body',
        'body',
        'meta_description',
        'cta_label',
        'cta_link',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getPathAttribute(): string
    {
        return match ($this->slug) {
            self::SLUG_HOME => route('home'),
            self::SLUG_ABOUT => route('pages.show', 'about-us'),
            self::SLUG_GAMES => route('pages.show', 'games'),
            self::SLUG_MEMBERSHIP => route('pages.show', 'membership'),
            self::SLUG_CONTACT => route('pages.show', 'contact-us'),
            default => route('home'),
        };
    }

    public function scopeNavigation($query)
    {
        return $query
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
