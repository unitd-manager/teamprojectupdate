<?
class CPL_Admin_Themes_Blue_View extends CP_Admin_Themes_Blue_View
{
    /**
     *
     */
    function getHeaderPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $mainNav = includeCPClass('Lib', 'Room', 'Room');
        Zend_Registry::set('mainNav', $mainNav);

        $SERVER = $_SERVER['HTTP_HOST'];

        $logoText = '';
        
        $logoText = "<h1 class='siteTitle'>{$cpCfg['cp.siteTitle']}</h1>";

        if ($cpCfg['cp.hasAdminOnly'] == false) {
            $logoText = "<a href='/' target='_blank'>{$logoText}</a>";
        }

        $logoText = "
        <div class='logo float_left'>
            {$logoText}
        </div>
        ";

        $rightText = '';
        $cpMultiYearText = '';
        $cpAdminInterfaceLangText = '';
        $cpMultiUniqueSiteText = '';
        $poweredTxt = '';

        if (!isLoggedInAdmin()){
            $poweredTxt = "
            <div class='float_right mt5'>
                <div class='cubosaleTxtInHeader'>Powered by: <a href='http://www.cubosale.com/' target='_blank'>CUBOSALE</a></div>
            </div>
            ";
        }

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

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiUniqueSites'] && $fn->isDeveloper()) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
                $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite');

                $cpMultiUniqueSiteText = "
                <div class='float_left'>
                    <div class='multi-unique-site'>{$wMultiUniqueSite->getWidget()}</div>
                </div>
                ";
            }
        }

        $leftText = "
        <div id='header-left'>
            <div class='floatbox'>
                <div class='float_left'>
                    {$logoText}
                </div>
                {$poweredTxt}
                {$cpMultiYearText}
                {$cpAdminInterfaceLangText}
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
                        {$mainNav->getTopRooms()}
                    </div>
                </div>                    
                ";
            }
            $rightText = "
            <div id='topnav'>
                <div class='floatbox'>
                    <div class='float_right logoutWrap'>
                        <span class='username'>{$ln->gd('cp.lbl.welcome', 'Welcome')} {$_SESSION['userFullName']}</span>
                        <a href='index.php?plugin=common_login&_spAction=logout' class='logout'>
                            {$ln->gd('cp.lbl.logout', 'Logout')}
                        </a>
                    </div>
                    {$topRooms}
                </div>
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
        {$rightText}
        {$actions}
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

        $loginText = $fn->getSettingsValueByKey("cp.loginText");

        $mainInner = "
        <div class='mainInner'>
            <div id='col3'>
                <div id='col3_content' class='clearfix'>
                    <div class='floatbox'>
                        <div class='float_left logoInLoginPage'><img src='images/logo.jpg' width='70%'></div>
                        <div class='txtCenter loginTitle'><h1>Welcome to KRS CRM Software</h1></div>
                    </div>
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
}