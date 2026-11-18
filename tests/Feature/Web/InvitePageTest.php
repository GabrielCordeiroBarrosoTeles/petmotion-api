<?php

namespace Tests\Feature\Web;

use App\Models\PetInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitePageTest extends TestCase
{
    use RefreshDatabase;

    private function invite(array $attributes = []): PetInvite
    {
        $owner = User::where('email', 'dono@x.com')->first() ?? $this->makeUser(['name' => 'Gabriel Cordeiro', 'email' => 'dono@x.com']);
        $pet = $owner->pets()->first() ?? $this->makePet($owner, ['name' => 'Thor']);

        return PetInvite::create(array_merge([
            'pet_id' => $pet->id, 'code' => 'ABCD2345', 'created_by' => $owner->id, 'expires_at' => now()->addHours(48),
        ], $attributes));
    }

    public function test_invite_page_shows_pet_and_open_in_app_link(): void
    {
        $this->invite();

        $this->get('/convite/ABCD2345')
            ->assertOk()
            ->assertSee('Thor')
            ->assertSee('Gabriel')
            ->assertSee('petmotion://convite/ABCD2345', false)
            ->assertSee('Abrir no PetMotion')
            ->assertDontSee('Cordeiro')
            ->assertDontSee('dono@x.com');
    }

    public function test_invite_page_shows_pet_photo_when_there_is_one(): void
    {
        $invite = $this->invite();
        $invite->pet->update(['photo_path' => 'pets/thor.jpg']);

        $this->get('/convite/ABCD2345')->assertSee('storage/pets/thor.jpg', false);
    }

    public function test_invite_page_for_bad_code_says_expired(): void
    {
        $this->get('/convite/NAOEXIST')->assertOk()->assertSee('Esse convite não vale mais')->assertDontSee('petmotion://', false);
    }

    public function test_invite_page_for_used_or_expired_code_says_expired(): void
    {
        $this->invite(['code' => 'USADO234', 'accepted_by' => $this->makeUser()->id, 'accepted_at' => now()]);
        $this->invite(['code' => 'VELHO234', 'expires_at' => now()->subMinute()]);

        $this->get('/convite/USADO234')->assertOk()->assertSee('Esse convite não vale mais')->assertDontSee('Thor');
        $this->get('/convite/VELHO234')->assertOk()->assertSee('Esse convite não vale mais')->assertDontSee('Thor');
    }
}
