<?php

namespace App\Http\Middleware;

use App\Models\Sites;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySiteSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $siteId = $request->header('X-Site-Id');
        $siteSecret = $request->header('X-Site-Secret');

        if (!$siteId || !$siteSecret) {
            return response()->json(['message' => 'Unauthorized: Missing credentials'], 401);
        }

        $site = Sites::where('site_id', $siteId)->where('is_active', true)->first();

        if (!$site) {
            return response()->json(['message' => 'Unauthorized: Missing credentials'], 401);
        }

        if (!hash_equals($site->secret, $siteSecret)) {
            return response()->json(['message' => 'Unauthorized: Missing credentials'], 401);
        }

        if (!$site->last_used_at || $site->last_used_at->diffInMinutes(now()) >= 5) {
            $site->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $request->attributes->set('current_site', $site);
        return $next($request);
    }
}
