<?
class CP_Admin_Plugins_Common_Login_Model extends CP_Common_Lib_PluginModelAbstract
{
    /**
     *
     */
    function getLoginSubmitOld() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');

        $email     = $fn->getPostParam('email'    , '', true);
        $pass_word = $fn->getPostParam('pass_word', '', true);
        $saveLogin = $fn->getPostParam('saveLogin', '', true);
        $loginBySmartCard = $fn->getPostParam('loginBySmartCard', '', true);

        //-------------------------------------------------------------------------------------//
        $valArr   = $this->getLoginSubmitValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError){
            header('Content-type: application/json');
            return $xmlText;
        }

        if ($loginBySmartCard){
            $smartCardId = $fn->getPostParam('smartCardId', '', true);
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE smart_card_id = '{$smartCardId}'
              AND published = 1
            ";
        } else {
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE email = '{$email}'
              AND published = 1
            ";
        }
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            $userGroupId = $row['user_group_id'];

            //amended by syed for double login issue, not sure about the below condition
            //if($cpCfg['cp.hasMultiUsergroupPerStaff'] && $userGroupId != $cpCfg['cp.superAdminUGId']){
            if($cpCfg['cp.hasMultiUsergroupPerStaff']){
                $returnText = $this->view->getChooseUsergroupForm($row);
                return $validate->getSuccessMessageXML('', $returnText);
            } else {
                if($cpCfg['cp.captureAutoLogin']){

                    if ($row['developer'] != 1 && $row['staff_login_type'] != 'Agent') {
                        $today = date("D",mktime (0,0,0,date("m"),date("d"), date("Y")));

                        if ($today == 'Mon' || $today == 'Tue'|| $today == 'Wed'|| $today == 'Thu'|| $today == 'Fri') {
                            $SQLStaff     = "SELECT * FROM staff WHERE developer = 0 AND staff_id = {$row['staff_id']}";
                            $resultStaff  = $db->sql_query($SQLStaff);

                            while ($rowStaff = $db->sql_fetchrow($resultStaff)){

                                if ($today == 'Mon') {
                                    $previous_date = date("Y-m-d",mktime (0,0,0,date("m"),date("d")-3, date("Y")));
                                } else {
                                    $previous_date = date("Y-m-d",mktime (0,0,0,date("m"),date("d")-1, date("Y")));
                                }

                                //$previous_date = date("Y-m-d",mktime (0,0,0,date("m"),date("d")-1, date("Y")));
                                $SQLAtt = "
                                SELECT * FROM {$cpCfg['cp.attendanceTable']}
                                WHERE staff_id = {$rowStaff['staff_id']}
                                  AND record_date = '{$previous_date}'
                                ";
                                $resultAtt  = $db->sql_query($SQLAtt);
                                $numRowsAtt = $db->sql_numrows($resultAtt);

                                if ($numRowsAtt == 0) {
                                    $fa = array();

                                    if ($cpCfg['cp.hasMultiUniqueSites']) {
                                        $fa['site_id']      = $row['site_id'];
                                    }
                                    $fa['staff_id']         = $rowStaff['staff_id'];
                                    $fa['record_date']      = $previous_date;
                                    $fa['on_leave']         = 1;

                                    $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                                    $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                                    $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                                    $hist_id                = $db->sql_nextid();
                                }
                            }
                        }

                        $today = date('Y-m-d');
                        $SQLStaffAtt = "
                        SELECT *
                        FROM {$cpCfg['cp.attendanceTable']}
                        WHERE record_date = '{$today}'
                          AND staff_id = {$row['staff_id']}
                        ";
                        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
                        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

                        if ($numRowsStaffAtt == 0) {
                            $fa = array();

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id']      = $row['site_id'];
                            }

                            $fa['staff_id']         = $row['staff_id'];
                            $fa['record_date']      = $today;
                            $fa['time_in']          = date('H:i:s');
                            $fa['creation_date']    = date('Y-m-d H:i:s');
                            $fa['created_by']       = $row['first_name'] . ' ' . $row['last_name'];

                            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                            $hist_id                = $db->sql_nextid();
                        }
                    }
                }

                $retUrl = $this->setSessionValuesAfterLogin($row, $saveLogin);
                /** if there is a hook for homepage in the theme level then use that **/
                $theme = getCPThemeObj($cpCfg['cp.theme']);
                if (method_exists($theme->fns, 'setSessionValuesAfterLogin')){
                    $theme->fns->setSessionValuesAfterLogin($row);
                }
                return $validate->getSuccessMessageXML($retUrl);
            }
        }
    }

    /**
     *
     */
    function getLoginSubmit() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');

        $email     = $fn->getPostParam('email'    , '', true);
        $pass_word = $fn->getPostParam('pass_word', '', true);
        $saveLogin = $fn->getPostParam('saveLogin', '', true);
        $loginBySmartCard = $fn->getPostParam('loginBySmartCard', '', true);

        //-------------------------------------------------------------------------------------//
        $valArr   = $this->getLoginSubmitValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError){
            header('Content-type: application/json');
            return $xmlText;
        }

        if ($loginBySmartCard){
            $smartCardId = $fn->getPostParam('smartCardId', '', true);
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE smart_card_id = '{$smartCardId}'
              AND published = 1
            ";
        } else {
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE email = '{$email}'
              AND published = 1
            ";
        }
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            $userGroupId = $row['user_group_id'];

            //amended by syed for double login issue, not sure about the below condition
            //if($cpCfg['cp.hasMultiUsergroupPerStaff'] && $userGroupId != $cpCfg['cp.superAdminUGId']){
            if($cpCfg['cp.hasMultiUsergroupPerStaff']){
                $returnText = $this->view->getChooseUsergroupForm($row);
                return $validate->getSuccessMessageXML('', $returnText);
            } else {
                if($cpCfg['cp.captureAutoLogin']){
                    if ($row['developer'] != 1 && $row['staff_login_type'] != 'Agent') {

                        /* Checking Previous attendance */
                        $SQLAtt = "
                        SELECT * FROM {$cpCfg['cp.attendanceTable']}
                        WHERE staff_id = {$row['staff_id']}
                        ";
                        $resultAtt  = $db->sql_query($SQLAtt);
                        $numRowsAtt = $db->sql_numrows($resultAtt);

                        if ($numRowsAtt > 0) {
                            $this->getMarkPreviousAttendanceRecords($row);
                        }

                        $today = date('Y-m-d');
                        $SQLStaffAtt = "
                        SELECT *
                        FROM {$cpCfg['cp.attendanceTable']}
                        WHERE record_date = '{$today}'
                          AND staff_id = {$row['staff_id']}
                        ";
                        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
                        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

                        if ($numRowsStaffAtt == 0) {
                            $fa = array();

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id']      = $row['site_id'];
                            }

                            $fa['staff_id']         = $row['staff_id'];
                            $fa['record_date']      = $today;
                            $fa['time_in']          = date('H:i:s');
                            $fa['creation_date']    = date('Y-m-d H:i:s');
                            $fa['created_by']       = $row['first_name'] . ' ' . $row['last_name'];

                            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                            $hist_id                = $db->sql_nextid();
                        }
                    }
                }

                $retUrl = $this->setSessionValuesAfterLogin($row, $saveLogin);
                /** if there is a hook for homepage in the theme level then use that **/
                $theme = getCPThemeObj($cpCfg['cp.theme']);
                if (method_exists($theme->fns, 'setSessionValuesAfterLogin')){
                    $theme->fns->setSessionValuesAfterLogin($row);
                }
                return $validate->getSuccessMessageXML($retUrl);
            }
        }
    }

    /**
     *
     */
    function getMarkPreviousAttendanceRecords($row) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* Finding last attendance record */
        $sqlPrevAtt = "
        SELECT MAX(record_date) AS last_attendance_date FROM {$cpCfg['cp.attendanceTable']}
        WHERE staff_id = {$row['staff_id']}
        ";
        $resultPrevAtt  = $db->sql_query($sqlPrevAtt);
        $rowPrevAtt     = $db->sql_fetchrow($resultPrevAtt);

        $date = new DateTime($rowPrevAtt['last_attendance_date']);
        $date->modify('+1 day');
        $begin = $date->format('Y-m-d');
        $end   = date("Y-m-d",mktime (0,0,0,date("m"),date("d"), date("Y")));

        $begin = new DateTime($begin);
        $end   = new DateTime($end);

        $interval = array();
        //Create array with all dates within date span
    	while($begin < $end) {
    		$interval[] = $begin->format('Y-m-d');
    		$begin->modify('+1 day');
    	}

	    #$interval = new DateInterval('P1D');
        #$daterange = new DatePeriod($begin, $interval ,$end);

        foreach($interval as $date){

            #$record_date = $date->format("Y-m-d");
            $timestamp   = strtotime($date);
            $record_day  = date("D", $timestamp);

            // Inserting attendance record excluding Sunday
            if ($record_day != 'Sun') {
                $fa = array();
                $fa['staff_id']         = $row['staff_id'];
                $fa['record_date']      = $date;
                $fa['on_leave']         = 1;
                $fa['creation_date']    = date('Y-m-d H:i:s');
                $fa['created_by']       = $row['first_name'] . ' ' . $row['last_name'];

                $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                $hist_id                = $db->sql_nextid();
            }
        }
    }

    /**
     *
     */
    function getLoginSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $text  = "";

        $loginBySmartCard = $fn->getPostParam('loginBySmartCard', '', true);

        if ($loginBySmartCard){
            $smartCardId = $fn->getPostParam('smartCardId', '', true);
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE smart_card_id = '{$smartCardId}'
              AND published = 1
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            if ($numRows == 0) {
                $validate->errorArray['smart_card_id']['name'] = "smart_card_id";
                $validate->errorArray['smart_card_id']['msg']  = 'Invalid Card';
            }

        } else {
            $isEmailInvalidFormat    = $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email", "", "3", "50");
            $isPasswordInvalidFormat = $validate->validateData("pass_word", $ln->gd("cp.form.fld.password.err"), "empty", "", "3", "20" );

            if(!$isEmailInvalidFormat && !$isPasswordInvalidFormat){
                $email     = $fn->getPostParam('email', '', true);
                $pass_word = $fn->getPostParam('pass_word', '', true);
                $staffRow = $this->checkLogin($email, $pass_word);

                if(!$staffRow) {
                    $validate->errorArray['email']['name'] = "email";
                    $validate->errorArray['email']['msg']  = $ln->gd("p.member.login.form.err.invalidLogin");
                    $validate->errorArray['pass_word']['name'] = "pass_word";
                    $validate->errorArray['pass_word']['msg']  = "";
                } else {
                    if ($staffRow['published'] == 0 && $staffRow['activated'] == 0){
                        $validate->errorArray['email']['name'] = "email";
                        $validate->errorArray['email']['msg']  = $ln->gd("accountNotActivatedError");
                    } else if ($staffRow['published'] == 0){
                        $validate->errorArray['email']['name'] = "email";
                        $validate->errorArray['email']['msg']  = $ln->gd("p.member.login.form.err.invalidLogin");

                    } else {
                        if ($cpCfg['cp.hasMultiUniqueSites'] && $staffRow['site_id'] == '' && $staffRow['developer'] == 0){
                            $validate->errorArray['email']['name'] = "email";
                            $validate->errorArray['email']['msg'] = $ln->gd("p.member.login.form.err.noSiteLinked");
                        }
                    }
                }
            }
        }

        if (count($validate->errorArray) == 0){
            return array(0, $validate->getSuccessMessageXML());
        } else {
            $fn->resetCookie("adminUserNameC");
            $fn->resetCookie("adminPasswordC");
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
    function checkLogin($email, $pass_word) {
        $db = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $loginSuccess = false;
        $staffRow = null;
        if ($cpCfg['m.core.staff.hasPasswordSalt']) {
            //get the staff record
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
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
                FROM {$cpCfg['cp.modAccessStaffTable']}
                WHERE email = '{$email}'
                  AND pass_word = '{$auth_pass}'
                  AND published = 1
                ";
                $result  = $db->sql_query($SQL);
                $numRows = $db->sql_numrows($result);

                if ($numRows > 0) {
                    $loginSuccess = true;
                    $staffRow = $db->sql_fetchrow($result);
                }
            }

        } else {
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE email = '{$email}'
              AND pass_word = '{$pass_word}'
              AND published = 1
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $loginSuccess = true;
                $staffRow = $db->sql_fetchrow($result);
            }
        }

        return $staffRow;
    }

    /**
     *
     */
    function setSessionValuesAfterLogin($row, $saveLogin) {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $fn->sessionRegenerate();

        $userGroupName = '';
        $userGroupType = '';

        if ($row['user_group_id'] != ''){
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessUserGroupTable']}
            WHERE user_group_id = {$row['user_group_id']}
            ";
            $result  = $db->sql_query($SQL);
            $rowUserGroup = $db->sql_fetchrow($result);

            $userGroupName = $rowUserGroup['title'];
            $userGroupType = @$rowUserGroup['user_group_type'];
        }
        $_SESSION['userGroupID']   = $row['user_group_id'];
        $_SESSION['userGroupName'] = $userGroupName;
        $_SESSION['userGroupType'] = $userGroupType;

        if(isset($row['address_country'])){
            $_SESSION['staffGeoCountryCode']= $row['address_country'];
        }

        $_SESSION['staff_id']        = $row[$cpCfg['cp.modAccessStaffIdLabel']];
        $_SESSION['userFullName']    = $row['first_name'] . " " . $row['last_name'];
        $_SESSION['isLoggedInAdmin'] = true;
        $_SESSION['isDeveloper']     = @$row['developer'];
        $_SESSION['userName']        = $_SESSION['userFullName'];

        if ($cpCfg['cp.hasMultiUniqueSites']){
            $_SESSION['cp_site_id'] = @$row['site_id'];
        }

        if ($cpCfg['m.core.staff.hasChangePasswordNextLogin']){
            $_SESSION['changePasswordOnLogin'] = $row['change_password_next_login'];
        }

        if ($cpCfg['cp.hasProjectMg'] == 1) {
            $_SESSION['staff_type']           = $row['staff_type'];
            $_SESSION['showSensitiveDetails'] = $row['show_sensitive_details'];
            $_SESSION['staff_team']           = $row['team'];
            $_SESSION['email']                = $row['email'];
            $_SESSION['isLoggedInWWW']        = 1;
            $_SESSION['contact_id']           = $row['staff_id'];
            $_SESSION['userFullNameWWW']      = $row['first_name'] . " " . $row['last_name'];
        }

        if ($cpCfg['cp.hasFirstRoomValueInStaff'] == 1) {
            $_SESSION['sectionName'] = $row['section_name'];
        }

        if ($saveLogin == "1") {
            $fn->setCookie("adminUserNameC", $row['email']);
            $fn->setCookie("adminPasswordC", $row['pass_word']);
        } else {
            $fn->resetCookie("adminUserNameC");
            $fn->resetCookie("adminPasswordC");
        }

        $randomIDText = "";

        if ($cpCfg['cp.autoLoginToIntranet'] == 1) {
            $urlRand = $cpCfg['intranetUrl'] . "index.php?_spAction=autoLoginRandomID&showHTML=0&user_name={$row['email']}";

            require_once 'HTTP/Request2.php';

            $request = new HTTP_Request2($urlRand, HTTP_Request2::METHOD_GET);
            try {
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    $random_id = $response->getBody();
                    $randomIDText = "&random_id={$random_id}";
                } else {
                    echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                         $response->getReasonPhrase();
                }
            } catch (HTTP_Request2_Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }
        }

        $retUrl = '';
        if (@$_SESSION['returnUrlAfterLogin'] != ''){
            $retUrl = $_SESSION['returnUrlAfterLogin'];
        } else {
            $retUrl = '';
        }

        if ($retUrl != "") {
            $retUrl .= (strpos($retUrl , '?') === false) ? "?" : "&";
            $retUrl .= "logged_in=1{$randomIDText}";
        } else {
            $retUrl = "index.php?logged_in=1{$randomIDText}";
        }

        unset($_SESSION['returnUrlAfterLogin']);

        return $retUrl;

    }

    /**
     *
     * @return <type>
     */
    function loginWithCookie() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $text  = "";

        if (!$cpCfg['cp.adminHasRememberLogin']) {
            return;
        }
        $returnUrl      = $fn->getPostParam('returnUrl');
        $adminUserNameC = $fn->getCookieParam('adminUserNameC');
        $adminPasswordC = $fn->getCookieParam('adminPasswordC');

        //-----------------------------------------------------------------//
        if ($adminUserNameC == "" && $adminPasswordC == ""){
            return;

        } else {
            $email = $adminUserNameC;
            $pass_word = $adminPasswordC;

            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE email = '{$email}'
              AND pass_word = '{$pass_word}'
              AND published = 1
            ";

            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows > 0) {
                $row = $db->sql_fetchrow($result);

                if ($cpCfg['cp.hasMultiUniqueSites'] && $row['site_id'] == '' && $row['developer'] == 0){
                    $fn->resetCookie("adminUserNameC", "", time()-1209600);
                    $fn->resetCookie("adminPasswordC", "", time()-1209600);
                } else {
                    $_SESSION['returnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                    $retUrl = $this->setSessionValuesAfterLogin($row, 1);
                    $cpUtil->redirect($retUrl);
                }

            } else {
                $fn->resetCookie("adminUserNameC");
                $fn->resetCookie("adminPasswordC");
            }
        }
    }

    //==================================================================//
    function getAutoLoginRandomID() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $user_name = $fn->getReqParam('user_name');
        $random_id = $cpUtil->getRandomNumber();

        $fa = array();

        $fa['user_name'] = $user_name;
        $fa['random_id'] = $random_id;

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "auto_login");
        $result      = $db->sql_query($SQL);
        print $random_id;
    }

    //==================================================================//
    function getAutoLoginUserByRandomID() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $random_id = $fn->getReqParam('random_id', '', true);
        $rec = $fn->getRecordByCondition('auto_login', "random_id='{$random_id}'");

        if (isset($rec['user_name'])) {
            $user_name = $rec['user_name'];

            $user = $fn->getRecordByCondition($cpCfg['cp.modAccessStaffTable'], "user_name='{$user_name}'");

            if (!isset($user['user_name'])) {
                return;
            }

            if (class_exists('LoginLocal')) {
                $login = new LoginLocal();
            } else {
                $login = new Login();
            }

            $login->loginSubmit($user['user_name'], $user['pass_word'], '', '', 0);

            $SQL    = "DELETE FROM auto_login WHERE random_id = '{random_id}'";
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getChooseUsergroupSubmit() {
        $hook = getCPPluginHook('common_login', 'chooseUsergroupSubmit', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $user_group_id = $fn->getPostParam('user_group_id', '', true);
        $staffId       = $fn->getPostParam('staff_id', '', true);
        $saveLogin     = $fn->getPostParam('saveLogin', '', true);
        $count = $fn->getRecordCount('mod_acc_shop_user_group', "staff_id = '{$staffId}' AND user_group_id = '{$user_group_id}'");
        if ($count > 0){
            $row = $fn->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], 'staff_id', $staffId, array('condn' => 'AND published = 1'));
            $row['user_group_id'] = $user_group_id;

            $retUrl = $this->setSessionValuesAfterLogin($row, $saveLogin);
            /** if there is a hook for homepage in the theme level then use that **/
            $theme = getCPThemeObj($cpCfg['cp.theme']);
            if (method_exists($theme->fns, 'setSessionValuesAfterLogin')){
                $theme->fns->setSessionValuesAfterLogin($row);
            }
            return $validate->getSuccessMessageXML($retUrl);
        } {
            return $validate->getSuccessMessageXML("index.php");
        }
    }

    /**
     *
     */
    function getLogout() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($cpCfg['cp.captureAutoLogin']){
            $today = date('Y-m-d');

            $SQLStaffAtt = "
            SELECT *
            FROM {$cpCfg['cp.attendanceTable']}
            WHERE record_date = '{$today}'
              AND staff_id = '{$_SESSION['staff_id']}'
            ";
            $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
            $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

            if ($numRowsStaffAtt > 0) {
                $fa = array();

                $fa['leave_time']           = date('H:i:s');
                $fa['modification_date']    = date('Y-m-d H:i:s');
                $fa['modified_by']          = $_SESSION['userFullName'];

                $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$_SESSION['staff_id']}'";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, $cpCfg['cp.attendanceTable'], $whereCondition);
                $db->sql_query($SQL);
            }
        }

        session_destroy();
        $fn->resetCookie("adminUserNameC");
        $fn->resetCookie("adminPasswordC");
        $fn->sessionRegenerate();

        // added the below 2 lines by ahmad due to bug with resetCookie function
        setcookie('adminUserNameC', '',  time()-1209600);
        setcookie('adminPasswordC', '',  time()-1209600);

        $cpUtil->redirect('index.php');
    }
}