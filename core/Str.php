<?php

/**
 * Small string helpers shared across modules. `slugify()` mirrors
 * assets/js/dashboard.js's slugify() so a name typed in the dashboard
 * produces the same slug whether generated client-side (mock data) or
 * server-side (real API) — kept here instead of duplicated per module
 * (Categories and Posts both need it).
 */
class Str
{
    public static function slugify(string $text): string
    {
        $slug = mb_strtolower(trim($text));
        $slug = preg_replace('/[^\w\s-]/u', '', $slug) ?? '';
        $slug = preg_replace('/[\s_]+/u', '-', $slug) ?? '';
        $slug = preg_replace('/-+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}
