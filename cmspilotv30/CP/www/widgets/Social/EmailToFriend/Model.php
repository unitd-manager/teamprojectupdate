<?
class CP_Www_Widgets_Social_EmailToFriend_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fn->addRecord($fa, 'email_to_friend');
        $this->sendEmailToFriend($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("from_name"  , $ln->gd("w.social.emailToFriend.fromName.err")  );
        $validate->validateData("from_email" , $ln->gd("w.social.emailToFriend.fromEmail.err"), "email" );
        $validate->validateData("to_name"    , $ln->gd("w.social.emailToFriend.toName.err")  );
        $validate->validateData("to_email"   , $ln->gd("w.social.emailToFriend.toEmail.err"), "email" );
        
        //$validate->validateData("comments", $ln->gd("w.social.emailToFriend.comment.err") );

        $showCaptcha = $fn->getReqParam('w-social-emailToFriend_showCaptcha');

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

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'from_name');
        $fa = $fn->addToFieldsArray($fa, 'from_email');
        $fa = $fn->addToFieldsArray($fa, 'to_name');
        $fa = $fn->addToFieldsArray($fa, 'to_email');
        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'page_to_send');
        $fa = $fn->addToFieldsArray($fa, 'module');
        $fa = $fn->addToFieldsArray($fa, 'record_id');

        return $fa;
    }

    /**
     *
     */
    function sendEmailToFriend($fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $recordRow = getCPModuleObj($fa['module'])->model->getRecordById($fa['record_id']);

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $message = $ln->gd('w.social.emailToFriend.email.body');
        $message = str_replace("[[from_name]]"   , $fa["from_name"]   , $message );
        $message = str_replace("[[from_email]]"  , $fa["from_email"]  , $message );
        $message = str_replace("[[to_name]]"     , $fa["to_name"]     , $message );
        $message = str_replace("[[to_email]]"    , $fa["to_name"]     , $message );
        $message = str_replace("[[comments]]"    , $fa["comments"]    , $message );
        $message = str_replace("[[page_to_send]]", $fa["page_to_send"]   , $message );
        $message = str_replace("[[site_title]]"  , $cpCfg["cp.siteTitle"], $message );
        $message = str_replace("[[record_title]]", $recordRow["title"], $message );
        $message = str_replace("[[currentDate]]" , $currentDate       , $message );

        $subject   = $ln->gd('w.social.emailToFriend.email.subject');
        $subject   = str_replace("[[from_name]]", $fa["from_name"], $subject );
        $fromName  = $fa['from_name'];
        $fromEmail = $fa['from_email'];
        $toName    = $fa['to_name'];
        $toEmail   = $fa['to_email'];

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
}