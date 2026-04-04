<?php

namespace App\Services;

use App\Models\RecipeProduct;
use App\Models\MaterialLog;
use App\Models\Material;

class MaterialStockService
{
    public function reduceStock($productId, $qty, $transactionCode)
    {
        $recipes = RecipeProduct::with('material')
            ->where('id_product', $productId)
            ->get();

        $errors = [];

        foreach ($recipes as $recipe) {

            if (!$recipe->material) continue;

            $material = $recipe->material;

            // 🔥 jumlah yang dipakai
            $used = $recipe->amount_used * $qty;


            if ($material->stock < $used) {
                $errors[] = [
                    'name' => $material->name,
                    'stock' => $material->stock,
                    'needed' => $used,
                ];
            }
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        // 🔥 update stock
        foreach ($recipes as $recipe) {
            if (!$recipe->material) continue;

            $material = $recipe->material;

            $used = $recipe->amount_used * $qty;
            $material->stock -= $used;
            $material->save();

            // 🔥 log
            MaterialLog::create([
                'material_id' => $material->id,
                'type' => 'OUT',
                'amount' => $used,
                'note' => 'Transaction ' . $transactionCode
            ]);
        }
    }
}
