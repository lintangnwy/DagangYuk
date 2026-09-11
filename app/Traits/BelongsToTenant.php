<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                // Jika bukan Super Admin (role_id != 1), terapkan global scope tenant_id
                if ($user->role_id !== 1) {
                    $builder->where('tenant_id', $user->tenant_id);
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                // Otomatis isi tenant_id saat create jika bukan Super Admin dan tenant_id belum diisi
                if ($user->role_id !== 1 && empty($model->tenant_id)) {
                    $model->tenant_id = $user->tenant_id;
                }
            }
        });
    }
}
