<?php

// database/factories/TaskFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),  // Generates a random task name
            'completed' => $this->faker->boolean(),  // Randomly true or false
        ];
    }
}