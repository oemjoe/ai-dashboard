<?php

namespace App\Services\Dashboard;

use App\Models\AnalysisSnapshot;
use App\Models\AuditLog;
use App\Models\DashboardModule;
use App\Models\DataConnection;
use App\Models\User;
use App\Services\AI\AiProviderManager;
use App\Services\Connections\ConnectionProbeService;
use Illuminate\Support\Collection;
use Throwable;

class DashboardDataService
{
    public function __construct(
        private readonly ConnectionProbeService $connectionProbeService,
        private readonly AiProviderManager $aiProviderManager,
        private readonly LiveModuleAnalysisService $liveModuleAnalysisService,
    ) {
    }

    public function overview(?User $user = null): array
    {
        $modules = DashboardModule::query()->orderBy('display_order')->get();
        $connections = DataConnection::query()->orderBy('name')->get();
        $providers = $this->aiProviderManager->overview();
        $recentAudits = AuditLog::query()->latest()->limit(10)->get();

        return [
            'headline_metrics' => [
                ['label' => 'Active Modules', 'value' => $modules->where('is_enabled', true)->count()],
                ['label' => 'Configured Connections', 'value' => $connections->count()],
                ['label' => 'Healthy AI Providers', 'value' => collect($providers)->where('status', 'ok')->count()],
                ['label' => 'Pending Data Mapping', 'value' => $modules->where('mapping_status', 'data_mapping_incomplete')->count()],
            ],
            'module_groups' => $this->groupModules($modules),
            'connections' => $connections,
            'providers' => $providers,
            'recent_audits' => $recentAudits,
            'last_refresh' => now(),
            'current_user' => $user,
        ];
    }

    public function modulePayload(string $slug, ?User $user = null, array $filters = []): array
    {
        $module = DashboardModule::query()->where('slug', $slug)->firstOrFail();
        $snapshot = AnalysisSnapshot::query()->where('module_slug', $slug)->latest('generated_at')->first();
        $connections = DataConnection::query()->get()->filter(
            fn (DataConnection $connection) => in_array($slug, $connection->module_slugs ?? [], true)
        )->values();
        $livePayload = null;
        $liveError = null;

        if ($this->liveModuleAnalysisService->supports($slug)) {
            try {
                $livePayload = $this->liveModuleAnalysisService->analyze($slug, $filters);
            } catch (Throwable $exception) {
                report($exception);
                $liveError = 'Analisa live sementara gagal dijalankan. Halaman menampilkan baseline terakhir yang tersedia sambil koneksi/kueri diverifikasi ulang.';
            }
        }

        $kpis = $livePayload['kpis'] ?? $snapshot?->metrics ?? [
            ['label' => 'Configured Data Sources', 'value' => $connections->count()],
            ['label' => 'Validated Read-only', 'value' => $connections->where('is_read_only_validated', true)->count()],
            ['label' => 'Company Scope', 'value' => $user?->preferred_company_code ?: 'Not selected'],
            ['label' => 'Last Snapshot', 'value' => $snapshot?->generated_at?->diffForHumans() ?? 'Belum ada'],
        ];

        $findings = $livePayload['findings'] ?? $snapshot?->findings ?? [
            [
                'title' => 'Data Mapping Incomplete',
                'severity' => 'warning',
                'description' => 'Struktur query final untuk modul ini belum diverifikasi terhadap database sumber.',
            ],
            [
                'title' => 'Read-only Guard',
                'severity' => 'info',
                'description' => 'Semua koneksi tetap diarahkan ke mode analisis non-destruktif.',
            ],
        ];

        if ($liveError !== null) {
            array_unshift($findings, [
                'title' => 'Live analysis fallback',
                'severity' => 'warning',
                'description' => $liveError,
            ]);
        }

        return [
            'module' => $module,
            'filters' => $filters,
            'mapping_status' => $livePayload['mapping_status'] ?? $snapshot?->mapping_status ?? $module->mapping_status,
            'kpis' => $kpis,
            'findings' => $findings,
            'connections' => $connections,
            'ai_summary' => $this->aiProviderManager->summarize($module->name, [
                'mapping_status' => $livePayload['mapping_status'] ?? $snapshot?->mapping_status ?? $module->mapping_status,
                'kpis' => $kpis,
            ]),
            'data_as_of' => $livePayload['data_as_of'] ?? $snapshot?->data_as_of,
            'generated_at' => $livePayload['generated_at'] ?? $snapshot?->generated_at,
            'data_preview' => $livePayload['data_preview'] ?? null,
        ];
    }

    public function adminHealth(): array
    {
        $connections = DataConnection::query()->get()->map(function (DataConnection $connection) {
            return [
                'model' => $connection,
                'status' => [
                    'status' => $connection->last_failure_message ? 'warning' : 'unknown',
                    'message' => $connection->last_failure_message ?: 'Belum diuji pada sesi ini.',
                    'read_only_valid' => $connection->is_read_only_validated,
                    'response_time_ms' => $connection->response_time_ms,
                ],
            ];
        });

        return [
            'connections' => $connections,
            'providers' => $this->aiProviderManager->overview(),
            'recent_audits' => AuditLog::query()->latest()->limit(20)->get(),
        ];
    }

    public function testConnection(DataConnection $connection): array
    {
        return $this->connectionProbeService->probe($connection);
    }

    private function groupModules(Collection $modules): Collection
    {
        return $modules
            ->groupBy('navigation_group')
            ->map(fn (Collection $items, string $group) => [
                'group' => $group,
                'items' => $items->values(),
            ])
            ->values();
    }
}
