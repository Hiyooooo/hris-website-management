<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SeoSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'keywords' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (SeoSetting $seo) {
            if ($site = $seo->site) {
                Cache::forget("site:{$site->site_id}:config");
            }
        });

        static::deleted(function (SeoSetting $seo) {
            if ($site = $seo->site) {
                Cache::forget("site:{$site->site_id}:config");
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Sites::class, 'site_id');
    }
}
