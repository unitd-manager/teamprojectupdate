<?
class CP_Www_Widgets_Member_Unsubscribe_View extends CP_Common_Lib_WidgetViewAbstract
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = &$this->controller;

        $hook = getCPWidgetHook('member_unsubscribe', 'rowsHTML', $this->controller);
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
        
        $emailInQstr= $fn->getReqParam('email');

        $text = "
        <form name='loginForm' id='loginForm' class='yform columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                {$heading}
                {$infoText}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $emailInQstr)}
                {$captchaText}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='successMsg' value=\"" . htmlspecialchars($ln->gd('w.member.unsubscribe.message.sucecss')) . "\" />
                {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            </fieldset>
        </form>
        ";

        return $text;
    }
}