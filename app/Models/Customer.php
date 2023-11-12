<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'governorate_id',
        'address',
        'balance',
        'note',
    ];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function customerTransactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
