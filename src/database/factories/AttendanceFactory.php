<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = \App\Models\User::factory();
        return [
            'user_id' => \App\Models\User::factory(),
            'work_action_id' => 1,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
