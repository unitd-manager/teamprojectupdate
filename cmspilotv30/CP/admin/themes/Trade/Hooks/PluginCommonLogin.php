<?
class CP_Admin_Themes_Trade_Hooks_PluginCommonLogin
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
            
        $url = 'index.php?plugin=member_forgotPassword&_spAction=view&showHTML=0';
        $forgotText = "
        <div class='forgotPasswordLink'>
            <a href='javascript:void(0)' link='{$url}' class='jqui-dialog-form' formId='forgotPasswordForm'
                w='400' h='300' title='{$ln->gd('Recover My Password')}'>
                {$ln->gd('Forgot Password')}
            </a>
        </div>
        ";
        
        $paypal = '';
        if ($cpCfg['p.common.login.adminHasPaypalSubscription']) {
            //one month subscribe code
            /*
            $paypal = "
            <div class='paypalLoginForm float_right'>
            <form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
            <input type='hidden' name='cmd' value='_s-xclick'>
            <input type='hidden' name='hosted_button_id' value='27E34SR4ZD6ME'>
            <input type='image' src='https://www.paypalobjects.com/en_GB/SG/i/btn/btn_subscribeCC_LG.gif' border='0' name='submit' alt='PayPal – The safer, easier way to pay online.'>
            <img alt='' border='0' src='https://www.paypalobjects.com/en_GB/i/scr/pixel.gif' width='1' height='1'>
            </form>
            </div>
            ";
            */
            //one day subscribe code
            $paypal="
            <div class='paypalLoginForm float_right'>
                <form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
                <input type='hidden' name='cmd' value='_s-xclick'>
                <input type='hidden' name='hosted_button_id' value='AKGUDDLMAQXCW'>
                <input type='image' src='https://www.paypalobjects.com/en_GB/SG/i/btn/btn_subscribeCC_LG.gif' border='0' name='submit' alt='PayPal – The safer, easier way to pay online.'>
                <img alt='' border='0' src='https://www.paypalobjects.com/en_GB/i/scr/pixel.gif' width='1' height='1'>
                </form>
            </div>
            ";
        }

        $text = "
        {$paypal}
            
        <div id='loginOuter'>
            <form name='loginForm' id='loginForm' class='yform columnar login cpJqForm' method='post' action='{$formAction}'>
                <fieldset>
                    <h1>Login</h1>
                    <div id='errorDisplayBox'></div>
                    {$formObj->getTextBoxRow('Email', 'email', '', $expEmail)}
                    {$formObj->getTextBoxRow('Password', 'pass_word', '', $expPass)}
                    <input type='submit' name='submit' class='button' value='Login' />
                    <div class='flaotbox'>
                        <div class='float_left'>
                            <div class='type-check mt10'>
                                <input type='checkbox' id='fld_save_login' class='checkBox' name='saveLogin' value='1' />
                                <label for='fld_save_login'>Save Login</label>
                            </div>
                        </div>
                        <div class='float_left'>
                            {$forgotText}
                        </div>                        
                    </div>
                </fieldset>
            </form>
        </div>
        ";

        return $text;
    }


}