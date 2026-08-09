<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory , SoftDeletes;
    protected $hidden = ['profit'];


    protected $fillable = [
        'user_id',
        'customer_id',
        'customer_transaction_id',
        'subtotal_amount',
        'total_amount',
        'discount_amount',
        'status',
        'note',
        'previous_balance',
        'driver_name',
        'profit',
    ];

    // withTrashed: users are soft deleted, and a receipt must keep showing who
    // wrote it long after that account is gone.
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // withTrashed: customers are soft deleted, and an invoice must keep showing
    // whose it was long after that customer record is gone.
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    // withTrashed: products are soft deleted, and an invoice must keep listing
    // every line it was billed for. Without it the pivot row survives but the
    // product vanishes from the collection, so the invoice silently shows fewer
    // lines than it was charged for.
    public function products()
    {
        return $this->belongsToMany(Product::class , 'sale_product' )
            ->withTrashed()
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
