<div>

    <div x-data="(() => {
            let address = '', marker = null, map = null;
            function initMap() {
                // Initialize the map and marker
                map = new google.maps.Map(document.getElementById('map'), {
                    center: { lat: -34.397, lng: 150.644 },
                    zoom: 8
                });
                marker = new google.maps.Marker({
                    map: map,
                    position: { lat: -34.397, lng: 150.644 }
                });
            }
            function updateMarker() {
                // Use the Google Maps API to geocode the address and update the marker
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: address }, (results, status) => {
                    if (status === 'OK') {
                        marker.setPosition(results[0].geometry.location);
                        map.setCenter(results[0].geometry.location);
                    }
                });
            }
            function mapClicked() {
                // Get the lat and lng of the marker on the map
                const lat = marker.getPosition().lat();
                const lng = marker.getPosition().lng();
                // Use the Google Maps API to reverse geocode the lat and lng
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat: lat, lng: lng } }, (results, status) => {
                    if (status === 'OK') {
                        address = results[0].formatted_address;
                    }
                });
            }
            return { address, marker, map, initMap, updateMarker, mapClicked }
        })()"
    >
        <input type="text" x-model="address" x-on:input="updateMarker()">
        <div id="map" x-on:click="mapClicked()"></div>
    </div>
</div>
