<?php

namespace App\Services\Documentation;

use App\Models\HelpArticle;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationService
{
    public function index()
    {
        $persisted = HelpArticle::query()->orderBy('sort_order')->get();

        if ($persisted->isNotEmpty()) {
            return $persisted;
        }

        return collect(config('ai_dashboard.modules'))
            ->map(fn (array $module) => HelpArticle::make([
                'slug' => $module['guide_slug'],
                'title' => $module['name'],
                'section' => 'Module Guide',
                'markdown_path' => "docs/user-guide/{$module['guide_slug']}.md",
            ]));
    }

    public function article(string $slug): array
    {
        $article = HelpArticle::query()->where('slug', $slug)->first();

        if (! $article) {
            $module = collect(config('ai_dashboard.modules'))->firstWhere('guide_slug', $slug);
            abort_unless($module, 404);

            $article = HelpArticle::make([
                'slug' => $slug,
                'title' => $module['name'],
                'section' => $module['navigation_group'],
                'markdown_path' => "docs/user-guide/{$slug}.md",
            ]);
        }

        $path = base_path($article->markdown_path);
        $markdown = File::exists($path) ? File::get($path) : "# {$article->title}\n\nDokumentasi belum tersedia.";

        return [
            'article' => $article,
            'markdown' => $markdown,
            'html' => Str::markdown($markdown),
        ];
    }
}
