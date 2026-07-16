<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Help Center</div>
            <h2 class="h3 fw-bold mb-1">Manual Book</h2>
            <div class="text-soft">Panduan operasional dan teknis untuk setiap modul.</div>
        </div>
    </x-slot>

    <div class="row g-3">
        @foreach ($articles as $article)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card p-3 h-100">
                    <div class="text-soft small text-uppercase">{{ $article->section }}</div>
                    <div class="h5 fw-semibold">{{ $article->title }}</div>
                    <div class="text-soft small mb-3">{{ $article->summary ?: 'Klik untuk membuka dokumentasi.' }}</div>
                    <a href="{{ route('help.show', $article->slug) }}" class="btn btn-primary btn-sm">Buka Panduan</a>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
