<?
class CP_Www_Plugins_Member_ResetPassword_Model extends CP_Common_Lib_PluginModelAbstract
{

    /**
     *
     * @return <type>
     */
    function getSubmit() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $validate = Zend_Registry::get('validate');

        $email = $fn->getPostParam('email', '', true);

        //-------------------------------------------------------------------------------------//
        $valArr = $this->getSubmitValidate();
        $hasError = $valArr[0];
        $xmlText = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $SQL = "
        SELECT *
        FROM parent
        WHERE email = '{$email}'
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);

            $fa = array();
            $fa['reset_password'] = mt_rand();

            $whereCondition = "WHERE parent_id = '{$row['parent_id']}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "parent", $whereCondition);
            $result = $db->sql_query($SQL);


            $reset_password_link = '';
            $currentDate = $fn->getCPDate(date("Y-m-d H:i:s"), $cpCfg['cp.dateDisplayFormatLong']);
            //$reset_password_link = "<a href='{$cpCfg['cp.siteUrl']}index.php?plugin=member_resetPassword&_spAction=resetPasswordForm&ran_no={$row['reset_password']}&showHTML=0'>Reset Password Link</a>";
            $reset_password_link = "<a href='{$cpCfg['cp.siteUrl']}kite/reset-password/?ran_no={$fa['reset_password']}'>Reset Password Link</a>";

            $message  = $ln->gd("p.member.resetPassword.form.email.notifyUserBody");
            $subject   = $ln->gd("p.member.resetPassword.form.email.notifyUserSubject");

            $message  = str_replace("[[first_name]]"  , $row["first_name"] , $message );
            $message  = str_replace("[[last_name]]"   , $row["last_name"]  , $message );
            $message  = str_replace("[[email]]"       , $row["email"]      , $message );
            $message  = str_replace("[[pass_word]]"   , $row["pass_word"]  , $message );
            $message  = str_replace("[[reset_password_link]]", $reset_password_link, $message );
            $message  = str_replace("[[currentDate]]" , $currentDate       , $message );

            $fromName  = 'Edukite';
            $fromEmail = $cpCfg['cp.adminEmail'];
            $toName    = $row['first_name'] . " " . $row['last_name'];
            $toEmail   = $row['email'];

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
    }

    /**
     *
     * @return <type>
     */
    function getSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $text = "";
        $isEmailInvalidFormat    = $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email", "", "3", "50");

        if (!$isEmailInvalidFormat) {
            $email     = $fn->getPostParam('email', '', true);
            $pass_word = $fn->getPostParam('pass_word', '', true);

            $SQL = "
            SELECT *
            FROM parent
            WHERE email = '{$email}'
            ";

            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            $row = $db->sql_fetchrow($result);

            $emailLink = "/index.php?plugin=member_resetPassword&_spAction=sendMessageToAdmin&email={$email}&showHTML=0";
            $click ="<a href='javascript:void(0)' link='{$emailLink}' class='jqui-dialog-form' id='contactLink' formId='messageToAdminForm'
                    w='450' h='400' title='{$ln->gd('w.member.loginForm.link.messageToAdmin')}'>
                    Click Here
                    </a>
                    ";

            if ($numRows == 0) {
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg'] = "Please be informed that your email is not registered with us. <br/>Please <u>{$click}</u> to contact us";
            }
        }

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }

        return $text;
    }

    /**
     *
     * @return <type>
     */
    function getResetSubmit() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $validate = Zend_Registry::get('validate');

        $pass_word = $fn->getPostParam('pass_word', '', true);
        $ran_no    = $fn->getReqParam('ran_no');

        //-------------------------------------------------------------------------------------//
        $valArr = $this->getResetSubmitValidate();
        $hasError = $valArr[0];
        $xmlText = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $SQL = "
        SELECT *
        FROM parent
        WHERE reset_password = '{$ran_no}'
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);

            $fa = array();
            $fa['pass_word'] = $pass_word;
            $fa['reset_password'] = '';

            $whereCondition = "WHERE parent_id = '{$row['parent_id']}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "parent", $whereCondition);
            $result = $db->sql_query($SQL);

        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     * @return <type>
     */
    function getResetSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $text = "";
        $validate->validateData("pass_word", 'Please enter a valid new password. Passwords must be 6 characters.', "alphaNumeric", $field2 = "", $minCharLength = "6", $maxCharLength = "20" );
        $validate->validateData("cpass_word",  'New password and confirm passwords are not matching.', "equal", "pass_word", 6, 20);

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }

        return $text;
    }

    /**
     *
     */
    function getSendMessageToAdminSubmit() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getSendMessageToAdminValidate()){
            return $validate->getErrorMessageXML();
        }

        //-----------------------------------------------------------------------//
        $fa = array();

        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['student_name']  = $fn->getPostParam('student_name');
        $fa['email']         = $fn->getPostParam('email');
        $fa['school_name']   = $fn->getPostParam('school_name');
        $fa['comments']      = $fn->getPostParam('comments');
        $fa['creation_date'] = date('Y-m-d H:i:s');

        //-----------------------------------------------------------------//
        $currentDate  = date('d-M-Y l h:i:s A');
        $hostName     = $_SERVER['HTTP_HOST'];

        $message = $ln->gd('p.member.emailToAdmin.form.enquiry.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[student_name]]', $fa['student_name'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);
        $message = str_replace('[[comments]]', $fa['comments'], $message);
        $message = str_replace('[[school_name]]', $fa['school_name'], $message);
        $message = str_replace('[[currentDate]]', $currentDate, $message);
        //$message = str_replace('[[url]]', $hostName, $message);

        $subject   = $ln->gd('w.member.emailToAdmin.form.enquiry.notifySubject');
        // $fromName  = $fa['first_name'] . ' ' . $fa['last_name'];
        $fromName  = 'Edukite';
        $fromEmail = $fa['email'];
        $firstName = $fa['first_name'];
        $toName    = 'Edukite';
        $toEmail   = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'firstName' => $firstName
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );


        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $exp = array('showHeader' => false);
        $emailMsg->sendEmail($exp);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSendMessageToAdminValidate() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name", "Please enter your name");
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $validate->validateData("school_name", "Please enter the school name");
        $validate->validateData("comments", $ln->gd("cp.form.fld.comments.err"));

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}