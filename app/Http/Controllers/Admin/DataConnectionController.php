<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DataConnection;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DataConnectionController extends Controller
{
    public function index(): View
    {
        return view('admin.data-connections.index', [
            'connections' => DataConnection::query()->with('company')->orderBy('name')->get(),
            'companies' => Company::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.data-connections.form', [
            'connection' => new DataConnection(),
            'companies' => Company::query()->orderBy('name')->get(),
            'method' => 'POST',
            'action' => route('admin.data-connections.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DataConnection::query()->create($this->validated($request));

        return redirect()->route('admin.data-connections.index')->with('status', 'Data connection created.');
    }

    public function show(DataConnection $dataConnection): RedirectResponse
    {
        return redirect()->route('admin.data-connections.edit', $dataConnection);
    }

    public function edit(DataConnection $dataConnection): View
    {
        return view('admin.data-connections.form', [
            'connection' => $dataConnection,
            'companies' => Company::query()->orderBy('name')->get(),
            'method' => 'PUT',
            'action' => route('admin.data-connections.update', $dataConnection),
        ]);
    }

    public function update(Request $request, DataConnection $dataConnection): RedirectResponse
    {
        $dataConnection->update($this->validated($request));

        return redirect()->route('admin.data-connections.index')->with('status', 'Data connection updated.');
    }

    public function destroy(DataConnection $dataConnection): RedirectResponse
    {
        $dataConnection->delete();

        return redirect()->route('admin.data-connections.index')->with('status', 'Data connection deleted.');
    }

    public function test(DataConnection $dataConnection, DashboardDataService $dashboardDataService): RedirectResponse
    {
        $result = $dashboardDataService->testConnection($dataConnection);

        return redirect()
            ->route('admin.data-connections.index')
            ->with('status', "Probe {$dataConnection->name}: {$result['message']}");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'slug' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'system_name' => ['required', 'string', 'max:255'],
            'db_driver' => ['required', 'in:mysql,mariadb,sqlsrv'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'database_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password_env_key' => ['nullable', 'string', 'max:255'],
            'module_slugs' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'host_masked' => null,
            'is_active' => $request->boolean('is_active'),
            'module_slugs' => collect(explode(',', (string) $request->input('module_slugs')))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->values()
                ->all(),
        ];
    }
}
