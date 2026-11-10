<?
class CP_Www_Modules_LawNews_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL   = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
        FROM contact c
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->sqlSearchVar[] = "c.published = 1";
        $searchVar->sqlSearchVar[] = "c.contact_id = '{$_SESSION['cpContactId']}'";
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateCSRFToken();

        $validate->validateData("salutation"  , $ln->gd("cp.form.fld.salutation.err") );
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err")  );
        $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err")   );
        $validate->validateData("company_name", $ln->gd("cp.form.fld.companyName.err")   );
        $validate->validateData("company_type", $ln->gd("cp.form.fld.companyType.err")   );

        $validate->validateData("address1", $ln->gd("cp.form.fld.address1.err")   );
        $validate->validateData("address_city", $ln->gd("cp.form.fld.addressCity.err")   );
        $validate->validateData("phone", $ln->gd("cp.form.fld.phone.err")   );
        $validate->validateData("fax", $ln->gd("cp.form.fld.fax.err")   );

        $validate->validateData("agree_terms", $ln->gd("cp.form.fld.agreeTerms.err")   );
        $validate->validateData("agree_privacy", $ln->gd("cp.form.fld.agreePrivacy.err")   );

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
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'company_type');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address3');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'chi_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_position');
        $fa = $fn->addToFieldsArray($fa, 'chi_department');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'dont_contact_by_phone', 0);
        $fa = $fn->addToFieldsArray($fa, 'dont_contact_by_fax', 0);
        $fa = $fn->addToFieldsArray($fa, 'agree_contact_by_third_party', 0);
        $fa = $fn->addToFieldsArray($fa, 'site_id', $cpCfg['cp.site_id']);

        return $fa;
    }

    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['subscribe'] = 1;
        $fa['published'] = 1;

        if ($cpCfg['cp.hasPasswordSalt']) {
            $actual_pass_word = $fa['pass_word'];
            $email = $fa['email'];

            $arr = $cpUtil->getSaltAndPasswordArray($email, $actual_pass_word);
            $fa['salt'] = $arr['salt'];
            $fa['pass_word'] = $arr['pass_word'];
        }

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'contact');

        //-----------------------------------------------------------------------//
        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'contact');
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        //AutoLogin After register
        $row = $fn->getRecordRowByID('contact', 'contact_id', $id);
        $pLoginObj = getCPPluginObj('member_login');
        $pLoginObj->model->setLoginAfterRegister($row);

        $this->sendNewMemberNotificationToAdmin($fa);
        $this->sendRegistrationNotificationToUser($fa);
        $fn->sessionRegenerate();
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
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $isPasswordInvalidFormat = $validate->validateData("pass_word", $ln->gd("w.member.changePassword.form.fld.password.err.length"), "empty", $field2 = "", $minCharLength = "6", $maxCharLength = "20");
        $validate->validateData("cpass_word",  $ln->gd("w.member.changePassword.form.fld.password.err.compare"), "equal", "pass_word", 6, 20);

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");

        if (is_array($rec)){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = $ln->gd("cp.form.fld.email.err.alreadyExists");
        }

        $validate->validateData("salutation"  , $ln->gd("cp.form.fld.salutation.err") );
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err")  );
        $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err")   );
        $validate->validateData("company_name", $ln->gd("cp.form.fld.companyName.err")   );
        $validate->validateData("company_type", $ln->gd("cp.form.fld.companyType.err")   );

        $validate->validateData("address1", $ln->gd("cp.form.fld.address1.err")   );
        $validate->validateData("address_city", $ln->gd("cp.form.fld.addressCity.err")   );
        $validate->validateData("phone", $ln->gd("cp.form.fld.phone.err")   );
        $validate->validateData("fax", $ln->gd("cp.form.fld.fax.err")   );

        $validate->validateData("agree_terms", $ln->gd("cp.form.fld.agreeTerms.err")   );
        $validate->validateData("agree_privacy", $ln->gd("cp.form.fld.agreePrivacy.err")   );

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

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $contact_id = $fn->getSessionParam('cpContactId');
        $fn->sessionRegenerate();
        return parent::getSave('contact', 'contact_id', $contact_id);
    }

    /**
     *
     */
    function sendNewMemberNotificationToAdmin($fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $message = $ln->gd('m.membership.contact.form.new.email.notifyBody');
        $message = str_replace("[[first_name]]"  , $fa["first_name"] , $message );
        $message = str_replace("[[last_name]]"   , $fa["last_name"]  , $message );
        $message = str_replace("[[company_name]]", $fa["company_name"], $message );
        $message = str_replace("[[email]]"       , $fa["email"]      , $message );
        $message = str_replace("[[currentDate]]" , $currentDate      , $message );

        $subject   = $ln->gd('m.membership.contact.form.new.email.notifySubject');
        $fromName  = $fa['first_name'] . " " . $fa['last_name'];
        $fromEmail = $fa['email'];
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
    function sendRegistrationNotificationToUser($fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        if ($cpCfg['cp.hasPasswordSalt']) {
            $fa["pass_word"] = $fn->getPostParam('pass_word');
        }

        $message = $ln->gd('m.membership.contact.form.new.email.notifyUserBody');
        $message = str_replace("[[first_name]]"  , $fa["first_name"] , $message );
        $message = str_replace("[[last_name]]"   , $fa["last_name"]  , $message );
        $message = str_replace("[[company_name]]", $fa["company_name"], $message );
        $message = str_replace("[[email]]"       , $fa["email"]      , $message );
        $message = str_replace("[[pass_word]]"   , $fa["pass_word"]  , $message );
        $message = str_replace("[[currentDate]]" , $currentDate      , $message );

        $subject   = $ln->gd('m.membership.contact.form.new.email.notifyUserSubject');
        $toName  = $fa['first_name'] . " " . $fa['last_name'];
        $toEmail = $fa['email'];
        $fromName    = $cpCfg['cp.companyName'];
        $fromEmail   = $cpCfg['cp.adminEmail'];

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
     * @param int $content_id
     * @return array
     */
    function getMyClippingsArray(){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,s.section_type
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
              ,cc.contact_content_id
        FROM content c
        LEFT JOIN (section s)      ON (c.section_id       = s.section_id)
        LEFT JOIN (category ca)    ON (c.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (c.sub_category_id  = sc.sub_category_id)
        LEFT JOIN contact_content cc ON c.content_id = cc.content_id
        WHERE cc.contact_id = {$_SESSION['cpContactId']}
          AND c.published = 1
        ORDER BY cc.creation_date DESC
        ";

        $result  = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        return $dataArray;
    }

    /**
     *
     * @return type
     */
    function getDeleteClipping(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUrl  = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');

        $contact_content_id = $fn->getReqParam('id');
        if($contact_content_id > 0){
            $SQL = "
            DELETE FROM contact_content
            WHERE contact_content_id = {$contact_content_id}
              AND contact_id = {$_SESSION['cpContactId']}
            ";

            $result = $db->sql_query($SQL);
        }

        $redirectUrl = $cpUrl->getUrlByCatType('My Clippings');
        return $cpUtil->redirect($redirectUrl);
    }
}