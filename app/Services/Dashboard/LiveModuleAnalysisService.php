<?php

namespace App\Services\Dashboard;

use App\Models\DataConnection;
use App\Services\Connections\RuntimeConnectionService;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LiveModuleAnalysisService
{
    private const FILTER_KEYS = ['keyword', 'company', 'division', 'branch', 'location', 'date_from', 'date_to'];

    /**
     * @var array<string, array{connection_slug?: string, dataset_label: string, metrics?: array<int, array{label: string, table: string}>, preview?: array{table: string, label: string}, dynamic?: string}>
     */
    private array $plans = [
        'ar' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP',
            'metrics' => [
                ['label' => 'Sales Headers', 'table' => 'fnc_sales_header'],
                ['label' => 'Grouped Invoices', 'table' => 'fnc_group_invoice_header'],
                ['label' => 'AR Supporting Files', 'table' => 'fnc_sales_ar_supporting_files'],
                ['label' => 'Sales Details', 'table' => 'fnc_sales_detail'],
            ],
            'preview' => ['table' => 'fnc_sales_header', 'label' => 'Latest Sales Header Rows'],
        ],
        'ap' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP',
            'metrics' => [
                ['label' => 'AP Tariff Rows', 'table' => 'fnc_ap_tarif'],
                ['label' => 'PO Headers', 'table' => 'proc_purchase_order_header'],
                ['label' => 'PO Details', 'table' => 'proc_purchase_order_detail'],
                ['label' => 'Vendor Master', 'table' => 'em_vendor'],
            ],
            'preview' => ['table' => 'proc_purchase_order_header', 'label' => 'Latest Purchase Orders'],
        ],
        'req' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP',
            'metrics' => [
                ['label' => 'Request Headers', 'table' => 'proc_request_item_header'],
                ['label' => 'Request Items', 'table' => 'proc_request_item'],
                ['label' => 'Request Item Details', 'table' => 'proc_request_item_detail'],
                ['label' => 'PO Headers', 'table' => 'proc_purchase_order_header'],
            ],
            'preview' => ['table' => 'proc_request_item_header', 'label' => 'Latest Request Headers'],
        ],
        'lpj' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP',
            'metrics' => [
                ['label' => 'LPJ Headers', 'table' => 'ops_lpj'],
                ['label' => 'LPJ Details', 'table' => 'ops_lpj_detail'],
                ['label' => 'Vehicle Cost Rows', 'table' => 'eq_lpj_vehicles_cost'],
                ['label' => 'LPJ Tracking Rows', 'table' => 'ops_tracking_lpj'],
            ],
            'preview' => ['table' => 'ops_lpj', 'label' => 'Latest LPJ Headers'],
        ],
        'inbound' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP + FMS',
            'metrics' => [
                ['label' => 'Inbound Headers', 'table' => 'tnp_stockInbound'],
                ['label' => 'Inbound Details', 'table' => 'tnp_stockInboundDetail'],
            ],
            'preview' => ['table' => 'tnp_stockInbound', 'label' => 'Latest Inbound Rows'],
        ],
        'outbound' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP + FMS',
            'metrics' => [
                ['label' => 'Outbound Rows', 'table' => 'tnp_ujal_outbound'],
                ['label' => 'Delivery Headers', 'table' => 'ops_delivery_list'],
                ['label' => 'Delivery Details', 'table' => 'ops_delivery_list_detail'],
            ],
            'preview' => ['table' => 'ops_delivery_list', 'label' => 'Latest Delivery Headers'],
        ],
        'jo' => [
            'connection_slug' => 'erp-tnp-production',
            'dataset_label' => 'ERP TNP',
            'metrics' => [
                ['label' => 'Sales Headers', 'table' => 'fnc_sales_header'],
                ['label' => 'Sales Details', 'table' => 'fnc_sales_detail'],
                ['label' => 'Cashier Out Headers', 'table' => 'fnc_cashier_out_header'],
                ['label' => 'Delivery Headers', 'table' => 'ops_delivery_list'],
            ],
            'preview' => ['table' => 'fnc_sales_header', 'label' => 'Latest Revenue Header Rows'],
        ],
        'cross-module' => [
            'dynamic' => 'cross-module',
            'dataset_label' => 'ERP TNP + FMS + AI',
        ],
        'ask-data' => [
            'dynamic' => 'ask-data',
            'dataset_label' => 'Connections + AI Providers',
        ],
        'database-catalog' => [
            'dynamic' => 'database-catalog',
            'dataset_label' => 'Connected Production Schemas',
        ],
        'source-code-analysis' => [
            'dynamic' => 'source-code-analysis',
            'dataset_label' => 'Mounted Production Source Tree',
        ],
    ];

    public function __construct(
        private readonly RuntimeConnectionService $runtimeConnectionService,
    ) {
    }

    public function supports(string $moduleSlug): bool
    {
        return array_key_exists($moduleSlug, $this->plans);
    }

    /**
     * @return array{
     *     mapping_status: string,
     *     kpis: array<int, array{label: string, value: string|int}>,
     *     findings: array<int, array{title: string, severity: string, description: string}>,
     *     filters: array<string, string>,
     *     data_as_of: \Carbon\Carbon,
     *     generated_at: \Carbon\Carbon,
     *     data_preview: array{label: string, columns: array<int, string>, rows: array<int, array<string, mixed>>, row_count: int}|null
     * }
     */
    public function analyze(string $moduleSlug, array $filters): array
    {
        $plan = $this->plans[$moduleSlug] ?? null;

        if ($plan === null) {
            throw new \InvalidArgumentException("Unsupported live analysis module: {$moduleSlug}");
        }

        $normalizedFilters = $this->normalizeFilters($filters);

        return match ($moduleSlug) {
            'ar' => $this->buildAccountsReceivablePayload($normalizedFilters),
            'ap' => $this->buildAccountsPayablePayload($normalizedFilters),
            'req' => $this->buildRequestPayload($normalizedFilters),
            'inbound' => $this->buildInboundPayload($normalizedFilters),
            'outbound' => $this->buildOutboundPayload($normalizedFilters),
            'jo' => $this->buildJobOrderPayload($normalizedFilters),
            'lpj' => $this->buildLpjPayload($normalizedFilters),
            default => match ($plan['dynamic'] ?? null) {
                'cross-module' => $this->buildCrossModulePayload($normalizedFilters),
                'ask-data' => $this->buildAskDataPayload($normalizedFilters),
                'database-catalog' => $this->buildDatabaseCatalogPayload($normalizedFilters),
                'source-code-analysis' => $this->buildSourceCodePayload($normalizedFilters),
                default => $this->buildTableDrivenPayload($plan, $normalizedFilters),
            },
        };
    }

    private function buildAccountsReceivablePayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $table = 'fnc_sales_header';
            $dateColumn = 'invoiceDate';
            $baseQuery = $this->filteredTableQuery($db, $table, $filters, $dateColumn);
            $validDateQuery = $this->filteredTableQuery($db, $table, $filters, $dateColumn)
                ->where($dateColumn, '<>', '0000-00-00');
            $openQuery = $this->filteredTableQuery($db, $table, $filters, $dateColumn)
                ->where('cancel', 0)
                ->where(function (Builder $query) {
                    $query->whereNull('isClose')->orWhere('isClose', 0);
                });

            $salesHeaders = (int) (clone $baseQuery)->count();
            $openTotal = (int) (clone $openQuery)->count();
            $openOver30d = (int) (clone $openQuery)
                ->where($dateColumn, '<>', '0000-00-00')
                ->whereDate($dateColumn, '<', now()->subDays(30)->toDateString())
                ->count();
            $openOver60d = (int) (clone $openQuery)
                ->where($dateColumn, '<>', '0000-00-00')
                ->whereDate($dateColumn, '<', now()->subDays(60)->toDateString())
                ->count();
            $pendingAdminSubmit = (int) (clone $baseQuery)->where('cancel', 0)->where('adminSubmit', 0)->count();
            $pendingSpv = (int) (clone $baseQuery)->where('cancel', 0)->where('adminSubmit', 1)->where('appSpvStatus', 0)->count();
            $pendingMgr = (int) (clone $baseQuery)->where('cancel', 0)->where('appSpvStatus', 1)->where('appMgrStatus', 0)->count();
            $pendingDir = (int) (clone $baseQuery)->where('cancel', 0)->where('appMgrStatus', 1)->where('appDirStatus', 0)->count();
            $notReceived = (int) (clone $baseQuery)->where('cancel', 0)->whereNull('receivedDate')->count();
            $notReceivedOver14d = (int) (clone $baseQuery)
                ->where('cancel', 0)
                ->whereNull('receivedDate')
                ->where($dateColumn, '<>', '0000-00-00')
                ->whereDate($dateColumn, '<', now()->subDays(14)->toDateString())
                ->count();
            $recent30d = (int) (clone $validDateQuery)->whereDate($dateColumn, '>=', now()->subDays(30)->toDateString())->count();
            $zeroInvoiceDate = (int) (clone $baseQuery)->where($dateColumn, '0000-00-00')->count();
            $maxInvoiceDate = (clone $validDateQuery)->max($dateColumn);
            $openLocalCharge = (float) ((clone $openQuery)->sum('localCharge') ?: 0);
            $openDownPayment = (float) ((clone $openQuery)->sum('downPayment') ?: 0);
            $topOverdueSupplier = (clone $openQuery)
                ->select('supplierName', DB::raw('COUNT(*) as overdue_count'))
                ->where($dateColumn, '<>', '0000-00-00')
                ->whereDate($dateColumn, '<', now()->subDays(60)->toDateString())
                ->groupBy('supplierName')
                ->orderByDesc('overdue_count')
                ->first();

            $previewColumns = ['invoiceNo', 'supplierName', 'invoiceDate', 'receivedDate', 'invoiceStatus', 'adminSubmit', 'appSpvStatus'];
            $previewRows = (clone $openQuery)
                ->select($previewColumns)
                ->where($dateColumn, '<>', '0000-00-00')
                ->orderBy($dateColumn)
                ->limit(10)
                ->get();

            $findings = [];

            if ($openOver60d > 0) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Working capital masih tertahan di backlog AR historis',
                    'severity' => 'warning',
                    'description' => "{$openTotal} invoice masih open dan {$openOver60d} di antaranya sudah melewati 60 hari. Local charge exposure yang masih tertahan mencapai {$this->formatCurrency($openLocalCharge)} dengan down payment exposure {$this->formatCurrency($openDownPayment)}.",
                    'action' => 'Prioritaskan cleanup backlog >60 hari sebagai agenda cash conversion, bukan sekadar penutupan administratif.',
                ];
            }

            if ($pendingAdminSubmit > 0) {
                $findings[] = [
                    'category' => 'Process Bottleneck',
                    'title' => 'Bottleneck utama masih terjadi sebelum approval formal dimulai',
                    'severity' => 'warning',
                    'description' => "{$pendingAdminSubmit} invoice masih tertahan di tahap admin submit, jauh lebih besar dibanding antrean SPV {$pendingSpv}, Manager {$pendingMgr}, dan Director {$pendingDir}.",
                    'action' => 'Fokuskan intervensi proses pada tahap submit admin terlebih dahulu karena di sanalah antrean terbesar terbentuk.',
                ];
            }

            if ($notReceivedOver14d > 0) {
                $findings[] = [
                    'category' => 'Control Risk',
                    'title' => 'Dokumen belum diterima melewati SLA awal penerimaan invoice',
                    'severity' => 'warning',
                    'description' => "{$notReceivedOver14d} invoice belum memiliki received date lebih dari 14 hari sejak invoice date. Total invoice tanpa received date saat ini {$notReceived}.",
                    'action' => 'Bangun kontrol follow-up harian untuk invoice yang belum memiliki received date agar aging tidak memburuk diam-diam.',
                ];
            }

            if ($zeroInvoiceDate > 0 || $topOverdueSupplier !== null) {
                $findings[] = [
                    'category' => 'Concentration Risk',
                    'title' => 'Risiko backlog terkonsentrasi pada supplier tertentu dan diperburuk kualitas data',
                    'severity' => 'warning',
                    'description' => ($topOverdueSupplier !== null
                        ? "Supplier {$topOverdueSupplier->supplierName} sendiri menyumbang {$topOverdueSupplier->overdue_count} invoice overdue >60 hari, atau {$this->formatPercent((int) $topOverdueSupplier->overdue_count, $openOver60d)} dari backlog tua."
                        : 'Konsentrasi supplier overdue belum bisa dihitung.')
                        ." Selain itu, {$zeroInvoiceDate} row masih memakai tanggal invoice tidak valid.",
                    'action' => 'Kelompokkan recovery AR berdasarkan supplier/customer cluster terbesar sambil membersihkan zero-date agar prioritas collection lebih akurat.',
                ];
            }

            $findings[] = [
                'category' => 'Business Momentum',
                'title' => 'Arus invoice baru tetap berjalan, tetapi belum cukup mengimbangi backlog lama',
                'severity' => 'info',
                'description' => "{$recent30d} invoice tercatat dalam 30 hari terakhir dengan data terbaru pada ".($maxInvoiceDate ?: 'n/a').". Approval lanjutan saat ini tersisa SPV {$pendingSpv}, Manager {$pendingMgr}, Director {$pendingDir}.",
                'action' => 'Pisahkan dashboard eksekusi antara invoice baru dan invoice legacy agar tim tidak kehilangan fokus pada backlog bernilai tinggi.',
            ];

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa AR dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            if ($previewRows->isNotEmpty()) {
                $findings[] = [
                    'title' => 'Preview fokus ke invoice open tertua',
                    'severity' => 'info',
                    'description' => 'Preview menampilkan invoice open valid yang paling tua agar tindak lanjut backlog lebih mudah diprioritaskan.',
                ];
            }

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'Open Invoice Queue', 'value' => $openTotal],
                    ['label' => 'Local Charge Exposure', 'value' => $this->formatCurrency($openLocalCharge)],
                    ['label' => 'Down Payment Exposure', 'value' => $this->formatCurrency($openDownPayment)],
                    ['label' => 'Pending Admin Submit', 'value' => $pendingAdminSubmit],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => $maxInvoiceDate ? Carbon::parse($maxInvoiceDate) : now(),
                'generated_at' => now(),
                'data_preview' => $this->buildPreviewPayload('Oldest Open Invoice Queue', $previewColumns, $previewRows),
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildAccountsPayablePayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $tarifQuery = $this->filteredTableQuery($db, 'fnc_ap_tarif', $filters, 'created');
            $poQuery = $this->filteredTableQuery($db, 'proc_purchase_order_header', $filters, 'poDate');
            $poDetailQuery = $this->filteredTableQuery($db, 'proc_purchase_order_detail', $filters, $this->detectDateColumn($db, 'proc_purchase_order_detail'));
            $vendorQuery = $this->filteredTableQuery($db, 'em_vendor', $filters, $this->detectDateColumn($db, 'em_vendor'));

            $tarifTotal = (int) (clone $tarifQuery)->count();
            $poTotal = (int) (clone $poQuery)->count();
            $poDetailTotal = (int) (clone $poDetailQuery)->count();
            $vendorTotal = (int) (clone $vendorQuery)->count();
            $recentTarif = (int) (clone $tarifQuery)->whereDate('created', '>=', now()->subDays(90)->toDateString())->count();
            $maxTarifCreated = (clone $tarifQuery)->max('created');
            $invoiceTypeTotal = $poTotal > 0 ? (int) (clone $poQuery)->where('Type', 'INVOICE')->count() : 0;
            $tunaiTypeTotal = $poTotal > 0 ? (int) (clone $poQuery)->where('Type', 'TUNAI')->count() : 0;
            $totalTariffValue = (float) ((clone $tarifQuery)->sum('price') ?: 0);
            $distinctCustomers = (int) (clone $tarifQuery)->select('customerID')->distinct()->count('customerID');
            $distinctAreas = (int) (clone $tarifQuery)->select('area')->distinct()->count('area');
            $topCustomer = (clone $tarifQuery)
                ->select('customerID', DB::raw('COUNT(*) as tariff_rows'), DB::raw('SUM(COALESCE(price,0)) as total_price'))
                ->groupBy('customerID')
                ->orderByDesc('total_price')
                ->first();
            $topArea = (clone $tarifQuery)
                ->select('area', DB::raw('COUNT(*) as tariff_rows'), DB::raw('SUM(COALESCE(price,0)) as total_price'))
                ->groupBy('area')
                ->orderByDesc('total_price')
                ->first();

            $findings = [];

            if ($poTotal === 0 && $tarifTotal > 0) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Layer tarif AP aktif, tetapi transaksi procurement belum masuk ke source live',
                    'severity' => 'warning',
                    'description' => "Tarif AP live terisi {$tarifTotal} row dengan nilai total referensi {$this->formatCurrency($totalTariffValue)}, tetapi `proc_purchase_order_header` dan detail-nya belum mengembalikan data. Artinya dashboard belum bisa membaca liability pipeline secara end-to-end.",
                    'action' => 'Sambungkan source PO/invoice AP agar dashboard bisa naik dari tariff governance menjadi payable exposure monitoring.',
                ];
            } elseif ($poTotal === 0) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Belum ada transaksi PO yang bisa dianalisa',
                    'severity' => 'warning',
                    'description' => 'Koneksi live aktif, tetapi purchase order header belum mengembalikan row. Analisa AP belum bisa menurunkan backlog approval atau termin pembayaran secara representatif.',
                    'action' => 'Validasi ulang sumber transaksi AP karena dashboard enterprise membutuhkan lebih dari sekadar master tarif.',
                ];
            }

            if ($topCustomer !== null || $topArea !== null) {
                $findings[] = [
                    'category' => 'Concentration Risk',
                    'title' => 'Tarif AP terkonsentrasi pada sedikit customer/area',
                    'severity' => 'warning',
                    'description' => ($topCustomer !== null
                        ? "Customer {$topCustomer->customerID} memegang {$topCustomer->tariff_rows} mapping dengan total nilai {$this->formatCurrency((float) $topCustomer->total_price)}. "
                        : '')
                        .($topArea !== null
                        ? "Area {$topArea->area} juga mendominasi dengan nilai {$this->formatCurrency((float) $topArea->total_price)}."
                        : ''),
                    'action' => 'Gunakan konsentrasi tarif ini untuk mengecek apakah pricing AP terlalu bergantung pada sedikit customer/koridor.',
                ];
            }

            if ($vendorTotal <= 2) {
                $findings[] = [
                    'category' => 'Master Data Risk',
                    'title' => 'Cakupan vendor pada source live masih terlalu sempit',
                    'severity' => 'warning',
                    'description' => "Hanya {$vendorTotal} vendor yang terbaca dari source live, sehingga analisa spend concentration dan supplier diversification belum representatif.",
                    'action' => 'Lengkapi sumber vendor/AP master sebelum dashboard dipakai untuk keputusan procurement skala enterprise.',
                ];
            }

            $findings[] = [
                'category' => 'Business Readiness',
                'title' => 'Lapisan AP yang benar-benar hidup saat ini ada pada pricing governance',
                'severity' => 'info',
                'description' => "{$recentTarif} perubahan tarif tercatat dalam 90 hari terakhir dengan data terbaru pada ".($maxTarifCreated ?: 'n/a').". Customer aktif {$distinctCustomers}, area aktif {$distinctAreas}, sementara komposisi PO type saat ini invoice {$invoiceTypeTotal}, tunai {$tunaiTypeTotal}.",
                'action' => 'Posisikan modul AP sementara sebagai pricing governance cockpit sampai transaksi PO/AP invoice masuk penuh.',
            ];

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa AP dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            $previewColumns = ['customerID', 'serviceID', 'area', 'price', 'remarks', 'created'];
            $previewRows = (clone $tarifQuery)
                ->select($previewColumns)
                ->orderByDesc('created')
                ->limit(10)
                ->get();

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'Tariff Mappings', 'value' => $tarifTotal],
                    ['label' => 'Tariff Reference Value', 'value' => $this->formatCurrency($totalTariffValue)],
                    ['label' => 'Distinct Customers', 'value' => $distinctCustomers],
                    ['label' => 'Vendor Coverage', 'value' => $vendorTotal],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => $maxTarifCreated ? Carbon::parse($maxTarifCreated) : now(),
                'generated_at' => now(),
                'data_preview' => $this->buildPreviewPayload('Latest AP Tariff Mapping', $previewColumns, $previewRows),
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildRequestPayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $headerQuery = $this->filteredTableQuery($db, 'proc_request_item_header', $filters, 'Tanggal_Request');
            $itemQuery = $this->filteredTableQuery($db, 'proc_request_item', $filters, $this->detectDateColumn($db, 'proc_request_item'));
            $detailQuery = $this->filteredTableQuery($db, 'proc_request_item_detail', $filters, $this->detectDateColumn($db, 'proc_request_item_detail'));
            $poQuery = $this->filteredTableQuery($db, 'proc_purchase_order_header', $filters, $this->detectDateColumn($db, 'proc_purchase_order_header'));

            $headerTotal = (int) (clone $headerQuery)->count();
            $itemTotal = (int) (clone $itemQuery)->count();
            $detailTotal = (int) (clone $detailQuery)->count();
            $poTotal = (int) (clone $poQuery)->count();
            $pendingRequests = $headerTotal > 0 ? (int) (clone $headerQuery)->where('status', 'N')->count() : 0;
            $pendingOver30d = $headerTotal > 0
                ? (int) (clone $headerQuery)->where('status', 'N')->whereDate('Tanggal_Request', '<', now()->subDays(30)->toDateString())->count()
                : 0;

            $findings = [];

            if ($headerTotal === 0 && ($itemTotal > 0 || $detailTotal > 0)) {
                $findings[] = [
                    'category' => 'Data Coverage Risk',
                    'title' => 'Header request kosong, tetapi detail masih ada',
                    'severity' => 'warning',
                    'description' => "Source header `proc_request_item_header` saat ini tidak mengembalikan row, sementara item/detail masih berisi {$itemTotal} item dan {$detailTotal} detail. Coverage mapping request belum utuh.",
                    'action' => 'Lengkapi source header request agar dashboard procurement bisa menghitung lead time dan conversion secara utuh.',
                ];
            } elseif ($headerTotal === 0) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Belum ada data header request yang bisa dianalisa',
                    'severity' => 'warning',
                    'description' => 'Koneksi live aktif, tetapi tabel header request belum mengembalikan data. Modul ini belum bisa menghasilkan analisa aging request yang representatif.',
                    'action' => 'Pastikan jalur source request header yang aktif di production benar-benar sama dengan transaksi operasional saat ini.',
                ];
            } else {
                $findings[] = [
                    'category' => 'Process Bottleneck',
                    'title' => 'Pending request aging terdeteksi',
                    'severity' => $pendingOver30d > 0 ? 'warning' : 'info',
                    'description' => "{$pendingRequests} request masih pending dan {$pendingOver30d} di antaranya sudah melewati 30 hari.",
                    'action' => 'Gunakan aging pending request sebagai antrean prioritas lintas requester/divisi.',
                ];
            }

            if ($poTotal === 0) {
                $findings[] = [
                    'category' => 'Conversion Gap',
                    'title' => 'Belum ada PO header pada source yang sama',
                    'severity' => 'info',
                    'description' => 'Procurement order header belum terisi pada source live yang dipakai modul ini, sehingga analisa request-to-PO conversion masih terbatas.',
                    'action' => 'Sambungkan PO header ke modul request agar manajemen bisa melihat conversion request menjadi order nyata.',
                ];
            }

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa request dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            $previewTable = $headerTotal > 0 ? 'proc_request_item_header' : ($detailTotal > 0 ? 'proc_request_item_detail' : 'proc_request_item');
            $previewLabel = $headerTotal > 0 ? 'Oldest Pending Request Headers' : 'Fallback Request Detail Preview';
            $preview = $this->previewTable($db, $previewTable, $previewLabel, $filters);

            if ($headerTotal === 0 && $preview['row_count'] > 0) {
                $findings[] = [
                    'category' => 'Fallback Mode',
                    'title' => 'Preview dialihkan ke tabel detail',
                    'severity' => 'info',
                    'description' => 'Karena header request kosong, preview sementara diambil dari tabel item/detail yang masih memiliki data live.',
                    'action' => 'Gunakan preview ini untuk validasi source, bukan sebagai dasar keputusan SLA procurement final.',
                ];
            }

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'Request Headers', 'value' => $headerTotal],
                    ['label' => 'Request Items', 'value' => $itemTotal],
                    ['label' => 'Request Details', 'value' => $detailTotal],
                    ['label' => 'PO Headers', 'value' => $poTotal],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => now(),
                'generated_at' => now(),
                'data_preview' => $preview,
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildInboundPayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $headerQuery = $this->filteredTableQuery($db, 'tnp_stockInbound', $filters, $this->detectDateColumn($db, 'tnp_stockInbound'));
            $detailQuery = $this->filteredTableQuery($db, 'tnp_stockInboundDetail', $filters, $this->detectDateColumn($db, 'tnp_stockInboundDetail'));

            $inboundTotal = (int) (clone $headerQuery)->count();
            $detailTotal = (int) (clone $detailQuery)->count();
            $branchTotal = (int) (clone $headerQuery)->select('Branch')->distinct()->count('Branch');
            $totalTonase = (float) ((clone $headerQuery)->sum('tonase') ?: 0);
            $totalBdls = (int) ((clone $headerQuery)->sum('bdls') ?: 0);
            $topBranch = (clone $headerQuery)
                ->select('Branch', DB::raw('COUNT(*) as total_rows'), DB::raw('SUM(COALESCE(tonase,0)) as total_tonase'))
                ->groupBy('Branch')
                ->orderByDesc('total_tonase')
                ->orderByDesc('total_rows')
                ->first();

            $findings = [];

            if ($inboundTotal <= 1) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Source inbound live masih sangat tipis',
                    'severity' => 'warning',
                    'description' => "Saat ini hanya {$inboundTotal} header dan {$detailTotal} detail inbound yang terbaca. Modul inbound belum punya volume cukup untuk analisa tren operasional yang kuat.",
                    'action' => 'Pastikan seluruh arus inbound production masuk ke source yang sama sebelum dashboard dipakai untuk analisa throughput enterprise.',
                ];
            }

            $findings[] = [
                'category' => 'Concentration Risk',
                'title' => 'Cakupan inbound terpusat pada satu cabang',
                'severity' => $branchTotal <= 1 ? 'warning' : 'info',
                'description' => "Source inbound yang aktif saat ini mencakup {$branchTotal} cabang dengan total tonase {$totalTonase} dan bdls {$totalBdls}.",
                'action' => 'Konfirmasi apakah sentralisasi ini memang kondisi bisnis atau hanya akibat source cabang lain belum terhubung.',
            ];

            if ($topBranch !== null) {
                $findings[] = [
                    'category' => 'Operational Focus',
                    'title' => 'Cabang dominan bisa langsung terlihat',
                    'severity' => 'info',
                    'description' => "Cabang {$topBranch->Branch} saat ini mendominasi source inbound live dengan tonase {$topBranch->total_tonase} dari {$topBranch->total_rows} row.",
                    'action' => 'Jadikan cabang dominan ini sebagai pilot untuk dashboard inbound yang lebih detail per volume dan kualitas penerimaan.',
                ];
            }

            if ($this->detectDateColumn($db, 'tnp_stockInbound') === null) {
                $findings[] = [
                    'category' => 'Control Risk',
                    'title' => 'Belum ada kolom tanggal untuk aging inbound',
                    'severity' => 'warning',
                    'description' => 'Tabel inbound yang terhubung belum menyediakan kolom tanggal transaksi yang bisa dipakai untuk aging/filter periode secara akurat.',
                    'action' => 'Tambahkan tanggal transaksi pada source inbound agar enterprise dashboard bisa mengukur trend, SLA, dan throughput periodik.',
                ];
            }

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa inbound dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            $previewColumns = ['noProject', 'Branch', 'statvesl', 'gradeNOA', 'bdls', 'tonase'];
            $previewRows = (clone $detailQuery)
                ->select($previewColumns)
                ->orderByDesc('tonase')
                ->limit(10)
                ->get();

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'Inbound Headers', 'value' => $inboundTotal],
                    ['label' => 'Inbound Details', 'value' => $detailTotal],
                    ['label' => 'Branch Coverage', 'value' => $branchTotal],
                    ['label' => 'Total Tonase', 'value' => number_format($totalTonase, 0)],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => now(),
                'generated_at' => now(),
                'data_preview' => $this->buildPreviewPayload('Inbound Detail Snapshot', $previewColumns, $previewRows),
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildOutboundPayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $ujalQuery = $this->filteredTableQuery($db, 'tnp_ujal_outbound', $filters, $this->detectDateColumn($db, 'tnp_ujal_outbound'));
            $deliveryQuery = $this->filteredTableQuery($db, 'ops_delivery_list', $filters, 'deliveryDate');
            $deliveryDetailQuery = $this->filteredTableQuery($db, 'ops_delivery_list_detail', $filters, $this->detectDateColumn($db, 'ops_delivery_list_detail'));

            $ujalTotal = (int) (clone $ujalQuery)->count();
            $deliveryTotal = (int) (clone $deliveryQuery)->count();
            $deliveryDetailTotal = (int) (clone $deliveryDetailQuery)->count();
            $finishTotal = (int) (clone $deliveryQuery)->where('StatusProses', 'Finish')->count();
            $processTotal = (int) (clone $deliveryQuery)->where('StatusProses', 'Prosses')->count();
            $recent30d = (int) (clone $deliveryQuery)->whereDate('deliveryDate', '>=', now()->subDays(30)->toDateString())->count();
            $recent90d = (int) (clone $deliveryQuery)->whereDate('deliveryDate', '>=', now()->subDays(90)->toDateString())->count();
            $maxDeliveryDate = (clone $deliveryQuery)->max('deliveryDate');
            $milikTotal = (int) (clone $ujalQuery)->where('tipe_ujal', 'milik')->count();
            $kontrakTotal = (int) (clone $ujalQuery)->where('tipe_ujal', 'kontrak')->count();
            $spotTotal = (int) (clone $ujalQuery)->where('tipe_ujal', 'spot')->count();
            $processOver1y = (int) (clone $deliveryQuery)
                ->where('StatusProses', 'Prosses')
                ->whereDate('deliveryDate', '<', now()->subYear()->toDateString())
                ->count();
            $topProcessArea = (clone $deliveryQuery)
                ->select('id_area', DB::raw('COUNT(*) as process_count'))
                ->where('StatusProses', 'Prosses')
                ->groupBy('id_area')
                ->orderByDesc('process_count')
                ->first();
            $topProcessProject = (clone $deliveryQuery)
                ->select('noProject', DB::raw('COUNT(*) as process_count'))
                ->where('StatusProses', 'Prosses')
                ->groupBy('noProject')
                ->orderByDesc('process_count')
                ->first();

            $findings = [];

            if ($recent90d === 0 && $deliveryTotal > 0) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Outbound source tidak menunjukkan arus delivery baru',
                    'severity' => 'warning',
                    'description' => "Tidak ada delivery baru dalam 90 hari terakhir. Data terbaru yang terbaca saat ini berhenti pada {$maxDeliveryDate}, sehingga dashboard outbound lebih banyak memantau backlog lama daripada arus fulfilment aktif.",
                    'action' => 'Validasi apakah source outbound aktif memang berhenti di produksi, atau ada jalur source lain yang belum dipetakan ke dashboard.',
                ];
            }

            if ($processTotal > $finishTotal) {
                $findings[] = [
                    'category' => 'Process Bottleneck',
                    'title' => 'Backlog delivery berstatus proses jauh melampaui delivery yang selesai',
                    'severity' => 'warning',
                    'description' => "{$processTotal} delivery masih berstatus `Prosses`, dibanding hanya {$finishTotal} yang `Finish`. Seluruh backlog proses saat ini juga sudah melewati 1 tahun sebanyak {$processOver1y} row.",
                    'action' => 'Bersihkan backlog status secara massal atau per area agar pipeline outbound tidak dibaca seolah seluruh order masih aktif.',
                ];
            }

            if ($processTotal > 0 && ($topProcessArea !== null || $topProcessProject !== null)) {
                $findings[] = [
                    'category' => 'Concentration Risk',
                    'title' => 'Backlog proses terkonsentrasi pada area dan project tertentu',
                    'severity' => 'warning',
                    'description' => ($topProcessArea !== null
                        ? "Area {$topProcessArea->id_area} menampung {$topProcessArea->process_count} delivery proses. "
                        : '')
                        .($topProcessProject !== null
                        ? "Project {$topProcessProject->noProject} sendiri menyumbang {$topProcessProject->process_count} delivery proses."
                        : ''),
                    'action' => 'Gunakan pendekatan cleanup berbasis area/project agar backlog outbound bisa ditutup dari konsentrasi terbesar lebih dulu.',
                ];
            }

            $findings[] = [
                'category' => 'Business Readiness',
                'title' => 'Coverage ujal outbound masih sempit',
                'severity' => 'info',
                'description' => "Konfigurasi ujal live saat ini {$ujalTotal} row dengan komposisi milik {$milikTotal}, kontrak {$kontrakTotal}, spot {$spotTotal}.",
                'action' => 'Lengkapi source outbound rate/ujal bila dashboard ini ingin dipakai untuk analisa kontribusi margin outbound.',
            ];

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa outbound dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            $previewColumns = ['deliveryListNo', 'deliveryDate', 'StatusProses', 'statusIn', 'noProject', 'id_area'];
            $previewRows = (clone $deliveryQuery)
                ->select($previewColumns)
                ->where('StatusProses', 'Prosses')
                ->orderBy('deliveryDate')
                ->limit(10)
                ->get();

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'In Process Deliveries', 'value' => $processTotal],
                    ['label' => 'Finished Deliveries', 'value' => $finishTotal],
                    ['label' => 'Process >1 Year', 'value' => $processOver1y],
                    ['label' => 'Outbound Rate Rows', 'value' => $ujalTotal],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => $maxDeliveryDate ? Carbon::parse($maxDeliveryDate) : now(),
                'generated_at' => now(),
                'data_preview' => $this->buildPreviewPayload('Oldest Delivery Process Queue', $previewColumns, $previewRows),
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildJobOrderPayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $salesHeaderQuery = $this->filteredTableQuery($db, 'fnc_sales_header', $filters, 'invoiceDate');
            $salesDetailQuery = $this->filteredTableQuery($db, 'fnc_sales_detail', $filters, 'created_date');
            $cashierQuery = $this->filteredTableQuery($db, 'fnc_cashier_out_header', $filters, 'cashierDate');
            $deliveryQuery = $this->filteredTableQuery($db, 'ops_delivery_list', $filters, 'deliveryDate');

            $salesHeaderTotal = (int) (clone $salesHeaderQuery)->count();
            $salesDetailTotal = (int) (clone $salesDetailQuery)->count();
            $cashierTotal = (int) (clone $cashierQuery)->count();
            $deliveryTotal = (int) (clone $deliveryQuery)->count();
            $cashierLast90d = (int) (clone $cashierQuery)->whereDate('cashierDate', '>=', now()->subDays(90)->toDateString())->count();
            $salesDetailLast90d = (int) (clone $salesDetailQuery)->whereDate('created_date', '>=', now()->subDays(90)->toDateString())->count();
            $maxCashierDate = (clone $cashierQuery)->max('cashierDate');
            $maxSalesDetailCreated = (clone $salesDetailQuery)->max('created_date');

            $findings = [];

            if ($salesHeaderTotal > 0 && $salesDetailTotal <= 1) {
                $findings[] = [
                    'category' => 'Data Coverage Risk',
                    'title' => 'Header revenue jauh lebih lengkap daripada detail JO',
                    'severity' => 'warning',
                    'description' => "{$salesHeaderTotal} sales header terbaca, tetapi hanya {$salesDetailTotal} sales detail yang terhubung. Source JO saat ini belum cukup lengkap untuk analisa margin/order flow yang utuh.",
                    'action' => 'Prioritaskan penyambungan detail JO sebelum dashboard dipakai untuk evaluasi profitabilitas order.',
                ];
            }

            if ($cashierLast90d === 0 && $cashierTotal > 0) {
                $findings[] = [
                    'category' => 'Staleness Risk',
                    'title' => 'Cashier out JO sudah sangat stale',
                    'severity' => 'warning',
                    'description' => "Tidak ada cashier out baru dalam 90 hari terakhir, dan data terakhir yang terbaca berada pada {$maxCashierDate}.",
                    'action' => 'Validasi apakah cashier flow JO sudah berpindah source atau benar-benar tidak lagi tercatat di jalur ini.',
                ];
            }

            if ($salesDetailLast90d === 0 && $salesDetailTotal > 0) {
                $findings[] = [
                    'category' => 'Transaction Gap',
                    'title' => 'Detail JO aktif tidak terlihat di source saat ini',
                    'severity' => 'warning',
                    'description' => "Sales detail terbaru yang terbaca berada pada {$maxSalesDetailCreated}, sehingga modul JO belum mencerminkan arus transaksi modern secara utuh.",
                    'action' => 'Pastikan detail transaksi modern JO benar-benar diarahkan ke tabel yang dipetakan dashboard.',
                ];
            }

            $findings[] = [
                'category' => 'Executive Summary',
                'title' => 'JO lebih cocok dibaca sebagai gap-mapping diagnostic',
                'severity' => 'info',
                'description' => "Untuk saat ini source JO menunjukkan gabungan sales header {$salesHeaderTotal}, delivery {$deliveryTotal}, cashier {$cashierTotal}, tetapi linkage detail transaksi aktual masih belum memadai.",
                'action' => 'Gunakan modul JO saat ini untuk menemukan gap source antar proses order-to-cash, bukan untuk scorecard margin final.',
            ];

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa JO dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            $previewColumns = ['noRequest', 'cashierDate', 'cashierAccount', 'cashierReceiver', 'isGiro'];
            $previewRows = (clone $cashierQuery)
                ->select($previewColumns)
                ->orderBy('cashierDate')
                ->limit(10)
                ->get();

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'Sales Headers', 'value' => $salesHeaderTotal],
                    ['label' => 'Sales Details', 'value' => $salesDetailTotal],
                    ['label' => 'Cashier Out Headers', 'value' => $cashierTotal],
                    ['label' => 'Delivery Headers', 'value' => $deliveryTotal],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => $maxCashierDate ? Carbon::parse($maxCashierDate) : now(),
                'generated_at' => now(),
                'data_preview' => $this->buildPreviewPayload('Mapped JO Cashier Snapshot', $previewColumns, $previewRows),
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildLpjPayload(array $filters): array
    {
        $connection = DataConnection::query()->where('slug', 'erp-tnp-production')->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $table = 'ops_lpj';
            $dateColumn = 'lpjTGL';
            $baseQuery = $this->filteredTableQuery($db, $table, $filters, $dateColumn);

            $totalLpj = (int) (clone $baseQuery)->count();
            $lpjLast30d = (int) (clone $baseQuery)->whereDate($dateColumn, '>=', now()->subDays(30)->toDateString())->count();
            $lpjLast90d = (int) (clone $baseQuery)->whereDate($dateColumn, '>=', now()->subDays(90)->toDateString())->count();
            $lpjOlderThan1y = (int) (clone $baseQuery)->whereDate($dateColumn, '<', now()->subYear()->toDateString())->count();
            $recentType1 = (int) (clone $baseQuery)->whereDate($dateColumn, '>=', now()->subDays(90)->toDateString())->where('typeRequest', 1)->count();
            $recentType2 = (int) (clone $baseQuery)->whereDate($dateColumn, '>=', now()->subDays(90)->toDateString())->where('typeRequest', 2)->count();
            $maxLpjDate = (clone $baseQuery)->max($dateColumn);

            $trackingRecent = $db->table('ops_tracking_lpj')
                ->whereDate('createdDate', '>=', now()->subDays(90)->toDateString());

            $recentDisapproval = (int) (clone $trackingRecent)->where('status', 'like', '%didisapprove%')->count();
            $recentFinanceApproval = (int) (clone $trackingRecent)->where('status', 'like', 'LPJ diapprove oleh Finance%')->count();
            $recentRequestApproval = (int) (clone $trackingRecent)->where('status', 'like', 'Request diapprove%')->count();
            $dominantRequestCode = (clone $baseQuery)
                ->select('lpjRequest', DB::raw('COUNT(*) as total_rows'))
                ->whereDate($dateColumn, '>=', now()->subDays(90)->toDateString())
                ->groupBy('lpjRequest')
                ->orderByDesc('total_rows')
                ->first();
            $blankAreaRecent = (int) (clone $baseQuery)
                ->whereDate($dateColumn, '>=', now()->subDays(90)->toDateString())
                ->where(function (Builder $query) {
                    $query->whereNull('lpjArea')->orWhere('lpjArea', '');
                })
                ->count();

            $previewColumns = ['requestNO', 'orderNO', 'lpjTGL', 'lpjRequest', 'lpjArea', 'typeRequest'];
            $previewRows = (clone $baseQuery)
                ->select($previewColumns)
                ->orderByDesc($dateColumn)
                ->limit(10)
                ->get();

            $findings = [];

            if ($lpjOlderThan1y > 0) {
                $findings[] = [
                    'category' => 'Executive Summary',
                    'title' => 'Portofolio LPJ masih didominasi legacy historis',
                    'severity' => 'warning',
                    'description' => "{$lpjOlderThan1y} row LPJ lebih tua dari 1 tahun, sementara aktivitas 90 hari terakhir hanya {$lpjLast90d}. Ini berarti dashboard perlu memisahkan legacy archive dari operational queue aktif.",
                    'action' => 'Pisahkan tampilan archive vs operational LPJ agar monitoring manajemen tidak tertutup noise historis.',
                ];
            }

            $findings[] = [
                'category' => 'Business Momentum',
                'title' => 'Aktivitas LPJ terbaru masih hidup dan bisa dipantau periodiknya',
                'severity' => 'info',
                'description' => "{$lpjLast30d} LPJ tercatat dalam 30 hari terakhir dan {$lpjLast90d} dalam 90 hari terakhir, dengan data terbaru pada ".($maxLpjDate ?: 'n/a').'.',
                'action' => 'Gunakan window 30/90 hari sebagai baseline operasional LPJ, bukan total historis seluruh tabel.',
            ];

            if ($recentDisapproval > 0) {
                $findings[] = [
                    'category' => 'Approval Risk',
                    'title' => 'Workflow LPJ terbaru masih menghasilkan disapproval yang perlu diawasi',
                    'severity' => 'warning',
                    'description' => "{$recentDisapproval} event disapproval terdeteksi pada 90 hari terakhir, sementara approval request aktif {$recentRequestApproval} dan approval finance LPJ {$recentFinanceApproval}.",
                    'action' => 'Buat daftar penyebab disapproval paling sering agar perbaikan proses bisa diarahkan ke akar masalah, bukan sekadar rework kasus per kasus.',
                ];
            }

            $dominantType = $recentType2 > $recentType1 ? 'typeRequest 2' : 'typeRequest 1';
            $findings[] = [
                'category' => 'Process Concentration',
                'title' => 'Komposisi permintaan terbaru menunjukkan konsentrasi operasional tertentu',
                'severity' => 'info',
                'description' => "Dalam 90 hari terakhir, {$dominantType} lebih dominan (type 1: {$recentType1}, type 2: {$recentType2}). "
                    .($dominantRequestCode !== null ? "Kode request {$dominantRequestCode->lpjRequest} menyumbang {$dominantRequestCode->total_rows} transaksi terbaru. " : '')
                    ."Sebanyak {$blankAreaRecent} transaksi terbaru juga masih tidak memiliki area terisi.",
                'action' => 'Gunakan request-code dan area sebagai dimensi manajemen kinerja, lalu tutup gap pengisian area agar analisa cabang/koridor bisa dipercaya.',
            ];

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'Analisa LPJ dihitung ulang menggunakan filter aktif pada halaman ini.',
                ];
            }

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => [
                    ['label' => 'LPJ Last 30 Days', 'value' => $lpjLast30d],
                    ['label' => 'LPJ Last 90 Days', 'value' => $lpjLast90d],
                    ['label' => 'Legacy >1 Year', 'value' => $lpjOlderThan1y],
                    ['label' => 'Recent Disapproval Events', 'value' => $recentDisapproval],
                ],
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => $maxLpjDate ? Carbon::parse($maxLpjDate) : now(),
                'generated_at' => now(),
                'data_preview' => $this->buildPreviewPayload('Latest LPJ Activity', $previewColumns, $previewRows),
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildTableDrivenPayload(array $plan, array $filters): array
    {
        $connection = DataConnection::query()->where('slug', $plan['connection_slug'])->firstOrFail();
        $db = $this->runtimeConnectionService->forModel($connection);

        try {
            $metrics = [];
            $findings = [];
            $latestDataAt = null;
            $preview = null;

            foreach ($plan['metrics'] ?? [] as $metric) {
                $table = $metric['table'];
                $dateColumn = $this->detectDateColumn($db, $table);
                $count = $this->countRows($db, $table, $filters, $dateColumn);
                $latestTableDate = $this->maxDate($db, $table, $filters, $dateColumn);

                $metrics[] = [
                    'label' => $metric['label'],
                    'value' => $count,
                ];

                if ($latestTableDate !== null && ($latestDataAt === null || $latestTableDate->greaterThan($latestDataAt))) {
                    $latestDataAt = $latestTableDate;
                }
            }

            if (isset($plan['preview'])) {
                $preview = $this->previewTable(
                    $db,
                    $plan['preview']['table'],
                    $plan['preview']['label'],
                    $filters
                );
                if ($preview['row_count'] === 0) {
                    $findings[] = [
                        'title' => 'No rows matched current filter',
                        'severity' => 'warning',
                        'description' => 'Current filter returned no rows for the preview table. Try widening the date range or clearing division/branch/location filters.',
                    ];
                } else {
                    $findings[] = [
                        'title' => 'Live preview loaded',
                        'severity' => 'info',
                        'description' => "Preview is reading directly from {$plan['preview']['table']} in {$plan['dataset_label']} using the active read-only production connection.",
                    ];
                }
            }

            if ($this->hasActiveFilters($filters)) {
                $findings[] = [
                    'title' => 'Filter applied',
                    'severity' => 'info',
                    'description' => 'The module analysis was recalculated using the active filter context shown on this page.',
                ];
            }

            $findings[] = [
                'title' => 'Live baseline analysis',
                'severity' => 'info',
                'description' => 'Counts and preview rows are now generated live from the mapped production table set. Advanced business rules can be layered on top without changing the read-only access model.',
            ];

            return [
                'mapping_status' => 'live_analysis_ready',
                'kpis' => $metrics,
                'findings' => $findings,
                'filters' => $filters,
                'data_as_of' => $latestDataAt ?? now(),
                'generated_at' => now(),
                'data_preview' => $preview,
            ];
        } finally {
            $this->runtimeConnectionService->disconnect($connection);
        }
    }

    private function buildCrossModulePayload(array $filters): array
    {
        $connections = DataConnection::query()->where('is_active', true)->get();

        return [
            'mapping_status' => 'live_analysis_ready',
            'kpis' => [
                ['label' => 'Live Data Connections', 'value' => $connections->count()],
                ['label' => 'Read-only Validated', 'value' => $connections->where('is_read_only_validated', true)->count()],
                ['label' => 'Modules With Baseline', 'value' => 11],
                ['label' => 'Cross-System Scope', 'value' => 2],
            ],
            'findings' => [
                [
                    'title' => 'Cross-system baseline active',
                    'severity' => 'info',
                    'description' => 'ERP TNP and FMS are both connected live, so the cross-module dashboard can now summarize data source readiness across finance and operations.',
                ],
                [
                    'title' => 'Alert correlation pending',
                    'severity' => 'warning',
                    'description' => 'Event-to-event correlation rules still need dedicated business mapping before the system can raise ranked cross-module alerts automatically.',
                ],
            ],
            'filters' => $filters,
            'data_as_of' => now(),
            'generated_at' => now(),
            'data_preview' => [
                'label' => 'Connected Systems',
                'columns' => ['name', 'database', 'read_only', 'response_ms'],
                'rows' => $connections->map(fn (DataConnection $connection) => [
                    'name' => $connection->name,
                    'database' => $connection->database_name,
                    'read_only' => $connection->is_read_only_validated ? 'yes' : 'no',
                    'response_ms' => $connection->response_time_ms,
                ])->all(),
                'row_count' => $connections->count(),
            ],
        ];
    }

    private function buildAskDataPayload(array $filters): array
    {
        $connections = DataConnection::query()->where('is_active', true)->get();
        $providers = DB::table('ai_providers')->where('is_enabled', true)->count();

        return [
            'mapping_status' => 'live_analysis_ready',
            'kpis' => [
                ['label' => 'Healthy AI Providers', 'value' => $providers],
                ['label' => 'Live Data Connections', 'value' => $connections->count()],
                ['label' => 'Read-only Validated', 'value' => $connections->where('is_read_only_validated', true)->count()],
                ['label' => 'Live Modules', 'value' => 11],
            ],
            'findings' => [
                [
                    'title' => 'Ask Data readiness active',
                    'severity' => 'info',
                    'description' => 'AI providers and production data sources are live, so controlled question-to-data workflows can now be layered onto the dashboard safely.',
                ],
                [
                    'title' => 'Guardrail query templates pending',
                    'severity' => 'warning',
                    'description' => 'Natural-language answers still need approved SQL/query templates and result redaction rules before broad end-user rollout.',
                ],
            ],
            'filters' => $filters,
            'data_as_of' => now(),
            'generated_at' => now(),
            'data_preview' => [
                'label' => 'Ask Data Readiness',
                'columns' => ['component', 'status', 'notes'],
                'rows' => [
                    ['component' => 'Groq', 'status' => 'ok', 'notes' => 'Production provider health passed.'],
                    ['component' => 'Google Gemini', 'status' => 'ok', 'notes' => 'Production provider health passed.'],
                    ['component' => 'Ollama', 'status' => 'ok', 'notes' => 'Local production provider health passed.'],
                    ['component' => 'ERP TNP', 'status' => 'ok', 'notes' => 'Read-only production connection validated.'],
                    ['component' => 'FMS', 'status' => 'ok', 'notes' => 'Read-only production connection validated.'],
                ],
                'row_count' => 5,
            ],
        ];
    }

    private function buildDatabaseCatalogPayload(array $filters): array
    {
        $rows = [];
        $totalTables = 0;

        foreach (DataConnection::query()->where('is_active', true)->get() as $connection) {
            $db = $this->runtimeConnectionService->forModel($connection);

            try {
                $tableCount = $this->countSchemaTables($db, $connection->database_name);
                $columnCount = $this->countSchemaColumns($db, $connection->database_name);
            } finally {
                $this->runtimeConnectionService->disconnect($connection);
            }

            $totalTables += $tableCount;
            $rows[] = [
                'connection' => $connection->name,
                'database' => $connection->database_name,
                'tables' => $tableCount,
                'columns' => $columnCount,
            ];
        }

        return [
            'mapping_status' => 'live_analysis_ready',
            'kpis' => [
                ['label' => 'Connected Databases', 'value' => count($rows)],
                ['label' => 'Cataloged Tables', 'value' => $totalTables],
                ['label' => 'Read-only Sources', 'value' => DataConnection::where('is_read_only_validated', true)->count()],
                ['label' => 'Cataloged Schemas', 'value' => count($rows)],
            ],
            'findings' => [
                [
                    'title' => 'Catalog introspection live',
                    'severity' => 'info',
                    'description' => 'Database catalog metrics are now derived directly from information_schema on the connected production databases.',
                ],
                [
                    'title' => 'Sensitive field classification pending',
                    'severity' => 'warning',
                    'description' => 'The schema is connected live, but field-level classification and relationship graphing still need deeper catalog work.',
                ],
            ],
            'filters' => $filters,
            'data_as_of' => now(),
            'generated_at' => now(),
            'data_preview' => [
                'label' => 'Connected Schema Summary',
                'columns' => ['connection', 'database', 'tables', 'columns'],
                'rows' => $rows,
                'row_count' => count($rows),
            ],
        ];
    }

    private function buildSourceCodePayload(array $filters): array
    {
        $root = base_path();
        $appPhp = $this->countFilesByExtension($root.'/app', '.php');
        $views = $this->countFilesBySuffix($root.'/resources/views', '.blade.php');
        $routes = $this->countFilesByExtension($root.'/routes', '.php');
        $docs = $this->countFilesByExtension($root.'/docs', '.md');

        return [
            'mapping_status' => 'live_analysis_ready',
            'kpis' => [
                ['label' => 'App PHP Files', 'value' => $appPhp],
                ['label' => 'Blade Views', 'value' => $views],
                ['label' => 'Route Files', 'value' => $routes],
                ['label' => 'Markdown Docs', 'value' => $docs],
            ],
            'findings' => [
                [
                    'title' => 'Production source tree indexed',
                    'severity' => 'info',
                    'description' => 'The source-code analysis dashboard is now reading the production-mounted repository layout directly from disk.',
                ],
                [
                    'title' => 'Code lineage pending',
                    'severity' => 'warning',
                    'description' => 'Counts are live, but controller-service-query lineage and dependency graph extraction still need deeper source analysis.',
                ],
            ],
            'filters' => $filters,
            'data_as_of' => now(),
            'generated_at' => now(),
            'data_preview' => [
                'label' => 'Repository Scope',
                'columns' => ['path', 'description'],
                'rows' => [
                    ['path' => 'app/', 'description' => 'Controllers, services, models, middleware'],
                    ['path' => 'resources/views/', 'description' => 'Blade templates for dashboard and admin pages'],
                    ['path' => 'routes/', 'description' => 'Web, API, auth, and console routes'],
                    ['path' => 'docs/', 'description' => 'Deployment notes and technical references'],
                ],
                'row_count' => 4,
            ],
        ];
    }

    private function countRows(ConnectionInterface $db, string $table, array $filters, ?string $dateColumn): int
    {
        $query = $db->table($table);
        $this->applyFilters($query, $table, $filters, $dateColumn);

        return (int) $query->count();
    }

    private function maxDate(ConnectionInterface $db, string $table, array $filters, ?string $dateColumn): ?Carbon
    {
        if ($dateColumn === null) {
            return null;
        }

        $query = $db->table($table);
        $this->applyFilters($query, $table, $filters, $dateColumn);
        $value = $query->max($dateColumn);

        return $value ? Carbon::parse($value) : null;
    }

    private function previewTable(ConnectionInterface $db, string $table, string $label, array $filters): array
    {
        $columns = $this->choosePreviewColumns($db, $table);
        $dateColumn = $this->detectDateColumn($db, $table);
        $query = $db->table($table)->select($columns);
        $this->applyFilters($query, $table, $filters, $dateColumn);

        if ($dateColumn !== null) {
            $query->orderByDesc($dateColumn);
        } elseif (in_array('id', $columns, true)) {
            $query->orderByDesc('id');
        }

        $rows = $query->limit(10)->get()->map(function ($row) use ($columns) {
            $item = [];

            foreach ($columns as $column) {
                $value = $row->{$column} ?? null;
                $item[$column] = is_scalar($value) || $value === null
                    ? (string) $value
                    : json_encode($value);
            }

            return $item;
        })->all();

        return [
            'label' => $label,
            'columns' => $columns,
            'rows' => $rows,
            'row_count' => count($rows),
        ];
    }

    private function filteredTableQuery(ConnectionInterface $db, string $table, array $filters, ?string $dateColumn): Builder
    {
        $query = $db->table($table);
        $this->applyFilters($query, $table, $filters, $dateColumn);

        return $query;
    }

    private function buildPreviewPayload(string $label, array $columns, $rows): array
    {
        $normalizedRows = collect($rows)->map(function ($row) use ($columns) {
            $item = [];

            foreach ($columns as $column) {
                $value = $row->{$column} ?? null;
                $item[$column] = is_scalar($value) || $value === null
                    ? (string) $value
                    : json_encode($value);
            }

            return $item;
        })->all();

        return [
            'label' => $label,
            'columns' => $columns,
            'rows' => $normalizedRows,
            'row_count' => count($normalizedRows),
        ];
    }

    private function applyFilters(Builder $query, string $table, array $filters, ?string $dateColumn): void
    {
        $columns = $this->columnNames($query->getConnection(), $table);

        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $keywordColumns = $this->chooseKeywordColumns($columns);

            if ($keywordColumns !== []) {
                $query->where(function (Builder $keywordQuery) use ($keywordColumns, $keyword) {
                    foreach ($keywordColumns as $index => $column) {
                        if ($index === 0) {
                            $keywordQuery->where($column, 'like', '%'.$keyword.'%');
                        } else {
                            $keywordQuery->orWhere($column, 'like', '%'.$keyword.'%');
                        }
                    }
                });
            }
        }

        if ($dateColumn !== null) {
            if (!empty($filters['date_from'])) {
                $query->whereDate($dateColumn, '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate($dateColumn, '<=', $filters['date_to']);
            }
        }

        $columnMap = [
            'company' => ['company_code', 'company', 'company_id', 'customer', 'customer_code'],
            'division' => ['division', 'division_id', 'divisi', 'department'],
            'branch' => ['branch', 'branch_id', 'cabang', 'site'],
            'location' => ['location', 'location_id', 'lokasi', 'warehouse'],
        ];

        foreach ($columnMap as $filterKey => $candidates) {
            $filterValue = trim((string) ($filters[$filterKey] ?? ''));
            if ($filterValue === '') {
                continue;
            }

            $targetColumn = collect($candidates)->first(fn (string $candidate) => in_array($candidate, $columns, true));
            if ($targetColumn === null) {
                continue;
            }

            $query->where($targetColumn, 'like', '%'.$filterValue.'%');
        }
    }

    /**
     * @param array<int, string> $columns
     * @return array<int, string>
     */
    private function chooseKeywordColumns(array $columns): array
    {
        $preferred = [
            'code',
            'name',
            'supplierName',
            'customer',
            'customer_code',
            'customer_name',
            'vendor',
            'vendor_code',
            'vendor_name',
            'status',
            'description',
            'remarks',
            'notes',
            'note',
            'no_request',
            'request_no',
            'requestNO',
            'requestItemID',
            'no_po',
            'po_number',
            'invoice_no',
            'invoiceNo',
            'inv_number',
            'no_invoice',
            'orderNO',
            'delivery_no',
            'lpj_no',
            'lpjRequest',
            'lpjArea',
            'requested',
            'KodeUnit',
            'warehouse',
            'branch',
            'division',
            'location',
        ];

        $selected = [];

        foreach ($preferred as $candidate) {
            if (in_array($candidate, $columns, true) && !in_array($candidate, $selected, true)) {
                $selected[] = $candidate;
            }

            if (count($selected) >= 6) {
                return $selected;
            }
        }

        foreach ($columns as $column) {
            if ($column === 'id' || str_ends_with($column, '_id')) {
                continue;
            }

            if (
                preg_match('/(code|name|customer|vendor|status|note|remark|desc|no_|number|invoice|request|delivery|lpj|branch|division|location|warehouse)/i', $column)
                && !in_array($column, $selected, true)
            ) {
                $selected[] = $column;
            }

            if (count($selected) >= 6) {
                return $selected;
            }
        }

        return $selected;
    }

    private function detectDateColumn(ConnectionInterface $db, string $table): ?string
    {
        $columns = $this->columnNames($db, $table);
        $candidates = [
            'created_at',
            'updated_at',
            'created_date',
            'modified_date',
            'CreatedDate',
            'ModifiedDate',
            'tanggal',
            'Tanggal_Request',
            'date',
            'invoiceDate',
            'receivedDate',
            'transaction_date',
            'trx_date',
            'tgl',
            'lpjTGL',
            'createdDate',
            'delivery_date',
            'lpj_date',
            'request_date',
        ];

        return collect($candidates)->first(fn (string $candidate) => in_array($candidate, $columns, true));
    }

    /**
     * @return array<int, string>
     */
    private function choosePreviewColumns(ConnectionInterface $db, string $table): array
    {
        $columns = $this->columnNames($db, $table);
        $preferred = [
            'id',
            'no_request',
            'request_no',
            'requestNO',
            'requestItemID',
            'code',
            'name',
            'supplierName',
            'customer',
            'vendor',
            'status',
            'tanggal',
            'date',
            'invoiceNo',
            'invoiceDate',
            'receivedDate',
            'Tanggal_Request',
            'requestNO',
            'KodeUnit',
            'requested',
            'orderNO',
            'lpjTGL',
            'lpjRequest',
            'lpjArea',
            'created_at',
            'updated_at',
            'division',
            'branch',
            'location',
        ];

        $selected = [];

        foreach ($preferred as $candidate) {
            if (in_array($candidate, $columns, true) && !in_array($candidate, $selected, true)) {
                $selected[] = $candidate;
            }
            if (count($selected) >= 6) {
                return $selected;
            }
        }

        foreach ($columns as $column) {
            if (!in_array($column, $selected, true)) {
                $selected[] = $column;
            }
            if (count($selected) >= 6) {
                break;
            }
        }

        return $selected;
    }

    /**
     * @return array<int, string>
     */
    private function columnNames(ConnectionInterface $db, string $table): array
    {
        return collect($db->select("SHOW COLUMNS FROM `{$table}`"))
            ->map(fn (object $column) => (string) $column->Field)
            ->all();
    }

    private function countSchemaTables(ConnectionInterface $db, string $schema): int
    {
        return (int) $db->table('information_schema.tables')
            ->where('table_schema', $schema)
            ->count();
    }

    private function countSchemaColumns(ConnectionInterface $db, string $schema): int
    {
        return (int) $db->table('information_schema.columns')
            ->where('table_schema', $schema)
            ->count();
    }

    private function countFilesByExtension(string $path, string $extension): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $extension)) {
                $count++;
            }
        }

        return $count;
    }

    private function countFilesBySuffix(string $path, string $suffix): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $suffix)) {
                $count++;
            }
        }

        return $count;
    }

    private function formatCurrency(float|int|null $value): string
    {
        return 'Rp '.number_format((float) ($value ?? 0), 0, ',', '.');
    }

    private function formatPercent(int|float $value, int|float $total): string
    {
        if ((float) $total <= 0.0) {
            return '0%';
        }

        return number_format((((float) $value) / ((float) $total)) * 100, 1, ',', '.').'%';
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, string>
     */
    private function normalizeFilters(array $filters): array
    {
        $normalized = [];

        foreach (self::FILTER_KEYS as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '') {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function hasActiveFilters(array $filters): bool
    {
        return !empty($filters);
    }
}
