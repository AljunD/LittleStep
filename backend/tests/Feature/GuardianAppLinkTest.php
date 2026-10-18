<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuardianAppLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->user = User::factory()->create([
            'email' => 'guardian@example.com',
            'role' => 'guardian',
            'email_verified_at' => null,
        ]);
    }

    private function verificationUrl(): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $this->user->id,
            'hash' => sha1($this->user->getEmailForVerification()),
        ]);
    }

    public function test_verifying_opens_the_app_login_screen_without_sending_any_credentials(): void
    {
        config(['littlestep.guardian_app_login_url' => 'littlestep://login']);

        $page = $this->followingRedirects()->get($this->verificationUrl());

        $this->assertNotNull($this->user->fresh()->email_verified_at);
        $page->assertOk()
            ->assertSee('href="littlestep://login?verified=1"', false)
            ->assertSee('Open LittleStep App')
            ->assertSee('window.location.href = link.href', false);
        $this->assertMatchesRegularExpression('/<script nonce="[^"]+">/', $page->getContent());

        $this->assertStringNotContainsString('guardian@example.com', $page->getContent());
        $this->assertStringNotContainsString('token=', $page->getContent());

        $this->get(route('guardian.verification.confirmation'))
            ->assertOk()
            ->assertSee('href="littlestep://login?verified=1"', false)
            ->assertDontSee('window.location.href = link.href', false);
    }

    public function test_expo_go_links_keep_their_path_and_existing_query(): void
    {
        config(['littlestep.guardian_app_login_url' => 'exp://192.168.1.5:8081/--/login']);
        $this->get(route('guardian.verification.confirmation'))
            ->assertSee('href="exp://192.168.1.5:8081/--/login?verified=1"', false);

        config(['littlestep.guardian_app_login_url' => 'https://app.example.com/login?source=email']);
        $this->get(route('guardian.verification.confirmation'))
            ->assertSee('href="https://app.example.com/login?source=email&amp;verified=1"', false);
    }

    public function test_unsafe_or_missing_app_links_are_never_rendered(): void
    {
        foreach ([
            'javascript:alert(1)',
            'data:text/html,hi',
            'http://evil.example/login',
            'https://user:secret@app.example.com/login',
            'littlestep://login#token',
            '//no-scheme/login',
            '',
        ] as $unsafe) {
            config(['littlestep.guardian_app_login_url' => $unsafe]);

            $this->withSession(['status' => 'Your email has been verified successfully.'])
                ->get(route('guardian.verification.confirmation'))
                ->assertOk()
                ->assertSee('Welcome to LittleStep!')
                ->assertDontSee('Open LittleStep App')
                ->assertDontSee('window.location.href', false);
        }
    }

    public function test_verification_emails_link_to_app_url_even_when_the_teacher_uses_localhost(): void
    {
        config(['app.url' => 'http://192.168.100.19/LittleStep/backend/public']);
        URL::forceRootUrl('http://localhost:8000');

        $mail = (new CustomVerifyEmail)->toMail($this->user);
        URL::forceRootUrl(null);
        $link = $mail->viewData['verificationUrl'];

        $this->assertStringStartsWith('http://192.168.100.19/LittleStep/backend/public/email/verify/'.$this->user->id.'/', $link);
        $this->assertTrue(URL::hasValidSignature(Request::create($link)));
        $this->assertStringStartsWith('http://localhost/', route('login'));
    }

    public function test_a_tampered_verification_link_neither_verifies_nor_opens_the_app(): void
    {
        $this->get($this->verificationUrl().'tampered')->assertForbidden();

        $this->assertNull($this->user->fresh()->email_verified_at);
    }
}
