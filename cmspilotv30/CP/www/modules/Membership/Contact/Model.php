<?
class CP_Www_Modules_Membership_Contact_Model extends CP_Common_Modules_Membership_Contact_Model
{
    /**
     *
     */
    function getSQL() {
        if (!isLoggedInWWW()){
            return;
        }
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
        $searchVar->mainTableAlias = 'c';

        if (!isLoggedInWWW()){
            return;
        }

        $searchVar->sqlSearchVar[] = "c.published = 1";
        $searchVar->sqlSearchVar[] = "c.contact_id = '{$_SESSION['cpContactId']}'";
        //$searchVar->sqlSearchVar[] = "c.company_id = '{$_SESSION['cpContactId']}'";
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $contact_id = $fn->getSessionParam('cpContactId');

        return parent::getSave('contact', 'contact_id', $contact_id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $memberType = $fn->getPostParam('memberType');
        $modDetail = $this->getModDetailsArray($memberType);
        $table = $modDetail['tableName'];
        $keyFld = $modDetail['keyField'];

        $validate->resetErrorArray();

        $validate->validateData('first_name', $ln->gd("cp.form.fld.firstName.err"));
        $validate->validateData('last_name' , $ln->gd("cp.form.fld.lastName.err"));

        $validate->validateData('email', $ln->gd('cp.form.fld.email.err'), 'email');

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition($table, "{$keyFld} != {$_SESSION['cpContactId']} AND email = '{$email}'");

        if (is_array($rec)){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = $ln->gd("cp.form.fld.email.err.alreadyExists");
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

        $fa = $fn->addToFieldsArray($fa, 'member_type');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'date_birth');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'chi_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_position');
        $fa = $fn->addToFieldsArray($fa, 'chi_department');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'edu_level');
        $fa = $fn->addToFieldsArray($fa, 'occupation');
        $fa = $fn->addToFieldsArray($fa, 'personal_income');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'children');
        $fa = $fn->addToFieldsArray($fa, 'interest');
        $fa = $fn->addToFieldsArray($fa, 'language');

        $hook = getCPModuleHook('membership_contact', 'fields', $fa, $this);
        if($hook['status']){
            return $hook['html'];
        }

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        /*
        $fbSecret = '4a1312c7b8b44349ca8db1298c84c710';

        if ($_REQUEST) {
            echo '<p>signed_request contents:</p>';
            $response = $fn->fbParseSignedRequest($_REQUEST['signed_request'], $fbSecret);
            echo '<pre>';
            print_r($response);
            echo '</pre>';
        } else {
            echo '$_REQUEST is empty';
        }
        */

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $memberType = $fn->getPostParam('w-member-registerForm_memberType');

        $modDetail = $this->getModDetailsArray($memberType);
        $table = $modDetail['tableName'];
        $keyFld = $modDetail['keyField'];

        $fa = $this->getFields();
        $fa['published'] = 1;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, $table);

        if ($cpCfg['cp.hasPasswordSalt']) {
            $pass_word = $fa['pass_word'];
            $email = $fa['email'];
            $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
            $fa['salt'] = $arr['salt'];
            $fa['pass_word'] = $arr['pass_word'];
        }

        //------------------------------------------------------------------//
        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, $table);
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        if ($cpCfg['m.membership.autoLoginOnNewRegistration']){
            $rec = $fn->getRecordRowByID($table, $keyFld, $id);
            $pLogin = getCPPluginObj('member_login');
            $pLogin->model->setSessionValuesAfterLogin($rec, false, $memberType);
        }

        $this->sendNewMemberNotificationToAdmin($fa, $memberType);
        $this->sendNewMemberNotificationToUser($fa, $memberType);

        $retUrlForm = $fn->getPostParam('returnUrl');

        if ($retUrlForm != ''){
            $retUrl = $retUrlForm;
            return $validate->getSuccessMessageXML($retUrl);
        } else if (@$_SESSION['cpReturnUrlAfterLogin'] != ''){
            $retUrl = $_SESSION['cpReturnUrlAfterLogin'];
            unset($_SESSION['cpReturnUrlAfterLogin']);
            return $validate->getSuccessMessageXML($retUrl);
        } else {
            return $validate->getSuccessMessageXML();
        }
    }

    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $hook = getCPModuleHook2('membership_contact', 'newValidate', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $memberType = $fn->getPostParam('w-member-registerForm_memberType');
        $modDetail = $this->getModDetailsArray($memberType);
        $table = $modDetail['tableName'];
        $keyFld = $modDetail['keyField'];

        $showConfirmEmail = $fn->getPostParam('w-member-registerForm_showConfirmEmail');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err"));
        $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err"));
        $validate->validateData("email"       , $ln->gd("cp.form.fld.email.err"), "email");

        $validate->validateData("pass_word", $ln->gd("w.member.changePassword.form.fld.password.err.length"), "alphaNumeric", $field2 = "", $minCharLength = "6", $maxCharLength = "20" );
        $validate->validateData("cpass_word",  $ln->gd("w.member.changePassword.form.fld.password.err.compare"), "equal", "pass_word", 6, 20);

        if ($showConfirmEmail){
            $isCEmailNotValid = $validate->validateData("confirm_email", $ln->gd("cp.form.fld.confirmEmail.err"), "email");

            if (!$isCEmailNotValid){
                $validate->validateData('confirm_email',  $ln->gd('cp.form.fld.email.err.compare'), 'equal', 'email');
            }
        }

        $email = $fn->getPostParam('email', '', true);

        if ($email != ''){
            $rec = $fn->getRecordByCondition($table, "email = '{$email}'");

            if (is_array($rec)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = $ln->gd("cp.form.fld.email.err.alreadyExists");
            }
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

    /**
     *
     */
    function sendNewMemberNotificationToAdmin($fa) {
        $hook = getCPModuleHook('membership_contact', 'sendNewMemberNotificationToAdmin', $fa, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $message = $ln->gd('m.membership.contact.form.new.email.notifyBody');
        $message = str_replace("[[first_name]]"  , $fa["first_name"] , $message );
        $message = str_replace("[[last_name]]"   , $fa["last_name"]  , $message );
        $message = str_replace("[[email]]"       , $fa["email"]      , $message );
        $message = str_replace("[[currentDate]]" , $currentDate      , $message );

        $subject   = $ln->gd('m.membership.contact.form.new.email.notifySubject');
        // $fromName  = $fa['first_name'] . " " . $fa['last_name'];
        // $fromEmail = $fa['email'];
        $fromName  = '';
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];

        /*
        $outputFileName = realpath($cpCfg['cp.mediaFolder']) . '/temp/test.pdf';
        $outputFileName = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $outputFileName = 'D:\Projects\deal\httpdocs\media\temp\test1.pdf';

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(40,60,"Description");
        $pdf->Output('D:\Projects\deal\httpdocs\media\temp\test1.pdf', "F");
        */
        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );
            //,'attachmentArray' => array($outputFileName)

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
    }

    /**
     *
     */
    function sendNewMemberNotificationToUser($fa) {
        $hook = getCPModuleHook('membership_contact', 'sendNewMemberNotificationToUser', $fa, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        /*** some of the previous sites using this key ***/
        $message = $ln->gd2('m.membership.contact.form.new.email.messageToUser');
        if ($message == ''){
            $message = $ln->gd('m.membership.contact.form.new.email.notifyUserBody');
        }

        $SERVER = $_SERVER['HTTP_HOST'];
        $siteUtl = "{$SERVER}/";

        $password = $fn->getIssetParam($fa, 'pass_word');
        $message = str_replace("[[first_name]]", $fa["first_name"], $message );
        $message = str_replace("[[last_name]]", $fa["last_name"], $message );
        $message = str_replace("[[email]]", $fa["email"], $message );
        $message = str_replace("[[pass_word]]", $password, $message );
        $message = str_replace("[[currentDate]]", $currentDate, $message );
        $message  = str_replace('[[activation_link]]', $siteUtl, $message);

        $subject = $ln->gd('m.membership.contact.form.new.email.notifyUserSubject');

        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $fa['first_name'] . " " . $fa['last_name'];
        $toEmail   = $fa['email'];

        $args = array(
             'toName'    => $fa['first_name'] . " " . $fa['last_name']
            ,'toEmail'   => $fa['email']
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
    function getModDetailsArray($module){
        $modulesArr = Zend_Registry::get('modulesArr');

        $arr = array();
        $arr['tableName'] = $modulesArr[$module]['tableName'];
        $arr['keyField']  = $modulesArr[$module]['keyField'];

        return $arr;
    }

    //==================================================================//
    function linkInterest($contact_id) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $idsArray = isset($_REQUEST['interest_ids']) ? $_REQUEST['interest_ids'] : array();

        $SQL = "SELECT interest_id FROM interest_contact
                WHERE contact_id   = " . qstr($contact_id) ;

        $result = $db->sql_query($SQL);

        //********* delete un-selected ones
        while ($row = $db->sql_fetchrow($result)) {
           if (in_array($row['interest_id'], $idsArray) == false) {
              $SQL = "DELETE FROM interest_contact
                      WHERE contact_id  = {$contact_id} AND
                      interest_id = {$row['interest_id']}
                     ";
              $db->sql_query($SQL);
           }
        }
        //----------------------------------------------------------------//

        $needToRunHistorySQL = 0;

        $SQLHist   = "INSERT INTO interest_contact (contact_id, interest_id, creation_date) VALUES ";

        foreach ($idsArray as $interest_id) {
           $SQL2 = "SELECT 1 FROM interest_contact
                       WHERE contact_id  = {$contact_id} AND
                             interest_id = {$interest_id}";
           $result2 = $db->sql_query($SQL2);
           $numRows = $db->sql_numrows($result2);
           if ($numRows == 0) {
              $SQLHist .= "($contact_id, $interest_id, NOW()),";
              $needToRunHistorySQL = 1;
           }
        }
        $SQLHist = substr($SQLHist, 0, -1); //*** remove the final comma
        if ($needToRunHistorySQL == 1) {
           $db->sql_query($SQLHist);
        }
    }

    /**
     *
     */
    function getAddPasswordForNonMember(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $contact_id = $fn->getSessionParam('cpNonMemberContactID');

        if(!$contact_id > 0){
            return;
        }

        $memberType = $fn->getPostParam('memberType');
        $modDetail = $this->getModDetailsArray($memberType);
        $table = $modDetail['tableName'];
        $keyFld = $modDetail['keyField'];

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("pass_word", $ln->gd("w.member.changePassword.form.fld.password.err.length"), "alphaNumeric", $field2 = "");
        $validate->validateData("cpass_word",  $ln->gd("w.member.changePassword.form.fld.password.err.compare"), "equal", "pass_word");

        if (count($validate->errorArray) == 0) {
            $fa = array();
            $fa = $fn->addToFieldsArray($fa, 'pass_word');
            $fa['published'] = 1;
            $fn->saveRecord($fa, $table, $keyFld, $contact_id);

            $fa = $fn->getRecordRowByID($table, $keyFld, $contact_id);

            $this->sendNewMemberNotificationToAdmin($fa, $memberType);
            $this->sendNewMemberNotificationToUser($fa, $memberType);
            return $validate->getSuccessMessageXML();
        } else {
            return $validate->getErrorMessageXML();
        }
    }

    function getModuleDataArray(){
        if (!isLoggedInWWW()){
            return;
        }
        return parent::getModuleDataArray();
    }

    function getTwigParams() {
        return array();
    }
}