<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'users_id',
        'transactions_id',
        'products_id',
        'quantity',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'products_id');
    }
}
