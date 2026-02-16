<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    public $timestamps = false;

    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_name',
        'price',
        'description',
        'image'
    ];

    protected $casts = [
        'price' => 'integer'
    ];



    public function recipe()
    {
        return $this->hasMany(RecipeProduct::class, 'id_product');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_product');
    }
}
