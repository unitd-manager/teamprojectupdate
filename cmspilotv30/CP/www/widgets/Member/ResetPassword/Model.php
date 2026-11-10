<?
class CP_Www_Widgets_Member_ResetPassword_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSave() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }


        $actual_pass_word = $fn->getPostParam('pass_word', '', true);
        $email = $fn->getPostParam('email', '', true);
        $reset_password_hash = $fn->getPostParam('hash', '', true);

        $arr = $cpUtil->getSaltAndPasswordArray($email, $actual_pass_word);

        $fa = array();
        $fa['salt'] = $arr['salt'];
        $fa['pass_word'] = $arr['pass_word'];
        $fa['reset_pass_word_hash'] = NULL;

        $whereCondition = "
            email = '{$email}'
        AND reset_pass_word_hash = '{$reset_password_hash}'
        ";

        $rec = $fn->getRecordByCondition('contact', $whereCondition);

        $fn->saveRecord($fa, 'contact', 'contact_id', $rec['contact_id']);

        if($cpCfg['cp.useRememberMeTokenForCookieLogin']){//delete all the remeber me tokens used by this user
            $fn->deleteAllRememberMeTokens($email);
        }
        $fn->sessionRegenerate();
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $isPasswordInvalidFormat = $validate->validateData("pass_word", $ln->gd("w.member.changePassword.form.fld.password.err.length"), "empty", $field2 = "", $minCharLength = "6", $maxCharLength = "20");
        $validate->validateData("cpass_word",  $ln->gd("w.member.changePassword.form.fld.password.err.compare"), "equal", "pass_word", 6, 20);

        $email = $fn->getPostParam('email', '', true);
        $reset_password_hash = $fn->getPostParam('hash', '', true);

        if($reset_password_hash == ''){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = $ln->gd("w.member.resetPassword.form.fld.resetPasswordHash.err");
        } else {
            $whereCondition = "
                email = '{$email}'
            AND reset_pass_word_hash = '{$reset_password_hash}'
            ";

            $rec = $fn->getRecordByCondition('contact', $whereCondition);

            if (!is_array($rec)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = $ln->gd("w.member.resetPassword.form.fld.resetPasswordHash.err");
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
        $fa = $fn->addToFieldsArray($fa, 'subscribe', 1);

        return $fa;
    }
}