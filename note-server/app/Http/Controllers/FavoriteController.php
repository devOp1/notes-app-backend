<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use AuthorizesRequests;

    // Seite zu den Favoriten hinzufügen
    public function store(Request $request, $uuidSlug)
    {
        $user = $request->user();

        $uuid = substr($uuidSlug, 0, 36);
        $page = Page::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $page);          // Seite muss sichtbar sein

        $user->favoritePages()->syncWithoutDetaching($page->id);

        return response()->json([
            'message' => 'Seite als Favorit gespeichert.',
            'page_id' => $page->id,
        ], 201);
    }

    // Favorit entfernen
    public function destroy(Request $request, $uuidSlug)
    {
        $user = $request->user();

        $uuid = substr($uuidSlug, 0, 36);
        $page = Page::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $page);

        $user->favoritePages()->detach($page->id);

        return response()->json([
            'message' => 'Seite aus Favoriten entfernt.',
            'page_id' => $page->id,
        ]);
    }

    // (Optional) Alle Favoriten eines Users auflisten
    public function index(Request $request)
    {
        return response()->json($request->user()->favoritePages);
    }
}
