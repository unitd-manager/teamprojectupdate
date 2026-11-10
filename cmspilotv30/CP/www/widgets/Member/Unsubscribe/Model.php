<?
class CP_Www_Widgets_Member_Unsubscribe_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSave() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $c = &$this->controller;

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");

        if (!is_array($rec)){
            exit('invalid access');
        } else {
            $fa['subscribe'] = 0;
            $fn->saveRecord($fa, 'contact', 'contact_id', $rec['contact_id']);
            
            if ($c->sendNotifyEmailToUser){
                $this->sendNotificationToUser($rec);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function sendNotificationToUser($rec) {
        $hook = getCPModuleHook('membership_contact', 'sendNotificationToUser', $rec, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        
        $message = $ln->gd('w.member.unsubscribe.email.notifyUserBody');

        $message = str_replace("[[first_name]]", $rec["first_name"], $message );
        $message = str_replace("[[last_name]]", $rec["last_name"], $message );
        $message = str_replace("[[email]]", $rec["email"], $message );
        $message = str_replace("[[siteurl]]", $cpCfg['cp.siteUrl'], $message );
        $message = str_replace("[[currentDate]]", $currentDate, $message );

        $subject = $ln->gd('w.member.unsubscribe.email.notifyUserSubject');

        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $rec['first_name'] . " " . $rec['last_name'];
        $toEmail   = $rec['email'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $hook = getCPWidgetHook('member_unsubscribe', 'editValidate', $this->controller);
        if($hook['status']){
            return $hook['html'];
        }

        $email = $fn->getPostParam('email', '', true);

        //==================================================================//
        $validate->resetErrorArray();
        $isEmailInvalidFormat = $validate->validateData("email" , $ln->gd("cp.form.fld.email.err"), "email");

        if (!$isEmailInvalidFormat){
            $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");

            if (!is_array($rec)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = $ln->gd("cp.form.fld.email.err.notFound");
            }
        }
        
        $showCaptcha = $fn->getReqParam('w-member-unsubscribe_showCaptcha');

        if ($showCaptcha){
       	    $captcha_code = $fn->getPostParam('captcha_code');
            require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
            $img = new Securimage;
            if ($img->check($captcha_code) == false) {
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}