<?php

namespace App\Console\Commands;

use App\Models\Raceevent;
use App\Services\DogEventService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateEventStatusCommand extends Command
{
    public function __construct(DogEventService $dogEventService)
    {
        parent::__construct();
        $this->dogEventService = $dogEventService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:status-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dogEvents = Http::get('http://vseintegration.kironinteractive.com:8013/VseGameServer/DataService/UpcomingEvents?type=PreRecDogs');
        $xml = simplexml_load_string($dogEvents->body());
        $json = json_encode($xml->children());


        foreach (json_decode($json, true) as $items) {
            foreach ($items as $item) {
                $event = Raceevent::where('id', $item['@attributes']['ID'])->first();
                $event->eventStatus = $item['@attributes']['EventStatus'];
                $event->update();
            }
        }
    }
}
