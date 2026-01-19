<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherCosts extends Model
{
    //
    protected $table = 'other_costs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name_cost',
        'cost_per_product'
    ];
}
