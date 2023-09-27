<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterialWithdrawalRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'raw_material_id',
        'user_id',
        'quantity',
        'note',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
