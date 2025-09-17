<?php
namespace App\Http\Middleware;

use App\Models\Link;
use Closure;
use Illuminate\Http\Request;

class EnsureLinkIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $code = $request->route('code');

        $link = Link::withTrashed()->where('code', $code)->first();

        if (! $link || $link->trashed()) {
            return $this->fail($request, 404, 'Este enlace no está disponible.');
        }

        if ($link->expires_at && now()->greaterThan($link->expires_at)) {
            return $this->fail($request, 410, 'Este enlace ha expirado.');
        }

        if (!is_null($link->max_clicks) && $link->click_count >= $link->max_clicks) {
            return $this->fail($request, 410, 'Este enlace alcanzó el máximo de clics.');
        }

        // Inyecta el modelo para evitar otra consulta en el controller
        $request->attributes->set('link', $link);

        return $next($request);
    }

    protected function fail(Request $request, int $status, string $msg)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $msg], $status);
        }
        if (view()->exists('links.blocked')) {
            return response()->view('links.blocked', ['reason' => $msg], $status);
        }
        return response($msg, $status);
    }
}
