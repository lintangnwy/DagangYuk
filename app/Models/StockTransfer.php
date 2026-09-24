<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'from_warehouse_id', 'to_warehouse_id',
        'product_id', 'user_id', 'quantity', 'note', 'status',
    ];

    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse()   { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function product()       { return $this->belongsTo(Product::class); }
    public function user()          { return $this->belongsTo(User::class); }
}
