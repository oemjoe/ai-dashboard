<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">{{ $article->section }}</div>
            <h2 class="h3 fw-bold mb-1">{{ $article->title }}</h2>
            <div class="text-soft">{{ $article->markdown_path }}</div>
        </div>
    </x-slot>

    <div class="metric-card p-4">
        <article>
            {!! $html !!}
        </article>
    </div>
</x-app-layout>
