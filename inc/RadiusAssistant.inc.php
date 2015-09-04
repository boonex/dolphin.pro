<?php

class RadiusAssistant
{
    var $maxLat;
    var $minLat;
    var $maxLong;
    var $minLong;

    function RadiusAssistant($Latitude, $Longitude, $Miles)
    {
        //global $maxLat, $minLat, $maxLong, $minLong;
        $EQUATOR_LAT_MILE = 69.172;
        $this->maxLat = $Latitude + $Miles / $EQUATOR_LAT_MILE;
        $this->minLat = $Latitude - ($this->maxLat - $Latitude);
        $this->maxLong = $Longitude + $Miles / (cos($minLat * M_PI / 180) * $EQUATOR_LAT_MILE);
        $this->minLong = $Longitude - ($this->maxLong - $Longitude);
    }

    function MaxLatitude()
    {
        //return $GLOBALS["maxLat"];
        return $this->maxLat;
    }
    function MinLatitude()
    {
        //return $GLOBALS["minLat"];
        return $this->minLat;
    }
    function MaxLongitude()
    {
        //return $GLOBALS["maxLong"];
        return $this->maxLong;
    }
    function MinLongitude()
    {
        //return $GLOBALS["minLong"];
        return $this->minLong;
    }

}
