<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function stats(Link $link)
{
    // últimos 25 clics
    $recent = $link->clicks()
        ->latest('clicked_at') // o 'created_at' si prefieres
        ->limit(25)
        ->get(['clicked_at','ip','user_agent','browser','country']);

    // totales últimos 7 días
    $last7 = $link->clicks()
        ->where('clicked_at', '>=', now()->subDays(6)->startOfDay())
        ->selectRaw('DATE(clicked_at) as day, COUNT(*) as clicks')
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    return response()->json([
        'total_clicks'     => (int) $link->click_count,
        'last_access_at'   => $link->last_access_at,
        'recent_clicks'    => $recent,
        'last_7_days'      => $last7, // [{"day":"2025-09-17","clicks":3}, ...]
    ]);
}

}
