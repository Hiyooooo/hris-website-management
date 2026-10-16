<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SiteConfigResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteConfigController extends Controller
{
    public function show(Request $request)
    {
        $site = $request->attributes->get('current_site');

        $data = Cache::remember("site:{$site->site_id}:config", 3600, function () use ($site, $request) {
            $site->loadMissing(['content', 'seo']);
            return (new SiteConfigResource($site))->toArray($request);
        });

        return response()->json($data);
    }
}
