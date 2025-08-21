<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $universities = [
            'Harvard University',
            'Stanford University',
            'MIT',
            'University of Oxford',
            'University of Cambridge',
            'University of Toronto',
            'McGill University',
            'University of British Columbia',
            'University of Melbourne',
            'University of Sydney',
        ];

        $countries = [
            'United States',
            'United Kingdom',
            'Canada',
            'Australia',
            'Germany',
            'France',
            'Netherlands',
            'Sweden',
            'Switzerland',
            'Japan',
        ];

        $programs = [
            'Computer Science',
            'Engineering',
            'Business Administration',
            'Medicine',
            'Law',
            'Arts and Humanities',
            'Social Sciences',
            'Natural Sciences',
            'Mathematics',
            'Economics',
        ];

        $statuses = ['pending', 'in_progress', 'approved', 'rejected', 'completed'];
        $priorityLevels = ['low', 'medium', 'high'];

        return [
            'user_id' => User::factory(),
            'university_name' => fake()->randomElement($universities),
            'country' => fake()->randomElement($countries),
            'program' => fake()->randomElement($programs),
            'status' => fake()->randomElement($statuses),
            'priority_level' => fake()->randomElement($priorityLevels),
            'estimated_completion_date' => fake()->dateTimeBetween('now', '+2 years'),
        ];
    }

    /**
     * Indicate that the application is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the application is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the application is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the application is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the application is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the application has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_level' => 'high',
        ]);
    }

    /**
     * Indicate that the application has medium priority.
     */
    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_level' => 'medium',
        ]);
    }

    /**
     * Indicate that the application has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_level' => 'low',
        ]);
    }

    /**
     * Indicate that the application is for a US university.
     */
    public function usUniversity(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'United States',
            'university_name' => fake()->randomElement([
                'Harvard University',
                'Stanford University',
                'MIT',
                'University of California, Berkeley',
                'Yale University',
            ]),
        ]);
    }

    /**
     * Indicate that the application is for a UK university.
     */
    public function ukUniversity(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'United Kingdom',
            'university_name' => fake()->randomElement([
                'University of Oxford',
                'University of Cambridge',
                'Imperial College London',
                'University College London',
                'London School of Economics',
            ]),
        ]);
    }

    /**
     * Indicate that the application is for a Canadian university.
     */
    public function canadianUniversity(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Canada',
            'university_name' => fake()->randomElement([
                'University of Toronto',
                'McGill University',
                'University of British Columbia',
                'University of Alberta',
                'University of Waterloo',
            ]),
        ]);
    }

    /**
     * Indicate that the application is for computer science.
     */
    public function computerScience(): static
    {
        return $this->state(fn (array $attributes) => [
            'program' => 'Computer Science',
        ]);
    }

    /**
     * Indicate that the application is for engineering.
     */
    public function engineering(): static
    {
        return $this->state(fn (array $attributes) => [
            'program' => 'Engineering',
        ]);
    }

    /**
     * Indicate that the application is for business.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'program' => 'Business Administration',
        ]);
    }

    /**
     * Indicate that the application is urgent (high priority and not completed).
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_level' => 'high',
            'status' => fake()->randomElement(['pending', 'in_progress']),
        ]);
    }



    /**
     * Indicate that the application has a specific completion date.
     */
    public function withCompletionDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_completion_date' => fake()->dateTimeBetween('now', '+2 years'),
        ]);
    }

    /**
     * Indicate that the application has no completion date.
     */
    public function withoutCompletionDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_completion_date' => null,
        ]);
    }
}
