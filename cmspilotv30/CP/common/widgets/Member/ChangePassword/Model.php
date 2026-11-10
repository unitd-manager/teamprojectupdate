<?
class CP_Common_Widgets_Member_ChangePassword_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSave() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $validate = Zend_Registry::get('validate');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mod = &$modulesArr[$_SESSION['cpLoginTypeWWW']];

        //-------------------------------------------------------------------------------------//
        $valArr = $this->getEditValidate();
        $hasError = $valArr[0];
        $xmlText = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }
        $fa = array();
        $fa['pass_word'] = $fn->getPostParam('new_password1', '', true);

        if ($cpCfg['cp.hasPasswordSalt']) {
            $old_password = $fn->getPostParam('old_password', '', true);
            $contactRow = $this->checkLogin($old_password);

            $new_pass_word = $fa['pass_word'];
            $email = $contactRow['email'];

            $arr = $cpUtil->getSaltAndPasswordArray($email, $new_pass_word);
            $fa['salt'] = $arr['salt'];
            $fa['pass_word'] = $arr['pass_word'];
        }

        $whereCondition = "WHERE {$mod['keyField']} = '{$_SESSION['cpContactId']}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, $mod['tableName'], $whereCondition);
        $result = $db->sql_query($SQL);

        if($cpCfg['cp.useRememberMeTokenForCookieLogin']){//delete all the remeber me tokens used by this user
            $rowContact = $fn->getRecordRowByID($mod['tableName'], $mod['keyField'], $_SESSION['cpContactId']);
            $fn->deleteAllRememberMeTokens($rowContact['email']);
        }
        $fn->sessionRegenerate();
        
        return $xmlText;
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mod = &$modulesArr[$_SESSION['cpLoginTypeWWW']];

        $c = &$this->controller;

        $validate->resetErrorArray();

        $validateCSRFToken = $fn->getPostParam('w-member-changePassword_validateCSRFToken', 0);
        if($validateCSRFToken == 1){
            $validate->validateCSRFToken();
        }

        $text = "";
        $isPasswordInvalid = $validate->validateData("old_password", $ln->gd("cp.form.fld.password.err"), "empty", "", "3", "20");
        $validate->validateData("new_password1",  $ln->gd("w.member.changePassword.form.fld.password.err.compare"), "equal", "new_password2", 6, 20);

        if (!$isPasswordInvalid ) {
            $old_password = $fn->getPostParam('old_password', '', true);

            $row = $this->checkLogin($old_password);

            if (!$row) {
                $validate->errorArray['old_password']['name'] = "old_password";
                $validate->errorArray['old_password']['msg'] = $ln->gd("w.member.changePassword.form.fld.oldPassword.err");
            }
        }

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }
    }

    /**
     *
     * @param type $email
     * @param type $pass_word
     * @return type
     */
    private function checkLogin($pass_word) {
        $db = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $modulesArr = Zend_Registry::get('modulesArr');
        $modDetail = &$modulesArr[$_SESSION['cpLoginTypeWWW']];

        $loginSuccess = false;
        $contactRow = null;
        if ($cpCfg['cp.hasPasswordSalt']) {

            $SQL = "
            SELECT *
            FROM {$modDetail['tableName']}
            WHERE {$modDetail['keyField']} = '{$_SESSION['cpContactId']}'
              AND published = 1
            ";

            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $row = $db->sql_fetchrow($result);

                $auth_pass = $cpUtil->getSaltedPassword($row['email'], $pass_word, $row['salt']);

                $SQL = "
                SELECT *
                FROM {$modDetail['tableName']}
                WHERE {$modDetail['keyField']} = '{$_SESSION['cpContactId']}'
                  AND email = '{$row['email']}'
                  AND pass_word = '{$auth_pass}'
                  AND published = 1
                ";
                $result  = $db->sql_query($SQL);
                $numRows = $db->sql_numrows($result);

                if ($numRows > 0) {
                    $loginSuccess = true;
                    $contactRow = $db->sql_fetchrow($result);
                }
            }

        } else {
            $SQL = "
            SELECT *
            FROM {$modDetail['tableName']}
            WHERE {$modDetail['keyField']} = '{$_SESSION['cpContactId']}'
              AND pass_word = '{$pass_word}'
              AND published = 1
            ";

            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $loginSuccess = true;
                $contactRow = $db->sql_fetchrow($result);
            }
        }

        return $contactRow;
    }
}