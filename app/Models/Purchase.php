<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'supplier_id',
        'subtotal_amount',
        'total_amount',
        'discount_amount',
        'discount_percentage',
        'supplier_transaction_id',
        'status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class)->withPivot('quantity', 'subtotal', 'total', 'unit_price', 'discount_amount', 'discount_percentage');
    }

    public function supplierTransaction()
    {
        return $this->belongsTo(SupplierTransaction::class);
    }
}
