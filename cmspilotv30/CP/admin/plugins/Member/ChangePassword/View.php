<?
class CP_Admin_Plugins_Member_ChangePassword_View extends CP_Common_Lib_PluginViewAbstract
{

    /**
     *
     */
    function getChangePasswordForm(){
        $hook = getCPPluginHook('member_changePassword', 'changePasswordForm', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'edit';

        if (!isset($_SESSION['returnUrlAfterLogin'])){
            $_SESSION['returnUrlAfterLogin'] =  @$_SERVER['HTTP_REFERER'];
        }

        $formAction = 'index.php?plugin=member_changePassword&_spAction=changePasswordSubmit&showHTML=0';
        $expPass['password'] = 1;
        $expPass['disableAutoComplete'] = $cpCfg['p.common.login.disableAutoCompleteTextFld'];

        $text = "
        <div id='changePasswordOuter'>
            <form name='changePasswordForm' id='loginchangePasswordForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
                <fieldset>
                    <h1>Change Password</h1>
                    <div id='errorDisplayBox'></div>
                    {$formObj->getTextBoxRow('Old Password', 'old_pass_word', '', $expPass)}
                    {$formObj->getTextBoxRow('New Password', 'pass_word', '', $expPass)}
                    {$formObj->getTextBoxRow('Confirm Password', 'cpass_word', '', $expPass)}
                    <input type='submit' name='submit' class='button' value='Change Password' />
                </fieldset>
            </form>
        </div>
        ";

        return $text;
    }    
}