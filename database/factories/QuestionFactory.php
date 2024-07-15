<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence(),
            'answer' => $this->faker->sentence(4),
            'author_id' => rand(3, 6),
            'level_id' => rand(1, 10),
            'object_type_id' => rand(1, 2),
            'status' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Question $question){
           $roleIds = Role::whereBetween('id', [3, 6])->inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray();
           $question->roles()->attach($roleIds);
        });
    }
}
