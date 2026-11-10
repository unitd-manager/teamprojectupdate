Util.createCPObject('cpm.directory.area');

cpm.directory.area = {
    init: function(){
        $('#frmEdit select#fld_country_id').livequery('change', function(){
            Util.loadDropdownByJSON('country_id', $(this).val(), 'fld_state_id', 'directory_state');
        });

        $('#frmEdit select#fld_state_id').livequery('change', function(){
            Util.loadDropdownByJSON('state_id', $(this).val(), 'fld_city_id', 'directory_city');
        });
        $('#frmEdit select#fld_city_id').livequery('change', function(){
            Util.loadDropdownByJSON('city_id', $(this).val(), 'fld_borough_id', 'directory_borough');
        });

        var cpAction = $('#cpAction').val();
        if (cpAction == 'edit' || cpAction == 'detail') {
            var gMap = new cpm.directory.area.gMap();
            gMap.initialize();

            $('.delete-polygon').click(function(e){
                e.preventDefault();
                Util.confirm('Are you sure to delete it?', function() {
                    gMap.deletePolygon();
                });
            });
        }
    }
}

cpm.directory.area.gMap = function() {
    var self = this;
    this.drawingManager = null;
    this.selectedColor = '#FF1493';
    this.selectedShape = null;
    this.map = null;
    this.polygon = null;
    this.polygonOptions = null;

    this.initialize = function() {
        var latLngCentralHK        = new google.maps.LatLng(22.281944,114.158056);
        var latLngCentralSingapore = new google.maps.LatLng(1.3614898951040653, 103.84723663330078);
        this.map = new google.maps.Map(document.getElementById('map-canvas'), {
            zoom: 14,
            center: latLngCentralSingapore,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: true,
            zoomControl: true
        });

        self.polygonOptions = {
            fillColor: '#FF4D4D'
           ,strokeWeight: 1
           ,fillOpacity: 0.45
           ,editable: true
        };
        var latLngStr = $('#fld_latlng_coordinates').val();
        if (latLngStr != '') {
            var paths = this.getPolygonPaths(latLngStr);
            self.polygonOptions['paths'] = paths;
            self.polygonOptions['map'] = this.map;
            self.polygon = new google.maps.Polygon(self.polygonOptions);
            self.map.setCenter(self.getPolygonCenter(self.polygon));

            google.maps.event.addListener(self.polygon.getPath(), 'insert_at', function(e) {
                self.setPolygonPaths(self.polygon);
            });
            google.maps.event.addListener(self.polygon.getPath(), 'remove_at', function(e) {
                self.setPolygonPaths(self.polygon);
            });
            google.maps.event.addListener(self.polygon.getPath(), 'set_at', function(e) {
                self.setPolygonPaths(self.polygon);
            });

        } else {
            this.initializeDrawingManager();
        }

    }

    this.initializeDrawingManager = function() {
        var drawingMode = google.maps.drawing.OverlayType.POLYGON;
        // Creates a drawing manager attached to the map that allows the user to draw
        // markers, lines, and shapes.
        self.drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: drawingMode
            ,drawingControl: false
            ,polygonOptions: self.polygonOptions
            ,map: self.map
        });

        google.maps.event.addListener(this.drawingManager, 'polygoncomplete', function(polygon) {
            // Switch back to non-drawing mode after drawing a shape.
            self.drawingManager.setDrawingMode(null);
            self.setPolygonPaths(polygon);
            self.polygon = polygon;
        });
        google.maps.event.addListener(this.map, 'click', this.clearSelection);

    }

    this.clearSelection = function() {
        if (this.selectedShape) {
            this.selectedShape.setEditable(false);
            this.selectedShape = null;
        }
    }

    this.getPolygonPaths = function(latLngStr) {
        var latLngsStrArr = latLngStr.split('\n');
        var latLngsArr = new Array();
        for (var i in latLngsStrArr) {
            if (latLngsStrArr[i] == '') {
                continue;
            }
            var latLngStrArr = latLngsStrArr[i].split(',');
            latLngsArr[latLngsArr.length] = new google.maps.LatLng(latLngStrArr[0], latLngStrArr[1]);
        }

        return latLngsArr;
    }

    this.setPolygonPaths = function(newShape) {
            var path = newShape.getPath().getArray();
            var latLng = null;
            var latLngStr = '';
            for (var i in path) {
                latLng = path[i];
                latLngStr += latLng.lat() + ',' + latLng.lng() + '\n';
            }
            $('#fld_latlng_coordinates').val(latLngStr);
    }

    this.deletePolygon = function() {
        if (self.polygon) {
            self.polygon.setMap(null);
        }
        $('#fld_latlng_coordinates').val('');
        self.initializeDrawingManager();
    }

    this.getPolygonCenter = function(newShape) {
        var bounds = new google.maps.LatLngBounds();
        var paths = newShape.getPath().getArray();
        for (i = 0; i < paths.length; i++) {
            bounds.extend(paths[i]);
        }
        var polyCenter = bounds.getCenter();

        return polyCenter;
    }

    this.getCenterMap = function(newShape) {
        var bounds = new google.maps.LatLngBounds();
        var paths = newShape.getPath().getArray();
        for (i = 0; i < paths.length; i++) {
            bounds.extend(paths[i]);
        }
        var polyCenter = bounds.getCenter();

        return polyCenter;
    }
}

