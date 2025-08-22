<?php

namespace Tests\Unit\Mail\User;

use App\Mail\User\WelcomeMail;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(WelcomeMail::class)]
class WelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_subject(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $mailable = new WelcomeMail($user);

        $mailable->assertHasSubject('Welcome to TurkTicaret - Your E-Commerce Journey Begins!');
    }

    #[Test]
    public function it_uses_correct_view(): void
    {
        $user = User::factory()->create();
        $mailable = new WelcomeMail($user);

        $this->assertEquals('emails.user.welcome', $mailable->content()->view);
    }

    #[Test]
    public function it_contains_user_name_in_content(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        $mailable = new WelcomeMail($user);

        $mailable->assertSeeInHtml('Jane Smith');
        $mailable->assertSeeInHtml('Welcome to TurkTicaret, Jane Smith!');
    }

    #[Test]
    public function it_contains_expected_content(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $mailable = new WelcomeMail($user);

        $mailable->assertSeeInHtml('Welcome to TurkTicaret');
        $mailable->assertSeeInHtml('Thank you for joining our marketplace');
        $mailable->assertSeeInHtml('browse thousands of products');
        $mailable->assertSeeInHtml('The TurkTicaret Team');
    }

    #[Test]
    public function it_renders_without_errors(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $mailable = new WelcomeMail($user);

        $renderedContent = $mailable->render();

        $this->assertIsString($renderedContent);
        $this->assertNotEmpty($renderedContent);
        $this->assertStringContainsString('Test User', $renderedContent);
    }

    #[Test]
    public function it_has_user_property(): void
    {
        $user = User::factory()->create(['name' => 'Property Test']);
        $mailable = new WelcomeMail($user);

        $this->assertInstanceOf(User::class, $mailable->user);
        $this->assertEquals($user->id, $mailable->user->id);
        $this->assertEquals('Property Test', $mailable->user->name);
    }

    #[Test]
    public function it_contains_layout_elements(): void
    {
        $user = User::factory()->create();
        $mailable = new WelcomeMail($user);

        $mailable->assertSeeInHtml('TurkTicaret');
        $mailable->assertSeeInHtml('Your Trusted E-Commerce Platform');
    }

    #[Test]
    public function it_has_correct_envelope_configuration(): void
    {
        $user = User::factory()->create();
        $mailable = new WelcomeMail($user);

        $envelope = $mailable->envelope();
        
        $this->assertEquals('Welcome to TurkTicaret - Your E-Commerce Journey Begins!', $envelope->subject);
    }

    #[Test]
    public function it_has_no_attachments(): void
    {
        $user = User::factory()->create();
        $mailable = new WelcomeMail($user);

        $attachments = $mailable->attachments();
        
        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    #[Test]
    public function it_has_correct_content_configuration(): void
    {
        $user = User::factory()->create();
        $mailable = new WelcomeMail($user);

        $content = $mailable->content();
        
        $this->assertEquals('emails.user.welcome', $content->view);
    }
}