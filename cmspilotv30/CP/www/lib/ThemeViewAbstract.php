<?
abstract class CP_Www_Lib_ThemeViewAbstract extends CP_Common_Lib_ThemeViewAbstract
{
    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');

        $loginText = '';
        $cartText = '';
        $siteSearch = '';
        $topMostRooms = '';
        $langToggle  = '';
        $logoText = '';
        $socialIcons = '';
        $registerText = '';

        if ($cpCfg['cp.showLoginInfoAtTheTop']){
            $pLogin = getCPPluginObj('member_login');
            $loginText = "
            {$pLogin->view->getLoginInfoText()}
            ";
        }

        if ($cpCfg['cp.showRegisterInfoAtTheTop']){
            $registerUrl = $cpUrl->getUrlBySecType('Register');
            $registerText = "
            <a class='btnRegister' href='{$registerUrl}'>
                <span>{$ln->gd('w.member.loginForm.btn.register')}</span>
            </a>
            ";
        }

        if ($cpCfg['cp.showViewCartAtTheTop']){
            $wBasket = getCPWidgetObj('ecommerce_addToCart');
            $cartText = "
            {$wBasket->view->getViewBasketText()}
            ";
        }

        if ($cpCfg['cp.showSiteSearchAtTheTop']){
            $siteSearch = "
            {$pSiteSearch->view->getSearchBox()}
            ";
        }

        if ($cpCfg['cp.showTopMostSections']){
            $mainNav = getCPWidgetObj('core_mainNav');
            $topMostRooms = "{$mainNav->getWidget(array(
                 'btnPos' => 'Top Most'
            ))}
            ";
        }

        if ($cpCfg['cp.showLangToggleAtTheTop']){
            $wLang = getCPWidgetObj('common_language');
            $langToggle = "{$wLang->getWidget(array(
                'hideCurrLang' => $cpCfg['cp.hideCurrentLang']
            ))}";
        }

        if ($cpCfg['cp.showSocialIconsInHeader']){
            $wRecordSocial = getCPWidgetObj('content_record');
            $socialIcons = "
            <div class='socialMediaIcons'>
                {$wRecordSocial->getWidget(array(
                     'contentType' => 'Social Media Icons'
                ))}
            </div>
            ";
        }

        if ($cpCfg['cp.showLogoText']){
            $logoText = "<div class='logoText'>{$ln->gd('cp.logoText')}</div>";
        }

        $text = "
        {$topMostRooms}
        {$loginText}
        {$registerText}
        {$cartText}
        {$siteSearch}
        {$langToggle}
        {$logoText}
        {$socialIcons}
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        if ($cpCfg['cp.showMainNavPanelAtTop']){

            $extraClass = '';
            if ($cpCfg['cp.showNavAsMenu']){
                $superFish = getCPWidgetObj('menu_superFish');
                $widget = "{$superFish->getWidget(array(
                    'btnPos' => 'Top'
                ))}
                ";

                $extraClass = 'hasMenu clearfix ym-clearfix';
                $text = "
                <nav id='nav' class='hasMenu clearfix ym-clearfix'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                </nav>
                ";
            } elseif ($cpCfg['cp.showNavAsMegaMenu']){
                $megaMenu = getCPWidgetObj('menu_megaMenu');
                $widget = "{$megaMenu->getWidget(array(
                ))}
                ";

                $extraClass = 'hasMenu clearfix ym-clearfix';
                $text = "
                <nav id='nav' class='hasMenu clearfix ym-clearfix'>
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
                    <div class='page_margins ym-wrapper'>
                        <div class='page ym-wbox'>
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
    function getBannerPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $banner = '';
        if ($cpCfg['cp.showBannerBelowHeader']){
            $wBanner = getCPWidgetObj('media_banner');
            $arr = array();
            if (isset($cpCfg['w.media.banner.calloutContentType'])) {
                $arr[] = $cpCfg['w.media.banner.calloutContentType'];
            }
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
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                     'clsName' => $clsName
                    ,'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $text = "
        <div class='bodyPanel'>
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getExtendedPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $clsInst = Zend_Registry::get('currentModule');

        if($cpCfg['cp.fullWidthTemplte']){
            if (method_exists($clsInst, 'getExtendedPanel')) {
                $text = $clsInst->getExtendedPanel($clsInst->model->dataArray);
                return $text;
            }
        }
    }

    /**
     *
     */
    function getLastPanelOutsideTemplate(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $clsInst = Zend_Registry::get('currentModule');

        if (method_exists($clsInst, 'getLastPanelOutsideTemplate')) {
            $text = $clsInst->getLastPanelOutsideTemplate();
            return $text;
        }
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = getCPWidgetObj('core_subNav');
        $clsInst = Zend_Registry::get('currentModule');

        if (method_exists($clsInst, 'getLeftPanel')) {
            $text = $clsInst->getLeftPanel();
        } else {
            $text = "
            <h6 class='vlist'>{$tv['secTitle']}</h6>
            {$subNav->getWidget()}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getMainBottomPanel(){
    }

    /**
     *
     */
    function getRightPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
            $text = "";
        }

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
            </div>
        </div>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');
        $pageCSSClass = $viewHelper->getPageCSSClass();

        $banner = $this->getBannerPanel();

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
                    {$navPanel2}
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

        $mainInner = "
        <div class='mainInner'>
            <aside id='col1' class='ym-col1'>
                <div id='col1_content' class='clearfix ym-clearfix ym-cbox-left'>
                    {$leftPanel}
                </div>
            </aside>
            <aside id='col2' class='ym-col2'>
                <div id='col2_content' class='clearfix ym-clearfix ym-cbox-right'>
                    {$rightPanel}
                </div>
            </aside>
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
        } else {
            $text = "
            <div class='page ym-wbox'>
                <header id='header'>
                    {$headerPanel}
                    <a id='logo' href='{$logoLink}'><span class='hideme ym-hideme'>Logo</span></a>
                    {$navInsideHeader}
                </header>
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
            ";

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

    /**
     *
     */
    function getOuterWrapper($text) {
        $fn = Zend_Registry::get('fn');

        $text = "
        <div id='page_margins' class='page_margins ym-wrapper'>
            {$text}
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getLoginThemeOutput() {
        $fn = Zend_Registry::get('fn');
        $wLogin = getCPWidgetObj('member_loginForm');

        $text = "
        <div class='page_margins loginPage'>
            <div class='page ym-wbox'>
                <header id='header'>
                    {$this->getHeaderPanel()}
                </header>
                <div id='main' class='hideboth loginTpl'>
                    <div id='col3'>
                        <div id='col3_content' class='clearfix ym-clearfix ym-cbox'>
                            {$wLogin->getWidget(array(
                                 'hasRegiserInfo' => false
                                ,'hasForgotPass'  => false
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getLogoLink() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $logoLink = '/';
        if($cpCfg['cp.hasMultiYears'] && $cpCfg['cp.hasMultiUniqueSites']) {
            $cp_site = $fn->getReqParam('cp_site');
            $cp_year = $fn->getReqParam('cp_year');
            $logoLink = '/' . $cp_site . '/' . $cp_year . '/';

        } else if($cpCfg['cp.hasMultiYears']){
            $cp_year = $fn->getReqParam('cp_year');
            $logoLink = '/' . $cp_year . '/';
        } else if($cpCfg['cp.multiLang']){
            $logoLink = '/' . $tv['lang'] . '/';
        }

        return $logoLink;
    }
}
