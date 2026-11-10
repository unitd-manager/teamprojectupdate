<?
class CP_Admin_Themes_Pos_View extends CP_Admin_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqJKey-1.1', 'jscrollpane-2.0');

    /**
     *
     */
	function getHeaderPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mainNav = includeCPClass('Lib', 'Room', 'Room');
        Zend_Registry::set('mainNav', $mainNav);

        $rooms = '';
        if (isLoggedInAdmin()){
            $rooms = $mainNav->getRooms($modulesArr);
        } else {
            $_SESSION['returnUrlAfterLogin'] = 'index.php?module=common_dashboard';
        }

        $text = "
        <a class='logo' href='index.php?module=common_dashboard'>
        </a>
        <div id='nav'>
            <div class='roomsWrapper'>
                <div class='hlist noBg'>
                    {$rooms}
                </div>
            </div>
        </div>
        <div class='back'><a href='javascript:history.go(-1);'>< back </a></div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
    }

    /**
     *
     */
    function getNavPanel2(){
    }

    /**
     *
     */
	function getFooterPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : '';
        $session_terminal_id = isset($_SESSION['terminalId']) ? $_SESSION['terminalId']  : '';
        $shopTitle = '';
        $terminalTitle = '';

        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $shop_title = $shop['title'];
            $shopTitle = " | Shop: {$shop_title}";
        }

        if($session_terminal_id != ''){
            $terminal = $fn->getRecordRowByID('terminal', 'terminal_id', $session_terminal_id);
            $terminal_title = $terminal['title'];
            $terminalTitle = " | Terminal: {$terminal_title}";
        }

        if (isLoggedInAdmin()){
            $text = "
            <div class='floatbox'>
                <div class='float_left logoutWrap'>
                    <span class='welcome'>Welcome</span>
                    <span class='username'>{$_SESSION['userFullName']}</span> |
                    <a href='index.php?plugin=common_login&_spAction=logout' class='logout mr10'>Logout
                    </a>
                    {$shopTitle} {$terminalTitle}
                </div>
                <div class='float_right'>
                    {$this->getPagerPanel()}
                </div>
            </div>
            ";
        } else {

            $footerLink = "";
            if ($cpCfg['cp.footerCompanyLink'] != '') {
                $footerLink = "
                <!--
                <div class='float_right'>Powered by:
                    {$cpCfg['cp.footerCompanyLink']}
                </div>
                -->
                ";
            }

            $text = "
            <div class='floatbox'>
                <div class='float_left version'>
                    version: {$cpCfg['cp.frameworkName']} {$cpCfg['cp.version']}
                </div>
                {$footerLink}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $searchHTML = Zend_Registry::get('searchHTML');
        $clsInst = Zend_Registry::get('currentModule');

        $actionName = ucfirst($tv['action']);
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

        $searchText = '';
        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        $text = "
        <div class='contentScroller listTblWrapper'>
            <div class='contentScrollerInner'>
                <div class='floatbox'>
                    <div class='float_right'>
                        {$searchText}
                    </div>
                </div>
                {$clsInst->$actionTemp()}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');
        $action = Zend_Registry::get('action');

        $actions = '';
        if ($tv['action'] != 'new') {
            $actions = $action->getActionButtons();
        }

        $text = "
        <div class='actionsWrapper'>
            <div class='actionBtns noBg'>
                {$actions}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
	function getSmartCardLoginForm() {
        $text = "
        <form action='index.php?_theme=pos&_spAction=smartCardLoginSubmit&showHTML=0' name='frmSignInOut' id='frmSmartLogin' method='post'>
            <input type='text' id='smartCardId' name='smartCardId' value='' onblur='javascript:this.focus();'>
            <input type='hidden' name='loginBySmartCard' value='1'>
            <input type='submit' class='submithidden'>
            <div id='smartIdErr'></div>
        </form>
        ";
        
        return $text;
    }

}