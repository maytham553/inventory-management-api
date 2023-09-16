<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

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

    public function supplierTransactions()
    {
        return $this->hasMany(SupplierTransaction::class);
    }
}
