<?
class CP_Www_Widgets_Member_RegisterForm_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget() {
        $c = &$this->controller;

        $text = "
        {$this->getRowsHTML()}
        ";

        if ($c->hasSubcols){
            $wBanner = getCPWidgetObj('media_banner');
            $pic = $wBanner->getWidget(array(
                'strictToPage' => true
            ));

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
        $hook = getCPWidgetHook('member_registerForm', 'rowsHTML', $this);
        if($hook['status']){
            return $hook['html'];
        }
        $ln = Zend_Registry::get('ln');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = &$this->controller;

        $formAction = $c->formAction;
        $expPass['password'] = 1;

        $infoText = $ln->gd2($c->infoText);

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        $formAction = $c->formAction;
        $expPass['password'] = 1;

        $hiddenFlds = '';
        if ($c->returnUrl != ''){
            $hiddenFlds = "
            <input type='hidden' name='returnUrl' value='{$c->returnUrl}' />
            ";
        } else  if (@$_SESSION['cpReturnUrlAfterLogin'] != ''){
            $retUrl = $_SESSION['cpReturnUrlAfterLogin'];
            unset($_SESSION['cpReturnUrlAfterLogin']);
            $hiddenFlds = "
            <input type='hidden' name='returnUrl' value='{$retUrl}' />
            ";
        } else {
            $hiddenFlds = "
            <input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('m.membership.contact.form.new.message.sucecss')) . "' />
            ";
        }

        $fbRegisterText = '';
        if ($c->hasFbRegister){
            $fbRegisterText = $this->getFbRegisterText();
        }

        $text = "
        <form name='registerForm' id='registerForm' class='yform columnar cpJqForm'
              method='post' action='{$formAction}'>
            <fieldset>
                <h1>{$ln->gd('m.membership.contact.form.new.heading')}</h1>
                <div class='infoText'>{$ln->gd('m.membership.contact.form.new.info')}</div>
            </fieldset>
            {$this->getFormFields()}
            <div class='type-button'>
                <div class='floatbox'>
                    <div class='float_left btnSubmit'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                    </div>
                    <div class='float_left btnReset'>
                        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}'
                               onclick='history.back()'/>
                    </div>
                </div>
            </div>
            <input type='submit' name='x_submit' class='submithidden' />
            {$hiddenFlds}
            {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
        </form>
        {$fbRegisterText}
        ";

        return $text;
    }

    //========================================================//
    function getFormFields() {
        $hook = getCPWidgetHook('member_registerForm', 'formFields', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $c = &$this->controller;

        $expPass['password'] = 1;
        if($c->showPasswordNotes){
            $expPass['notes'] = $ln->gd('cp.form.fld.password.notes');
        }

        $expCPass['password'] = 1;

        $confirmEmail = '';

        if ($c->showConfirmEmail){
            $cEmailNotes = $ln->gd2('cp.form.fld.confirmEmail.notes');
            $exp = array();

            if ($cEmailNotes != ''){
                $exp = array('notes' => $cEmailNotes);
            }
            $confirmEmail = $formObj->getTBRow($ln->gd('cp.form.fld.confirmEmail.lbl'), 'confirm_email', '', $exp);
        }

        if ($c->quickForm){
            $text = "
            {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
            {$confirmEmail}
            {$formObj->getTBRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
            {$formObj->getTBRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'cpass_word', '', $expCPass)}
    	    {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        } else {
            $fieldset1 = "
            {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
            {$confirmEmail}
            {$formObj->getTBRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
            {$formObj->getTBRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'cpass_word', '', $expCPass)}
            {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
            ";

            $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

            $fieldset2 = "
            {$formObj->getTBRow('Address 1', 'address1')}
            {$formObj->getTBRow('Address 2', 'address2')}
            {$formObj->getTBRow('Area', 'address_area')}
            {$formObj->getTBRow('City/Town', 'address_city')}
            {$formObj->getTBRow('State', 'address_state')}
            {$formObj->getTBRow('Zip Code', 'address_po_code')}
            {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry)}
            ";

            $text = "
            {$formObj->getFieldSetWrapped('Primary Details', $fieldset1)}
            {$formObj->getFieldSetWrapped('Address Details', $fieldset2)}
    	    {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }


        return $text;
    }

    //========================================================//
    function getFbRegisterText() {
        $hook = getCPWidgetHook('member_loginForm', 'fbRegisterText', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        $fbRegisterCaption = $ln->gd2($c->fbRegisterCaption);
        $fbRegisterInfoText = $ln->gd2($c->fbRegisterInfoText);

        if ($fbRegisterCaption != ''){
            $fbRegisterCaption = "<h1>{$ln->gd($c->fbRegisterCaption)}</h1>";
        }

        if ($fbRegisterInfoText != ''){
            $fbRegisterInfoText = "<div class='infoText'>{$fbRegisterInfoText}</div>";
        }

        $text = "
        <div class='facebookRegister'>
            {$fbRegisterCaption}
            {$fbRegisterInfoText}
            <div class='fbRegisterButton'>
                <a href='#'></a>
            </div>
        </div>
        ";

        return $text;
    }

}