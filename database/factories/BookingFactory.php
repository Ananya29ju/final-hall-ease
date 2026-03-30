<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Hall;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDatetime = Carbon::parse(fake()->dateTimeBetween('+2 days', '+60 days'))
            ->setHour(fake()->numberBetween(8, 18))
            ->setMinute(fake()->randomElement([0, 30]))
            ->setSecond(0);

        $durationHours = fake()->numberBetween(1, 8);
        $endDatetime = (clone $startDatetime)->addHours($durationHours);

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'hall_id' => Hall::inRandomOrder()->first()?->id ?? Hall::factory(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'booking_status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_status' => 'pending',
        ]);
    }
}
