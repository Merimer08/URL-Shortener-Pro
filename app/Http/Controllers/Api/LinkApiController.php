<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkApiController extends Controller
{
    /**
     * GET /api/v1/links
     * Lista los links del usuario autenticado (paginados)
     */
    public function index(Request $request)
    {
        return Link::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);
    }

    /**
     * POST /api/v1/links
     * Crea un nuevo link corto
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'target_url' => ['required', 'string', 'regex:/^https?:\\/\\//i', 'max:2000'],
            'max_clicks' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        // Genera un código único (7 chars alfanuméricos)
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
     * Muestra un link del usuario (autorizado por Policy en la ruta)
     */
    public function show(Link $link)
    {
        return response()->json($link);
    }

    /**
     * PUT /api/v1/links/{link}
     * Actualiza un link del usuario
     */
    public function update(Request $request, Link $link)
    {
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
     * Elimina (soft delete si lo activas) un link del usuario
     */
    public function destroy(Link $link)
    {
        $link->delete();
        return response()->noContent();
    }

    /**
     * GET /api/v1/links/{link}/stats
     * Estadísticas básicas del link
     */
    public function stats(Link $link)
    {
        // últimos 25 clics (usa clicked_at y los campos que tienes en la tabla)
        $recent = $link->clicks()
            ->latest('clicked_at')
            ->limit(25)
            ->get([
                'clicked_at',
                'ip',
                'user_agent',
                'browser',
                'country',
                'referrer',
                'referrer_host',
                'source_tag',
            ]);

        // clicks por día de los últimos 7 días (incluyendo hoy)
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
