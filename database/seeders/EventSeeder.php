<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Coldplay Jakarta - concert, 12h in the future
        Event::create([
            'event_name' => 'Coldplay Jakarta',
            'event_category' => 'concert',
            'event_datetime' => now()->addHours(12),
            'venue_name' => 'GBK Senayan',
            'city' => 'Jakarta',
            'event_poster_url' => 'https://picsum.photos/400/600',
        ]);

        // 2. Djakarta Warehouse Project - festival, 36h in the future
        Event::create([
            'event_name' => 'Djakarta Warehouse Project',
            'event_category' => 'festival',
            'event_datetime' => now()->addHours(36),
            'venue_name' => 'JIExpo Kemayoran',
            'city' => 'Jakarta',
            'event_poster_url' => 'https://picsum.photos/400/600',
        ]);

        // 3. Indonesia vs Argentina - sports, 5 days in the future
        Event::create([
            'event_name' => 'Indonesia vs Argentina',
            'event_category' => 'sports',
            'event_datetime' => now()->addDays(5),
            'venue_name' => 'GBK Senayan',
            'city' => 'Jakarta',
            'event_poster_url' => 'https://picsum.photos/400/600',
        ]);

        // 4. AI & Future Tech Summit - seminar, 14 days in the future
        Event::create([
            'event_name' => 'AI & Future Tech Summit',
            'event_category' => 'seminar',
            'event_datetime' => now()->addDays(14),
            'venue_name' => 'Trans Convention Centre',
            'city' => 'Bandung',
            'event_poster_url' => 'https://picsum.photos/400/600',
        ]);

        // 5. Taylor Swift Redux - concert, 2 days in the past (expired)
        Event::create([
            'event_name' => 'Taylor Swift Redux',
            'event_category' => 'concert',
            'event_datetime' => now()->subDays(2),
            'venue_name' => 'ICE BSD',
            'city' => 'Tangerang',
            'event_poster_url' => 'https://picsum.photos/400/600',
        ]);
    }
}
