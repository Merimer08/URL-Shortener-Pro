<?php
// app/Http/Controllers/RedirectController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function show(Request $request, string $code)
    {
        $link = $request->attributes->get('link')
            ?? \App\Models\Link::where('code', $code)->firstOrFail();

        $ua       = $request->userAgent() ?? '';
        $browser  = $this->detectBrowser($ua);
        $referrer = $request->headers->get('referer'); // puede ser null
        $host     = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;
        $sourceTag = $request->query('src') ?? $request->query('campaign');

        // crea el click vía relación (no necesitas link_id en fillable)
        $link->clicks()->create([
            'clicked_at'    => now(),
            'ip'            => $request->ip(),
            'user_agent'    => $ua,
            'browser'       => $browser,
            'country'       => null,
            'referrer'      => $referrer,
            'referrer_host' => $host,
            'source_tag'    => $sourceTag,
        ]);

        $link->increment('click_count');
        $link->forceFill(['last_access_at' => now()])->save();

        return redirect()->away($link->target_url, 302);
    }

    private function detectBrowser(string $ua): ?string
    {
        $uaL = strtolower($ua);
        return match (true) {
            str_contains($uaL, 'edg') => 'Edge',
            str_contains($uaL, 'chrome') => 'Chrome',
            str_contains($uaL, 'firefox') => 'Firefox',
            str_contains($uaL, 'safari') => 'Safari',
            str_contains($uaL, 'opr') || str_contains($uaL, 'opera') => 'Opera',
            default => null,
        };
    }
}
