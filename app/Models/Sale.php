<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_id',
        'customer_transaction_id',
        'subtotal_amount',
        'total_amount',
        'discount_amount',
        'status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class , 'sale_product' )
            ->withPivot(
                'quantity',
                'subtotal',
                'total',
                'unit_price',
                'discount_amount',
                'cost'
            );
    }

    public function customerTransaction()
    {
        return $this->belongsTo(CustomerTransaction::class);
    }
}
