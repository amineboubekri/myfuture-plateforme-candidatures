<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $documentTypes = [
            'transcript',
            'certificate',
            'passport',
            'cv',
            'motivation_letter',
            'reference_letter',
            'language_certificate',
            'financial_statement',
            'medical_certificate',
            'police_clearance',
        ];

        $statuses = ['pending', 'approved', 'rejected'];

        return [
            'application_id' => Application::factory(),
            'document_type' => fake()->randomElement($documentTypes),
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'status' => fake()->randomElement($statuses),
            'comments' => fake()->optional()->sentence(),
            'uploaded_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'validated_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
            'validated_by' => fake()->optional()->randomElement([User::factory()]),
        ];
    }

    /**
     * Indicate that the document is a transcript.
     */
    public function transcript(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'transcript',
            'file_path' => 'documents/transcript_' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is a certificate.
     */
    public function certificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'certificate',
            'file_path' => 'documents/certificate_' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is a passport.
     */
    public function passport(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'passport',
            'file_path' => 'documents/passport_' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is a CV.
     */
    public function cv(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'cv',
            'file_path' => 'documents/cv_' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is a motivation letter.
     */
    public function motivationLetter(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'motivation_letter',
            'file_path' => 'documents/motivation_letter_' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'validated_at' => null,
            'validated_by' => null,
        ]);
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'validated_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'validated_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the document is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'validated_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'validated_by' => User::factory(),
            'comments' => fake()->sentence(),
        ]);
    }



    /**
     * Indicate that the document is validated.
     */
    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'validated_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'validated_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the document is not validated.
     */
    public function notValidated(): static
    {
        return $this->state(fn (array $attributes) => [
            'validated_at' => null,
            'validated_by' => null,
        ]);
    }

    /**
     * Indicate that the document has comments.
     */
    public function withComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comments' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the document has no comments.
     */
    public function withoutComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comments' => null,
        ]);
    }

    /**
     * Indicate that the document was uploaded recently.
     */
    public function recentlyUploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the document was uploaded a long time ago.
     */
    public function oldUpload(): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_at' => fake()->dateTimeBetween('-6 months', '-3 months'),
        ]);
    }

    /**
     * Indicate that the document is a PDF file.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is a Word file.
     */
    public function word(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => 'documents/' . fake()->uuid() . '.docx',
        ]);
    }

    /**
     * Indicate that the document is an image file.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => 'documents/' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Indicate that the document is required.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => fake()->randomElement([
                'transcript',
                'certificate',
                'passport',
                'cv',
                'motivation_letter',
            ]),
        ]);
    }

    /**
     * Indicate that the document is optional.
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => fake()->randomElement([
                'reference_letter',
                'language_certificate',
                'financial_statement',
                'medical_certificate',
                'police_clearance',
            ]),
        ]);
    }

    /**
     * Indicate that the document is for a specific application.
     */
    public function forApplication(Application $application): static
    {
        return $this->state(fn (array $attributes) => [
            'application_id' => $application->id,
        ]);
    }

    /**
     * Indicate that the document was validated by a specific user.
     */
    public function validatedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'validated_by' => $user->id,
            'validated_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}
