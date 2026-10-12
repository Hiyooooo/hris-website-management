<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SiteContent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'social_links' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (SiteContent $content) {
            if ($site = $content->site) {
                Cache::forget("site:{$site->site_id}:config");
            }
        });

        static::deleted(function (SiteContent $content) {
            if ($site = $content->site) {
                Cache::forget("site:{$site->site_id}:config");
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Sites::class, 'site_id');
    }
}
