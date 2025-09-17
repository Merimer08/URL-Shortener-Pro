<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes; // ← descomenta si usas soft deletes

class Link extends Model
{
    use HasFactory;
    // use SoftDeletes; // ← descomenta si tu tabla links tiene deleted_at

    protected $fillable = [
        'user_id','code','target_url','max_clicks','click_count','expires_at','is_active','last_access_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'last_access_at' => 'datetime',
    ];

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function hasReachedLimit(): bool
    {
        return $this->max_clicks !== null && $this->click_count >= $this->max_clicks;
    }

    public function clicks()
    {
        return $this->hasMany(\App\Models\Click::class);
    }
}
