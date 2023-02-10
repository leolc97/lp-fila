<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Map extends Component
{
    public $address = '';
    public $marker;

    protected $listeners = [
        'initMap' => 'initMap',
        'updateMarker' => 'updateMarker',
        'mapClicked' => 'mapClicked'
    ];

    public function initMap()
    {
        // Initialize the map and marker
        $this->marker = [
            'lat' => -34.397,
            'lng' => 150.644
        ];
    }

    public function updateMarker($address)
    {
        // Use the Google Maps API to geocode the address and update the marker
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . env('GOOGLE_MAPS_API_KEY');
        $response = json_decode(file_get_contents($url), true);
        if ($response['status'] === 'OK') {
            $this->marker = [
                'lat' => $response['results'][0]['geometry']['location']['lat'],
                'lng' => $response['results'][0]['geometry']['location']['lng']
            ];
        }
    }

    public function mapClicked($event)
    {
        $lat = $event['latLng']['lat'];
        $lng = $event['latLng']['lng'];
        // Use the Google Maps API to reverse geocode the lat and lng
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&key=' . env('GOOGLE_MAPS_API_KEY');
        $response = json_decode(file_get_contents($url), true);
        if ($response['status'] === 'OK') {
            $this->address = $response['results'][0]['formatted_address'];
        }
    }

    public function render()
    {
        return view('livewire.map');
    }
}
