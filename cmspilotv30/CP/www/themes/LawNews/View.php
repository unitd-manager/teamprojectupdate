<?
class CP_Www_Themes_LawNews_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jFontSizer');

    /**
     *
     */
    function getHeaderPanel(){
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        $pLogin = getCPPluginObj('member_login');

        $advancerSearchUrl = $cpUrl->getUrlByCatType('Advanced Search', 'News Archive');
        $siteSearchExp = array(
             'url' => $advancerSearchUrl
            ,'showAdvSearchLink' => true
        );

        $SQL = "
        SELECT tag_line, tag_line2
        FROM site
        WHERE site_id = {$cpCfg['cp.site_id']}
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $text = "
        {$this->getAdBanner('Top')}
        <h2 class='tagLine'>{$row ['tag_line']}</h2>
        <!--{$pLogin->view->getLoginInfoText()}-->
        {$pSiteSearch->view->getSearchBox($siteSearchExp)}
        {$this->getSiteLogo()}
        ";

        return $text;
    }

    /**
     *
     */
    function getSiteLogo(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');

        $mediaExp['returnFileNameOnly'] = true;
        $mediaExp['folder'] = 'normal';
        $logoFile = $media->getMediaPicture('common_site', 'logo', $cpCfg['cp.site_id'], $mediaExp);

        $mediaExp['returnFileNameOnly'] = true;
        $mediaExp['folder'] = 'normal';
        $secondLogoFile = $media->getMediaPicture('common_site', 'secondLogo', $cpCfg['cp.site_id'], $mediaExp);


        $text = '';
        if($logoFile != ''){
            $text .= "
            <script>
            $(function(){
                cpt.lawNews.changeSiteHeaderLogo('{$logoFile}');
            });
            </script>
            ";
        }
        if($secondLogoFile != ''){
            $text .= "
            <script>
            $(function(){
                cpt.lawNews.changeSiteFooterLogo('{$secondLogoFile}');
            });
            </script>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT tag_line, tag_line2
        FROM site
        WHERE site_id = {$cpCfg['cp.site_id']}
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $tagLine2 = "<div class='tagLine2'>{$row ['tag_line2']}</div>";

        $text = '';
        if ($cpCfg['cp.showMainNavPanelAtTop']){

            $extraClass = '';
            if ($cpCfg['cp.showNavAsMenu']){
                $superFish = getCPWidgetObj('menu_superFish');
                $widget = "{$superFish->getWidget(array(
                    'btnPos' => 'Top'
                ))}
                ";

                $extraClass = 'hasMenu clearfix';
                $text = "
                <nav id='nav' class='hasMenu clearfix'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                    {$tagLine2}
                </nav>
                ";
            } else {
                $mainNav = Zend_Registry::get('mainNav');
                $hasSlidingDoorBtn = $cpCfg['w.core_mainNav.hasSlidingDoorBtn'];

                $widget = "{$mainNav->getWidget(array(
                     'btnPos' => 'Top'
                    ,'hasSlidingDoorBtn' => $hasSlidingDoorBtn
                ))}
                ";
            }

            if($cpCfg['cp.fullWidthTemplte'] && !$cpCfg['cp.placeNavInsideHeaderTag']){
                $text = "
                <nav id='nav' role='navigation'>
                    <a id='navigation' name='navigation'></a>
                    <div class='page_margins'>
                        <div class='page'>
                            {$widget}
                            {$tagLine2}
                        </div>
                    </div>
                </nav>
                ";
            } else {
                $text = "
                <nav id='nav' class='{$extraClass}'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                    {$tagLine2}
                </nav>
                ";
            }

        }
        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');

        $fn = Zend_Registry::get('fn');
        $fn->addLangKey('t.lawNews.canNotCopy.message');

        $wRecord = getCPWidgetObj('content_record');
        $wLinksSet1 = $wRecord->getWidget(array(
             'contentType'    => 'Footer Links Set 1'
            ,'heading'        => $ln->gd('cp.footer.linkSet1.heading')
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addUrlForTitle' => TRUE
            ,'displayLimit'   => 10
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wLinksSet2 = $wRecord->getWidget(array(
             'contentType'    => 'Footer Links Set 2'
            ,'heading'        => $ln->gd('cp.footer.linkSet2.heading')
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addUrlForTitle' => TRUE
            ,'displayLimit'   => 10
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wLinksSet3 = $wRecord->getWidget(array(
             'contentType'    => 'Footer Links Set 3'
            ,'heading'        => $ln->gd('cp.footer.linkSet3.heading')
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addUrlForTitle' => TRUE
            ,'displayLimit'   => 10
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wLinksSet4 = $wRecord->getWidget(array(
             'contentType'    => 'Footer Links Set 4'
            ,'heading'        => $ln->gd('cp.footer.linkSet4.heading')
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addUrlForTitle' => TRUE
            ,'displayLimit'   => 10
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wLinksSet5 = $wRecord->getWidget(array(
             'contentType'    => 'Footer Links Set 5'
            ,'heading'        => $ln->gd('cp.footer.linkSet5.heading')
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addUrlForTitle' => TRUE
            ,'displayLimit'   => 10
        ));

        $text = "
        <div class='footer_top'>
            <div class='floatbox'>
                <div class='float_right'>
                    <div id='fontsizer'></div>
                </div>
                <div class='float_right'>
                    <div class='fontsizechanger'>
                        {$ln->gd('cp.footer.changeFontText')}
                    </div>
                </div>
                <div class='float_right'>
                    <a href='#navigation' class='top'>{$ln->gd('cp.footer.bacToTopText')}</a>
                </div>
            </div>
        </div>
        <div class='footer_middle'>
            <div class='floatbox'>
                <div class='float_left'>
                    {$wLinksSet1}
                </div>
                <div class='float_left'>
                    {$wLinksSet2}
                </div>
                <div class='float_left'>
                    {$wLinksSet3}
                </div>
                <div class='float_left'>
                    {$wLinksSet4}
                </div>
                <div class='float_left'>
                    {$wLinksSet5}
                </div>
            </div>
        </div>
        <div class='footer_bottom'>
            <p class='copyright'>{$ln->gd('cp.footer.copyrightText')}</p>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRightPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
//            $rightBannerTop = $this->getAdBanner('Right');
//
//            $expBannerBottom = array(
//                 'displayLimit' => 2
//                ,'addSearchCondArr' => array('bl.sort_order > 1')
//            );
//            $rightBannerBottom = $this->getAdBanner('Right', $expBannerBottom);

            $rightBannerTop = $this->getAdBanner('RightTop');
            $rightBannerMiddle = $this->getAdBanner('RightMiddle');
            $rightBannerBottom = $this->getAdBanner('RightBottom');

            $wRecord = getCPWidgetObj('content_record');
            $wCountryUpdate = $wRecord->getWidget(array(
                 'helperFn'       => 'getWidgetByCategoryType'
                ,'sectionType'    => 'News Archive'
                ,'categoryType'   => 'Country Update'
                ,'showDesc'       => FALSE
                ,'showShortDesc'  => FALSE
                ,'showPic'        => FALSE
                ,'displayLimit'   => 8
            ));

            $wRecord = getCPWidgetObj('content_record');
            $wMostRead = $wRecord->getWidget(array(
                 'helperFn'       => 'getWidgetBySectionType'
                ,'sectionType'    => 'News Archive'
                ,'showDesc'       => FALSE
                ,'showShortDesc'  => FALSE
                ,'showPic'        => FALSE
                ,'heading'        => $ln->gd('w.content.record.mostReadArticle.heading')
                ,'addSearchCond' => " AND ct.category_type != 'Country Update' AND ct.category_type != 'Country Update External'"
                ,'orderBy'        => "c.click_count DESC, c.content_date DESC"
                ,'displayLimit'   => 4
            ));

            $sponsorsLogo = array(
                 'displayLimit' => 4
            );

            if($tv['secType'] == 'Event' && $tv['action'] == 'detail'){
                $text = $this->getSponsorsLogo();

            } else {
                $text = "
                <div class='rightPanel'>
                    <div class='rightBanner'>
                        {$rightBannerTop}
                    </div>
                    <div class='countryUpdate'>
                        {$wCountryUpdate}
                    </div>
                    <div class='mostRead mt20'>
                        {$wMostRead}
                    </div>
                    <h2 class='ruled'>{$ln->gd('w.event.record.events.heading')}</h2>
                    {$this->getLatestEvents()}
                    <div class='rightBanner mt20'>
                        {$rightBannerMiddle}
                    </div>
                    <div class='rightBanner mt20'>
                        {$rightBannerBottom}
                    </div>
                </div>
                ";
            }
        }
        return $text;

    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            print "<h3>{$clsName}->{$actionTemp} does not exist";
            exit();
        }

        $breadcrumb = '';
        if ($tv['secType'] != 'Basket'){
            $wBreadcrumb = getCPWidgetObj('common_breadcrumb');
            $breadcrumb = "
            {$wBreadcrumb->getWidget(array(
                 'hideInHome' => true
                ,'showPrefixText' => true
            ))}
            ";
        }

        $text = "
        <div class='bodyPanel'>
            {$breadcrumb}
            {$this->getAdBanner('Body')}
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPagerPanel($linkRecType = '') {
        $text = "
        {$this->getNavButtons(5, '', $linkRecType)}
        ";

        return $text;
    }

    /**
     *
     */
    function getPagerPanelTop() {
        $pager = Zend_Registry::get('pager');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $keyword = $fn->getReqParam('keyword');

        if($ln->gd2('cp.pager.lbl.topResultRow') != ''){
            $row = $ln->gd2('cp.pager.lbl.topResultRow');
            $row = str_replace('[[keyword]]', $keyword, $row);
            $row = str_replace('[[total_records]]', $pager->totalRecords, $row);
            $row = str_replace('[[start_record_no]]', $pager->startRecordNo, $row);
            $row = str_replace('[[end_record_no]]', $pager->endRecordNo, $row);
        } else {
            $row = "
            <div class='pagelinksTop'>
                <div class='floatbox'>
                    <div class='float_left'>
                        {$ln->gd('cp.pager.lbl.results')} <strong>{$pager->startRecordNo} - {$pager->endRecordNo}</strong>
                    </div>
                    <div class='float_left'>
                        {$ln->gd('cp.pager.lbl.of')} <strong>{$pager->totalRecords}</strong>
                    </div>
                    <div class='float_left'>
                        {$ln->gd('cp.pager.lbl.for')} <strong>{$keyword}</strong>
                    </div>
                </div>
            </div>
            ";
        }

        $text = "
        <div class='pagelinksTop'>
            {$row}
        </div>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $numPages
     * @return <type>
     */
    function getNavButtons($numPages, $action = '', $linkRecType = ''){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $modules = Zend_Registry::get('modules');
        $pager = Zend_Registry::get('pager');

        $text = "";

        $numPages = $numPages - 1;
        $pages = $pager->getPageNumbersLinks();

        $action = ($action != "") ? $action : $tv['action'];

        if ($pager->page == ''){
            return;
        }

        if ($action == "list" || $action == "detail"){
            $startRange = $pager->page;
            $endRange   = ($startRange + $numPages) > $pager->totalPages ? $pager->totalPages : ($startRange + $numPages);

            if ($endRange - $startRange <= $numPages){
                $startRange = ($endRange - $numPages) <= 0 ? 1 : ($endRange - $numPages);
            }

            $firstPage     = $pager->getPageNumbersLinks(1, 1, '...&nbsp;', $linkRecType);
            $firstPageText = ($startRange > 1) ? "{$firstPage}" : "";
            $lastPage      = $pager->getPageNumbersLinks($pager->totalPages, '', '', $linkRecType);
            $lastPageText  = ($endRange < $pager->totalPages) ? "...{$lastPage}" : "";

            $backToList = '';

            if ($action == "detail"){
                $backToList = "
                <div class='float_right'>
                    {$pager->getBackButton()}
                </div>
                ";
            }

            $startRange = 1;
            $endRange = $pager->totalPages;

            $row = "
            <div class='floatbox'>
                <div class='float_left'>共 <strong>[[total_pages]]</strong> 页, 第 <strong>[[current_page]]</strong> 页</div>
                <div class='float_left'>
                    [[previous_page_link]]
                </div>
                <div class='float_left'>
                    [[next_page_link]]
                </div>
            </div>
            <div class='floatbox linkNos'>
                [[page_nos_links]]
            </div>
            ";

            $previous_page_link = $pager->getPrevRecordsText($ln->gd('cp.pager.previous'), '', '', $linkRecType);
            $next_page_link = $pager->getNextRecordsText($ln->gd('cp.pager.next'), '', '', $linkRecType);
            $page_nos_links = $pager->getPageNumbersLinks($startRange, $endRange, '', $linkRecType);

            if($ln->gd2('cp.pager.lbl.bottomNavRow') != ''){
                $row = $ln->gd2('cp.pager.lbl.bottomNavRow');
                $row = str_replace('[[total_pages]]' , $pager->totalPages, $row);
                $row = str_replace('[[current_page]]', $pager->page, $row);
                $row = str_replace('[[previous_page_link]]', $previous_page_link , $row);
                $row = str_replace('[[next_page_link]]', $next_page_link , $row);
                $row = str_replace('[[page_nos_links]]', $page_nos_links , $row);
            } else {
                $row = "
                <div class='floatbox'>
                    <div class='float_left'>
                        {$previous_page_link}
                    </div>
                    <div class='float_left'>
                        {$ln->gd('cp.pager.lbl.page')} {$pager->page} {$ln->gd('cp.pager.lbl.of')} {$pager->totalPages}
                    </div>
                    <div class='float_left'>
                        {$next_page_link}
                    </div>
                </div>
                <div class='floatbox linkNos'>
                    {$page_nos_links}
                </div>
                ";
            }

            $text = "
            <div class='pagelinks'>
                {$row}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getAdBanner($position, $exp = array()) {
        $tv = Zend_Registry::get('tv');

        $exp['position'] = $position;

        $correspondent_id = 0;
         if($tv['secType'] == 'Jurisdiction' && $tv['action'] == 'detail'){ //correspondent banner in Jurisdiction detail
            $correspondent_id = getCPModuleObj('lawNews_jurisdiction')->model->getActiveCorrespondentId($tv['record_id']);
        } else if($tv['secType'] == 'News Archive' && $tv['action'] == 'detail'){
            $jurisdctionArr = getCPModuleObj('lawNews_newsArchive')->model->getJurisdictionsLinkedArray($tv['record_id']);
            if(count($jurisdctionArr) == 1){ // show the corres banner in article detail if one one jurisdiction is linked
                $correspondent_id = getCPModuleObj('lawNews_jurisdiction')->model->getActiveCorrespondentId($jurisdctionArr[0]['jurisdiction_id']);
            }
        }

        $corresBanner = '';
        if($correspondent_id > 0){
            $exp2 = $exp;
            $exp2['module'] = 'lawNews_correspondent';
            $exp2['record_id'] = $correspondent_id;
            $wBanner = getCPWidgetObj('ads_banner');
            $corresBanner = $wBanner->getWidget($exp2);
        }

        $wBanner = getCPWidgetObj('ads_banner');
        $text = ($corresBanner != '') ? $corresBanner : $wBanner->getWidget($exp);

        return $text;
    }

    /**
     *
     */
    function getSponsorsLogo() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (e.event_id = sl.record_id AND sl.module ='event_event')";
            }
        }

        $SQL = "
        SELECT e.*
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
        FROM event e
        LEFT JOIN category ca     ON (e.category_id     = ca.category_id)
        LEFT JOIN sub_category sc ON (e.sub_category_id = sc.sub_category_id)
        {$extraTableNames}
        WHERE e.event_id = {$tv['record_id']}
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $text = "
        {$media->getMediaPicture('event_event', 'sponsorsPicture', $row['event_id'])}
        ";

        return $text;
    }

    /**
     *
     */
    function getLatestEvents() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT e.*
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
        FROM event e
        LEFT JOIN category ca     ON (e.category_id     = ca.category_id)
        LEFT JOIN sub_category sc ON (e.sub_category_id = sc.sub_category_id)
        WHERE e.latest = 1
          AND e.published = 1
          AND e.event_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'event_event'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
        ORDER BY e.sort_order
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'event_id', array('secType'=>'Event'));
            $title ="
            <div class='title'>
                <a href='{$url}'>{$ln->gfv($row, 'title')}</a>
            </div>
            ";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('event_event', 'picture', $row['event_id'], $exp );

            //$date = $fn->getCPDate($row['event_date']);
            $date = $row['event_date_text'];

            $rows .= "
            <li>
                <div class='subcolumns'>
                    <div class='c50l'>
                        <div class='subcl'>
                            <div class='detailNews'>
                                {$title}
                                <div class='mt5'>{$ln->gfv($row, 'event_venue')}</div>
                                <div class='mt5'>{$date}</div>
                            </div>
                        </div>
                    </div>
                    <div class='c50r'>
                        <div class='subcr'>
                            {$pic}
                        </div>
                    </div>
                </div>
            </li>
            ";
        }

        $text = "
           <div class='latestEvents'>
            <ul class='noDefault'>
                {$rows}
            </ul>
           </div>
        ";

        return $text;
    }
}