<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_the_privacy_policy_is_public_and_names_the_responsible_organization(): void
    {
        config([
            'littlestep.privacy.controller' => 'Barangay Balite Day Care Center',
            'littlestep.privacy.contact_email' => 'daycare@example.com',
            'littlestep.privacy.contact_phone' => '09170000000',
        ]);

        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Barangay Balite Day Care Center')
            ->assertSee('Data Privacy Act of 2012')
            ->assertSee('In simple words (for children)')
            ->assertSee('mailto:daycare@example.com', false)
            ->assertSee('09170000000')
            ->assertSee('privacy.gov.ph');
    }

    public function test_contact_lines_are_hidden_until_they_are_configured(): void
    {
        config(['littlestep.privacy.contact_email' => null, 'littlestep.privacy.contact_phone' => null]);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertDontSee('mailto:', false)
            ->assertSee("speak with your child's teacher", false);
    }

    public function test_the_sign_in_page_links_to_the_privacy_policy(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('privacy').'"', false);
    }
}
