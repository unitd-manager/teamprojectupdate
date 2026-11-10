<?
class CP_Www_Modules_Museum_Facility_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('fullcalendar-1.5.4', 'jqForm-3.15'); 

    /**
     *
     */
    function getList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');

        $rows = '';
        $count = 1;
        $pic ='';

        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'facility_id', array('subCatType'=>'Booking'));
            $title ="
            <div class='title'>
                <a href='{$url}'>{$ln->gfv($row, 'title')}</a>
            </div>
            ";

           $shortDesc = "
            <div class='shortDesc mt5'>
                {$ln->gfv($row, 'description_short')}
                <a href='{$url}'>{$ln->gd('More details')}</a>
            </div>
            ";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('museum_facility', 'picture', $row['facility_id'], $exp );

            $date = $fn->getCPDate($row['date']);

            if ($pic != ''){
                $rows .= "
                <div class='subcolumns facilityList'>
                    <div class='c75l'>
                        <div class='subcl' >
                            {$title}
                            <div class='date mt5'>{$date}</div>
                            {$shortDesc}
                        </div>
                    </div>
                    <div class='c25r'>
                        <div class='subcr'>
                            {$pic}
                        </div>
                    </div>
                </div>
                ";
            } else {
                $rows .= "
                <div class='eventList' >
                    {$title}
                    <div class='date mt5'>{$ln->gfv($row, 'date')}</div>
                    {$shortDesc}
                </div>
                ";
            }
            $count++;

        }

        $pgr = ($cpCfg['cp.isMobileDevice']) ? '' : $pager->getNavButtons(10);

        $text = "
        {$rows}
        {$pgr}
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

//        $title = "<h4>{$ln->gfv($row, 'title', '0')}<h4>";
        $exp = array('style' => 'mb5 pic float_right', 'folder' => 'normal', 'zoomImage' => true);
        if($cpCfg['cp.isMobileDevice']){
            $exp['limit'] = 1;
            $exp['style'] = 'mb5 pic';
            $exp['zoomImage'] = false;
        }
        $pic = $media->getMediaPicture('museum_facility', 'picture', $row['facility_id'], $exp);

        $bookingForm = "/index.php?widget=museum_booking&_spAction=bookingForm&showHTML=0";
        
        $rows .= "
        <div class='subcolumns facilityDetail'>
            <div class='c50l'>
                <div class='subcl' >
                    <header>
                        <h1 class='title'>{$ln->gfv($row, 'title')}</h1>
                    </header>
                    <div class='date mt10'>{$ln->gfv($row, 'date')}</div>
                    <div class= mt10>{$ln->gfv($row, 'description')}</div>
                    <div>
                        <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
                    </div>
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    <div class='picWrap'>
                        {$pic}
                    </div>
                </div>
            </div>
        </div>
        <div id='calendar'></div>
        <script>
            $(function(){
                cpm.museum.facility.setUpBookingCalendar({$row['facility_id']});
            });
        </script>                
        ";
 

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getSpecialExhibitionBooking($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

//        $title = "<h4>{$ln->gfv($row, 'title', '0')}<h4>";
        $exp = array('style' => 'mb5 pic float_right', 'folder' => 'normal', 'zoomImage' => true);
        if($cpCfg['cp.isMobileDevice']){
            $exp['limit'] = 1;
            $exp['style'] = 'mb5 pic';
            $exp['zoomImage'] = false;
        }
        $pic = $media->getMediaPicture('museum_facility', 'picture', $row['facility_id'], $exp);

        $wMuseumBooking = getCPWidgetObj('museum_booking');
        $bookingForm = $wMuseumBooking->view->getSpExhibitBookingForm($row['facility_id']);
        
        $rows .= "
        {$pic}
        <header>
            <h1 class='title'>{$ln->gfv($row, 'title')}</h1>
        </header>
        <div class='description'>
            {$ln->gfv($row, 'description')}
        </div> 
        {$bookingForm}     
        ";
 

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getVenueHireForm($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

//        $title = "<h4>{$ln->gfv($row, 'title', '0')}<h4>";
        $exp = array('style' => 'mb5 pic float_right', 'folder' => 'normal', 'zoomImage' => true);
        if($cpCfg['cp.isMobileDevice']){
            $exp['limit'] = 1;
            $exp['style'] = 'mb5 pic';
            $exp['zoomImage'] = false;
        }
        $pic = $media->getMediaPicture('museum_facility', 'picture', $row['facility_id'], $exp);

        $wMuseumBooking = getCPWidgetObj('museum_booking');
        $bookingForm = $wMuseumBooking->view->getVenueHireForm($row['facility_id']);
        
        $rows .= "
        {$pic}
        <header>
            <h1 class='title'>{$ln->gfv($row, 'title')}</h1>
        </header>
        <div class='description'>
            {$ln->gfv($row, 'description')}
        </div> 
        {$bookingForm}     
        ";
 

        $text = "
        {$rows}
        ";

        return $text;
    }

    
}
