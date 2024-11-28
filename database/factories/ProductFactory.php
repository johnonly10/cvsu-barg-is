<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'short_description' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'quantity' => $this->faker->numberBetween(1, 100),
            'stock_status' => $this->faker->randomElement(['instock', 'outofstock', 'reorder']),
            'featured' => $this->faker->boolean,
            'image' => 'image_' . rand(1, 10) . '.jpg',
            'images' => json_encode(['image_1.jpg', 'image_2.jpg']),
            'sex' => $this->faker->randomElement(['male', 'female', 'all']),
            'low_stock_notified' => $this->faker->boolean,
            'archived' => $this->faker->boolean,
            'archived_at' => $this->faker->boolean ? now() : null,
            'category_id' => \App\Models\Category::factory(),
        ];
    }

    protected function generateImage()
    {
        $imageIndex = rand(1, 8);
        return 'img_' . $imageIndex . '.jpg';
    }
}
