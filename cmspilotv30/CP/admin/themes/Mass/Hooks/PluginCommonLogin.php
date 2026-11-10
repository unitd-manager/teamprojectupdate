<?
class CP_Admin_Themes_Mass_Hooks_PluginCommonLogin
{
    /**
     *
     */
    function getLoginForm(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');


        if (!isset($_SESSION['returnUrlAfterLogin'])){
            $_SESSION['returnUrlAfterLogin'] =  @$_SERVER['HTTP_REFERER'];
        }
    
        $formAction = 'index.php?plugin=common_login&_spAction=loginSubmit&showHTML=0';
        $expPass['password'] = 1;
        $expPass['disableAutoComplete'] = $cpCfg['p.common.login.disableAutoCompleteTextFld'];

        $expEmail = array('disableAutoComplete' => $cpCfg['p.common.login.disableAutoCompleteTextFld']);
            
        $text = "
        <div id='loginOuter'>
            <form name='loginForm' id='loginForm' class='yform columnar login cpJqForm' method='post' action='{$formAction}'>
                <fieldset>
                    <h1>Login</h1>
                    <div id='errorDisplayBox'></div>
                    {$formObj->getTextBoxRow('Email', 'email', '', $expEmail)}
                    {$formObj->getTextBoxRow('Password', 'pass_word', '', $expPass)}
                    <input type='submit' name='submit' class='button' value='Login' />
                </fieldset>
            </form>
        </div>
        ";

        return $text;
    }


}