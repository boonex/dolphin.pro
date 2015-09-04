<?php

$sLangCategory = 'ZIP Code Search';

$aLangContent = array(
    '_sys_module_zipcodesearch' => 'ZIP Search',
    '_bx_zip_administration' => 'ZIP Search',
    '_bx_zip_admin_menu' => 'ZIP Search',
    '_bx_zip' => 'ZIP Code Search',
    '_bx_zip_help' => 'Help',
    '_bx_zip_help_text' => '
<p>
Search powered by <b>Geonames</b> is a free geocoding service and the maximum search radius is always limited to 30km. 
To use it please <a href="http://www.geonames.org/login" target="_blank">register at Geonames website</a>, 
then <a href="http://www.geonames.org/manageaccount" target="_blank">enable free services in your Geonames account</a>, 
then enter your Geonames username here.
</p>
<hr />
<p>
Search powered by <b>Google</b> is working when "World Map" module from BoonEx is installed and profile positions are geocoded.
</p>
<hr />
<p>
To enable search by ZIP code in profiles\' search forms go to: <br />
<b>Dolphin admin panel -> Builders -> Profile Fields -> Search Profiles</b>.<br />
You need to drag "Country" and "Location" fields, at least. Please remove any other location related blocks like "zip code" and/or "city".
</p>
',
);
