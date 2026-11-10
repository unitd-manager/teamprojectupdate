<?
class CP_Admin_Themes_Course_View extends CP_Admin_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqForm-3.15','blend-2.2', 'jscrollpane-2.0', 'noty-2.0.3', 'nicescroll-3.2.0');

    /**
     *
     */
    function getHeaderPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module_name = $tv['module'];
        $module_title = $modulesArr[$module_name]['title'];

        $mainNav = includeCPClass('Lib', 'Room', 'Room');
        Zend_Registry::set('mainNav', $mainNav);

        $SERVER = $_SERVER['HTTP_HOST'];

        $logoText = '';

        if (isLoggedInAdmin()) {
            $logoText = "<h1 class='siteTitle'>{$cpCfg['cp.siteTitle']}</h1>";
        } else {
            //$logoText = "<img src='images/logo.png' width='120'/>";
        }

        if ($cpCfg['cp.hasAdminOnly'] == false) {
            $logoText = "<a href='/' target='_blank'>{$logoText}</a>";
        }

        if (isLoggedInAdmin()) {
            if($module_title == 'Home'){
                $logoText = $logoText;
            }else{
                if($tv['action'] != 'list') {
                    $logoText = "<h1 class='siteTitle {$module_name}'><span>".$modulesArr[$module_name]['title'] ." ". "Details</span></h1>";
                }else{
                    $logoText = "<h1 class='siteTitle {$module_name}'><span>{$module_title}</span></h1>";
                }

            }
        }

        $logoText = "
        <div class='logo float_left logoModuleText'>
            {$logoText}
        </div>
        ";

        $rightText = '';
        $cpMultiYearText = '';
        $cpAdminInterfaceLangText = '';
        $cpMultiUniqueSiteText = '';

        //multi country widget
        if (isLoggedInAdmin() && $cpCfg['cp.multiCountry']) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiCountry.ignoreModules'])) {
                $wMultiCountry = getCPWidgetObj('common_multiCountry');

                $cpMultiYearText = "
                <div class='float_left'>
                    <div class='multi-country'>{$wMultiCountry->getWidget()}</div>
                </div>
                ";
            }
        }

        //admin interface langs
        if (isLoggedInAdmin() && $cpCfg['cp.hasAdminInterfaceLangs']) {
            $wAdminTranslation = getCPWidgetObj('common_adminTranslation');

            $cpAdminInterfaceLangText = "
            <div class='float_left'>
                <div class='admin-langs'>{$wAdminTranslation->getWidget()}</div>
            </div>
            ";
        }

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiYears']) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiYear.ignoreModules'])) {
                $wMultiYear = getCPWidgetObj('common_multiYear');

                $cpMultiYearText = "
                <div class='float_left'>
                    <div class='multi-year'>{$wMultiYear->getWidget()}</div>
                </div>
                ";
            }
        }

        $userGroupType = $fn->getSessionParam('userGroupType');

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiUniqueSites'] && $fn->isDeveloper()) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
                $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite');

                $cpMultiUniqueSiteText = "
                <div class='float_right'>
                    <div class='multi-unique-site'>{$wMultiUniqueSite->getWidget()}</div>
                </div>
                ";
            }
        }

        $logged_IN_text_Logout = '';
        $homeMenuDisplay = '';
        $leftMenuShowHide = '';
        $helpAndGetStarted = '';
        $companyNameOnHeader = '';
        $seperatorForModule  = '';
        if (isLoggedInAdmin()) {
            if($module_title == 'Home' || $module_title == 'Dashboard' || $module_title == 'Reports'){
                $leftMenuShowHide = "<a class='leftnavShowHide leftnavShowHideicon'></a>";
            } else {
                $leftMenuShowHide = "<a class='leftnavShowHide'></a>";
            }

            $homeMenuDisplay = "<div>
                                    {$this->getHomeMenuDisplay()}
                                </div>";

            $companyNameOnHeader = '';
            $helpAndGetStarted = "
            <div class='float_right helpAndGetStarted'>
                <div class='helpContent float_right'>
                    <a class='helpContentTask button btn btn-info' module_name='{$module_title}' href='#'>Help</a>
                </div>
                <!--<div class='getStarted float_left'>
                    <a class='getStartedContentTask button btn btn-info' href='#'>Get Started</a>
                </div>-->
            </div>
            ";

            /**/
            if(!changePasswordOnLogin()){
                $logged_IN_text_Logout = "
                    <div class='float_right logoutWrap mainTopRight col-md-2 col-sm-6 col-xs-12 pull-right'>
                        <span class='username txtRight noPadding col-md-12 col-sm-12 col-xs-8'>
                            <span class='glyphicon glyphicon-user mr5'></span>
                            {$_SESSION['userFullName']}
                        </span>
                        <div class='txtRight noPadding col-md-12 col-sm-12 col-xs-4'>
                            <a href='index.php?plugin=common_login&_spAction=logout' class='logout'>
                                <span class='glyphicon glyphicon-log-out'></span> Logout
                            </a>
                        </div>
                    </div>
                ";
            }

            $seperatorForModule = "<span class='seperateIconTop glyphicon glyphicon-chevron-right'></span>";
        }

        $logoHeaderChange = "";
        if (isLoggedInAdmin()) {
            $logoHeaderChange = "
            <div class='float_left headerModuleText'>
                {$seperatorForModule}
                {$logoText}
                <div class='float_left headerCreateLink pt10'>
                    <a class='' href='/admin/index.php?_topRm=main&module=pms_course&_action=new&lang=eng'>
                        <div class='author-toolbar-choice-circle -create-course-circle v-middle'></div>
                        <span class='author-toolbar-choice-title'>Create Course</span>
                    </a>
                </div>
            </div>
            ";
        } else {
            $logoHeaderChange = "
            <div class='float_right'>
                {$logoText}
            </div>
            ";
        }

        $leftMenuShowHide = '';

        $leftText = "
        <div id='header-left'>
            <div class='floatbox'>
                {$logged_IN_text_Logout}
                {$companyNameOnHeader}
                {$cpMultiYearText}
                {$cpAdminInterfaceLangText}
                {$homeMenuDisplay}
                {$leftMenuShowHide}
                {$logoHeaderChange}
                {$cpMultiUniqueSiteText}
            </div>
        </div>
        ";

        if (isLoggedInAdmin()) {
            $topRooms = '';
            if(!changePasswordOnLogin()){
                $topRooms = "
                <div class='float_right'>
                    <div class='hlist noBg'>
                        {$this->getTopRooms()}
                    </div>
                </div>
                ";
            }
            $rightText = "
            <div id='topnav'>
            {$topRooms}
            </div>
            ";
        }

        $actions = '';
        if ($cpCfg['cp.showActionPanelInHeader']) {
            $action = Zend_Registry::get('action');

            if ($tv['action'] != 'new') {
                $actions = "
                <div class='hlist actionBtns noBg'>
                    {$action->getActionButtons()}
                </div>
                ";
            }
        }

        $text = "
        {$leftText}
        {$actions}
        ";

        return $text;
    }


    /**
     *
     */
    function getHomeMenuDisplay(){
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');

        $arrTr = $cpCfg['cp.topRooms'];
        $rowsTr  = '';
        foreach ($arrTr as $key1 => $value) {
            $urlTr = "index.php?_topRm={$key1}&module={$value['default']}";

            if ($cpCfg['cp.hasAccessModule']) {
                if (!$topRoomsArrAccess[$key1]['hasAccess']) {
                    continue;
                }
            }

            //$arr = $cpCfg['cp.topRooms'][$tv['topRm']]['modules'];
            $arr = $value['modules'];
            $rows  = '';
            foreach ($arr as $key => $module) {
                if ($cpCfg['cp.hasAccessModule']) {
                    $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
                    $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                    if ($hasAccess == 0) {
                        continue;
                    }
                }

                $title = $modulesArr[$module]['title'];
                //$url   = $modulesArr[$module]['url'];
                $url = "index.php?_topRm={$key1}&module={$module}";

                if ($tv['module'] == $module) {
                    $rows .= "
                    <li class='active'>
                        <a class='selected nav_{$module}' href='{$url}'><span>{$title}</span></a>
                    </li>\n
                    ";
                } else {
                    $rows .= "
                    <li>
                        <a href='{$url}' class='nav_{$module}'><span>{$title}</span></a>
                    </li>\n
                    ";
                }
            }

            if ($tv['topRm'] == $key1) {
                $rowsTr .= "
                <ul class='float_left ulModuleSet'>
                    <li class='active'>
                        <a class='selected nav_{$key1}' href='{$urlTr}'><span>{$value['title']}</span></a>
                    </li>\n
                    <ul>{$rows}</ul>
                </ul>
                ";
            } else {
                $rowsTr .= "
                <ul class='float_left ulModuleSet'>
                    <li>
                        <a href='{$urlTr}' class='nav_{$key1}'><span>{$value['title']}</span></a>
                    </li>\n
                    <ul class=''>{$rows}</ul>
                </ul>
                ";
            }
        }

        if ($rowsTr != ''){
            $textSiteMap = "
            <ul class='homeTop'>
                <li>
                <font><a>Site Map</a></font>
                <ul class='sub floatbox'>
                    {$rowsTr}
                </ul>
                </li>
            </ul>
            ";


            if(isLoggedInAdmin() && !changePasswordOnLogin()){
                $logged_IN_text_Logout = "
                    <div class='float_right logoutWrap mobileTopRight col-md-2 col-sm-6 col-xs-5 pull-right'>
                        <span class='username txtRight noPadding col-md-12 col-sm-12 col-xs-12'>
                            <span class='glyphicon glyphicon-user mr5'></span>
                            {$_SESSION['userFullName']}
                        </span>
                        <div class='txtRight noPadding col-md-12 col-sm-12 col-xs-12'>
                            <a href='index.php?plugin=common_login&_spAction=logout' class='logout'>
                                <span class='glyphicon glyphicon-log-out'></span> Logout
                            </a>
                        </div>
                    </div>
                ";
            }

            $text = "
            <div class='topLogoContainer'>
                <h3 class='float_left'>{$cpCfg['cp.companyName']}</h3>
                <button type='button' class='navbar-toggle float_right' data-toggle='collapse' data-target='#col1'>
                    <span class='icon-bar'></span>
                    <span class='icon-bar'></span>
                    <span class='icon-bar'></span>                        
                </button>
                {$logged_IN_text_Logout}
            </div>
            ";
        } else {
            $text = '';
        }

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mainNav = Zend_Registry::get('mainNav');
        $rooms = $this->getRooms($modulesArr);

        $text = "
        <div class='floatbox'>
            <div class='roomsWrapper'>
                <div class='hlist noBg'>
                    <span class='glyphicon glyphicon-remove-circle'></span>
                    {$rooms}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel2(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchHTML = Zend_Registry::get('searchHTML');
        $action = Zend_Registry::get('action');
        $pager = Zend_Registry::get('pager');

        $langBtns = '';
        $searchText = '';
        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        if (($tv['action'] == 'edit' || $tv['action'] == 'detail')
            && $modulesArr[$tv['module']]['hasMultiLang'] == 1
            && $cpCfg['cp.multiLang'] == 1
                ){
            $wLang = getCPWidgetObj('common_language');
            $langBtns = $wLang->getWidget();
        }

        $actions = '';
        if ($tv['action'] != 'new') {
            $actions = $action->getActionButtons();

            if($tv['action'] == 'edit' || $tv['action'] == 'detail'){
                $actions .=" 
                <div class='float_right backToList'>
                    {$pager->getBackButton()}
                </div>";
            }
        }

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$searchText}
                {$langBtns}
            </div>
            <div class='float_right'>
                <div class='hlist actionBtns'>
                    {$actions}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getExtendedPanel(){
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
            </div>
            <div class='float_right'>
                {$this->getPagerPanel()}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLoginThemeOutput() {
        $login = getCPPluginObj('common_login');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $headerPanel = $this->getHeaderPanel();
        $footerPanel = $this->getFooterPanel();

        $loginTitle ="
        <div class='floatbox'>
            <div class='col-md-6 noPadding'>
                <table class='table loginLeftPanelTableLogo'>
                    <tr>
                        <td class='loginTitle txtCenter' colspan='2'><h1>Welcome To<br/>Adult Course Management System</h1></td>
                    </tr>
                </table>
            </div>
        </div>
        ";

        $mainInner = "
        <div class='mainInner'>
            <div id='col3'>
                {$loginTitle}
                <div id='col3_content' class='clearfix'>
                    <div class='loginPanelLogin col-md-12 card-header card-header-tabs card-header-default '>
                        <div class='col-md-6 noPadding leftBackgroundSignInImages'>
                            <img src='/admin/images/signup-1.png' class='img-responsive' alt='Sign In'/>
                        </div>
                        <div class='col-md-offset-1 col-md-3 rightSideLoginPanel'>
                            {$login->getLoginForm()}
                        </div>
                    </div>
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <div class='tplLogin'>
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            </div>
            ";

        } else {
            $text = "
            <div class='page_margins tplLogin'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    <div id='main' class='clearfix floatbox'>
                        {$mainInner}
                    </div>
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getLoginThemeOutput1() {
        $login = getCPPluginObj('common_login');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $headerPanel = $this->getHeaderPanel();
        $footerPanel = $this->getFooterPanel();

        $loginTitle ="
        <div class='floatbox'>
            <div class='col-md-12 noPadding'>
                <table class='table loginLeftPanelTableLogo'>
                    <tr>
                        <td colspan='2'></td>
                    </tr>
                    <tr>
                        <td colspan='2'></td>
                    </tr>
                    <tr>
                        <td colspan='2'></td>
                    </tr>
                    <tr>
                        <td colspan='2'></td>
                    </tr>
                    <tr>
                        <td colspan='2'></td>
                    </tr>
                    <tr>
                        <td colspan='2'></td>
                    </tr>
                    <tr>
                        <td class='loginTitle txtCenter' colspan='2'><h1>{$cpCfg['cp.adminLoginFormWelcomeText']}</h1></td>
                    </tr>
                </table>
            </div>
        </div>
        ";

        $paymentReminder = '';
        if ($cpCfg['paymentReminder'] == 1) {
            $paymentReminder ="
            <div class='paymentReminderTextScroll'><marquee><strong>KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </strong></marquee></div>
            ";
        }

        $mainInner = "
        <div class='mainInner'>
            <div id='col3'>
                <div id='col3_content' class='clearfix'>
                    <div class='loginPanelLogin col-md-12 card-header card-header-tabs card-header-default '>
                        <div class='col-md-6 noPadding'>
                            {$loginTitle}
                        </div>
                        <div class='col-md-offset-1 col-md-3 rightSideLoginPanel'>
                            {$login->getLoginForm()}
                        </div>
                    </div>
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <div class='tplLogin'>
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            </div>
            ";

        } else {
            $text = "
            <div class='page_margins tplLogin'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    <div id='main' class='clearfix floatbox'>
                        {$mainInner}
                    </div>
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getMainThemeOutput() {
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $viewHelper = Zend_Registry::get('viewHelper');
        $formObj  = Zend_Registry::get('formObj');

        $headerPanel    = $this->getHeaderPanel();
        $navPanel       = $this->getNavPanel();
        $leftPanel      = $this->getLeftPanel();
        $moduleName     = $tv['module'];
        $moduleTitle = $modulesArr[$moduleName]['title'];
        if($tv['action'] != 'list') {
            $moduleTitle = $modulesArr[$moduleName]['title'] .' '. 'Details';
        }

        $rightPanel = '';
        if ($tv['action'] == 'list') {
            $rightPanel = $this->getListRightPanel();
        }
        $bodyPanel      = $this->getBodyPanel();
        $navPanel2      = $this->getNavPanel2(); //note: this line has to be below the $bodyPanel
        $extendedPanel  = $this->getExtendedPanel();
        $footerPanel    = $this->getFooterPanel();
        $pageCSSClass   = $viewHelper->getPageCSSClass();
        $pagerPanel = '';
        if($cpCfg['cp.showPagerPanelInFooter']){
            $pagerPanel = "
            <div class='float_left pagelinksBottom'>
                {$this->getPagerPanel()}
            </div>
            ";
        }
        
        $leftCol = '';
        if($tv['module'] != 'hms_home'){
            $leftCol = "
            <aside id='col1' style=''>
                <div id='col1_content' class='clearfix'>
                    {$leftPanel}
                </div>
            </aside>
            ";
        }

        $paymentReminder = '';
        if ($cpCfg['paymentReminder'] == 1) {
            $paymentReminder ="
            <div class='paymentReminderTextScroll'><marquee><strong>KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </strong></marquee></div>
            ";
        }

        if($tv['module'] != 'hms_home' && $tv['module'] != "common_dashboard"){
            $mainInner = "
            <div class='mainInner'>
                {$paymentReminder}
                {$leftCol}
                <aside id='col2'>
                    <div id='col2_content' class='clearfix'>
                        {$rightPanel}
                    </div>
                </aside>

                <div id='col3' class='fullleftlist'>
                    <div id='col3_content' class='clearfix contentScroller'>
                        <div id='goTop'></div>
                        <div class='card'>
                            <div class='actionBarPanel card-header card-header-tabs card-header-info'>
                                <nav id='nav2'>
                                    <div class=''>
                                        <div class='page'>
                                            {$navPanel2}
                                        </div>
                                    </div>
                                </nav>
                            </div>
                            <div class='card-body table-responsive mt40'>
                                {$bodyPanel}
                            </div>
                        </div>
                    </div>
                    <div id='ie_clearing'>&nbsp;</div>
                </div>
            </div>
            ";
        } else {
            $mainInner = "
            <div class='mainInner'>
                {$paymentReminder}
                {$leftCol}
                <aside id='col2'>
                    <div id='col2_content' class='clearfix'>
                        {$rightPanel}
                    </div>
                </aside>

                <div id='col3' class='fullleftlist'>
                    <div id='col3_content' class='clearfix contentScroller'>
                        <div id='goTop'></div>
                        <nav id='nav2'>
                            <div class=''>
                                <div class='page'>
                                    {$navPanel2}
                                </div>
                            </div>
                        </nav>

                        {$bodyPanel}
                    </div>
                    <div id='ie_clearing'>&nbsp;</div>
                </div>
            </div>";
        }

        if($cpCfg['cp.fullWidthTemplte']){
            $inputHiddenForActivation = "
            <input type='hidden' name='paymentReminder2' value='{$cpCfg['paymentReminder2']}' />
            ";

            $text = "
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='{$pageCSSClass} clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
                {$inputHiddenForActivation}
            </div>
            <div id='extended'>
                <div class='page_margins'>
                    <div class='page'>
                        {$extendedPanel}
                    </div>
                </div>
            </div>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            ";

        } else {

            if ($navPanel != ''){
                $navPanel = "
                <nav id='nav'>
                    {$navPanel}
                </nav>
                ";
            }

            if ($navPanel2 != ''){
                $navPanel2 = "
                <nav id='nav2'>
                    {$navPanel2}
                </nav>
                ";
            }

            $text = "
            <div class='page_margins'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    {$navPanel}
                    {$navPanel2}

                    <div id='main' class='{$pageCSSClass} clearfix'>
                        {$mainInner}
                    </div>
                    {$pagerPanel}
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        $logged_in = $fn->getReqParam('logged_in');
        $random_id = $fn->getReqParam('random_id');
        if ($logged_in == 1 && $random_id != "" && $cpCfg['cp.autoLoginToIntranet'] == 1){
            $autoLoginUrl = $cpCfg['intranetUrl'] . "index.php?_spAction=autoLoginUserByRandomID&showHTML=0&random_id={$random_id}";
            $text .= "<iframe id='utilFrame' name='utilFrame' class='utilFrame' src='{$autoLoginUrl}'></iframe>";
        }
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-3.15'));
        return $text;
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module = $modulesArr[$tv['module']];
        $scrollContent = $module['scrollContent'];

        $actionName = ucfirst($tv['action']);
        $actionTemp = "get{$actionName}";  //eg: getList
        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);

            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                    'clsName' => $clsName
                    , 'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $text = $clsInst->$actionTemp();
        if ($scrollContent) {
            $text = "
            <div class='listTblWrapper'>
                {$text}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');
        $navPanel       = $this->getNavPanel();

        /*if (method_exists($clsInst->view, 'getLeftPanel')) {
            $text = $clsInst->view->getLeftPanel();
        } else {*/
            $text = "
            <div class='leftNav'>
                {$navPanel}
            </div>
            ";
        //}
        return $text;
    }

    /**
     *
     */
    function getFooterPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $footerLink = "";
        if ($cpCfg['cp.footerCompanyLink'] != '') {
            $footerLink = "
            <div class='float_right mr10'>Powered by:
                {$cpCfg['cp.footerCompanyLink']}
            </div>
            ";
        } else {
            $footerLink = "
            <div class='float_right mr10'>Powered by:
                <a href='http://www.cubosale.in'>cubosale.in</a>
            </div>
            ";
        }

        $text = "
        <div class='floatbox'>
            <div class='float_left version ml10'>
                version: {$cpCfg['cp.frameworkName']} {$cpCfg['cp.version']}
            </div>
            {$footerLink}
        </div>
        ";

        return $text;
    }

    //==================================================================//
    function getTopRooms($seperator = "") {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');

        $arr = $cpCfg['cp.topRooms'];

        $rows = "";

        foreach ($arr as $key => $value) {
            $url = "index.php?_topRm={$key}&module={$value['default']}";

            if ($cpCfg['cp.hasAccessModule']) {
                if (!$topRoomsArrAccess[$key]['hasAccess']) {
                    continue;
                }
            }

            if ($tv['topRm'] == $key) {
                $rows .= "
                <li class='active'>
                    <a class='selected nav_{$key}' href='{$url}'><span>{$value['title']}</span></a>
                </li>\n
                ";
            } else {
                $rows .= "
                <li>
                    <a href='{$url}' class='nav_{$key}'><span>{$value['title']}</span></a>
                </li>\n
                ";
            }
        }

        if ($rows != ""){
            $text = "<ul>{$rows}</ul>";
        } else {
            $text = "";
        }

        return $text;
    }

    /**
     *
     */
    function getRooms($seperator = "") {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');

        $arrTr = $cpCfg['cp.topRooms'];
        $rowsTr  = '';

        foreach ($arrTr as $key1 => $value) {
            $urlTr = "index.php?_topRm={$key1}&module={$value['default']}";

            if ($cpCfg['cp.hasAccessModule']) {
                if (!$topRoomsArrAccess[$key1]['hasAccess']) {
                    continue;
                }
            }

            //$arr = $cpCfg['cp.topRooms'][$tv['topRm']]['modules'];
            $arr = $value['modules'];
            $rows  = '';
            foreach ($arr as $key => $module) {
                if ($cpCfg['cp.hasAccessModule']) {
                    $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
                    $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                    if ($hasAccess == 0) {
                        continue;
                    }
                }

                $title = $modulesArr[$module]['title'];
                //$url   = $modulesArr[$module]['url'];
                $url   =  "index.php?_topRm={$key1}&module={$module}";

                if ($tv['module'] == $module) {
                    $rows .= "
                    <li class='active'>
                        <a class='selected nav_{$module}' href='{$url}'><span>{$title}</span></a>
                    </li>\n
                    ";
                } else {
                    $rows .= "
                    <li>
                        <a href='{$url}' class='nav_{$module}'><span>{$title}</span></a>
                    </li>\n
                    ";
                }
            }

            if ($tv['topRm'] == $key1) {
                if($key1 != 'common_dashboard'){
                    $rowsTr .= "
                    <ul>
                        <li class='active'>
                            <a class='selected nav_{$key1}'><span>{$value['title']}</span></a>
                        </li>\n
                        <ul>{$rows}</ul>
                    </ul>
                    ";
                } else {
                    $rowsTr .= "
                    <ul>
                        <ul>{$rows}</ul>
                    </ul>
                    ";                        
                }
            } else {
                if($key1 != 'common_dashboard'){
                    $rowsTr .= "
                    <ul>
                        <li>
                            <a class='nav_{$key1}'><span>{$value['title']}</span></a>
                        </li>\n
                        <ul class='displayNone'>{$rows}</ul>
                    </ul>
                    ";
                } else {
                    $rowsTr .= "
                    <ul>
                        <ul class=''>{$rows}</ul>
                    </ul>
                    ";
                }
            }
        }


        if ($rowsTr != ''){
            $text = "{$rowsTr}";
        } else {
            $text = '';
        }

        return $text;
    }

    /**
     *
     * @return <type>
     */
    function getBackButton($returnUrlOnly = false){
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');

        if ($cpCfg['cp.useSEOUrl'] == 1){
            $url = "javascript:history.back();";
        } else {
            $listLimit  =  $modulesArr[$tv['module']]['listLimit'];
            $page = ceil($this->startRecordNo / $listLimit);
            $searchQueryString = $this->removeQueryString(array("_page", "_action", 'record_id'));
            $url = $searchQueryString . $cpUrl->getQnMarkForUrl($searchQueryString)
                                      . $cpUrl->getAmpForUrl($searchQueryString)
                                      . "_action=list&_page=" . $page;
        }

        if ($returnUrlOnly){
            return $url;
        } else {
            $text = "
            <a href='{$url}'>
                Back To List
            </a>
            ";
            return $text;
        }
    }
}