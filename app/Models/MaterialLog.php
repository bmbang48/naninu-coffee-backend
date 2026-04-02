<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialLog extends Model
{
    protected $fillable = [
        'material_id',
        'type',
        'amount',
        'note'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
