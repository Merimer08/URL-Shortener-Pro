<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent; // opcional si usas una librería para detectar browser

class RedirectController extends Controller
{
    public function show(Request $request, string $code)
    {
        $link = $request->attributes->get('link')
            ?? Link::where('code', $code)->firstOrFail();

        // (Opcional) detección simple del navegador sin librería:
        $ua = $request->userAgent() ?? '';
        $browser = $this->detectBrowser($ua);

        // Crear registro de click vía relación (no necesitas link_id en fillable):
        $link->clicks()->create([
            'clicked_at' => now(),
            'ip'         => $request->ip(),
            'user_agent' => $ua,
            'browser'    => $browser,
            'country'    => null, // si luego integras GeoIP, pon aquí el código de país
        ]);

        // Counters
        $link->increment('click_count');
        $link->forceFill(['last_access_at' => now()])->save();

        return redirect()->away($link->target_url, 302);
    }

    private function detectBrowser(string $ua): ?string
    {
        $uaL = strtolower($ua);
        return match (true) {
            str_contains($uaL, 'edg')      => 'Edge',
            str_contains($uaL, 'chrome')   => 'Chrome',
            str_contains($uaL, 'firefox')  => 'Firefox',
            str_contains($uaL, 'safari')   => 'Safari',
            str_contains($uaL, 'opr') || str_contains($uaL, 'opera') => 'Opera',
            default => null,
        };
    }
}
