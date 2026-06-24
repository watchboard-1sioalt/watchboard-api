<?php

namespace App\Http\Controllers;

use App\Models\FluxRss;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FeedController extends Controller
{
    public function index(): JsonResponse
    {
        $feeds = FluxRss::where('id_utilisateur', Auth::id())->get();

        return response()->json($feeds);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'  => 'required|url|max:255',
            'name' => 'nullable|string|max:150',
        ]);

        $this->validateRssUrl($validated['url']);

        $feed = FluxRss::create([
            'url'            => $validated['url'],
            'name'           => $validated['name'] ?? null,
            'id_utilisateur' => Auth::id(),
        ]);

        return response()->json($feed, 201);
    }

    public function show(int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        return response()->json($feed);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
        ]);

        $feed->update(['name' => $validated['name']]);

        return response()->json($feed);
    }

    public function destroy(int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        // Detach tags without deleting saved resources
        $feed->tags()->detach();
        $feed->delete();

        return response()->json(null, 204);
    }

    private function validateRssUrl(string $url): void
    {
        $response = Http::timeout(10)->get($url);

        abort_if(! $response->successful(), 422, 'The URL could not be reached.');

        $contentType = $response->header('Content-Type') ?? '';
        $body = $response->body();

        $isRss  = str_contains($contentType, 'rss') || str_contains($contentType, 'xml') || str_contains($contentType, 'atom');
        $hasRssTag = str_contains($body, '<rss') || str_contains($body, '<feed') || str_contains($body, '<channel');

        abort_if(! $isRss && ! $hasRssTag, 422, 'The URL does not point to a valid RSS or Atom feed.');
    }
}
