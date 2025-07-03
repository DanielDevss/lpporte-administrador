<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Pantalones',
            'Camisas estilo polo',
            'Sudaderas',
            'Chamarras',
        ];

        foreach($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category)],
                [
                    'name' => $category,
                    'slug' => Str::slug($category),
                ]
            );
        }
    }
}
