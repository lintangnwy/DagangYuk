<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'address', 'phone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function users()     { return $this->hasMany(User::class); }
    public function warehouses(){ return $this->hasMany(Warehouse::class); }
    public function orders()    { return $this->hasMany(Order::class); }
}
