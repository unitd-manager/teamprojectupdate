<?
class CP_Www_Themes_Museum_View extends CP_Www_Lib_ThemeViewAbstract
{

    var $jssKeys = array('nyroModal-2.0.0', 'jqPrettyPhoto-3.1.3_museum');
    /**
     *
     */
    function getBodyPanel() {
        $clsInst = Zend_Registry::get('currentModule');

        $wBreadcrumb = getCPWidgetObj('common_breadcrumb');
        $breadcrumb = "
        {$wBreadcrumb->getWidget(array(
             'hideInHome' => true
            ,'showPrefixText' => false
            ,'showRecordTitle' => false
        ))}
        ";

        $text = "
        <div class='bodyPanel'>
            {$breadcrumb}
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getBannerPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        $banner = '';
        if ($cpCfg['cp.showBannerBelowHeader'] && $tv['secType'] != 'Home'){
            $wBanner = getCPWidgetObj('media_banner');
            $arr = array();
            if (isset($cpCfg['w.media.banner.calloutContentType'])) {
                $arr[] = $cpCfg['w.media.banner.calloutContentType'];
            }

            $arr['showDefaultBanner'] = true;
            $arr['defaultBanner'] = $cpCfg['w.media.banner.defaultBanner'];
            $arr['defaultBannerCaption'] = $ln->gd2('w.media.banner.defaultBanner.caption');
            $arr['subColLeft'] = 'c66l';
            $arr['subColRight'] = 'c33r';
            $arr['showNavArrows'] = false;
            $banner = $wBanner->getWidget($arr);

            if ($banner != ''){
                CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                    $('body').addClass('hasBannerBelowHeader');
                "));
            }
        }


        return $banner;
    }

    /**
     *
     */
    function getLeftPanel(){
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');

        if (method_exists($clsInst, 'getLeftPanel')) {
            $text = $clsInst->getLeftPanel();
        } else {
            $text = "
            {$subNav->getWidget()}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $fn->addLangKey('cp.home.calendar.whatson');

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
                </nav>
                ";
            } elseif ($cpCfg['cp.showNavAsMegaMenu']){
                $megaMenu = getCPWidgetObj('menu_megaMenu');
                $widget = "{$megaMenu->getWidget(array(
                     'noOfCatToDisplay' => 10
                    ,'noOfSubCatToDisplay' => 10
                ))}
                ";

                $extraClass = 'hasMenu clearfix';
                $text = "
                <nav id='nav' class='hasMenu clearfix'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
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
                        </div>
                    </div>
                </nav>
                ";
            } else {
                $text = "
                <nav id='nav' class='{$extraClass}'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
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
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $wRecord = getCPWidgetObj('content_record');
        $socialIcons = "
        <div class='socialMediaIcons'>
            {$wRecord->getWidget(array(
                 'contentType' => 'Social Media Icons'
                ,'mediaExp' => array('folder' => 'thumb')
                ,'showShortDesc' => false
            ))}
        </div>
        ";

        $wRecord = getCPWidgetObj('media_carousel');
        $supportedBy = "
        <div class='supportedBy'>
            {$wRecord->getWidget(array(
                 'contentType' => 'Footer Logo'
                ,'type' => 'picByContentType'
                ,'items' => 1
                ,'speed' => 1
                ,'autoStart' => true
                ,'showNav' => false
                ,'showPager' => false
                ,'hideWidgetOnLoading' => true
                ,'fx' => 'fade'
             ))}
        </div>
        ";

        $cpNoticeModalDisplayed = $fn->getSessionParam('cpNoticeModalDisplayed'.$tv['lang']);

        $SQL = "
        SELECT c.*
        FROM content c
        WHERE content_type = 'Notice'
        AND c.published = 1 
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        $noticeJS = '';
        if($numRows > 0 && !$cpNoticeModalDisplayed){
            $noticeJS = "
            <script>
                $(function(){
                    setTimeout(function(){ cpt.museum.openNoticeModal() }, 2000);
                });
            </script>
            ";
        }

        $text = "
        <div class='floatbox'>
            <div class='float_left copyright'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <!--<div class='float_left supportedByTitle'>
                {$ln->gd('cp.footer.rightText')}
            </div>
            <div class='float_left'>
                {$supportedBy}
            </div>-->
            <div class='float_right'>
                {$socialIcons}
            </div>
        </div>
        {$noticeJS}    
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

            $previous_page_link = $pager->getPrevRecordsText($ln->gd('cp.pager.previous'), '', '', $linkRecType);
            $next_page_link = $pager->getNextRecordsText($ln->gd('cp.pager.next'), '', '', $linkRecType);
            $page_nos_links = "{$firstPageText}{$pager->getPageNumbersLinks($startRange, $endRange, '', $linkRecType)} {$lastPageText}";

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
                    <div class='float_left preBtn'>
                        {$previous_page_link}
                    </div>
                    <div class='float_left linkNos'>
                        {$page_nos_links}
                    </div>
                    <div class='float_left nxtBtn'>
                        {$next_page_link}
                    </div>
                    <div class='float_right'>
                        {$ln->gd('cp.pager.lbl.showing')} {$pager->startRecordNo} {$ln->gd('cp.pager.lbl.to')} {$pager->endRecordNo} {$ln->gd('cp.pager.lbl.of')} {$pager->totalRecords}
                    </div>
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
}