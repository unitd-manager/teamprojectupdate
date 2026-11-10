<?
class CP_Admin_Plugins_Member_ForgotPassword_Model extends CP_Common_Lib_PluginModelAbstract
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
        FROM staff
        WHERE email = '{$email}'
          AND published = 1
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $siteTitle  = $cpCfg['cp.siteTitle'];

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);

            $reset_password_link = '';
            $currentDate = $fn->getCPDate(date("Y-m-d H:i:s"), $cpCfg['cp.dateDisplayFormatLong']);
            if ($cpCfg['cp.hasPasswordSalt']) { //reset password
                $expUrl = array(
                    'prependUrl' => $cpCfg['cp.siteUrl']
                );
                $resetPasswordHash = $this->getResetPasswordHash($row['staff_id']);
                $reset_password_link = $cpUrl->getUrlByCatType('Reset Password', '', $expUrl)."?hash={$resetPasswordHash}";

                $message  = $ln->gd("p.member.forgotPassword.form.email.notifyUserBody");
                $subject   = $ln->gd("p.member.forgotPassword.form.email.notifyUserSubject");
            } else {
                $message  = "                
                <table cellpadding= 5  border= 1 align= 'left'>
                
                <tr>
                    <td colspan=2><u><b>Password Reminder from [[siteTitle]]</b></u></td>
                </tr>
                
                <tr>
                   <td>First Name</td>
                   <td>[[first_name]]</td>
                </tr>
                
                <tr>
                   <td>Last Name</td>
                   <td>[[last_name]]</td>
                </tr>
                
                <tr>
                   <td>Email</td>
                   <td>[[email]]</td>
                </tr>

                <tr>
                   <td>Password</td>
                   <td>[[pass_word]]</td>
                </tr>

                <tr>
                   <td>Submitted On</td>
                   <td>[[currentDate]]</td>
                </tr>
                
                </table>
                                
                ";
                $subject   = "Password Reminder {$siteTitle}";
            }

            $message  = str_replace("[[first_name]]"  , $row["first_name"] , $message );
            $message  = str_replace("[[last_name]]"   , $row["last_name"]  , $message );
            $message  = str_replace("[[email]]"       , $row["email"]      , $message );
            $message  = str_replace("[[pass_word]]"   , $row["pass_word"]  , $message );
            $message  = str_replace("[[reset_password_link]]", $reset_password_link, $message );
            $message  = str_replace("[[currentDate]]" , $currentDate       , $message );
            $message  = str_replace("[[siteTitle]]" , $siteTitle       , $message );

            $fromName  = $cpCfg['cp.companyName'];
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
            FROM staff
            WHERE email = '{$email}'
              AND published = 1
            ";

            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            $row = $db->sql_fetchrow($result);

            if ($numRows == 0) {
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg'] = $ln->gd("Sorry, the email address you entered is not found in our database.");
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
     * @param type $staff_id
     */
    function getResetPasswordHash($staff_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $row = $fn->getRecordRowByID("staff", "staff_id", $staff_id);

        $fa = array();
        $fa['reset_pass_word_hash'] = $cpUtil->getResetPasswordHash($row['email']);

        $whereCondition = "WHERE staff_id = '{$staff_id}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "staff", $whereCondition);
        $result = $db->sql_query($SQL);

        return $fa['reset_pass_word_hash'];
    }
}