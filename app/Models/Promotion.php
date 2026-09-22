<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = ['id'];

    protected $casts = [
        'value'        => 'float',
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'valid_from'   => 'date',
        'valid_until'  => 'date',
        'is_active'    => 'boolean',
    ];

    public function isValid(float $totalPurchase): bool
    {
        if (!$this->is_active) return false;
        
        $today = now()->startOfDay();
        
        if ($this->valid_from && $today->lt($this->valid_from)) return false;
        if ($this->valid_until && $today->gt($this->valid_until)) return false;
        
        if ($this->quota !== null && $this->used >= $this->quota) return false;
        
        if ($this->min_purchase > 0 && $totalPurchase < $this->min_purchase) return false;

        return true;
    }

    public function calculateDiscount(float $totalPurchase): float
    {
        if (!$this->isValid($totalPurchase)) return 0;

        $discount = 0;
        if ($this->type === 'fixed') {
            $discount = $this->value;
        } else if ($this->type === 'percentage') {
            $discount = $totalPurchase * ($this->value / 100);
        }

        if ($this->max_discount !== null && $this->max_discount > 0) {
            $discount = min($discount, $this->max_discount);
        }

        return min($discount, $totalPurchase); // Diskon max adalah total pembelian
    }
}
