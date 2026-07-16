<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use App\Services\Security\MaskingService;

class AiProviderManager
{
    public function __construct(private readonly MaskingService $maskingService)
    {
    }

    public function overview(): array
    {
        return AiProvider::query()
            ->orderBy('priority')
            ->get()
            ->map(fn (AiProvider $provider) => $this->health($provider))
            ->all();
    }

    public function health(AiProvider $provider): array
    {
        return $this->providerFor($provider)->health($provider);
    }

    public function summarize(string $moduleName, array $payload): string
    {
        $headline = match ($payload['mapping_status'] ?? null) {
            'data_mapping_incomplete' => 'Data mapping untuk modul ini belum tervalidasi penuh.',
            'live_analysis_ready' => 'Analisa live modul ini sudah aktif dari sumber production read-only.',
            default => 'Analisis dasar tersedia dari snapshot lokal.',
        };

        $topMetric = collect($payload['kpis'] ?? [])->first();
        $metricText = $topMetric
            ? "{$topMetric['label']}: {$topMetric['value']}"
            : 'Belum ada KPI yang siap ditampilkan.';

        return "{$moduleName}: {$headline} {$metricText} Gunakan hasil ini sebagai baseline operasional sambil aturan analisa lanjutan terus ditingkatkan.";
    }

    private function providerFor(AiProvider $provider): AiProviderInterface
    {
        return match ($provider->driver) {
            'groq' => new GroqProvider($this->maskingService),
            'gemini' => new GeminiProvider($this->maskingService),
            default => new OllamaProvider($this->maskingService),
        };
    }
}
