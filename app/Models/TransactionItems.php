<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItems extends Model
{
    //
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'hpp',
        'subtotal_hpp'
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'product_id'     => 'integer',
        'quantity'       => 'integer',
        'price'          => 'integer',
        'subtotal'       => 'integer',
        'discount'       => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
