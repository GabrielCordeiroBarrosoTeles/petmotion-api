<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    // Centro do trajeto demo (Parque Ibirapuera, SP)
    private const LAT = -23.5874;

    private const LNG = -46.6576;

    public function run(): void
    {
        // Em produção, SEED_ADMIN_PASSWORD (infra/.env) troca a senha das contas com acesso ao monitor.
        $adminPassword = env('SEED_ADMIN_PASSWORD');

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@petmotion.app', 'password' => $adminPassword ?: 'petmotion']);
        $admin->forceFill(['is_admin' => true])->save();

        // Acesso pedido pelo Gabriel: suporte (monitor)
        User::create(['name' => 'Cordeiro Suporte', 'email' => 'cordeiro@suporte.com', 'password' => $adminPassword ?: '12345678'])
            ->forceFill(['is_admin' => true])->save();

        $tutors = [
            ['Gabriel Cordeiro', 'gabriel@petmotion.app', [['Thor', 'dog', 'Golden Retriever', 32.4, 40, 'seed/thor.jpg'], ['Mel', 'dog', 'Vira-lata caramelo', 14.1, 30, 'seed/mel.jpg']]],
            ['Ana Souza', 'ana@petmotion.app', [['Pipoca', 'dog', 'Shih-tzu', 6.2, 20, null]]],
            ['Lucas Lima', 'lucas@petmotion.app', [['Bidu', 'dog', 'Beagle', 11.8, 35, null], ['Nina', 'cat', 'Siamês', 4.0, 10, null]]],
        ];

        $thor = null;

        foreach ($tutors as [$name, $email, $pets]) {
            $user = User::create(['name' => $name, 'email' => $email, 'password' => 'petmotion']);
            if ($email === 'gabriel@petmotion.app') {
                // conta demo completa: da Matilha e com foto (rosto gerado por IA, não é uma pessoa real)
                $user->forceFill([
                    'premium_until' => now()->addDays(30),
                    'trial_started_at' => now()->subDays(10),
                    'photo_path' => 'seed/gabriel.jpg',
                ])->save();
            }

            foreach ($pets as [$petName, $species, $breed, $weight, $goal, $photo]) {
                $pet = $user->pets()->create([
                    'name' => $petName,
                    'species' => $species,
                    'breed' => $breed,
                    'birth_date' => now()->subMonths(rand(10, 90))->toDateString(),
                    'weight_kg' => $weight,
                    'daily_goal_minutes' => $goal,
                    'photo_path' => $photo,
                ]);

                if ($petName === 'Thor') {
                    $thor = $pet;
                }

                $this->seedHealth($pet);

                if ($species === 'dog') {
                    $this->seedWalks($user, $pet);
                }
            }
        }

        $this->seedWalker($thor);
    }

    /** Passeador demo: João passeia com o Thor do Gabriel e já fez um passeio hoje. */
    private function seedWalker(Pet $thor): void
    {
        $joao = User::create(['name' => 'João Passeador', 'email' => 'joao@passeador.app', 'password' => 'petmotion']);
        $thor->walkers()->attach($joao->id);

        $start = now()->subMinutes(50);
        $points = $this->route(20);
        $distance = $this->distance($points);

        $walk = $joao->walks()->create([
            'pet_id' => $thor->id,
            'started_at' => $start,
            'ended_at' => $start->copy()->addMinutes(20),
            'duration_seconds' => 20 * 60,
            'distance_meters' => $distance,
            'steps' => (int) ($distance * 1.35),
            'temperature_c' => 24,
            'points' => $points,
        ]);

        [$lat, $lng] = $points[intdiv(count($points), 2)];
        $walk->events()->create(['type' => 'pee', 'lat' => $lat, 'lng' => $lng, 'occurred_at' => $start->copy()->addMinutes(8)]);
    }

    private function seedWalks(User $user, Pet $pet): void
    {
        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            // Pula alguns dias antigos para a sequência ficar realista
            if ($daysAgo > 6 && rand(0, 3) === 0) {
                continue;
            }

            foreach (range(1, rand(1, 2)) as $i) {
                $start = Carbon::today()->subDays($daysAgo)->setTime($i === 1 ? rand(6, 9) : rand(18, 21), rand(0, 59));
                $minutes = rand(15, 50);
                if ($start->copy()->addMinutes($minutes)->isFuture()) {
                    continue;
                }
                $points = $this->route($minutes);
                $distance = $this->distance($points);

                $walk = $user->walks()->create([
                    'pet_id' => $pet->id,
                    'started_at' => $start,
                    'ended_at' => $start->copy()->addMinutes($minutes),
                    'duration_seconds' => $minutes * 60 + rand(0, 59),
                    'distance_meters' => $distance,
                    'steps' => (int) ($distance * 1.35),
                    'temperature_c' => rand(17, 31),
                    'points' => $points,
                ]);

                foreach (['pee', 'pee', 'poop', 'water'] as $type) {
                    if (rand(0, 4) === 0) {
                        continue;
                    }
                    [$lat, $lng] = $points[array_rand($points)];
                    $walk->events()->create([
                        'type' => $type,
                        'lat' => $lat,
                        'lng' => $lng,
                        'occurred_at' => $start->copy()->addMinutes(rand(1, $minutes)),
                    ]);
                }
            }
        }
    }

    /** Laço irregular em volta do parque, ~80 m por minuto. */
    private function route(int $minutes): array
    {
        $count = max(12, $minutes * 2);
        $radius = 0.0015 + $minutes * 0.00009;
        $offset = rand(0, 628) / 100;
        $points = [];

        for ($i = 0; $i <= $count; $i++) {
            $t = $offset + ($i / $count) * 2 * M_PI;
            $wobble = 1 + sin($t * 3) * 0.18 + (rand(-30, 30) / 1000);
            $points[] = [
                round(self::LAT + sin($t) * $radius * $wobble, 6),
                round(self::LNG + cos($t) * $radius * 1.3 * $wobble, 6),
            ];
        }

        return $points;
    }

    private function distance(array $points): int
    {
        $total = 0;
        for ($i = 1; $i < count($points); $i++) {
            [$lat1, $lng1] = $points[$i - 1];
            [$lat2, $lng2] = $points[$i];
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);
            $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
            $total += 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
        }

        return (int) $total;
    }

    private function seedHealth(Pet $pet): void
    {
        $pet->healthRecords()->createMany([
            ['type' => 'vaccine', 'title' => 'V10 (polivalente)', 'date' => now()->subMonths(11)->toDateString(), 'next_date' => now()->addDays(rand(2, 20))->toDateString()],
            ['type' => 'vaccine', 'title' => 'Antirrábica', 'date' => now()->subMonths(4)->toDateString(), 'next_date' => now()->addMonths(8)->toDateString()],
            ['type' => 'vermifuge', 'title' => 'Drontal Plus', 'date' => now()->subMonths(3)->toDateString(), 'next_date' => now()->addDays(rand(1, 6))->toDateString()],
            ['type' => 'vet', 'title' => 'Check-up anual', 'date' => now()->subMonths(2)->toDateString(), 'notes' => 'Tudo ótimo. Manter peso.'],
        ]);
    }
}
