<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'branch_id', 'name', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function stocks()  { return $this->hasMany(WarehouseStock::class); }
    public function transfersOut() { return $this->hasMany(StockTransfer::class, 'from_warehouse_id'); }
    public function transfersIn()  { return $this->hasMany(StockTransfer::class, 'to_warehouse_id'); }
}
