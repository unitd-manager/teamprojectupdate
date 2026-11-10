<?
class CP_Www_Themes_Quest_Hooks_ModuleMembershipContact
{
    /**
     *
     */
    function getController($contObj) {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        if ($tv['secType'] == 'Login'){
            $wLogin = getCPWidgetObj('member_loginForm');
            return $wLogin->getWidget(array(
                 'hasRegiserInfo' => $cpCfg['m.membership.allowRegistration']
                ,'loginTypeArr' => array('pms_contact' => 'Individual', 'pms_company' => 'Company')
                ,'loginType' => 'pms_contact'
            ));

        } else if ($tv['secType'] == 'Register' || $tv['catType'] == 'Register' ){
            $wRegister = getCPWidgetObj('member_registerForm');
            $memberType = $fn->getreqParam('memberType', 'pms_contact');
            return $wRegister->getWidget(array(
                'memberType' => $memberType
            ));

        } else if ($tv['secType'] == 'Newsletter Signup' || $tv['catType'] == 'Newsletter Signup' ){
            $wNewsletter = getCPWidgetObj('member_newsletterSignup');
            return $wNewsletter->getWidget(array(
            ));

        } else if ($tv['secType'] == 'Unsubscribe' 
                || $tv['catType'] == 'Unsubscribe'
                || $tv['subCatType'] == 'Unsubscribe'
                ){
            $wUnsubscribe= getCPWidgetObj('member_unsubscribe');
            return $wUnsubscribe->getWidget(array(
            ));

        } else {
            checkLoggedIn();

            if ($tv['catType'] == 'My Profile' || $tv['secType'] == 'My Profile'){
                if ($tv['action'] == 'edit'){
                    $text = $contObj->getEdit();
                } else {
                    $tv['action'] = 'detail';
                    CP_Common_Lib_Registry::arrayMerge('tv', $tv);
                    $text = $contObj->getDetail();
                }
                return $text;
            } else if ($tv['secType'] == 'My Orders' || $tv['catType'] == 'My Orders'){
                $wOrders = getCPWidgetObj('ecommerce_orders');
                return $wOrders->getWidget(array(
                ));
            }
        }
    }

    /**
     *
     */
    function getSendNewMemberNotificationToAdmin($fa, $modelObj) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');
        
        if ($memberType == 'pms_company'){
            $subject   = $ln->gd('m.membership.contact.form.new.email.notifySubjectCompany');
            $message = $ln->gd('m.membership.contact.form.new.email.notifyBodyCompany');
            $rec = $fn->getRecordRowByID('company', 'company_id', $_SESSION['cpContactId']);

            $message = str_replace("[[title]]"  , $rec["title"] , $message );
            $message = str_replace("[[reg_number]]"  , $rec["reg_number"] , $message );
            $message = str_replace("[[address1]]"  , $rec["address1"] , $message );
            $message = str_replace("[[address2]]"  , $rec["address2"] , $message );
            $message = str_replace("[[address_po_code]]"  , $rec["address_po_code"] , $message );
            $message = str_replace("[[address_country]]"  , $rec["address_country_code"] , $message );
            $message = str_replace("[[fax]]"  , $rec["fax"] , $message );
            $message = str_replace("[[nature_of_business]]"  , $rec["nature_of_business"] , $message );

        } else {
            $subject   = $ln->gd('m.membership.contact.form.new.email.notifySubject');
            $message = $ln->gd('m.membership.contact.form.new.email.notifyBody');
            $rec = $fn->getRecordRowByID('contact', 'contact_id', $_SESSION['cpContactId']);
        }
        
        $message = str_replace("[[first_name]]"  , $rec["first_name"] , $message );
        $message = str_replace("[[last_name]]"   , $rec["last_name"]  , $message );
        $message = str_replace("[[email]]"       , $rec["email"]      , $message );
        $message = str_replace("[[phone]]"  , $rec["phone"] , $message );
        $message = str_replace("[[currentDate]]" , $currentDate      , $message );

        $fromName  = $rec['first_name'] . " " . $rec['last_name'];
        $fromEmail = $rec['email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $cpCfg['cp.companyName']
            ,'toEmail'   => $cpCfg['cp.adminEmail']
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
    function getSendNewMemberNotificationToUser($fa, $modelObj) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');
        
        if ($memberType == 'pms_company'){
            $subject = $ln->gd('m.membership.contact.form.new.email.notifyUserSubjectCompany');
            $message = $ln->gd('m.membership.contact.form.new.email.notifyUserBodyCompany');
            $rec = $fn->getRecordRowByID('company', 'company_id', $_SESSION['cpContactId']);
            $message = str_replace("[[title]]", $rec["title"], $message );

        } else {
            $subject = $ln->gd('m.membership.contact.form.new.email.notifyUserSubject');
            $message = $ln->gd('m.membership.contact.form.new.email.notifyUserBody');
            $rec = $fn->getRecordRowByID('contact', 'contact_id', $_SESSION['cpContactId']);
        }
        

        $password = $fn->getIssetParam($rec, 'pass_word');
        $message = str_replace("[[first_name]]", $rec["first_name"], $message );
        $message = str_replace("[[last_name]]", $rec["last_name"], $message );
        $message = str_replace("[[email]]", $rec["email"], $message );
        $message = str_replace("[[pass_word]]", $password, $message );
        $message = str_replace("[[currentDate]]", $currentDate, $message );

        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $rec['first_name'] . " " . $rec['last_name'];
        $toEmail   = $rec['email'];

        $args = array(
             'toName'    => $rec['first_name'] . " " . $rec['last_name']
            ,'toEmail'   => $rec['email']
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
    function getFields($fa, $modelObj) {
        $fn = Zend_Registry::get('fn');
        $memberType = $fn->getreqParam('w-member-registerForm_memberType');

        if ($memberType == 'pms_company'){
            $fa = $fn->addToFieldsArray($fa, 'title');
            $fa = $fn->addToFieldsArray($fa, 'fax');
            $fa = $fn->addToFieldsArray($fa, 'reg_number');
            $fa = $fn->addToFieldsArray($fa, 'nature_of_business');
            $fa = $fn->addToFieldsArray($fa, 'address1');
            $fa = $fn->addToFieldsArray($fa, 'address2');
        }
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'reg_number');
        $fa = $fn->addToFieldsArray($fa, 'nature_of_business');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'school_highest_qual');
        $fa = $fn->addToFieldsArray($fa, 'salary_range');

        return $fa;
    }

    /**
     *
     */
    function getNewValidate($modelObj) {
        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $memberType = $fn->getreqParam('w-member-registerForm_memberType');

        $validate->resetErrorArray();

        if ($memberType == 'pms_company'){
            $validate->validateData('title', $ln->gd("cp.form.fld.companyName.err"));
            $validate->validateData('reg_number', $ln->gd("cp.form.fld.regNumber.err"));

        } else {
            $validate->validateData('first_name', $ln->gd("cp.form.fld.firstName.err"));
            $validate->validateData('last_name' , $ln->gd("cp.form.fld.lastName.err"));
        }

        $isPasswordInvalidFormat = $validate->validateData("pass_word", $ln->gd("w.member.changePassword.form.fld.password.err.length"), "alphaNumeric", $field2 = "", $minCharLength = "6", $maxCharLength = "20" );
        $validate->validateData("cpass_word",  $ln->gd("w.member.changePassword.form.fld.password.err.compare"), "equal", "pass_word", 6, 20);

        $modDetail = $modelObj->getModDetailsArray($memberType);
        $table = $modDetail['tableName'];
        $keyFld = $modDetail['keyField'];

        $validate->validateData('email', $ln->gd('cp.form.fld.email.err'), 'email');
        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition($table, "email = '{$email}'");

        if (is_array($rec)){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = $ln->gd("cp.form.fld.email.err.alreadyExists");
        }

       	$captcha_code = $fn->getPostParam('captcha_code');
        require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
        $img = new Securimage;
        if ($img->check($captcha_code) == false) {
            $validate->errorArray['captcha_code']['name'] = "captcha_code";
            $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

}