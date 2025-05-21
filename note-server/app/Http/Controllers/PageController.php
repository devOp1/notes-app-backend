<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PageController extends Controller
{
    use AuthorizesRequests;

    // Einzelne Seite anzeigen
    public function show(Page $page)
    {
        $this->authorize('view', $page);
        return response()->json($page);
    }

    // Seite erstellen
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:pages,id',
        ]);
        $validated['user_id'] = Auth::id();

        $page = Page::create($validated);

        return response()->json($page, 201);
    }

    // Seite löschen
    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);
        $page->delete();

        return response()->json(['message' => 'Seite gelöscht']);
    }

    // Seite verschieben (Eltern ändern + Sortierung)
    public function move(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:pages,id',
            'order' => 'nullable|integer',
        ]);

        $page->update($validated);

        return response()->json(['message' => 'Seite verschoben', 'page' => $page]);
    }

    // Icon aktualisieren
    public function changeIcon(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'icon' => 'required|string|max:255',
        ]);

        $page->icon = $validated['icon'];
        $page->save();

        return response()->json(['message' => 'Icon geändert', 'page' => $page]);
    }

    public function showByUuidSlug($uuidSlug): \Illuminate\Http\JsonResponse
    {
        $uuid = substr($uuidSlug, 0, 36);
        $page = Page::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $page);
        return response()->json($page);
    }

}
