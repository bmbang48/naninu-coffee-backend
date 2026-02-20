<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'transaction_date',
        'customer_name',
        'tax',
        'discount',
        'total_price',
        'order_method',
        'payment_method',
        'pay',
        'change'
    ];

    protected $casts = [
        'total_price' => 'integer',
        'pay' => 'integer',
        'change' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer'
    ];

    // Relasi ke Product
    public function items()
    {
        return $this->hasMany(Transaction_items::class, 'transaction_id');
    }
}
