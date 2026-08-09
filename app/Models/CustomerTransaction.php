<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_id',
        'amount',
        'type',
        'note',
    ];

    // withTrashed: customers are soft deleted, and a ledger entry must keep
    // showing whose it was long after that customer record is gone.
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    // withTrashed: users are soft deleted, and a receipt must keep showing who
    // wrote it long after that account is gone.
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
