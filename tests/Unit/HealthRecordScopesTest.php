<?php

namespace Tests\Unit;

use App\Models\HealthRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthRecordScopesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 18)->setTime(0, 20));
    }

    public function test_a_dose_due_today_is_not_overdue(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeHealth($pet, ['title' => 'Ontem', 'next_date' => '2026-11-17']);
        $this->makeHealth($pet, ['title' => 'Hoje', 'next_date' => '2026-11-18']);
        $this->makeHealth($pet, ['title' => 'Sem data', 'next_date' => null]);

        $this->assertSame(['Ontem'], HealthRecord::overdue()->pluck('title')->all());
    }

    public function test_due_within_includes_today_and_the_last_day(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeHealth($pet, ['title' => 'Hoje', 'next_date' => '2026-11-18']);
        $this->makeHealth($pet, ['title' => 'Limite', 'next_date' => '2026-12-18']);
        $this->makeHealth($pet, ['title' => 'Depois', 'next_date' => '2026-12-19']);
        $this->makeHealth($pet, ['title' => 'Ontem', 'next_date' => '2026-11-17']);

        $this->assertSame(['Hoje', 'Limite'], HealthRecord::dueWithin(30)->orderBy('next_date')->pluck('title')->all());
    }

    public function test_due_since_includes_the_first_day(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeHealth($pet, ['title' => 'Exatos 7 dias', 'next_date' => '2026-11-11']);
        $this->makeHealth($pet, ['title' => '8 dias', 'next_date' => '2026-11-10']);

        $this->assertSame(['Exatos 7 dias'], HealthRecord::dueSince(7)->pluck('title')->all());
    }
}
