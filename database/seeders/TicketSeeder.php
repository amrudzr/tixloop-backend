<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coldplay = Event::where('event_name', 'Coldplay Jakarta')->firstOrFail();
        $dwp = Event::where('event_name', 'Djakarta Warehouse Project')->firstOrFail();
        $sports = Event::where('event_name', 'Indonesia vs Argentina')->firstOrFail();
        $seminar = Event::where('event_name', 'AI & Future Tech Summit')->firstOrFail();
        $taylor = Event::where('event_name', 'Taylor Swift Redux')->firstOrFail();

        $student = User::where('email', 'student.seller@tixloop.com')->firstOrFail();
        $enthusiast = User::where('email', 'music.enthusiast@tixloop.com')->firstOrFail();
        $hunter = User::where('email', 'concert.hunter@tixloop.com')->firstOrFail();
        $collector = User::where('email', 'event.collector@tixloop.com')->firstOrFail();

        $otherUsers = User::role(['seller', 'buyer'])
            ->whereNotIn('email', [$student->email, $enthusiast->email, $hunter->email, $collector->email])
            ->get();

        // Total 40 tickets:
        // - 25 aktif
        // - 10 digunakan
        // - 5 hangus

        // 1. Coldplay: 8 aktif
        Ticket::factory()->count(2)->aktif()->create([
            'event_id' => $coldplay->id,
            'current_owner_id' => $student->id,
            'ticket_code' => fn () => 'CP-STU-'.fake()->unique()->numerify('#####'),
        ]);
        Ticket::factory()->count(2)->aktif()->create([
            'event_id' => $coldplay->id,
            'current_owner_id' => $enthusiast->id,
            'ticket_code' => fn () => 'CP-ENT-'.fake()->unique()->numerify('#####'),
        ]);
        foreach (range(1, 4) as $i) {
            Ticket::factory()->aktif()->create([
                'event_id' => $coldplay->id,
                'current_owner_id' => $otherUsers->random()->id,
                'ticket_code' => 'CP-FAC-'.fake()->unique()->numerify('#####'),
            ]);
        }

        // 2. DWP: 8 aktif
        Ticket::factory()->count(2)->aktif()->create([
            'event_id' => $dwp->id,
            'current_owner_id' => $student->id,
            'ticket_code' => fn () => 'DWP-STU-'.fake()->unique()->numerify('#####'),
        ]);
        Ticket::factory()->count(2)->aktif()->create([
            'event_id' => $dwp->id,
            'current_owner_id' => $enthusiast->id,
            'ticket_code' => fn () => 'DWP-ENT-'.fake()->unique()->numerify('#####'),
        ]);
        foreach (range(1, 4) as $i) {
            Ticket::factory()->aktif()->create([
                'event_id' => $dwp->id,
                'current_owner_id' => $otherUsers->random()->id,
                'ticket_code' => 'DWP-FAC-'.fake()->unique()->numerify('#####'),
            ]);
        }

        // 3. Sports: 5 aktif
        Ticket::factory()->aktif()->create([
            'event_id' => $sports->id,
            'current_owner_id' => $student->id,
            'ticket_code' => 'SPO-STU-'.fake()->unique()->numerify('#####'),
        ]);
        Ticket::factory()->aktif()->create([
            'event_id' => $sports->id,
            'current_owner_id' => $enthusiast->id,
            'ticket_code' => 'SPO-ENT-'.fake()->unique()->numerify('#####'),
        ]);
        foreach (range(1, 3) as $i) {
            Ticket::factory()->aktif()->create([
                'event_id' => $sports->id,
                'current_owner_id' => $otherUsers->random()->id,
                'ticket_code' => 'SPO-FAC-'.fake()->unique()->numerify('#####'),
            ]);
        }

        // 4. Seminar: 4 aktif
        Ticket::factory()->aktif()->create([
            'event_id' => $seminar->id,
            'current_owner_id' => $enthusiast->id,
            'ticket_code' => 'SEM-ENT-'.fake()->unique()->numerify('#####'),
        ]);
        foreach (range(1, 3) as $i) {
            Ticket::factory()->aktif()->create([
                'event_id' => $seminar->id,
                'current_owner_id' => $otherUsers->random()->id,
                'ticket_code' => 'SEM-FAC-'.fake()->unique()->numerify('#####'),
            ]);
        }

        // 5. Taylor Swift: 10 digunakan, 5 hangus
        // 10 digunakan
        Ticket::factory()->count(2)->digunakan()->create([
            'event_id' => $taylor->id,
            'current_owner_id' => $hunter->id,
            'ticket_code' => fn () => 'TS-HUN-'.fake()->unique()->numerify('#####'),
        ]);
        Ticket::factory()->count(2)->digunakan()->create([
            'event_id' => $taylor->id,
            'current_owner_id' => $collector->id,
            'ticket_code' => fn () => 'TS-COL-'.fake()->unique()->numerify('#####'),
        ]);
        foreach (range(1, 6) as $i) {
            Ticket::factory()->digunakan()->create([
                'event_id' => $taylor->id,
                'current_owner_id' => $otherUsers->random()->id,
                'ticket_code' => 'TS-FAC-U-'.fake()->unique()->numerify('#####'),
            ]);
        }

        // 5 hangus
        Ticket::factory()->count(1)->hangus()->create([
            'event_id' => $taylor->id,
            'current_owner_id' => $student->id,
            'ticket_code' => fn () => 'TS-STU-B-'.fake()->unique()->numerify('#####'),
        ]);
        Ticket::factory()->count(1)->hangus()->create([
            'event_id' => $taylor->id,
            'current_owner_id' => $enthusiast->id,
            'ticket_code' => fn () => 'TS-ENT-B-'.fake()->unique()->numerify('#####'),
        ]);
        foreach (range(1, 3) as $i) {
            Ticket::factory()->hangus()->create([
                'event_id' => $taylor->id,
                'current_owner_id' => $otherUsers->random()->id,
                'ticket_code' => 'TS-FAC-B-'.fake()->unique()->numerify('#####'),
            ]);
        }
    }
}
