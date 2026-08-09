<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'type',
        'amount',
        'note',
    ];

    // withTrashed: suppliers are soft deleted, and a ledger entry must keep
    // showing whose it was long after that supplier record is gone.
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    // withTrashed: users are soft deleted, and a receipt must keep showing who
    // wrote it long after that account is gone.
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
