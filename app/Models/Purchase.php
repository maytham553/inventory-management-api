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

    // withTrashed: users are soft deleted, and a receipt must keep showing who
    // wrote it long after that account is gone.
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class)->withPivot('quantity', 'subtotal', 'total', 'unit_price', 'discount_amount', 'discount_percentage', 'cost');
    }

    public function supplierTransaction()
    {
        return $this->belongsTo(SupplierTransaction::class);
    }
}
