<?
class CP_Www_Plugins_Member_Login_Model extends CP_Common_Lib_PluginModelAbstract
{
    /**
     *
     */
    function getLoginSubmit() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $email = $fn->getPostParam('email', '', true);
        $pass_word = $fn->getPostParam('pass_word', '', true);
        $saveLogin = $fn->getPostParam('saveLogin', '', true);

        $loginType = $fn->getPostParam('loginType');
        $modDetail = $this->getModDetailsArray($loginType);
        //-------------------------------------------------------------//
        $valArr = $this->getLoginSubmitValidate();
        $hasError = $valArr[0];
        $xmlText = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $tableName = $modDetail['tableName'];
        $keyField = $modDetail['keyField'];
        $SQL = "
        SELECT *
        FROM {$tableName}
        WHERE email = '{$email}'
          AND published = 1
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);

            if ($cpCfg['p.member.login.updateLoginCount']) {
                $this->updateLoginCount($tableName, $keyField, $row[$keyField]);
            }
            if ($cpCfg['p.member.login.updateLastLoggedIn']) {
                $this->updateLastLoggedIn($tableName, $keyField, $row[$keyField]);
            }

            $retUrl = $this->setSessionValuesAfterLogin($row, $saveLogin);
            return $validate->getSuccessMessageXML($retUrl);
        }
    }

    /**
     *
     */
    function getLoginSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        if($cpCfg['cp.hasLoginHistory']){
            $this->resetStatusInLoginHistory();
        }

        $validate->resetErrorArray();

        $text = "";
        $isEmailInvalidFormat    = $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email", "", "3", "50");
        $isPasswordInvalidFormat = $validate->validateData("pass_word", $ln->gd("cp.form.fld.password.err"), "empty", "", "6", "20");

        if (!$isEmailInvalidFormat && !$isPasswordInvalidFormat) {
            $email     = $fn->getPostParam('email', '', true);
            $pass_word = $fn->getPostParam('pass_word', '', true);

            $row = $this->checkLogin($email, $pass_word);
            if (!$row) {
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg'] = $ln->gd("p.member.login.form.err.invalidLogin");
                $validate->errorArray['pass_word']['name'] = "pass_word";
                $validate->errorArray['pass_word']['msg'] = "";
            } else {
                if ($row['published'] == 0 && $row['activated'] == 0) {
                    $validate->errorArray['email']['name'] = "email";
                    $validate->errorArray['email']['msg'] = $ln->gd("accountNotActivatedError");

                } else if ($row['published'] == 0) {
                    $validate->errorArray['email']['name'] = "email";
                    $validate->errorArray['email']['msg'] = $ln->gd("p.member.login.form.err.invalidLogin");

                } else if($cpCfg['cp.hasLoginHistory']){
                    $count = $fn->getRecordCount('login_history', "contact_id = {$row['contact_id']} AND active = 1");
                    if ($count >= $cpCfg['cp.maxConcurrentLoginsAllowed']){
                        $validate->errorArray['email']['name'] = "email";
                        $validate->errorArray['email']['msg'] = $ln->gd("p.member.login.form.err.exceededConcurrentLogin");
                    }
                }

            }
        }

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            $fn->resetCookie("cpWWWUserNameC");
            $fn->resetCookie("cpWWWPasswordC");
            return array(1, $validate->getErrorMessageXML());
        }

        return $text;
    }

    /**
     *
     * @param type $email
     * @param type $pass_word
     * @return type
     */
    private function checkLogin($email, $pass_word) {
        $db = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $loginType = $fn->getPostParam('loginType');
        $modDetail = $this->getModDetailsArray($loginType);

        $loginSuccess = false;
        $contactRow = null;
        if ($cpCfg['cp.hasPasswordSalt']) {
            //get the staff record
            $SQL = "
            SELECT *
            FROM {$modDetail['tableName']}
            WHERE email = '{$email}'
              AND published = 1
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $row = $db->sql_fetchrow($result);

                $auth_pass = $cpUtil->getSaltedPassword($email, $pass_word, $row['salt']);

                $SQL = "
                SELECT *
                FROM {$modDetail['tableName']}
                WHERE email = '{$email}'
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
            WHERE email = '{$email}'
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

    /**
     *
     * @param type $row
     * @return type
     */
    function setLoginAfterRegister($row){
        return $this->setSessionValuesAfterLogin($row, 0);
    }

    /**
     *
     * @return <type>
     */
    function setSessionValuesAfterLogin($row, $saveLogin, $loginType = '') {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($loginType == ''){
            $loginType = $fn->getPostParam('loginType', 'membership_contact');
        }
        $modDetail = $this->getModDetailsArray($loginType);

        $userFullName = '';
        if (isset($row['first_name'])){
            $userFullName = $row['first_name'] . " " . $row['last_name'];
        } else {
            $userFullName = $row[$modDetail['titleField']];
        }

        $_SESSION['cpContactId']       = $row[$modDetail['keyField']];
        $_SESSION['cpUserNameWWW']     = isset($row['first_name']) ? $row['first_name'] : '';
        $_SESSION['cpUserNameWWW']    .= isset($row['last_name']) ? ' ' . $row['last_name'] : '';
        $_SESSION['cpEmail']           = $row['email'];
        $_SESSION['cpUserFullNameWWW'] = $userFullName;
        $_SESSION['cpIsLoggedInWWW']   = true;
        $_SESSION['cpLoginTypeWWW']    = $loginType;

        $tv['isLoggedInWWW'] = true; // used in twig

        if ($saveLogin == "1") {
            if($cpCfg['cp.useRememberMeTokenForCookieLogin']){
                $this->createRemberMeToken($row['email'], $row['pass_word']);
            } else {
                $fn->setCookie("cpWWWUserNameC", $row['email']);
                $fn->setCookie("cpWWWPasswordC", $row['pass_word']);
                $fn->setCookie("cpWWWLoginTypeC", $loginType);
            }
        } else {
            $fn->resetCookie("cpWWWUserNameC");
            $fn->resetCookie("cpWWWPasswordC");
            $fn->resetCookie("cpWWWLoginTypeC");
        }

        if($cpCfg['cp.hasLoginHistory']){
            $this->logLogin($_SESSION['cpContactId']);
        }

        $retUrl = $this->setReturnUrlAfterLogin($row);
        $fn->sessionRegenerate();

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
        return $retUrl;
    }

    /**
     *
     * @param type $email
     * @param type $pass_word
     */
    function createRemberMeToken($email, $pass_word){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $table_name = 'remember_me_token';

        $this->deleteCurrentRememberMeToken();

        $fa = array();
        $fa['user_name'] = $email;
        $fa['pass_word'] = $pass_word;
        $fa['token'] = hash('sha256', uniqid(mt_rand(), true));
        $fa = $fn->addCreationDetailsToFieldsArray($fa, $table_name);

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, $table_name);
        $result = $db->sql_query($SQL);
        $fn->setCookie("cpWWWRememberMeTokenC", $fa['token']);

    }

    /**
     *
     */
    function deleteCurrentRememberMeToken(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $cpWWWRememberMeTokenC = $fn->getCookieParam('cpWWWRememberMeTokenC', '', true);

        if($cpWWWRememberMeTokenC != ''){//delete the old token
            $table_name = 'remember_me_token';
            $SQL = "
            DELETE
            FROM {$table_name}
            WHERE token = '{$cpWWWRememberMeTokenC}'";
            $result = $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function setReturnUrlAfterLogin($row = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');

        $retUrlForm = $fn->getPostParam('returnUrl');
        $returnUrlAfterLoginCatRecType = $fn->getIssetParam($cpCfg,
                                            'm.membership.returnUrlAfterLoginCatRecType');
        $returnUrlAfterLoginSecRecType = $fn->getIssetParam($cpCfg,
                                            'm.membership.returnUrlAfterLoginSecRecType');
        if ($retUrlForm != ''){
            $retUrl = $retUrlForm;
        } else if (@$_SESSION['cpReturnUrlAfterLogin'] != ''){
            $retUrl = $_SESSION['cpReturnUrlAfterLogin'];
        } else if ($returnUrlAfterLoginCatRecType != '' && $returnUrlAfterLoginSecRecType != ''){
            $retUrl = $cpUrl->getUrlByCatType($returnUrlAfterLoginCatRecType,
                                              $returnUrlAfterLoginSecRecType);

        } else if ($returnUrlAfterLoginCatRecType != ''){
            $retUrl = $cpUrl->getUrlByCatType($returnUrlAfterLoginCatRecType);

        } else if ($returnUrlAfterLoginSecRecType != ''){
            // this is set as My Profile in cp/www/modules/membership/lib/config.php
            $retUrl = $cpUrl->getUrlBySecType($returnUrlAfterLoginSecRecType);

        } else {
            $retUrl = '/';
        }

        if (isset($_SESSION['cpReturnUrlAfterLogin'])){
            unset($_SESSION['cpReturnUrlAfterLogin']);
        }

        return $retUrl;
    }

    /**
     *
     */
    function getLogout() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['cpContactId']);
        unset($_SESSION['cpUserNameWWW']);
        unset($_SESSION['cpEmail']);
        unset($_SESSION['cpUserFullNameWWW']);
        unset($_SESSION['cpIsLoggedInWWW']);
        unset($_SESSION['cpLoginTypeWWW']);

        $fn->resetCookie("cpWWWUserNameC");
        $fn->resetCookie("cpWWWPasswordC");

        if (isset($_SESSION['shippingDetails'])){
            unset($_SESSION['shippingDetails']);
        }

        if($cpCfg['cp.useRememberMeTokenForCookieLogin']){
            $this->deleteCurrentRememberMeToken();
            $fn->resetCookie("cpWWWRememberMeTokenC");
        }

        if($cpCfg['cp.hasLoginHistory']){
            $session_id = session_id();
            $SQL = "
            UPDATE login_history
            SET active = 0
            WHERE session_id = '{$session_id}'
            ";
            $result = $db->sql_query($SQL);
        }
        $fn->sessionRegenerate();
        $cpUtil->redirect('/');
    }

    /**
     *
     * @return <type>
     */
    function loginWithCookie() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $text = "";

        $returnUrl = $fn->getPostParam('returnUrl');
        $cpWWWUserNameC = $fn->getCookieParam('cpWWWUserNameC');
        $cpWWWPasswordC = $fn->getCookieParam('cpWWWPasswordC');
        $cpWWWLoginTypeC = $fn->getCookieParam('cpWWWLoginTypeC');
        $cpWWWRememberMeTokenC = $fn->getCookieParam('cpWWWRememberMeTokenC');
        //-----------------------------------------------------------------//

        if($cpCfg['cp.useRememberMeTokenForCookieLogin'] && $cpWWWRememberMeTokenC != ''){
            $this->loginWithRememberMeTokenCookie();
        } else if ($cpWWWUserNameC == "" || $cpWWWPasswordC == "" || $cpWWWLoginTypeC == "") {
            return;
        } else {
            $email     = $cpWWWUserNameC;
            $pass_word = $cpWWWPasswordC;
            $modDetail = $this->getModDetailsArray($cpWWWLoginTypeC);

            $SQL = "
            SELECT *
            FROM {$modDetail['tableName']}
            WHERE email = '{$email}'
              AND pass_word = '{$pass_word}'
              AND published = 1
            ";

            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $row = $db->sql_fetchrow($result);
                $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                $retUrl = $this->setSessionValuesAfterLogin($row, 1, $cpWWWLoginTypeC);
                $cpUtil->redirect($retUrl);
            } else {
                $fn->resetCookie("cpWWWUserNameC");
                $fn->resetCookie("cpWWWPasswordC");
                $fn->resetCookie("cpWWWLoginTypeC");
            }
        }
    }

    /**
     *
     * @return <type>
     */
    function loginWithRememberMeTokenCookie() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $text = "";

        $returnUrl = $fn->getPostParam('returnUrl');

        $cpWWWLoginTypeC = $fn->getCookieParam('cpWWWLoginTypeC', 'membership_contact', true);
        $cpWWWRememberMeTokenC = $fn->getCookieParam('cpWWWRememberMeTokenC', '', true);
        //-----------------------------------------------------------------//
        if($cpCfg['cp.useRememberMeTokenForCookieLogin'] && $cpWWWRememberMeTokenC == ''){
            return;
        } else {
            $SQL = "
            SELECT *
            FROM remember_me_token
            WHERE token = '{$cpWWWRememberMeTokenC}'
            ";

            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $row = $db->sql_fetchrow($result);
                $modDetail = $this->getModDetailsArray($cpWWWLoginTypeC);
                $SQL2 = "
                SELECT *
                FROM {$modDetail['tableName']}
                WHERE email = '{$row['user_name']}'
                AND pass_word = '{$row['pass_word']}'
                AND published = 1
                ";

                $result2 = $db->sql_query($SQL2);
                $numRows2 = $db->sql_numrows($result2);

                if ($numRows2 > 0) {
                    $row2 = $db->sql_fetchrow($result2);
                    $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                    $retUrl = $this->setSessionValuesAfterLogin($row2, 1, $cpWWWLoginTypeC);
                    $cpUtil->redirect($retUrl);
                } else {
                    $fn->resetCookie("cpWWWRememberMeTokenC");
                }

            } else {
                $fn->resetCookie("cpWWWRememberMeTokenC");
            }

        }
    }

    /**
     *
     */
    function getModDetailsArray($module){
        $modulesArr = Zend_Registry::get('modulesArr');

        $arr = array();
        $arr['tableName'] = $modulesArr[$module]['tableName'];
        $arr['keyField']  = $modulesArr[$module]['keyField'];
        $arr['titleField']  = $modulesArr[$module]['titleField'];

        return $arr;
    }

    /**
     *
     */
    function logLogin($contact_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($contact_id == ''){
            return;
        }

        $fa = array();
        $fa['contact_id']    = $contact_id;
        $fa['ip_number']     = $_SERVER['REMOTE_ADDR'];
        $fa['session_id']    = session_id();
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $SQL  = $dbUtil->getInsertSQLStringFromArray($fa, 'login_history');
        $result  = $db->sql_query($SQL);

        /*
        $count = $fn->getRecordCount('login_history', "contact_id = {$contact_id} AND active = 1");
        if ($count > $cpCfg['cp.maxConcurrentLoginsAllowed']){
            $rec = $fn->getRecordByCondition('login_history', "contact_id = {$contact_id} AND active = 1", 'creation_date');

            if (is_array($rec)){
                $SQL = "
                UPDATE login_history
                SET active = 0
                WHERE login_history_id = '{$rec['login_history_id']}'
                ";
                $result = $db->sql_query($SQL);
            }
        }
        */
    }

    /**
     *
     */
    function resetStatusInLoginHistory(){
        $db = Zend_Registry::get('db');

        $SQL = "
        UPDATE login_history
        SET active = 0
        WHERE (TIME_TO_SEC(TIMEDIFF(NOW(), creation_date))) > 43200
        ";
        $result = $db->sql_query($SQL);
    }

    function updateLoginCount($tableName, $keyField, $keyFieldValue){
        $db = Zend_Registry::get('db');

        $SQL = "
        UPDATE {$tableName}
        SET login_count = login_count + 1
        WHERE {$keyField} = {$keyFieldValue}
        ";
        $db->sql_query($SQL);
    }

    function updateLastLoggedIn($tableName, $keyField, $keyFieldValue){
        $db = Zend_Registry::get('db');

        $SQL = "
        UPDATE {$tableName}
        SET last_logged_in_date = NOW()
        WHERE {$keyField} = {$keyFieldValue}
        ";
        $db->sql_query($SQL);
    }


}