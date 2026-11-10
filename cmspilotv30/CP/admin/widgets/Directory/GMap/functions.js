Util.createCPObject('cpw.directory.gMap');

cpw.directory.gMap = {
    currentLat:'',
    currentLng:'',
    init: function(){
       $('#saveLatLng').livequery('click', function(e){
           cpw.directory.gMap.saveLatLng.call(this, e);
       });
    },
    initMap: function(exp){

        var cpAction = $('#cpAction').val();
        if (cpAction == 'list') {
            return;
        }

        if(exp.lat == '' || exp.lng == ''){
            geoCodeAddress(exp);
            return;
        } else {
            cpw.directory.gMap.initialize(exp);
        }
    },
    initialize:function(){
        var handle  = exp.handle;
        var address = exp.address;
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
        var map = new google.maps.Map(document.getElementById(handle),
            mapOptions);

        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lng)
           ,draggable: true
           ,map: map
           ,title: address
        });

        // Update current position info.
        updateMarkerPosition(latLng);
        geocodePosition(latLng);

        // Add dragging event listeners.
        google.maps.event.addListener(marker, 'dragstart', function() {
            updateMarkerAddress('Dragging...');
        });

        google.maps.event.addListener(marker, 'drag', function() {
            updateMarkerStatus('Dragging...');
            updateMarkerPosition(marker.getPosition());
        });

        google.maps.event.addListener(marker, 'dragend', function() {
              updateMarkerStatus('Drag ended');
              geocodePosition(marker.getPosition());
        });
    },
    saveLatLng: function(e){
        e.preventDefault();
        var curLat = cpw.directory.gMap.currentLat;
        var curLng = cpw.directory.gMap.currentLng;

        var url = $(this).attr('href');
        if (url == '' || url == 'javascript:void(0)'){
            url = $(this).attr('link');
        }

        Util.showProgressInd();
        $.get(url, {lat: curLat, lng: curLng}, function(){
            //Util.hideProgressInd();
            document.location = document.location;
        })

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

            cpw.directory.gMap.initialize(exp);

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
    cpw.directory.gMap.currentLat = lat;
    cpw.directory.gMap.currentLng = lng;
}

function updateMarkerAddress(str) {
    document.getElementById('address').innerHTML = str;
}
