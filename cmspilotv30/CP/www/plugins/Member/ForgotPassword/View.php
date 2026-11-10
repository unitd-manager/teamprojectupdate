<?
class CP_Www_Plugins_Member_ForgotPassword_View extends CP_Common_Lib_PluginViewAbstract
{

    /**
     *
     */
    function getView($exp = array()) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $infoText = $ln->gd2('p.member.forgotPassword.form.info');
        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        $showSubmitBtn = $fn->getIssetParam($exp, 'showSubmitBtn', false);
        $submitButton  = '';
        if($showSubmitBtn){
            $submitButton = "
            <div class='type-button'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                    </div>
                </div>
            </div>
            ";
        }

        $formAction = '/index.php?plugin=member_forgotPassword&_spAction=submit&showHTML=0';
        $text = "
        <form name='forgotPasswordForm' id='forgotPasswordForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                {$infoText}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                <input type='hidden' name='dialogMessage' value='{$ln->gd('p.member.forgotPassword.form.message.success')}' />
                <input type='submit' name='x_submit' class='submithidden' />
                {$submitButton}
            </fieldset>
        </form>
        ";

        return $text;
    }
}