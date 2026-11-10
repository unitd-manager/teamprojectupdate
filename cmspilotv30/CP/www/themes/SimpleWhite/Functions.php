<?
class CP_Www_Themes_SimpleWhite_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $wRecord = getCPWidgetObj('content_record');
        $latestEvents = $wRecord->getWidget(array(
             'sectionType'    => 'Event'
            ,'heading'        => $ln->gd('m.event.event.dontMissEvents.heading')
            ,'headingTag'     => 'h1'
            ,'showDesc'       => false
            ,'showShortDesc'  => true
            ,'showReadMore'   => true
            ,'showDate'       => true
            ,'showPic'        => false
            ,'addUrlForTitle' => true
            ,'addSearchCond'  => " AND c.latest = 1"
            ,'displayLimit'   => 3
            ,'dataArrayPostCallback' => array($this, 'newsEventDataArrayCallback')
            ,'dataArrayPostCallbackParamArr' => array('type' => 'Event')
        ));

        $wRecord = getCPWidgetObj('content_record');
        $latestNews = $wRecord->getWidget(array(
             'sectionType' => 'News'
            ,'heading' => $ln->gd('m.event.event.latestNews.heading')
            ,'headingTag' => 'h1'
            ,'showDesc' => false
            ,'showShortDesc' => true
            ,'showDate' => true
            ,'showPic' => false
            ,'showReadMore' => true
            ,'addUrlForTitle' => true
            ,'addSearchCond' => " AND c.latest = 1"
            ,'displayLimit' => 3
            ,'dataArrayPostCallback' => array($this, 'newsEventDataArrayCallback')
            ,'dataArrayPostCallbackParamArr' => array('type' => 'News')
        ));


        $row = $dataArray[0];//first row
        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';

        $description = $ln->gfv($row, 'description');

        $text = "
        <div class='home-content subcolumns'>
            <div class='c33l col1'>
                <div class='subcl'>
                    {$title}
                    {$description}

                </div>
            </div>
            <div class='c33l col2'>
                <div class='subcl'>
                    {$latestNews}
                </div>
            </div>
            <div class='c33r col3'>
                <div class='subcr'>
                    {$latestEvents}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    function newsEventDataArrayCallback(&$dataArray, $parArr) {
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');

        $type = $parArr['type'];

        foreach($dataArray as $key => &$row) {
            $SQL = "
            SELECT YEAR (c.content_date) AS year
            FROM content c
            WHERE c.content_id = {$row['content_id']}
            ";
            $rowYr = $fn->getRecordBySQL($SQL);
            $row['url'] = $cpUrl->getUrlBySecType($type)
                        . '?year=' . $rowYr['year']
                        . '&content_id=' . $row['content_id'];
        }
    }

    /**
     *
     */
    function getModuleWebBasicContentListHook($dataArray) {
        $tv = Zend_Registry::get('tv');

        $text = '';
        $row = '';

        if($tv['secType'] == 'Event' || $tv['secType'] == 'News'){
            $text = $this->getEventList($dataArray, $tv['secType']);

       } else if($tv['secType'] == 'List Detail Combo'){
            return getCPModuleObj('webBasic_content')->controller->getList('listDetailCombo');
       } else {
            $text = "
            {$this->getContentList($dataArray)}
            ";
       }

        return $text;

    }

    /**
     *
     * use for both event and new list/detail
     */
    function getEventList($dataArray, $secType) {
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');


        $content_id = $fn->getReqParam('content_id');

        $rows = '';
        $row = null;
        foreach($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id');

            $exp = array('folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

            $content_date = $fn->getCPDate($row['content_date']);
            $title = "
            <h1 class='mb10'>{$content_date}: {$ln->gfv($row, 'title')}</h1>
            ";

            $shortDesc = $ln->gfv($row, 'description_short');
            $longDesc = $ln->gfv($row, 'description');

            $showHideDesc = '';
            if (trim($longDesc) != '') {
                $showHideDesc = "
                <div class='more'>
                    <a class='showHideDesc' href='#'>
                    <span class='more'>&gt; {$ln->gd('more')}</span>
                    <span class='less'>&gt; {$ln->gd('less')}</span>
                    </a>
                </div>
                ";
            }
            $rows .= "
            <div class='subcolumns row' content_id='{$row['content_id']}'>
                <div class='c25l'>
                    <div class='subcl txtCenter'>
                        {$pic}
                    </div>
                </div>
                <div class='c75r'>
                    <div class='subcr'>
                        {$title}
                        <div class='short-description'>
                        {$shortDesc}
                        </div>
                        <div class='long-description'>
                            {$longDesc}
                        </div>
                        {$showHideDesc}
                    </div>
                </div>
            </div>
            ";

        }
        $year = '';
        if ($row) {
            $year = $fn->getCPDate($row['content_date'], 'Y');
        }

        $contentType = $secType . ' Info'; //News Info

        $wRecord = getCPWidgetObj('content_record');
        $eventInfo = $wRecord->getWidget(array(
             'contentType'    => $contentType
            ,'showDesc'       => true
            ,'showShortDesc'  => true
        ));


        $moreEventText = $this->getEventYears($secType);
        $text = "
        <div class='subcolumns infoPanel'>
            <div class='c25l'>
                <div class='subcl'>
                    <h1>{$year}</h1>
                    <h2>{$tv['secTitle']}</h2>
                    <div class='yr-menu floatbox'>
                        {$moreEventText}
                    </div>
                </div>
            </div>
            <div class='c75r'>
                <div class='subcr'>
                    {$eventInfo}
                </div>
            </div>
        </div>

        <div class='contentList'>
        {$rows}
        </div>
        {$formObj->getHiddenFldObj('content_id', $content_id)}
        ";

        return $text;
    }

    function getEventYears($sectionType) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';

        $urlSec = $cpUrl->getUrlBySecType($sectionType);

        $SQL = "
        SELECT DISTINCT YEAR (c.content_date) AS year
        FROM content c
        JOIN section s ON s.section_id = c.section_id
        WHERE s.section_type = '{$sectionType}'
          AND c.published = 1
        ORDER BY c.content_date DESC
        ";
        $result = $db->sql_query($SQL);

        $rows = '';
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $url = $urlSec . '?year=' . $row['year'];
            $rows .= "<li><a href='{$url}'>{$row['year']}</a></li>";
        }

        $section = $sectionType == 'News' ? $ln->gd('cp.lbl.news') : $ln->gd('cp.lbl.events');

        $text = "
        <ul class='sf-menu'>
            <li><a href='#'>&gt; {$ln->gd('cp.lbl.more')} {$section}</a>
                <ul>
                {$rows}
                </ul>
            </li>
        </ul>
        ";

        return $text;

    }

    function getContentList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';

        $row = '';
        foreach ($dataArray as $row){
        $title = ($row['show_title'] == 1) ? "<header><h1>{$ln->gfv($row, 'title', '0')}</h1></header>" : '';

        $exp = array('style' => 'mb5 pic', 'zoomImage' => 1);

        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='float_right picWrap'>{$pic}</div>";
        }

        $embedCode = '';
        if (isset($row['embed_code']) && $row['embed_code'] != ''){
            $embedCode = "<div class='float_right embedObj'>{$row['embed_code']}</div>";
        }

        $text = "
        {$embedCode}
        {$pic}
        {$title}
        <div class='description'>
            {$ln->gfv($row, 'description', '0')}
        </div>

        {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
        ";
        }

        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContentSearchVarHook() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $retVal = '';
        if ($tv['currentViewRecType'] == 'News' || $tv['currentViewRecType'] == 'Event') {
            $year = $fn->getReqParam('year', date('Y'));
            $retVal = "YEAR(c.content_date) = {$year}";
        }

        return $retVal;
    }
}