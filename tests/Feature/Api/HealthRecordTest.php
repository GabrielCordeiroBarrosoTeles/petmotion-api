<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_records_newest_first(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);
        $this->makeHealth($pet, ['title' => 'Antiga', 'date' => '2025-01-01']);
        $this->makeHealth($pet, ['title' => 'Recente', 'date' => '2026-10-01']);

        $this->getJson("/api/pets/{$pet->id}/health-records", $this->bearer($me))
            ->assertOk()
            ->assertJsonPath('0.title', 'Recente')
            ->assertJsonPath('1.date', '2025-01-01');
    }

    public function test_store_creates_record(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);

        $this->postJson("/api/pets/{$pet->id}/health-records", [
            'type' => 'vermifuge', 'title' => 'Drontal', 'date' => '2026-11-01', 'next_date' => '2027-02-01', 'notes' => 'Lote 12',
        ], $this->bearer($me))->assertCreated()->assertJsonPath('next_date', '2027-02-01');

        $this->assertDatabaseHas('health_records', ['pet_id' => $pet->id, 'title' => 'Drontal']);
    }

    public function test_store_validates_type_and_next_date_after_date(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);

        $this->postJson("/api/pets/{$pet->id}/health-records", [
            'type' => 'banho', 'title' => 'X', 'date' => '2026-11-01', 'next_date' => '2026-10-01',
        ], $this->bearer($me))->assertUnprocessable()->assertJsonValidationErrors(['type', 'next_date']);
    }

    public function test_records_of_other_users_pet_are_not_found(): void
    {
        $pet = $this->makePet($this->makeUser());
        $record = $this->makeHealth($pet);
        $headers = $this->bearer($this->makeUser());

        $this->getJson("/api/pets/{$pet->id}/health-records", $headers)->assertNotFound();
        $this->postJson("/api/pets/{$pet->id}/health-records", ['type' => 'vet', 'title' => 'X', 'date' => '2026-11-01'], $headers)->assertNotFound();
        $this->deleteJson("/api/health-records/{$record->id}", [], $headers)->assertNotFound();
    }

    public function test_destroy_deletes_my_record(): void
    {
        $me = $this->makeUser();
        $record = $this->makeHealth($this->makePet($me));

        $this->deleteJson("/api/health-records/{$record->id}", [], $this->bearer($me))->assertNoContent();
        $this->assertDatabaseMissing('health_records', ['id' => $record->id]);
    }
}
