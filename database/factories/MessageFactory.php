<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            'Application Update',
            'Document Request',
            'Status Change',
            'Additional Information Required',
            'Application Approved',
            'Application Rejected',
            'Interview Scheduled',
            'Payment Reminder',
            'Welcome Message',
            'System Notification',
        ];

        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'application_id' => Application::factory(),
            'subject' => fake()->randomElement($subjects),
            'content' => fake()->paragraph(),
            'read_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the message is from an admin to a student.
     */
    public function fromAdminToStudent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => User::factory()->admin(),
            'receiver_id' => User::factory()->student(),
        ]);
    }

    /**
     * Indicate that the message is from a student to an admin.
     */
    public function fromStudentToAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => User::factory()->student(),
            'receiver_id' => User::factory()->admin(),
        ]);
    }

    /**
     * Indicate that the message is between students.
     */
    public function betweenStudents(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => User::factory()->student(),
            'receiver_id' => User::factory()->student(),
        ]);
    }

    /**
     * Indicate that the message is between admins.
     */
    public function betweenAdmins(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => User::factory()->admin(),
            'receiver_id' => User::factory()->admin(),
        ]);
    }

    /**
     * Indicate that the message is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the message is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the message is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the message is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Indicate that the message is about application update.
     */
    public function applicationUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Application Update',
            'content' => fake()->paragraph() . ' Your application status has been updated.',
        ]);
    }

    /**
     * Indicate that the message is about document request.
     */
    public function documentRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Document Request',
            'content' => fake()->paragraph() . ' Please upload the requested documents.',
        ]);
    }

    /**
     * Indicate that the message is about status change.
     */
    public function statusChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Status Change',
            'content' => fake()->paragraph() . ' Your application status has changed.',
        ]);
    }

    /**
     * Indicate that the message is about approval.
     */
    public function approval(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Application Approved',
            'content' => fake()->paragraph() . ' Congratulations! Your application has been approved.',
        ]);
    }

    /**
     * Indicate that the message is about rejection.
     */
    public function rejection(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Application Rejected',
            'content' => fake()->paragraph() . ' We regret to inform you that your application has been rejected.',
        ]);
    }

    /**
     * Indicate that the message is about interview.
     */
    public function interview(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Interview Scheduled',
            'content' => fake()->paragraph() . ' An interview has been scheduled for your application.',
        ]);
    }

    /**
     * Indicate that the message is about payment.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Payment Reminder',
            'content' => fake()->paragraph() . ' Please complete the payment for your application.',
        ]);
    }

    /**
     * Indicate that the message is a welcome message.
     */
    public function welcome(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Welcome Message',
            'content' => fake()->paragraph() . ' Welcome to our platform! We are here to help you.',
        ]);
    }

    /**
     * Indicate that the message is a system notification.
     */
    public function systemNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'System Notification',
            'content' => fake()->paragraph() . ' This is an automated system notification.',
        ]);
    }

    /**
     * Indicate that the message has a long content.
     */
    public function longContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * Indicate that the message has a short content.
     */
    public function shortContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the message is for a specific application.
     */
    public function forApplication(Application $application): static
    {
        return $this->state(fn (array $attributes) => [
            'application_id' => $application->id,
        ]);
    }

    /**
     * Indicate that the message is from a specific sender.
     */
    public function fromSender(User $sender): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => $sender->id,
        ]);
    }

    /**
     * Indicate that the message is to a specific receiver.
     */
    public function toReceiver(User $receiver): static
    {
        return $this->state(fn (array $attributes) => [
            'receiver_id' => $receiver->id,
        ]);
    }

    /**
     * Indicate that the message is between specific users.
     */
    public function betweenUsers(User $sender, User $receiver): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
        ]);
    }

    /**
     * Indicate that the message was read at a specific time.
     */
    public function readAt(\DateTime $readAt): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $readAt,
        ]);
    }

    /**
     * Indicate that the message was created at a specific time.
     */
    public function createdAt(\DateTime $createdAt): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $createdAt,
        ]);
    }
}

