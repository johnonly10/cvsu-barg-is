<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{

    protected $model = Category::class;
    public function definition()
    {
        $name = $this->faker->word;
        $slug = Str::slug($name);

        // Ensure the slug is unique
        while (Category::where('slug', $slug)->exists()) {
            $name = $this->faker->word;
            $slug = Str::slug($name);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'image' => $this->generateImage(),
        ];
    }

    protected function generateImage()
    {
        $imageIndex = rand(1, 8);
        return 'img_' . $imageIndex . '.jpg';
    }
}
