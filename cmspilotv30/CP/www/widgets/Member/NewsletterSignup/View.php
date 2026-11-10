<?
class CP_Www_Widgets_Member_NewsletterSignup_View extends CP_Common_Lib_WidgetViewAbstract
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = &$this->controller;

        $hook = getCPWidgetHook('member_newsletterSignup', 'rowsHTML', $this->controller);
        if($hook['status']){
            return $hook['html'];
        }

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

        $captchaText = '';
        if ($c->showCaptcha){
            $captchaText = "{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}";
        }

        $langPrefText = '';
        if ($c->showLangPref){
            $langPrefText = "{$formObj->getRRow($ln->gd('w.member.newsletterSignup.form.fld.languagePreference.lbl'), 'language', $tv['lang'], $cpCfg['cp.availableLanguages'], array('useKey'=>true))}";
        }

        $emailInQstr= $fn->getReqParam('email');

        $extraFlds = '';
        if (!$c->showEmailOnly){
            $extraFlds = "
            {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
            ";
        }

        $resetBtn = '';
        if ($c->showResetBtn){
            $resetBtn = "
            <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
            ";
        }

        $text = "
        <form name='newsletterSignupForm' id='newsletterSignupForm' class='{$c->formStyleCls} columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                {$heading}
                {$infoText}
                {$extraFlds}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $emailInQstr)}
                {$langPrefText}
                {$captchaText}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            {$resetBtn}
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('w.member.newsletterSignup.message.sucecss')) . "' />
                {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            </fieldset>
        </form>
        ";

        return $text;
    }
}