<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Services\AI\AiProviderManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiProviderController extends Controller
{
    public function index(AiProviderManager $aiProviderManager): View
    {
        return view('admin.ai-providers.index', [
            'providers' => AiProvider::query()->orderBy('priority')->get(),
            'health' => collect($aiProviderManager->overview())->keyBy('slug'),
        ]);
    }

    public function create(): View
    {
        return view('admin.ai-providers.form', [
            'provider' => new AiProvider(),
            'method' => 'POST',
            'action' => route('admin.ai-providers.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AiProvider::query()->create($this->validated($request));

        return redirect()->route('admin.ai-providers.index')->with('status', 'AI provider created.');
    }

    public function show(AiProvider $aiProvider): RedirectResponse
    {
        return redirect()->route('admin.ai-providers.edit', $aiProvider);
    }

    public function edit(AiProvider $aiProvider): View
    {
        return view('admin.ai-providers.form', [
            'provider' => $aiProvider,
            'method' => 'PUT',
            'action' => route('admin.ai-providers.update', $aiProvider),
        ]);
    }

    public function update(Request $request, AiProvider $aiProvider): RedirectResponse
    {
        $aiProvider->update($this->validated($request));

        return redirect()->route('admin.ai-providers.index')->with('status', 'AI provider updated.');
    }

    public function destroy(AiProvider $aiProvider): RedirectResponse
    {
        $aiProvider->delete();

        return redirect()->route('admin.ai-providers.index')->with('status', 'AI provider deleted.');
    }

    public function test(AiProvider $aiProvider, AiProviderManager $aiProviderManager): RedirectResponse
    {
        $health = $aiProviderManager->health($aiProvider);

        $aiProvider->forceFill([
            'last_success_at' => $health['status'] === 'ok' ? now() : $aiProvider->last_success_at,
            'last_error_at' => $health['status'] !== 'ok' ? now() : $aiProvider->last_error_at,
            'last_error_message' => $health['status'] !== 'ok' ? $health['message'] : null,
        ])->save();

        return redirect()->route('admin.ai-providers.index')->with('status', "{$aiProvider->name}: {$health['message']}");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['required', 'in:groq,gemini,ollama'],
            'model' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'api_key_env_key' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'integer', 'min:1', 'max:99'],
            'timeout_seconds' => ['required', 'integer', 'min:1', 'max:120'],
            'max_retries' => ['required', 'integer', 'min:0', 'max:10'],
            'min_confidence' => ['required', 'numeric', 'min:0', 'max:1'],
            'is_enabled' => ['nullable', 'boolean'],
        ]) + [
            'is_enabled' => $request->boolean('is_enabled'),
        ];
    }
}
