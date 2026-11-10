<?

class CP_Www_Themes_LawNews_Functions {
    /*
     *
     */

    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';
        foreach ($dataArray as $row) {

        }

        $readMoreUrl = $cpUrl->getUrlBySecType('News Archive');
        $homeNewsDisplayLimit = $fn->getIssetParam($cpCfg, 'w.content.record.homeNewsDisplayLimit.displayLimit', 8);
        $wRecord = getCPWidgetObj('content_record');
        $wNewsAndAnalysis = $wRecord->getWidget(array(
            'helperFn' => 'getWidgetByCategoryType'
            , 'sectionType' => 'News Archive'
            , 'categoryType' => 'News & Analysis'
            , 'showDesc' => FALSE
            , 'showPicInDesc' => FALSE
            , 'showShortDesc' => TRUE
            , 'addSearchCond' => " AND c.editors_pick != 1"
            , 'displayLimit' => $homeNewsDisplayLimit
            , 'showGroupReadMore' => true
            , 'groupReadMoreUrl' => $readMoreUrl
            , 'mediaExp' => array('folder' => 'cropped', 'cropAltfolder' => 'thumbFolderAlias')
                ));

        $editorsPickDisplayLimit = $fn->getIssetParam($cpCfg, 'w.content.record.editorsPick.displayLimit', 6);
        $wRecord = getCPWidgetObj('content_record');
        $wEditorsPick = $wRecord->getWidget(array(
            'helperFn' => 'getWidgetByCategoryType'
            , 'sectionType' => 'News Archive'
            , 'categoryType' => 'News & Analysis'
            , 'showDesc' => FALSE
            , 'showShortDesc' => TRUE
            , 'showPicInDesc' => FALSE
            , 'addSearchCond' => " AND c.editors_pick = 1"
            , 'heading' => $ln->gd('w.content.record.editorsPick.heading')
            , 'displayLimit' => $editorsPickDisplayLimit
                ));

        $wRecord = getCPWidgetObj('content_record');
        $wNewsInBrief = $wRecord->getWidget(array(
            'helperFn' => 'getWidgetByCategoryType'
            , 'sectionType' => 'News Archive'
            , 'categoryType' => 'News In Brief'
            , 'showDesc' => FALSE
            , 'showShortDesc' => FALSE
            , 'showPic' => FALSE
            , 'displayLimit' => 20
                ));

        $text = "
        <div class=''>
            <div class='floatbox newsAnalysis'>
                {$wNewsAndAnalysis}
            </div>
            <div class='floatbox editorPicks'>
                {$wEditorsPick}
            </div>
            <div class='floatbox newsInBrief'>
                {$wNewsInBrief}
            </div>
        </div>
        ";


        return $text;
    }

    /**
     *
     * @return type
     */
    function getLogoutLinkHook() {
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $myAccountRec = getCPModelObj('webBasic_section')->getRecordByType('My Account');
        $myClipsRec = getCPModelObj('webBasic_category')->getRecordByType('My Clippings');
        $logoutRec = getCPModelObj('webBasic_category')->getRecordByType('Logout');
        $logoutUrl = '/index.php?plugin=member_login&_spAction=logout';
        //$logoutUrl = $cpUrl->getUrlByCatType('Logout'); //this makes issue when login is saved in cookies

        $text = "
        <nav class='logged_in_links'>
            <ul class='noDefault'>
                <li>{$ln->gd('p.member.login.lbl.welcome')} {$_SESSION['cpUserFullNameWWW']}</li>
                <li>
                    <a href='{$cpUrl->getUrlBySecType('My Account')}'>
                        <span>{$ln->gfv($myAccountRec, 'title', 0)}</span>
                    </a>
                </li>
                <li>
                    <a href='{$cpUrl->getUrlByCatType('My Clippings')}'>
                        <span>{$ln->gfv($myClipsRec, 'category_title', 0)}</span>
                    </a>
                </li>
                <li>
                    <a href='{$logoutUrl}'>
                        <span>{$ln->gfv($logoutRec, 'category_title', 0)}</span>
                    </a>
                </li>
            </ul>
        </nav>
        ";
        return $text;
    }

    function getEventListHook($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $count = 1;
        $pic = '';

        foreach ($dataArray as $row) {
            $url = $cpUrl->getUrlByRecord($row, 'event_id', array('secType'=>'Event'));
            $title = "
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
            $pic = $media->getMediaPicture('event_event', 'picture', $row['event_id'], $exp);

            //$date = $fn->getCPDate($row['event_date']);
            $date = $row['event_date_text'];

            if ($pic != '') {
                $rows .= "
                <div class='subcolumns eventList'>
                    <div class='c75l'>
                        <div class='subcl' >
                            {$title}
                            <div class='mt5'>{$ln->gfv($row, 'event_venue')} {$date}</div>
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
                    <div class='mt5'>{$ln->gfv($row, 'event_venue')} {$date}</div>
                    {$shortDesc}
                </div>
                ";
            }
            $count++;
        }


        $text = "
        <h1 class='ruled'>{$ln->gd('m.event.event.eventList.heading')}</h1>
        <p>{$ln->gd('m.event.event.eventList.info')}</p>
        <h2 class='ruled'>{$ln->gd('m.event.event.upcomingEvents.heading')}</h2>
        {$rows}
        {$this->getOtherEvents()}
        ";

        return $text;
    }

    /**
     *
     */
    function getOtherEvents() {

        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT e.*
        FROM event e
        WHERE e.content_type = 'External Event'
          AND e.published = 1
          AND e.event_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'event_event'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
        ORDER BY e.sort_order ASC, e.event_date DESC
        ";

        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows = '';
        foreach ($dataArray as $row) {
            $url = $cpUrl->getUrlByRecord($row, 'event_id', array('secType'=>'Event'));
            $target = ($row['external_link'] != '') ? "target='_blank'" : '';
            $venue = ($ln->gfv($row, 'event_venue') != '') ? " / {$ln->gfv($row, 'event_venue')}" : '';
            $event_date = ($row['event_date'] != '') ? " / {$row['event_date']}" : '';

            $rows .= "
            <li><a href='{$url}' {$target}>{$ln->gfv($row, 'title')} {$venue} {$event_date}</a></li>
            ";
        }

        $text = '';
        if ($rows != '') {
            $text = "
            <div class='otherEvents'>
                <h2 class='ruled'>{$ln->gd('m.event.event.otherEvents.heading')}</h2>
                <ul class='noDefault'>
                    {$rows}
                </ul>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     * @param type $row
     */
    function getEventDetailHook($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows = '';

        $title = "<h4>{$ln->gfv($row, 'title', '0')}</h4>";
        $exp = array('style' => '', 'folder' => 'thumb');
        $pic = $media->getMediaPicture('event_event', 'picture', $row['event_id'], $exp);
        //$date = $fn->getCPDate($row['event_date']);
        $date = $row['event_date_text'];

        if ($pic != '') {
            $rows .= "
            <div class='subcolumns eventDetail'>
                <div class='c75l'>
                    <div class='subcl' >
                        {$title}
                        <div class= mt10>{$ln->gfv($row, 'event_venue')}</div>
                        <div class= mt10>{$date}</div>
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
                <div class= mt10>{$date}</div>
            </div>
            ";
        }

        $speakerRow = '';
        if($row['show_speaker'] == 1){
            $speakerRow = "
            <h4>{$ln->gd('m.event.event.speakersInclude.heading')}</h4>
            <div class='m10'>{$ln->gfv($row, 'speaker')}</div>
            ";
        }

        $wEventReg = '';
        if($row['show_event_item'] == 1 && $row['show_registration'] == 1){
            $currencyArr = array(
                 'rmb' => $ln->gd('cp.currency.rmb.lbl')
                ,'hkd' => $ln->gd('cp.currency.hkd.lbl')
                ,'usd' => $ln->gd('cp.currency.usd.lbl')
            );

            $wEventRegObj = getCPWidgetObj('lawNews_eventRegister');
            $showCurrencySelection = ($row['free'] == 1) ? false : true;
            $wEventReg = $wEventRegObj->getWidget(array(
                 'event_id' => $row['event_id']
                ,'showCurrencySelection' => $showCurrencySelection
                ,'currencyArray'         => $currencyArr
                ,'currency'              => 'rmb'
                ,'currencyDisplay'       => $ln->gd('cp.currency.rmb.lbl')
                ,'unitPriceFld'          => 'price_rmb'
                ,'maxQuantity'           => 1
            ));
        }

        $text = "
        {$rows}
        <h4>{$ln->gd('m.event.event.eventInformation.heading')}</h4>
        <div class='mt10'>{$ln->gfv($row, 'description')}</div>
        {$speakerRow}
        {$media->getMediaFilesDisplayThin('event_event', 'attachment', $row['event_id'])}
        {$wEventReg}
        ";

        return $text;
    }

    /**
     *
     */
    function checkLoggedIn($logiSecType) {
        $db = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!isLoggedInWWW()) {
            $loginUrl = $cpUrl->getUrlBySecType($logiSecType);
            $cpUtil->redirect($loginUrl);
        }
    }

    /**
     *
     */
    function checkLoggedInUser() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $this->checkLoggedIn('Login');

        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType != 'membership_contact') {
            exit('invalid access');
        }
    }

    /**
     *
     */
    function getModuleEcommerceBasketOrderSuccessHook($order_id = ''){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        if ($order_id == ''){
            $order_id = $fn->getSessionParam('cpOrderId');
        }

        $text = "
        {$ln->gd('m.ecommerce.basket.order.message.success')}
        ";

        return $text;
    }

}