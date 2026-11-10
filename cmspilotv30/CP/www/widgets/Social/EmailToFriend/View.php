<?
class CP_Www_Widgets_Social_EmailToFriend_View extends CP_Common_Lib_WidgetViewAbstract
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');
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
        
        $pageToSend = '';
        if ($c->pageToSend){
            $pageToSend = $c->pageToSend;
        } else {
            $content_id = $fn->getReqParam('content_id');
            $row = getCPModuleObj('webBasic_content')->model->getRecordById($content_id);
            $expUrl = array(
                'prependUrl' => $cpCfg['cp.siteUrl']
            );
            $pageToSend = $cpUrl->getUrlByRecord($row, 'content_id', $expUrl);
            $c->pageToSend = $pageToSend;
        }
        
        $captchaText = '';
        if ($c->showCaptcha){
            $captchaText = "{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}";
        }

        $text = "
        <form name='emailToFriendForm' id='emailToFriendForm' class='yform columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                {$heading}
                {$infoText}
                {$formObj->getTBRow($ln->gd('w.social.emailToFriend.fromName.lbl'),  'from_name', $c->from_name)}
                {$formObj->getTBRow($ln->gd('w.social.emailToFriend.fromEmail.lbl'), 'from_email', $c->from_email)}
                {$formObj->getTBRow($ln->gd('w.social.emailToFriend.toName.lbl'),  'to_name')}
                {$formObj->getTBRow($ln->gd('w.social.emailToFriend.toEmail.lbl'), 'to_email')}
                {$formObj->getTARow($ln->gd('w.social.emailToFriend.comment.lbl'), 'comments')}
                {$captchaText}
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='page_to_send' value='{$pageToSend}'>
                <input type='hidden' name='module' value='{$c->module}'>
                <input type='hidden' name='record_id' value='{$c->record_id}'>
                {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            </fieldset>
        </form>
        ";

        return $text;
    }
}