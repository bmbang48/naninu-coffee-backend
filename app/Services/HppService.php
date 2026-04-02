<?php

namespace App\Services;

use App\Models\RecipeProduct;

class HppService
{
    public function calculate($productId)
    {
        $recipes = RecipeProduct::with('material')
            ->where('id_product', $productId)
            ->get();

        $totalHpp = 0;

        if ($recipes->isEmpty()) {
            return 0;
        }

        foreach ($recipes as $recipe) {
            // 🔥 FIX: cek dulu sebelum akses
            if (!$recipe->material) {
                continue;
            }

            $materialPrice = $recipe->material->price ?? 0;
            $amount = $recipe->amount_used ?? 0;

            $totalHpp += ($materialPrice / $recipe->material->amount) * $amount;
        }

        return $totalHpp;
    }
}
