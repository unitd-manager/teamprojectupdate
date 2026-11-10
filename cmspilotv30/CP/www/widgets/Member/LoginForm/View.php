<?
class CP_Www_Widgets_Member_LoginForm_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        if ($c->hasSubcols){
            $exp = array('imgStyle' => 'float_right', 'folder' => 'large');
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
        $hook = getCPWidgetHook('member_loginForm', 'rowsHTML', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        $c = &$this->controller;

        $formAction = $c->formAction;
        $expPass['password'] = 1;

        $infoText = $ln->gd2($c->infoText);

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        if ($c->returnUrlAfterLogin != ''){
            $_SESSION['cpReturnUrlAfterLogin'] = $c->returnUrlAfterLogin;
        }

        $forgotText = '';
        if ($c->hasForgotPass){
            $url = '/index.php?plugin=member_forgotPassword&_spAction=view&showHTML=0';
            $forgotText = "
            <div class='forgotPasswordLink'>
                <a href='javascript:void(0)' link='{$url}' class='jqui-dialog-form' formId='forgotPasswordForm'
                    w='400' h='300' title='{$ln->gd('p.member.forgotPassword.form.heading')}'>
                    {$ln->gd('w.member.loginForm.form.lbl.forgotPassword')}
                </a>
            </div>
            ";
        }

        $retUrlText = '';
        if ($c->returnUrl){
            $retUrlText = "<input type='hidden' name='returnUrl' value='{$c->returnUrl}' />";
        }

        $regiserInfo = '';
        if ($c->hasRegiserInfo){
            $regiserInfo = $this->getRegisterInfo();
        }

        $fbLoginText = '';
        if ($c->hasFbLogin){
            $fbLoginText = $this->getFbLoginText();
        }

        $loginType = '';
        if (is_array($c->loginTypeArr)){
            $exp = array('useKey' => true, 'hideFirstOption' => true);
            $loginType = $formObj->getDDRowByArr($ln->gd('cp.form.fld.loginType'), 'loginType', $c->loginTypeArr, $c->loginType, $exp);
        } else {
            $loginType = "<input type='hidden' name='loginType' value='{$c->loginType}' />";
        }

        $text = "
        <form name='loginForm' id='loginForm' class='yform columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                <h1>{$ln->gd($c->heading)}</h1>
                {$infoText}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
                {$loginType}
                <div class='type-check'>
                    <input type='checkbox' id='fld_save_login' class='checkBox' name='saveLogin' value='1' />
                    <label for='fld_save_login'>{$ln->gd('w.member.loginForm.lbl.saveLogin')}</label>
                </div>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='btns'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                {$forgotText}
                {$retUrlText}
            </fieldset>
        </form>
        {$regiserInfo}
        {$fbLoginText}
        ";

        return $text;
    }

    //========================================================//
    function getRegisterInfo() {
        $hook = getCPWidgetHook('member_loginForm', 'registerInfo', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $c = &$this->controller;

        $formAction = $c->formAction;
        $expPass['password'] = 1;

        $url = ($c->registerUrl != '') ? $c->registerUrl : $cpUrl->getUrlBySecType('Register');

        $text = "
        <div class='registerInfo'>
            <form class='yform'>
            <fieldset>
                <h1>{$ln->gd($c->registerCaption)}</h1>
                <div class='infoText'>{$ln->gd($c->registerInfoText)}</div>
                <div class='type-button'>
                    <a class='button btnRegister' href='{$url}'>{$ln->gd($c->registerBtnText)}</a>
                </div>
            </fieldset>
            </form>
        </div>
        ";

        return $text;
    }

    //========================================================//
    function getFbLoginText() {
        $hook = getCPWidgetHook('member_loginForm', 'fbLoginText', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        $fbLoginCaption = $ln->gd2($c->fbLoginCaption);
        $fbLoginInfoText = $ln->gd2($c->fbLoginInfoText);

        if ($fbLoginCaption != ''){
            $fbLoginCaption = "<h1>{$ln->gd($c->fbLoginCaption)}</h1>";
        }

        if ($fbLoginInfoText != ''){
            $fbLoginInfoText = "<div class='infoText'>{$fbLoginInfoText}</div>";
        }

        $text = "
        <div class='facebookLogin'>
            {$fbLoginCaption}
            {$fbLoginInfoText}
            <div class='fbLoginButton'>
                <a href='#'></a>
            </div>
        </div>
        ";

        return $text;
    }

}