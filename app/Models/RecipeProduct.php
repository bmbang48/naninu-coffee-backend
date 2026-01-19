<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeProduct extends Model
{
    //
    protected $table = 'recipe_products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_product',
        'id_material',
        'amount_used',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material');
    }
}
