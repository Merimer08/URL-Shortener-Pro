<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkApiController extends Controller
{
    // helper simple si no usas Policies:
    private function assertOwner(Request $request, Link $link): void
    {
        abort_if($link->user_id !== $request->user()->id, 403, 'Forbidden');
    }

    /**
     * GET /api/v1/links?view=active|trashed|all&per_page=10&page=1
     */
    public function index(Request $request)
    {
        $view    = $request->query('view', 'active');
        $perPage = (int) $request->query('per_page', 10);

        $query = Link::where('user_id', $request->user()->id);

        if ($view === 'trashed') {
            $query->onlyTrashed();
        } elseif ($view === 'all') {
            $query->withTrashed();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * POST /api/v1/links
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'target_url' => ['required', 'string', 'regex:/^https?:\\/\\//i', 'max:2000'],
            'max_clicks' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        do {
            $code = Str::random(7);
        } while (Link::where('code', $code)->exists());

        $link = Link::create([
            'user_id'     => $request->user()->id,
            'code'        => $code,
            'target_url'  => $data['target_url'],
            'max_clicks'  => $data['max_clicks'] ?? null,
            'expires_at'  => $data['expires_at'] ?? null,
            'is_active'   => true,
            'click_count' => 0,
        ]);

        return response()->json($link, 201);
    }

    /**
     * GET /api/v1/links/{link}
     */
    public function show(Request $request, Link $link)
    {
        $this->assertOwner($request, $link);
        return response()->json($link);
    }

    /**
     * PUT /api/v1/links/{link}
     */
    public function update(Request $request, Link $link)
    {
        $this->assertOwner($request, $link);

        $data = $request->validate([
            'target_url' => ['required', 'string', 'regex:/^https?:\\/\\//i', 'max:2000'],
            'max_clicks' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        $link->update($data);

        return response()->json($link);
    }

    /**
     * DELETE /api/v1/links/{link}
     * Soft delete (archive)
     */
    public function destroy(Request $request, Link $link)
    {
        $this->assertOwner($request, $link);
        $link->delete(); // soft delete
        return response()->noContent();
    }

    /**
     * POST /api/v1/links/{id}/restore
     */
    public function restore(Request $request, $id)
    {
        $link = Link::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        // $this->authorize('update', $link); // si usas Policies
        $link->restore();

        return response()->json($link, 200);
    }

    /**
     * DELETE /api/v1/links/{id}/force
     * Borrado definitivo
     */
    public function forceDelete(Request $request, $id)
    {
        $link = Link::withTrashed()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        // $this->authorize('delete', $link); // si usas Policies
        $link->forceDelete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/links/{link}/stats
     */
    public function stats(Request $request, Link $link)
    {
        $this->assertOwner($request, $link);

        $recent = $link->clicks()
            ->latest('clicked_at')
            ->limit(25)
            ->get([
                'clicked_at', 'ip', 'user_agent', 'browser', 'country',
                'referrer', 'referrer_host', 'source_tag',
            ]);

        $last7 = $link->clicks()
            ->where('clicked_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(clicked_at) as day, COUNT(*) as clicks')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return response()->json([
            'total_clicks'   => (int) $link->click_count,
            'last_access_at' => $link->last_access_at,
            'recent_clicks'  => $recent,
            'last_7_days'    => $last7,
        ]);
    }
}
