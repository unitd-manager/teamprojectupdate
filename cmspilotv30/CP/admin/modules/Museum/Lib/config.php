<?
$cpCfg = array();

//------------- Collection -------------//
$cpCfg['m.museum.collection.showMetaData'] = 1;
$cpCfg['m.museum.collection.btnPosArr'] = array (
	 'Published'
	,'Not-Published'
	,'Latest'
	,'Flag'
);

//------------- Library -------------//
$cpCfg['m.museum.library.showMetaData'] = 1;
$cpCfg['m.museum.library.btnPosArr'] = array (
	 'Published'
	,'Not-Published'
	,'Latest'
	,'Flag'
);

//------------- Booking -------------//
$cpCfg['m.museum.booking.availabilityArr'] = array (
        'notAvailable' => 'Not Available'
	,'pending' => 'Pending'
	,'confirmed' => 'Confirmed'
	,'cancelled' => 'Cancelled'
);

//------------- Facility -------------//
$cpCfg['m.museum.facility.dayArr'] = array (
     'All'  => 'All'
    ,'Mon'  => 'Mon'
    ,'Tue'  => 'Tue'
    ,'Wed'  => 'Wed'
    ,'Thu'  => 'Thu'
    ,'Fri'  => 'Fri'
    ,'Sat'  => 'Sat'
    ,'Thu'  => 'Thu'
    ,'Sun'  => 'Sun'
    ,'Date Range' => 'Date Range'
);

$cpCfg['m.museum.facility.availArr'] = array (
     'open'         => 'Open'
    ,'semiOpen'     => 'Semi Open'
    ,'limited'      => 'Limited'
    ,'notAvailable' => 'Not Available'
);


return $cpCfg;
