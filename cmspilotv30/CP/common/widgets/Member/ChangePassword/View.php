<?
class CP_Common_Widgets_Member_ChangePassword_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        $text = "
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');
        
        $c = &$this->controller;

        $formAction = $c->formAction;
        $exp['password'] = 1;
        $exp['disableAutoComplete'] = true;

        $infoText = $ln->gd2('w.member.changePassword.info');

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        $dialogMessage = '';
        if ($c->showSuccessMsgInDialog){
            $dialogMessage = "<input name='dialogMessage' type='hidden' value='{$ln->gd('w.member.changePassword.message.success')}' />";
        }

        $submitBtn = '';
        if (!$c->showFormInModal){
            $submitBtn = "
            <div class='type-button'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                    </div>
                </div>
            </div>
            ";
        }

        $text = "
        <form name='changePasswordForm' id='changePasswordForm' class='yform columnar cpJqForm linearize-form' method='post' action='{$c->formAction}'>
            <fieldset>
                <h1>{$ln->gd('w.member.changePassword.heading')}</h1>
                {$infoText}
                {$formObj->getTextBoxRow($ln->gd('w.member.changePassword.form.fld.oldPassword.lbl'), 'old_password', '', $exp)}
                {$formObj->getTextBoxRow($ln->gd('w.member.changePassword.form.fld.newPassword.lbl'), 'new_password1', '', $exp)}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'new_password2', '', $exp)}
                {$submitBtn}
                <input type='submit' name='x_submit' class='submithidden' />
                {$dialogMessage}
                {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            </fieldset>
        </form>
        ";

        return $text;
    }
}