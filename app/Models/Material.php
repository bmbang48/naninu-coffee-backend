<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    //
    public $timestamps = false;

    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'unit',
        'price',
        'amount',
        'stock',
        'min_stock'
    ];

    public function recipe()
    {
        return $this->hasMany(RecipeProduct::class, 'id_material');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'recipe_products', 'id_material', 'id_product')
            ->withPivot('amount_used');
    }

    public function logs()
    {
        return $this->hasMany(MaterialLog::class);
    }
}
