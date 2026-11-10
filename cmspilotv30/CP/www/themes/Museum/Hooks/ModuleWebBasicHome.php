<?
class CP_Www_Themes_Museum_Hooks_ModuleWebBasicHome
{
    /*
     *
     */
    function getList($dataArray) {
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');        
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_anythingSlider');
        $slideshow = $wSlideshow->getWidget(array(
             'width' => 960
            ,'height' => 241
            ,'subColLeft' => 'c66l'
            ,'subColRight' => 'c33r'
            ,'showNavArrows' => false
            ,'showReadMore' => false
            ,'hideWidgetOnLoading' => true
        ));


        $calloutHome = $this->getHomeCallout();

        // $newsFeed = "
        // <iframe src='{$cpCfg['m.webBasic.content.newsFeedUrlHome']}'
        //    width='307'
        //    height='400'
        //    scrolling='no'
        //    frameborder='0'
        //    allowtransparency='true' style='overflow-x: hidden;'>
        // </iframe>
        // ";

        $rowsEvent = "";
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array("simplyscroll-1.0.4"));
        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: 'simplyScroll1'
                ,speed: '1'
                ,frameRate: '40'
                ,scrollDirection: 'vertical'
                ,scrollClass: 'simply-scroll-vert'
                ,autoMode: 'loop'
            }
            cpt.museum.loadHomeEventScroll(exp);
        "));   

        $SQL = "
        SELECT e.*
              ,s.section_id
              ,s.title AS section_title
              ,c.category_id
              ,c.title AS category_title             
        FROM event e
        LEFT JOIN section s ON s.section_id = e.section_id
        LEFT JOIN category c ON c.category_id = e.category_id  
        WHERE e.latest = 1 
          AND e.published = 1    
          AND e.content_type != 'Always On' AND e.always_on != 1
          AND CURDATE() < e.event_end_date
        ORDER BY e.event_date ASC, e.sort_order ASC
        ";
        $result = $db->sql_query($SQL);

        $dataArray = array();
        $counter = 0;

        $modEvent = getCPModelObj('event_event');

        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $arrTemp = &$dataArray[$counter];
            $title = $ln->gfv($row, 'title');

            $dateText = ($tv['lang'] == 'chi') ? $ln->gfv($row, 'event_date_text') : 
                                                date('d M Y', strtotime($row['event_date']));
            $eventDate = ($row['content_type'] == 'Always On' || $row['always_on'] == 1 ) ? '' : $dateText;
            $dateTime = $eventDate . ' ' . $ln->gfv($row, 'event_time');

            $progTypeRow = $fn->getVlRowByValue('programType', $row['program_type']);
            $program_type = $ln->gfv($progTypeRow, 'value');

            $url = $modEvent->getUrlByRecord($row, 'event_id', array('catType'=>'Event' ));

            //$readMore = "... <a href='{$url}'>{$ln->gd('cp.lbl.more')}</a>";
            $readMore = " ...";
            $noOfChars = strlen($readMore) + 115;
            $desc_short = $ln->gfv($row, 'description_short');

            if($tv['lang'] == 'chi') {
                $desc_short = mb_strimwidth($desc_short,0,$noOfChars,$readMore,'utf-8');
            } else { 
                $desc_short = $cpUtil->truncate($desc_short, $noOfChars , 
                   $readMore, false, false); 
            }

            $desc_short = (strpos($desc_short, $readMore) !== false) ?
                                $desc_short : $desc_short . $readMore;
            $rowsEvent .= "
            <div class='row mt10'>
                <h5><a href='{$url}'>{$title}</a></h5>
                <p class='date'>{$program_type}</p>
                <p class='date'>{$dateTime}</p>
                <p>{$desc_short}</p>
                <hr>
            </div>
            ";

        }

        $eventUrl = $cpUrl->getUrlByCatType('Event');
        $text = "
        {$slideshow}
        <div class='home-content subcolumns'>
            <div class='c33l'>
                <div class='subcl'>
                    <div class='events-calendar'>
                        <h4><a href='{$eventUrl}'>{$ln->gd('m.webBasic.home.newsFeed.calendarOfEvents')}</a></h4>
                        <div id='simplyScroll1'>{$rowsEvent}</div>
                    </div>
                </div>
            </div>

            {$calloutHome}
        </div>
        ";

        return $text;
    }

    /**
     * [getHomeCallout description]
     * @return [type] [description]
     */
    function getHomeCallout() {
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $mediaExp          = array();

        $wRecord = getCPWidgetObj('content_record');
        $dataArray = $wRecord->getWidget(array(
             'contentType'    => 'Callout Home'
            ,'showShortDesc'  => false
            ,'showPicInDesc'  => false
            ,'showReadMore'   => false
            ,'addUrlForTitle' => true
            ,'truncateDesc' => false
            , 'orderBy' => 'sort_order'
            ,'returnDataOnly' => true
        ));
        //print_r($dataArray);
        $text = '';
        foreach($dataArray AS $row){
            $mediaExp['folder'] = 'large';
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $mediaExp);
            if ($pic != ''){
                $pic = "<div class='pic'>{$pic}</div>";
            }

            $title =
            $picAsBgStyle = '';
            // $mediaExp['returnFileNameOnly'] = true;
            // $mediaExp['folder'] = 'large';
            // $filename = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $mediaExp);
            // $picAsBgStyle = " style='background: url({$filename}) no-repeat;'";

           $title = ($row['show_title'] == 1) ?
                     "<h3 class='title'>{$row['title']}</h3>" : '';

            $text .= "
            <div class='c33l'>
                <div class='subcl'>
                    <div class='calloutHome'>
                        <div class='inner'>
                            {$pic}
                            {$title}
                            <div class='desc'>{$row['description']}</div>
                        </div>
                    </div>
                </div>
            </div>
            ";

        }

        return $text;
    }
}