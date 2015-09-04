
function BxWmap (map, sGetDataUrl, sInstanceName, sElementId) {
    this._map = map;
    this._sInstanceName = sInstanceName;
    this._sGetDataUrl = sGetDataUrl;
    this._sSaveDataUrl = false;
    this._sSaveLocationUrl = false;
    this._sShadowUrl = false;
    this._sElementId = sElementId;
    this._sParts = '';
    this._sCustom = '';
    this._oMarkers = new Object();
    this._aOverlays = [];
    this._tt = '' + new Date();
}

BxWmap.prototype.updateLocations = function () {

    this.onStartLoadMap ();    

    var $this = this;
    var zoom = this._map.getZoom();
    var center = this._map.getCenter();
	var bounds = this._map.getBounds();
	var southWest = bounds.getSouthWest();
	var northEast = bounds.getNorthEast();    
	var span = bounds.toSpan();    

    var sUrl = this._sGetDataUrl;
    sUrl = sUrl.replace('{zoom}', zoom);
    sUrl = sUrl.replace('{instance}', this._sInstanceName);
    sUrl = sUrl.replace('{lat_min}', southWest.lat());
    sUrl = sUrl.replace('{lat_max}', southWest.lat()+span.lat());
    sUrl = sUrl.replace('{lng_min}', southWest.lng());
    sUrl = sUrl.replace('{lng_max}', southWest.lng()+span.lng());
    sUrl = sUrl.replace('{lat_center}', center.lat());
    sUrl = sUrl.replace('{lng_center}', center.lng());
    sUrl = sUrl.replace('{parts}', this._sParts);
    sUrl = sUrl.replace('{custom}', this._sCustom);
    sUrl = sUrl.replace('{ts}', (new Date()).getTime());

    this.loading(1);
	$.getJSON(sUrl, function(data) {        

        $this._oMarkers = null;
        $this._oMarkers = new Object();
        $this.clearOverlays();

        var l = ('object' == typeof data && null != data) ? data.length : 0;

        for (var i=0 ; i < l ; ++i ) {

            var o = data[i];

            var fLat = parseFloat(o.lat);
            var fLng = parseFloat(o.lng);

            var point = new google.maps.LatLng(fLat, fLng);
            var marker = $this.createMarker(point, o.data, o.icon.url, {w:o.icon.w, h:o.icon.h});

            if (o.usernames === undefined)
                continue;
            var ll = o.usernames.length;
            for (var ii=0 ; ii < ll ; ++ii )                 
                $this._oMarkers[o.usernames[ii]] = marker;

         }

        $this.onLoadMap ();
        $this.loading(0);
	});
}


BxWmap.prototype.loading = function (b) {

    if (!this._sElementId) 
        return;

    bx_map_loading (this._sElementId, b);
}

BxWmap.prototype.createMarker = function (point, html, image, options) {  
    var marker;

    if (image) {
        var markerIcon = new google.maps.MarkerImage(
                image, 
                // image size
                new google.maps.Size(options.w, options.h), 
                // origin for this image within a sprite, if any
                new google.maps.Point(0, 0), 
                // anchor for this image
                new google.maps.Point(options.w/2, options.h/2)
        );
        
        marker = new google.maps.Marker({
            map: this._map,
            position: point,
            icon: markerIcon
        });
    } else {
    	marker = new google.maps.Marker({
            map: this._map,
            position: point,
        });
    }

    var $this = this;
	if (html && html.length) {        
	    google.maps.event.addListener(marker, "click", function() {

            if (typeof($this._oInfoWindow) != 'undefined')
                $this._oInfoWindow.close();
            $this._oInfoWindow = new google.maps.InfoWindow({
                content: html,
                maxWidth: 300
            });
            $this._oInfoWindow.open($this._map, marker);

        });
    }

    this.addOverlay(marker);

    return marker;
}

BxWmap.prototype.magnify = function (fLat, fLng, iZoom) {
    this._map.setCenter(new google.maps.LatLng(fLat, fLng), iZoom);
}

BxWmap.prototype.setShadowUrl = function (sUrl) {
    this._sShadowUrl = sUrl;
}

BxWmap.prototype.setSaveLocationUrl = function (sUrl) {
    this._sSaveLocationUrl = sUrl;
}

BxWmap.prototype.setSaveDataUrl = function (sUrl) {

    this._sSaveDataUrl = sUrl;

    if (false == sUrl || '' == sUrl) {

		google.maps.event.clearListeners(this._map, "click");
		google.maps.event.clearListeners(this._map, "zoomend");
		google.maps.event.clearListeners(this._map, "maptypechanged");

    } else {

    	var $this = this;

		var hh = function(ev) {
			var sMapType = 'normal';
			switch ($this._map.getMapTypeId())
			{
				case google.maps.MapTypeId.SATELLITE: sMapType = 'satellite'; break;
				case google.maps.MapTypeId.HYBRID: sMapType = 'hybrid'; break;
                case google.maps.MapTypeId.TERRAIN: sMapType = 'terrain'; break;
			};
            $this.saveData ('null', 'null', $this._map.getZoom(), sMapType);
		};

		var h = function(ev) {
            if ('undefined' == typeof(ev.latLng)) 
                return;
            $this.saveData (ev.latLng.lat(), ev.latLng.lng(), 'null', 'null');
		    $this.clearOverlays();
			$this.createMarker(ev.latLng);
		};

		google.maps.event.addListener(this._map, "click", h);
		google.maps.event.addListener(this._map, "zoom_changed", hh);
		google.maps.event.addListener(this._map, "maptypeid_changed", hh);
    }
}

BxWmap.prototype.saveData = function (fLat, fLng, iZoom, sMapType) { 

    var $this = this;
    var sUrl = this._sSaveDataUrl;
    sUrl = sUrl.replace('{zoom}', iZoom);
    sUrl = sUrl.replace('{map_type}', sMapType);
    sUrl = sUrl.replace('{lat}', fLat);
    sUrl = sUrl.replace('{lng}', fLng);
    sUrl = sUrl.replace('{instance}', this._sInstanceName);
    sUrl = sUrl.replace('{parts}', this._sParts);
    sUrl = sUrl.replace('{ts}', (new Date()).getTime());

    if ('null' == fLat || 'null' == fLng) {

        sUrl = sUrl.replace('{address}', 'null');
        sUrl = sUrl.replace('{country}', 'null');
        this.loading(1);
        $.get(sUrl, function(responseText) {
            $this.loading(0);
        });

    } else {

        var geocoder = new google.maps.Geocoder();
        var $this = this;    
        this.loading(1);    
        geocoder.geocode({ 'latLng': new google.maps.LatLng(fLat, fLng) }, function(results, status) {

            var sAddress = 'null';
            var sCountry = 'null';

            if (status == google.maps.GeocoderStatus.OK) {                

                for (var i in results) {
                    var place = results[i];

                    // get address
                    if ('null' == sAddress) {
                        for (var t in place.types) {
                            if ('political' == place.types[t]) {
                                sAddress = place.formatted_address;
                                break;
                            }
                        }
                    }

                    // get country
                    if ('null' == sCountry) {
                        for (var t in place.types) {
                            if ('country' == place.types[t]) {
                                sCountry = place.address_components[0].short_name;
                                break;
                            }
                        }
                    }

                    if ('null' != sAddress && 'null' != sCountry)
                        break;
                }
            }

            sUrl = sUrl.replace('{address}', sAddress);
            sUrl = sUrl.replace('{country}', sCountry);
            $.get(sUrl, function(responseText) {
                $this.onUpdateAddress (sAddress, sCountry);
                $this.loading(0);
            });
        
        });

    }
}

BxWmap.prototype.saveLocation = function () {

    if (!this._sSaveLocationUrl || '' == this._sSaveLocationUrl)
        return false;

    var sUrl = this._sSaveLocationUrl;
    var sMapType = 'normal';

	switch (this._map.getMapTypeId())	{
		case google.maps.MapTypeId.SATELLITE: sMapType = 'satellite'; break;
		case google.maps.MapTypeId.HYBRID: sMapType = 'hybrid'; break;
        case google.maps.MapTypeId.TERRAIN: sMapType = 'terrain'; break;
	};        
    sUrl = sUrl.replace('{zoom}', this._map.getZoom());
    sUrl = sUrl.replace('{map_type}', sMapType);
    sUrl = sUrl.replace('{lat}', this._map.getCenter().lat());
    sUrl = sUrl.replace('{lng}', this._map.getCenter().lng());
    sUrl = sUrl.replace('{instance}', this._sInstanceName);
    sUrl = sUrl.replace('{parts}', this._sParts);
    sUrl = sUrl.replace('{ts}', (new Date()).getTime());

    this.loading(1);
    var $this = this;    
        $.get(sUrl, function(responseText) {
            $this.loading(0);
            if ('ok' == responseText)
                alert ('Location has been saved');
            else
                alert ('Location saving failed');
        });
    return false;
}

BxWmap.prototype.onStartLoadMap = function () {
    this._isMapLoaded = 0;
    if (window.glBxWmapLocationsMapOnStartLoadCallback === undefined) 
        return;
    glBxWmapLocationsMapOnStartLoadCallback(this);
}

BxWmap.prototype.onLoadMap = function () {
    this._isMapLoaded = 1;
    if (window.glBxWmapLocationsMapOnLoadCallback === undefined) 
        return;
    glBxWmapLocationsMapOnLoadCallback(this);
}

BxWmap.prototype.onUpdateAddress = function (sAddress, sCountry) {
    $('#bx_map_curr_loc').html (sAddress); 
}

BxWmap.prototype.setParts = function (s) {
    this._sParts = s;
}

BxWmap.prototype.setCustom = function (s) {
    this._sCustom = s;
}

BxWmap.prototype.addOverlay = function (o) {
    this._aOverlays[this._aOverlays.length] = o;
}

BxWmap.prototype.clearOverlays = function () {
    while(this._aOverlays[0])
        this._aOverlays.pop().setMap(null);
}

function bx_map_loading (sId, b) {
    bx_loading(sId, b);
}
