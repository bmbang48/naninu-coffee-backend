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
        'amount'
    ];

    public function recipe()
    {
        return $this->hasMany(RecipeProduct::class, 'id_material');
    }
}
