<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_name' => $this->faker->words(3, true).' Concert',
            'event_category' => $this->faker->randomElement(['Music', 'Sports', 'Theatre']),
            'event_datetime' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'venue_name' => $this->faker->company().' Arena',
            'city' => $this->faker->city(),
            'event_poster_url' => 'https://picsum.photos/400/600',
        ];
    }

    /**
     * Indicate that the event is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_datetime' => now()->subDays(2),
        ]);
    }

    /**
     * Indicate that the event is upcoming.
     */
    public function upcoming(int $hours): static
    {
        return $this->state(fn (array $attributes) => [
            'event_datetime' => now()->addHours($hours),
        ]);
    }
}
