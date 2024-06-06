<?php

namespace Database\Factories;

use App\Models\ClientType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientType>
 */
class ClientTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = ClientType::class;

    public function definition(): array
    {
        return [
            'user_type_id' => $this->faker->numberBetween(1,10),
            'name' => $this->faker->text(50),
        ];
    }
}
