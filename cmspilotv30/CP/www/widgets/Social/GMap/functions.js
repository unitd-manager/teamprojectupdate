Util.createCPObject('cpw.social.gMap');

cpw.social.gMap = {
    currentLat:'',
    currentLng:'',
    init: function(){
       $('#saveLatLng').livequery('click', function(e){
           cpw.social.gMap.saveLatLng.call(this, e);
       });
    },
    initMap: function(exp){

        if(exp.lat == '' || exp.lng == ''){
            geoCodeAddress(exp);
            return;
        } else {
            cpw.social.gMap.initialize(exp);
        }
    },
    initialize:function(exp){
        var handle  = exp.handle;
        var address = exp.address;
        var addressDisplay = exp.addressDisplay;
        var lat  = exp.lat;
        var lng  = exp.lng;
        var centerLat = (exp.centerLat != '') ? exp.centerLat : lat;
        var centerLng = (exp.centerLng != '') ? exp.centerLng : lng;
        var zoom      = exp.zoom;

        var latLng = new google.maps.LatLng(centerLat, centerLng);
        var mapOptions = {
          center: latLng,
          zoom: zoom,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById(handle), mapOptions);

        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lng)
           ,draggable: true
           ,map: map
           ,title: address
        });

        infowindow = new google.maps.InfoWindow();

        google.maps.event.addListener(marker, 'click', function(e) {
            infowindow.setContent(addressDisplay);
            infowindow.setPosition(marker.position);
            infowindow.open(map)
        });
        
        google.maps.event.addListener(marker, 'click', function() { 
        });
    }
};

//https://code.google.com/p/gmaps-samples-v3/source/browse/trunk/draggable-markers/draggable-markers.html?spec=svn49&r=49
var geocoder = new google.maps.Geocoder();

function geocodePosition(pos) {
    geocoder.geocode({
        latLng: pos
    }, function(responses) {
        if (responses && responses.length > 0) {
            updateMarkerAddress(responses[0].formatted_address);
        } else {
            updateMarkerAddress('Cannot determine address at this location.');
        }
    });
}

function geoCodeAddress(exp) {

    geocoder.geocode( { 'address': exp.address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
            var location = results[0].geometry.location;

            exp.lat = location.lat().toString().substr(0, 12);
            exp.lng = location.lng().toString().substr(0, 12);

            cpw.social.gMap.initialize(exp);

      } else {
        updateMarkerAddress("Geocode was not successful for the following reason: " + status);
      }
    });
}

function updateMarkerStatus(str) {
    document.getElementById('markerStatus').innerHTML = str;
}

function updateMarkerPosition(latLng) {
    document.getElementById('info').innerHTML = [
        latLng.lat(),
        latLng.lng()
    ].join(', ');
    updateCurrentLatLng(latLng.lat(), latLng.lng());
}

function updateCurrentLatLng(lat, lng) {
    cpw.social.gMap.currentLat = lat;
    cpw.social.gMap.currentLng = lng;
}

function updateMarkerAddress(str) {
    //document.getElementById('address').innerHTML = str;
}
