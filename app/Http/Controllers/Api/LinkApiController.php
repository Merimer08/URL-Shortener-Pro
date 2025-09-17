<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'target_url' => ['required', 'url', 'max:2000'],
            'max_clicks' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        // Genera un código único (7 chars alfanuméricos)
        $code = Str::random(7);
        while (Link::where('code', $code)->exists()) {
            $code = Str::random(7);
        }

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
     * Muestra un link del usuario
     */
    public function show(Link $link)
    {
        // Si usas middleware ->can('view','link') en rutas, ya viene autorizado
        return response()->json($link);
    }

    /**
     * PUT /api/v1/links/{link}
     * Actualiza un link del usuario
     */
    public function update(Request $request, Link $link)
    {
        $data = $request->validate([
            'target_url' => ['required', 'url', 'max:2000'],
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
        // Si aún no tienes modelo Click, leemos directo de la tabla
        $recentClicks = DB::table('clicks')
            ->where('link_id', $link->id)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get(['created_at', 'ip', 'referrer', 'user_agent']);

        return response()->json([
            'total_clicks'   => $link->click_count,
            'last_access_at' => $link->last_access_at,
            'recent_clicks'  => $recentClicks,
        ]);
    }
}
