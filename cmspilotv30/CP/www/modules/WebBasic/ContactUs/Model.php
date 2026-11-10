<?
class CP_Www_Modules_WebBasic_ContactUs_Model extends CP_Common_Lib_ModuleModelAbstract
{

    //==================================================================//
    function getSQL() {
        $modObj = getCPModuleObj('webBasic_content');
        return $modObj->model->getSQL();
    }

    //==================================================================//
    function setSearchVar($linkRecType) {
        $modObj = getCPModuleObj('webBasic_content');
        $modObj->model->setSearchVar($linkRecType);
    }

    /**
     *
     */
    function getAdd() {
        $hook = getCPModuleHook2('webBasic_contactUs', 'add', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        //-----------------------------------------------------------------------//
        $fa = array();

        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['last_name']     = $fn->getPostParam('last_name');
        $fa['email']         = $fn->getPostParam('email');
        $fa['subject']       = $fn->getPostParam('subject');
        $fa['phone']         = $fn->getPostParam('phone');
        $fa['enquiry_type']  = $fn->getPostParam('enquiry_type');
        $fa['country']       = $fn->getPostParam('country_code');
        $fa['comments']      = $fn->getPostParam('comments');
        $fa['creation_date'] = date('Y-m-d H:i:s');

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, 'enquiry');
        $result      = $db->sql_query($SQL);
        $contact_id  = $db->sql_nextid();

        //-----------------------------------------------------------------//
        $currentDate  = date('d-M-Y l h:i:s A');
        $gcRec = $fn->getRecordByCondition('geo_country', "country_code='{$fa['country']}'");

        $message = $ln->gd('m.webBasic.contactUs.form.enquiry.email.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[last_name]]', $fa['last_name'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);
        $message = str_replace('[[phone]]', $fa['phone'], $message);
        $message = str_replace('[[subject]]', $fa['subject'], $message);
        $message = str_replace('[[country]]', $gcRec['name'], $message);
        $message = str_replace('[[adress_country]]', $gcRec['name'], $message);
        $message = str_replace('[[enquiry_type]]', $fa['enquiry_type'], $message);
        $message = str_replace('[[comments]]', $fa['comments'], $message);
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = $ln->gd('m.webBasic.contactUs.form.enquiry.email.notifySubject');
        $fromName  = $fa['first_name'] . ' ' . $fa['last_name'];
        $fromEmail = $fa['email'];
        //$fromName  = '';
        //$fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];

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

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getNewValidate() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook2('webBasic_contactUs', 'newValidate', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name", $ln->gd("cp.form.fld.firstName.err"));
        $validate->validateData("last_name", $ln->gd("cp.form.fld.lastName.err"));
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $validate->validateData("comments", $ln->gd("cp.form.fld.comments.err"));

        if (!$cpCfg['m.webBasic.contactUs.hideCaptcha']) {
            $captcha_code = $fn->getPostParam('captcha_code');
            require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
            $img = new Securimage;
            if ($img->check($captcha_code) == false) {
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
            }
        }

        $resp  = null;
        $error = null;
        if (!$cpCfg['m.webBasic.contactUs.hideRobotCaptcha']) {
            $googleCaptcha = $fn->getPostParam('g-recaptcha-response');
            if($googleCaptcha == ""){
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.robotCaptchaNotTick.err");
            }

            // Was there a reCAPTCHA response?
            if ($googleCaptcha) {
                $siteKey   = $cpCfg['cp.dataRobotSiteKey'];
                $secretKey = $cpCfg['cp.dataRobotSecretKey'];
                require_once "recaptchalib.php";
                $reCaptcha = new ReCaptcha($secretKey);
                
                $resp = $reCaptcha->verifyResponse(
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["g-recaptcha-response"]
                );

                if ($resp->success != 1) {
                    $validate->errorArray['captcha_code']['name'] = "captcha_code";
                    $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.robotCaptchaNotValid.err");
                }
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
    function getTwigParams() {
        $dataArray = getCPWidgetObj('content_record')->getWidget(array(
             'contentType' => 'Location Map'
            ,'returnDataOnly' => true
            ,'global' => false
            ,'includeMediaRec' => true
        ));
        
        $params = array(
            'm_webBasic_contactUs_map' => $dataArray
        );
        
        return $params;
    }
}