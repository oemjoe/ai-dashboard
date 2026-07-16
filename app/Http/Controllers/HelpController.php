<?php

namespace App\Http\Controllers;

use App\Services\Documentation\DocumentationService;
use Illuminate\Contracts\View\View;

class HelpController extends Controller
{
    public function index(DocumentationService $documentationService): View
    {
        return view('help.index', [
            'articles' => $documentationService->index(),
        ]);
    }

    public function show(string $slug, DocumentationService $documentationService): View
    {
        return view('help.show', $documentationService->article($slug));
    }
}
