<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Forecast;
use App\Models\Highlow;
use App\Models\Market;
use App\Models\Oddeven;
use App\Models\Raceevent;
use App\Models\Reverseforecast;
use App\Models\Reversetricast;
use App\Models\Selection;
use App\Models\Swinger;
use App\Models\Tricast;
use App\Models\Winandplace;
use Illuminate\Support\Facades\Http;


class DogEventService
{
    public function store()
    {
        $dogEvents = Http::get('http://vseintegration.kironinteractive.com:8013/VseGameServer/DataService/UpcomingEvents?type=PreRecDogs');
        $xml = simplexml_load_string($dogEvents->body());
        $local_time = json_decode(json_encode($xml), true)['@attributes']['LocalTime'];
        $json = json_encode($xml->children());


        foreach (json_decode($json, true) as $items) {
            foreach ($items as $item) {
                $event_id = $item['@attributes']['ID'];
                $forecast = Http::get('http://vseintegration.kironinteractive.com:8013/vsegameserver/dataservice/raceeventcombinationodds/' . $event_id . '');
                $forecast_xml = simplexml_load_string($forecast->body());
                $forecast_json = json_encode($forecast_xml->children());
                $forecast_item = json_decode($forecast_json, true)["Forecast"];
                $tricast_item = json_decode($forecast_json, true)["Tricast"];
                $reverseForecast_item = json_decode($forecast_json, true)["ReverseForecast"];
                $reverseTricast_item = json_decode($forecast_json, true)["ReverseTricast"];
                $swinger_item = json_decode($forecast_json, true)["Swinger"];
                $forecast_item = explode("|", $forecast_item);
                $tricast_item = explode("|", $tricast_item);
                $reverseForecast_item = explode("|", $reverseForecast_item);
                $reverseTricast_item = explode("|", $reverseTricast_item);
                $swinger_item = explode("|", $swinger_item);

                if (!Raceevent::where('id', $item['@attributes']['ID'])->first()) {
                    $race_event = new Raceevent();
                    $race_event->id = $item['@attributes']['ID'];
                    $race_event->eventType = $item['@attributes']['EventType'];
                    $race_event->eventNumber = $item['@attributes']['EventNumber'];
                    $race_event->eventTime = $item['@attributes']['EventTime'];
                    $race_event->finishTime = $item['@attributes']['FinishTime'];
                    $race_event->eventStatus = $item['@attributes']['EventStatus'];
                    $race_event->distance = $item['@attributes']['Distance'];
                    $race_event->name = $item['@attributes']['Name'];
                    $race_event->playsPaysOn = $item['@attributes']['PlacePaysOn'];
                    $race_event->localTime = $local_time;
                    $race_event->save();
                } else {
                    $editable_race_event = Raceevent::where('id', $item['@attributes']['ID'])->first();
                    $editable_race_event->delete();
                }
                foreach ($item['Entry'] as $entry) {
                    if (isset($entry)) {
                        if (!Entry::where('id', $entry['@attributes']['ID'])->first()) {
                            $new_entry = new Entry();
                            $new_entry->id = $entry['@attributes']['ID'];
                            $new_entry->draw = $entry['@attributes']['Draw'] ?? null;
                            $new_entry->name = $entry['@attributes']['Name'] ?? null;
                            $new_entry->event_id = $item['@attributes']['ID'];
                            $new_entry->event_number = $item['@attributes']['EventNumber'];
                            $new_entry->save();
                        } else {
                            $editable_entry = Entry::where('id', $entry['@attributes']['ID'])->first();
                            $editable_entry->delete();
                        }
                    }
                }
                foreach ($item['Market'] as $market) {
                    if (!Market::where('id', $market['@attributes']['ID'])->first()) {
                        $new_market = new Market();
                        $new_market->id = $market['@attributes']['ID'];
                        $new_market->save();
                    } else {
                        $editable_market = Market::where('id', $market['@attributes']['ID'])->first();
                        $editable_market->delete();
                    }
                        foreach ($market['Selection'] as $selection) {
                            if ($market['@attributes']['ID'] == "Win") {
                                $entry = Entry::where('event_number', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->where('draw', $selection['@attributes']['ID'])->first();
                                $winandplace = new Winandplace();
                                $winandplace->win_odd = $selection['@attributes']['Odds'] ?? null;
                                $winandplace->event_id = $item['@attributes']['ID'];
                                $winandplace->event_no = $item['@attributes']['EventNumber'];
                                $winandplace->draw = $selection['@attributes']['ID'];
                                if ($entry) {
                                    $winandplace->name = $entry->name;
                                }
                                $winandplace->save();
                            }
                            if ($market['@attributes']['ID'] == "Place") {
                            $winandplace = Winandplace::where('event_no', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->where('draw', $selection['@attributes']['ID'])->first();
                            $entry = Entry::where('event_number', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->where('draw', $selection['@attributes']['ID'])->first();
                            $winandplace->place_odd = $selection['@attributes']['Odds'] ?? null;
                            $winandplace->event_id = $item['@attributes']['ID'];
                            $winandplace->event_no = $item['@attributes']['EventNumber'];
                            $winandplace->draw = $selection['@attributes']['ID'];
                            if ($entry) {
                                $winandplace->name = $entry->name;
                            }
                            $winandplace->update();
                        }
                        }
//                    }

//                    }
                    if ($market['@attributes']['ID'] == "OE") {
                        if (!Oddeven::where('event_no', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->first()) {
                            $new_oddeven = new Oddeven();
                            foreach ($market['Selection'] as $selection) {
                                if ($selection['@attributes']['ID'] === "O") {
                                    $new_oddeven->o_odd = $selection['@attributes']['Odds'] ?? null;
                                }
                                if ($selection['@attributes']['ID'] === "E") {
                                    $new_oddeven->e_odd = $selection['@attributes']['Odds'] ?? null;
                                }
                                $new_oddeven->event_id = $item['@attributes']['ID'];
                                $new_oddeven->event_no = $item['@attributes']['EventNumber'];
                                $new_oddeven->save();
                            }
                        } else {
                            $editable_oddeven = Oddeven::where('event_no', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->first();
                             $editable_oddeven->delete();

                        }
                    }
                    if ($market['@attributes']['ID'] == "HL") {
                        if (!Highlow::where('event_no', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->first()) {
                            $new_highlow = new Highlow();
                            foreach ($market['Selection'] as $selection) {
                                if ($selection['@attributes']['ID'] === "H") {
                                    $new_highlow->h_odd = $selection['@attributes']['Odds'] ?? null;
                                }
                                if ($selection['@attributes']['ID'] === "L") {
                                    $new_highlow->l_odd = $selection['@attributes']['Odds'] ?? null;
                                }
                                $new_highlow->event_id = $item['@attributes']['ID'];
                                $new_highlow->event_no = $item['@attributes']['EventNumber'];
                                $new_highlow->save();
                            }
                        } else {
                            $editable_highlow = Highlow::where('event_no', $item['@attributes']['EventNumber'])->where('event_id', $item['@attributes']['ID'])->first();
                                $editable_highlow->delete();

                        }
                    }

                    foreach ($market['Selection'] as $selection) {
                        $new_selection = new Selection();
                        $new_selection->odds = $selection['@attributes']['Odds'] ?? null;
                        $new_selection->event_id = $item['@attributes']['ID'];
                        $new_selection->event_number = $item['@attributes']['EventNumber'];
                        $new_selection->market = $market['@attributes']['ID'];
                        $new_selection->selection_id = $selection['@attributes']['ID'];
                        $new_selection->save();
                    }

                    $forecast_items = $this->split_data($forecast_item);
                    foreach ($forecast_items as $forecast) {
                        if (!Forecast::where('odd', $forecast[1])->where('name', $forecast[0])->first()) {
                            $new_forecast = new Forecast();
                            $new_forecast->odd = $forecast[1];
                            $new_forecast->name = $forecast[0];
                            $new_forecast->event_no = $item['@attributes']['EventNumber'];
                            $new_forecast->event_id = $item['@attributes']['ID'];
                            $new_forecast->save();
                        } else {
                            $editable_forecast = Forecast::where('odd', $forecast[1])->where('name', $forecast[0])->first();
                            $editable_forecast->delete();
                        }
                    }
                    $tricast_items = $this->split_data($tricast_item);
                    foreach ($tricast_items as $tricast) {
                        if (!Tricast::where('odd', $tricast[1])->where('name', $tricast[0])->first()) {
                            $new_tricast = new Tricast();
                            $new_tricast->odd = $tricast[1];
                            $new_tricast->name = $tricast[0];
                            $new_tricast->event_no = $item['@attributes']['EventNumber'];
                            $new_tricast->event_id = $item['@attributes']['ID'];
                            $new_tricast->save();
                        } else {
                            $editable_tricast = Tricast::where('odd', $tricast[1])->where('name', $tricast[0])->first();
                            $editable_tricast->delete();
                        }
                    }
                    $reverseForecast_items = $this->split_data($reverseForecast_item);
                    foreach ($reverseForecast_items as $reverseForecast) {
                        if (!Reverseforecast::where('odd', $reverseForecast[1])->where('name', $reverseForecast[0])->first()) {
                            $new_reversed_forecast = new Reverseforecast();
                            $new_reversed_forecast->odd = $reverseForecast[1];
                            $new_reversed_forecast->name = $reverseForecast[0];
                            $new_reversed_forecast->event_no = $item['@attributes']['EventNumber'];
                            $new_reversed_forecast->event_id = $item['@attributes']['ID'];
                            $new_reversed_forecast->save();
                        } else {
                            $editable_reversed_forecast = Reverseforecast::where('odd', $reverseForecast[1])->where('name', $reverseForecast[0])->first();
                            $editable_reversed_forecast->delete();
                        }
                    }
                    $reverseTricast_items = $this->split_data($reverseTricast_item);
                    foreach ($reverseTricast_items as $reverseTricast) {
                        if (!Reversetricast::where('odd', $reverseTricast[1])->where('name', $reverseTricast[0])->first()) {
                            $new_reversed_tricast = new Reversetricast();
                            $new_reversed_tricast->odd = $reverseTricast[1];
                            $new_reversed_tricast->name = $reverseTricast[0];
                            $new_reversed_tricast->event_no = $item['@attributes']['EventNumber'];
                            $new_reversed_tricast->event_id = $item['@attributes']['ID'];
                            $new_reversed_tricast->save();
                        } else {
                            $editable_reversed_tricast = Reversetricast::where('odd', $reverseTricast[1])->where('name', $reverseTricast[0])->first();
                            $editable_reversed_tricast->delete();
                        }
                    }
                    $swinger_items = $this->split_data($swinger_item);
                    foreach ($swinger_items as $swinger) {
                        if (!Swinger::where('odd', $swinger[1])->where('name', $swinger[0])->first()) {
                            $new_swinger = new Swinger();
                            $new_swinger->odd = $swinger[1];
                            $new_swinger->name = $swinger[0];
                            $new_swinger->event_no = $item['@attributes']['EventNumber'];
                            $new_swinger->event_id = $item['@attributes']['ID'];
                            $new_swinger->save();
                        } else {
                            $editable_swinger = Swinger::where('odd', $swinger[1])->where('name', $swinger[0])->first();
                            $editable_swinger->delete();
                        }
                    }
                }
            }
        }
    }
    public function split_data($string) {
        $forecast_items = [];
        $new_item = [];
        foreach ($string as $i) {
            if (count($new_item) === 2) {
                $forecast_items []= $new_item;
                $new_item = [];
            } else {
                $new_item []= $i;
            }
        }
        return $forecast_items;
    }
}

