<?
class CP_Admin_Themes_Trade_View extends CP_Admin_Lib_ThemeViewAbstract
{
    var $jssKeys = array('blend-2.2', 'jscrollpane-2.0', 'noty-2.0.3');

    /**
     *
     */
    function getNavPanel(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mainNav = Zend_Registry::get('mainNav');
        $rooms = $mainNav->getRooms($modulesArr);

        $text = "
        <div class='floatbox'>
        <div class='roomsWrapper'>
            <div class='hlist noBg'>
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

        $loginTitle = '';
        if ($cpCfg['cp.imsLoginText'] == 1) {
            $loginTitle ="
            <div class='floatbox'>
                <div class='float_left'><img src='images/logo-print.jpg'></div>
                <div class='float_left loginTitle'><h1>Welcome to Institute Management System</h1></div>
            </div>
            ";
        }

        if ($cpCfg['cp.manpowerLoginText'] == 1) {
            $loginTitle ="
            <div class='floatbox'>
                <div class='float_left'><img src='images/logo-print.jpg'></div>
                <div class='float_left loginTitle'><h1>Welcome to Man Power Management System</h1></div>
            </div>
            ";
        }

        if ($cpCfg['cp.tradingLoginText'] == 1) {
            $loginTitle ="
            <div class='floatbox'>
                <div class='float_left'><img src='images/logo-print.jpg'></div>
                <div class='float_left loginTitle'><h1>Welcome to Trading Management System</h1></div>
            </div>
            ";
        }

        if ($cpCfg['cp.engextTadingLoginText'] == 1) {
            $loginTitle ="
            <div class='floatbox'>
                <div class='float_left'><img src='images/logo-print.jpg'></div>
                <div class='float_left loginTitle'><h1>Welcome to Trading Management System</h1></div>
            </div>
            ";
        }

        $mainInner = "
        <div class='mainInner'>
            <div id='col3'>
                <div id='col3_content' class='clearfix'>
                    {$loginTitle}
                    {$login->getLoginForm()}
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

        $headerPanel    = $this->getHeaderPanel();
        $navPanel       = $this->getNavPanel();
        $leftPanel      = $this->getLeftPanel();


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

        $mainInner = "
        <div class='mainInner'>
            <aside id='col1'>
                <div id='col1_content' class='clearfix'>
                    {$leftPanel}
                </div>
            </aside>

            <aside id='col2'>
                <div id='col2_content' class='clearfix'>
                    {$rightPanel}
                </div>
            </aside>

            <div id='col3'>
                <div id='col3_content' class='clearfix'>
                    {$bodyPanel}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <nav id='nav2'>
                <div class='page_margins'>
                    <div class='page'>
                        {$navPanel2}
                    </div>
                </div>
            </nav>
            <div id='main' class='{$pageCSSClass} clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
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

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');
        $navPanel       = $this->getNavPanel();

        if (method_exists($clsInst->view, 'getLeftPanel')) {
            $text = $clsInst->view->getLeftPanel();
        } else {
            $text = "
            <div class='leftNav'>
                {$navPanel}
            </div>
            ";
        }
        return $text;
    }
}