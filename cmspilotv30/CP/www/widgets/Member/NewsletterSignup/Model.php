<?
class CP_Www_Widgets_Member_NewsletterSignup_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $c = &$this->controller;

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");

        if (!is_array($rec)){
            $fn->addRecord($fa, 'contact');
        } else {
            $fn->saveRecord($fa, 'contact', 'contact_id', $rec['contact_id']);
        }

        $subscribeToMailChimp = $fn->getReqParam('w-member-newsletterSignup_subscribeToMailChimp');

        if ($subscribeToMailChimp){
            $mailChimp = getCPPluginObj('common_mailChimp');
            $mailChimp = $mailChimp->model;
            $retVal = $mailChimp->listSubscribe($fa['email'], $fa['first_name'], $fa['last_name']);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $hook = getCPWidgetHook('member_newsletterSignup', 'newValidate', $this->controller);
        if($hook['status']){
            return $hook['html'];
        }

        //==================================================================//
        $showCaptcha = $fn->getReqParam('w-member-newsletterSignup_showCaptcha');
        $showEmailOnly = $fn->getReqParam('w-member-newsletterSignup_showEmailOnly');

        $validate->resetErrorArray();
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");

        if (!$showEmailOnly){
            $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err")  );
            $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err")   );
        }

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

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'subscribe', 1);
        $fa = $fn->addToFieldsArray($fa, 'language');

        return $fa;
    }
}