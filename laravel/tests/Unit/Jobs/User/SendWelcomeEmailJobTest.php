<?php

namespace Tests\Unit\Jobs\User;

use App\Jobs\User\SendWelcomeEmailJob;
use App\Mail\User\WelcomeMail;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(SendWelcomeEmailJob::class)]
class SendWelcomeEmailJobTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function it_can_be_dispatched(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        SendWelcomeEmailJob::dispatch($user);

        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    #[Test]
    public function it_sends_welcome_email_to_user(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $job = new SendWelcomeEmailJob($user);
        $job->handle();

        Mail::assertSent(WelcomeMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id &&
                   $mail->hasTo($user->email);
        });
    }

    #[Test]
    public function it_handles_execution_correctly(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $job = new SendWelcomeEmailJob($user);

        // Should not throw any exceptions
        $this->assertNull($job->handle());

        Mail::assertSentCount(1);
    }

    #[Test]
    public function it_has_correct_queue_interactions(): void
    {
        $user = User::factory()->create();
        $job = (new SendWelcomeEmailJob($user))->withFakeQueueInteractions();

        Mail::fake();
        $job->handle();

        $job->assertNotReleased();
        $job->assertNotDeleted();
        $job->assertNotFailed();
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        SendWelcomeEmailJob::dispatch($user);

        Queue::assertPushed(SendWelcomeEmailJob::class);
    }
}
