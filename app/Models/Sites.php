<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Sites extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'secret' => 'encrypted',
        'last_used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Sites $site) {
            Cache::forget("site:{$site->site_id}:config");
        });

        static::deleted(function (Sites $site) {
            Cache::forget("site:{$site->site_id}:config");
        });
    }

    public function content(): HasOne
    {
        return $this->hasOne(SiteContent::class, 'site_id');
    }

    public function seo(): HasOne
    {
        return $this->hasOne(SeoSetting::class, 'site_id');
    }
}
