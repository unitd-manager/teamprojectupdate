<?
class CP_Www_Widgets_Member_ResetPassword_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        if ($c->hasSubcols){
            $exp = array('imgStyle' => 'float_right', 'folder' => 'large');
            $pic = $media->getMediaPicture('webBasic_section', 'picture', $tv['room'], $exp);

            $text = "
            <div class='subcolumns'>
                <div class='{$c->col1Css}'>
                    <div class='subcl'>
                        {$this->getRowsHTML()}
                    </div>
                </div>
                <div class='{$c->col2Css}'>
                    <div class='subcr'>
                        {$pic}
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            {$this->getRowsHTML()}
            ";
        }

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = &$this->controller;

        $formAction = $c->formAction;
        $heading    = $ln->gd2($c->heading);
        $infoText   = $ln->gd2($c->infoText);

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        if ($heading != ''){
            $heading = "<h1>{$heading}</h1>";
        }

        $retUrlText = '';
        if ($c->returnUrl){
            $retUrlText = "<input type='hidden' name='returnUrl' value='{$c->returnUrl}' />";
        }

        $reset_password_hash = $fn->getReqParam('hash');

        $expPass = array(
             'password' => 1
            ,'required' => true
            ,'notes'    => $ln->gd('cp.form.fld.password.notes')
            ,'disableAutoComplete'    => true
        );

        $expConfPass = array(
             'password' => 1
            ,'required' => true
            ,'disableAutoComplete'    => true
        );

        $expReq = array(
            'required' => true
            ,'disableAutoComplete'    => true
        );

        $text = "
        <form name='resetPasswordForm' id='resetPasswordForm' class='yform columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                {$heading}
                {$infoText}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.newPassword.lbl'), 'pass_word', '', $expPass)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'cpass_word', '', $expConfPass)}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='hidden' name='hash' value='{$reset_password_hash}' />
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('w.member.resetPassword.message.success')) . "' />
                {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            </fieldset>
        </form>
        ";

        return $text;
    }
}