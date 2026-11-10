<?
class CP_Www_Modules_Event_Event_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        /** if there is a hook for event in the theme level then use that **/
        $theme = getCPThemeObj($cpCfg['cp.theme']);

        if (method_exists($theme->fns, 'getEventListHook')){
            return $theme->fns->getEventListHook($dataArray);
        }

        $rows = '';
        $count = 1;
        $pic ='';

        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'event_id', array('secType'=>'Event'));
            $title ="
            <div class='title'>
                <a href='{$url}'>{$ln->gfv($row, 'title')}</a>
            </div>
            ";

            $shortDesc = "
            <div class='shortDesc mt5'>
                {$ln->gfv($row, 'description_short')}
            </div>
            ";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('event_event', 'picture', $row['event_id'], $exp );

            $venue = '';
            if ($cpCfg['cp.event.event.showVenue']== 1){
                $venue = $ln->gfv($row, 'event_venue');
            }

            $date = '';
            if ($cpCfg['cp.event.event.showDate']== 1){
                $date = $fn->getCPDate($row['event_date']);
            }

            if ($pic != ''){
                $rows .= "
                <div class='subcolumns eventList'>
                    <div class='c75l'>
                        <div class='subcl' >
                            {$title}
                            <div class='mt5'>{$venue} {$date}</div>
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
                    <div class='mt5'>{$ln->gfv($row, 'event_venue')} {$ln->gfv($row, 'event_date')}</div>
                    {$shortDesc}
                </div>
                ";
            }
            $count++;

        }

        $heading = '';
        if ($cpCfg['m.event.event.showHeading']){
            $heading = $ln->gd('m.event.event.upcomingEvents.heading');
        }

        $text = "
        <h1 class='ruled'>{$heading}</h1>
        {$rows}
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

        /** if there is a hook for event in the theme level then use that **/
        $theme = getCPThemeObj($cpCfg['cp.theme']);
        if (method_exists($theme->fns, 'getEventDetailHook')){
            return $theme->fns->getEventDetailHook($row);
        }

        $rows = '';

        $title = "<h4>{$ln->gfv($row, 'title', '0')}<h4>";
        $exp = array('style' => '', 'folder' => 'thumb');
        $pic = $media->getMediaPicture('event_event', 'picture', $row['event_id'], $exp);

        if ($pic != ''){
            $rows .= "
            <div class='subcolumns eventDetail'>
                <div class='c75l'>
                    <div class='subcl' >
                        {$title}
                        <div class= mt10>{$ln->gfv($row, 'event_venue')}</div>
                        <div class= mt10>{$ln->gfv($row, 'event_date')}</div>
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
                <div class='eventDetail' >
                    {$title}
                    <div class= mt10>{$ln->gfv($row, 'event_venue')}</div>
                    <div class= mt10>{$ln->gfv($row, 'event_date')}</div>
                </div>
                ";
            }

        $text = "
        {$rows}
        <h4>{$ln->gd('m.event.event.eventInformation.heading')}</h4>
        <div class= mt10>{$ln->gfv($row, 'description')}</div>
        <h4>{$ln->gd('m.event.event.speakersInclude.heading')}</h4>
        <div class= mt10>{$ln->gfv($row, 'speaker')}</div>
        ";

        return $text;
    }
}
