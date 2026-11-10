<?
class CP_Www_Plugins_PaymentMethods_MoneyBookers_View extends CP_Common_Lib_PluginViewAbstract
{
    /**
     *
     */
    function getLoginInfoText() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        if (isLoggedInWWW()){
            $text = $this->getLogoutLink();
        } else {
            $text = $this->getLoginLink();
        }

        $text = "
        <div class='loginInfoText'>
            {$text}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLoginLink() {
        $cpCfg = Zend_Registry::get('cpCfg');

        /** if there is a hook in the current then use that **/
        $theme = getCPThemeObj($cpCfg['cp.theme']);

        if (method_exists($theme->fns, 'getLoginLink')){
                return $theme->fns->getLoginLink();
        }

        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $loginUrl = $cpUrl->getUrlBySecType('Login');
        $text = "
        <a class='btnLogin' href='{$loginUrl}'>
            <span>{$ln->gd('w.member.loginForm.form.lbl.login')}</span>
        </a>
        ";
        return $text;
    }

    /**
     *
     */
    function getLogoutLink() {
        $ln = Zend_Registry::get('ln');
        $logoutUrl = '/index.php?plugin=member_login&_spAction=logout';

        $text = "
        {$ln->gd('p.member.login.lbl.welcome')} {$_SESSION['cpUserFullNameWWW']} |
        <a class='btnLogout' href='{$logoutUrl}'>
            <span>{$ln->gd('logout')}</span>
        </a>
        ";
        return $text;
    }
}