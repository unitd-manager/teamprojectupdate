<?
class CP_Www_Themes_Kite_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqForm-2.69', 'jqUploadify3.2');
    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $pLogin = getCPPluginObj('member_login');
        $mainNav = getCPWidgetObj('core_mainNav');

        $text = "
        {$pLogin->view->getLoginInfoText()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $mainNav = Zend_Registry::get('mainNav');

        return;

        $text = "
        <nav id='nav'>
            <a id='navigation' name='navigation'></a>
            {$mainNav->getWidget(array(
                 'btnPos' => 'Top'
            ))}
        </nav>
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel2() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchHTML = Zend_Registry::get('searchHTML');

        $langBtns = '';
        $searchText = '';
        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        $text = "
        <div class='navPanel'>
            <div class='floatbox'>
                <div class='float_left'>
                    {$this->getPagerPanel()}
                </div>
                <div class=''>
                    {$this->getActionButtons()}
                </div>
            </div>
            {$searchText}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getActionButtons(){
        $action = Zend_Registry::get('action');
        $tv = Zend_Registry::get('tv');
        $actionBtns = $action->getActionButtons();

        if ($actionBtns != '') {
            $actionBtns = "
            <div class='hlist actionBtns noBg'>
                {$actionBtns}
            </div>
            ";
        }
        $text = "
       {$actionBtns}
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');

            $text = "
            ";

        return $text;
    }

    /**
     *
     */
    function getMainThemeOutput() {
        //body panel must be in the top since (in twopresents) there are some variables
        //set in here which is re-used in the left panel
        $bodyPanel       = $this->getBodyPanel();
        $headerPanel     = $this->getHeaderPanel();
        $navPanel        = $this->getNavPanel();
        $navPanel2       = '';
        $leftPanel       = $this->getLeftPanel();
        $rightPanel      = $this->getRightPanel();
        $mainBottomPanel = $this->getMainBottomPanel();
        $footerPanel     = $this->getFooterPanel();
        $extendedPanel   = $this->getExtendedPanel();
        $lastPanel       = $this->getLastPanelOutsideTemplate();

        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');
        $pageCSSClass = $viewHelper->getPageCSSClass();

        $banner = $this->getBannerPanel();
        $teacherKiteId = $fn->getSessionParam('teacherKiteId');

        $bannerInCol3 = '';
        if ($cpCfg['cp.showBannerInCol3Top'] && $tv['secType'] != 'Home'){
            $wBanner = getCPWidgetObj('media_banner');
            $bannerInCol3 = $wBanner->getWidget();
        }

        if (method_exists($this, 'getNavPanel2')) {
            $navPanel2 = $this->getNavPanel2();
            if ($navPanel2 != '') {
                $navPanel2 = "
                <nav id='nav2'>
                </nav>
                ";
            }
        }

        $footerText = "
        <footer id='footer' class='clearfix ym-clearfix'>
            {$footerPanel}
        </footer>
        ";

        if($cpCfg['cp.fullWidthTemplte']){;
            $footerText = "
            <footer id='footer'>
                <a id='navigation' name='navigation'></a>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            ";
        }

        $navInsideHeader = '';
        $navOutsideHeader = '';
        if($cpCfg['cp.placeNavInsideHeaderTag']){
            $navInsideHeader = $navPanel;
        } else {
            $navOutsideHeader = $navPanel;
        }

        $footerInside = '';
        $footerOutside = '';
        if($cpCfg['cp.placeFooterOutsidePageTag']){
            $footerOutside = $footerText;
        } else {
            $footerInside = $footerText;
        }

        if ($mainBottomPanel != ''){
            $mainBottomPanel = "
            <div class='mainBottom'>
                {$mainBottomPanel}
            </div>
            ";
        }

        $logoLink = $this->getLogoLink();
        $refreshSiteOneTime = $fn->getSessionParam('refreshSiteOneTime');
        $hostName   = $_SERVER['HTTP_HOST'];

        $mainInner = "
        <div class='mainInner'>
            <div id='col3' class='ym-col3'>
                <div id='col3_content' class='clearfix ym-clearfix ym-cbox'>
                    <a id='contentMain' name='contentMain'></a>
                    {$bannerInCol3}
                    {$bodyPanel}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <header id='header'>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                      {$headerPanel}
                      <a id='logo' href='{$logoLink}'><span class='hideme ym-hideme'>Logo</span></a>
                      {$navInsideHeader}
                    </div>
                </div>
            </header>
            {$navOutsideHeader}
            {$banner}
            <div id='main' class='{$pageCSSClass} clearfix ym-clearfix'>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <div id='extended'>
                <div class='page_margins ym-wrapper'>
                    <div class='page ym-wbox'>
                        {$extendedPanel}
                    </div>
                </div>
            </div>
            {$footerInside}
            {$lastPanel}
            ";
        } else if(!isLoggedInWWW()) {
            $text = "
            <div id='page_margins' class='page_margins ym-wrapper'>
                <div class='page ym-wbox'>
                    <div id='main' class='{$pageCSSClass} clearfix ym-clearfix'>
                        {$mainInner}
                        {$mainBottomPanel}
                    </div>
                </div>
            </div>
            ";
        } else {
            $controller='';
            $parentProfile = '';
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_teacher'){
                $controller="
                <div class='controller'>
                    <a href='/controller/notice/'>controller</a>
                </div>
                ";
            }
            $facebook = '';
            $contactSchool='';
            if($_SESSION['cpLoginTypeWWW'] != 'edukite_teacher'){
                $facebook ="<div class='facebookLike'><a href='https://www.facebook.com/pages/EduKite/124503690986' target='_blank'></a></div>";

                if(strpos($hostName, 'tss') !== false && $teacherKiteId == 1){
                    $contactSchool= "
                    <a class='contactSchoolLink1' href='https://tass.tss.qld.edu.au/kiosk/TIALogin.cfm' target='_blank'>
                        <img src='/cmspilotv30/CP/www/themes/Kite/images/School.png'/>
                    </a>
                    ";
                }
                else{
                    $contactSchool= "
                    <a class='contactSchoolLink' href='/index.php?module=edukiteWeb_notice&_spAction=contactSchoolContent&showHTML=0'>
                        <img src='/cmspilotv30/CP/www/themes/Kite/images/School.png'/>
                    </a>
                    ";
                }
            }
            if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
                $parentProfile ="
                <div class='parentProfile'>
                    <a href='/index.php?module=edukiteWeb_notice&_spAction=parentProfileForm&parent_id={$_SESSION['cpContactId']}&showHTML=0' class=''>Parent Profile</a>
                </div>
                ";
            }

            $text = "
            <div class='page ym-wbox'>
                <header id='header'>
                    {$facebook}
                    {$headerPanel}
                    {$controller}
                    {$parentProfile}
                    <a id='logo' href='{$logoLink}'><span class='hideme ym-hideme'>Logo</span></a>
                    {$navInsideHeader}
                </header>
                <div class='parentLounge'></div>
                <div class='pto'></div>
                <div class='contactSchool'>
                    {$contactSchool}
                </div>
                {$navOutsideHeader}
                {$navPanel2}
                {$banner}
                <div id='main' class='{$pageCSSClass} clearfix ym-clearfix'>
                    {$mainInner}
                    {$mainBottomPanel}
                </div>
                {$footerInside}
            </div>
            {$footerOutside}
            <input type='hidden' id='refreshSiteOneTime' value='{$refreshSiteOneTime}'>
            ";
                //<div class='edukiteIcon'><a href='http://www.edukite.com/' target='_blank'><img src='/cmspilotv30/CP/www/themes/Kite/images/KiteBoy.png'/></a></div>

            $text = "
            {$this->getOuterWrapper($text)}
            {$lastPanel}
            ";
        }

        $themeObj = Zend_Registry::get('currentTheme');

        if (method_exists($themeObj, 'init')) {
            $themeObj->init();
        }

        return $text;
    }

}