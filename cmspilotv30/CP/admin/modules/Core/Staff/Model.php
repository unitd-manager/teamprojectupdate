<?
class CP_Admin_Modules_Core_Staff_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames  = '';
        $countryAppendSQL = '';
        $countryJoinSQL   = '';

        if ($cpCfg['m.core.staff.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL = "LEFT JOIN (country co) ON (a.country_id = co.country_id)";
        }

        $staff_group_id = $fn->getReqParam('staff_group_id');

        if($staff_group_id != "") {
            $extraTableNames .= "staff_group_history sg_hist,";
        }

        $staffGroupSQL = "";

        if($cpCfg['cp.hasProjectMg'] == 1 && $cpCfg['m.core.hasStaffGroup'] == 1) {
            $staffGroupSQL = "(
                SELECT GROUP_CONCAT(stfGrp.title ORDER BY stfGrp.title SEPARATOR ', ')
                FROM staff_group stfGrp
                    ,staff_group_history stfGrpHist
                WHERE stfGrpHist.staff_id = a.staff_id
                  AND stfGrp.staff_group_id = stfGrpHist.staff_group_id
            ) AS staff_group_names,
            ";
        }

        $SQL = "
        SELECT {$staffGroupSQL}
               a.*
              ,gc.name AS country_title
              ,b.title AS user_group_title
              ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name
              {$countryAppendSQL}
              {$fn->getSiteTitleFld()}
        FROM {$extraTableNames}
              {$cpCfg['cp.modAccessStaffTable']} a
        LEFT JOIN ({$cpCfg['cp.modAccessUserGroupTable']} b) ON (a.user_group_id = b.user_group_id)
        LEFT JOIN geo_country gc ON (a.address_country = gc.country_code)
        {$countryJoinSQL}
        {$fn->getSiteFldSqlJoin('a')}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv        = Zend_Registry::get('tv');
        $cpCfg     = Zend_Registry::get('cpCfg');
        $fn        = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'a';

        $user_group_id  = $fn->getReqParam('user_group_id');
        $staff_group_id = $fn->getReqParam('staff_group_id');
        $staff_id       = $fn->getReqParam('staff_id');
        $status         = $fn->getReqParam('status');
        $country_id     = $fn->getReqParam('country_id');

        $searchVar->sqlSearchVar[] = "(a.developer = 0 OR a.developer IS NULL)"; // do not show the developer record in the list

        if ($cpCfg['m.core.staff.showCountry'] == 1) {
            if ($country_id != '') {
                $searchVar->sqlSearchVar[] = "co.country_id = {$country_id}";
            }
        }

        if ($cpCfg['cp.hasProjectMg'] == 1 && $status == "" && $tv['searchDone'] == 0 && $staff_id == '' ) {
            $status = "Current";
        }

        if ($staff_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.staff_id = '{$staff_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "a.staff_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.staff_id');

            if ($status != '' ) {
                $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
            }

            if ($user_group_id != '' ) {
                $searchVar->sqlSearchVar[] = "a.user_group_id = '{$user_group_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        a.first_name LIKE '%{$tv['keyword']}%'  OR
                                        a.last_name  LIKE '%{$tv['keyword']}%'  OR
                                        a.user_name  LIKE '%{$tv['keyword']}%'  OR
                                        a.email      LIKE '%{$tv['keyword']}%'
                                      )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "a.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
            }

            if ($staff_group_id != '' ) {
                $searchVar->sqlSearchVar[] = "a.staff_id = sg_hist.staff_id";
                $searchVar->sqlSearchVar[] = "sg_hist.staff_group_id = {$tv['staff_group_id']}";
            }

            //------------------------------------------------------------------------//
            $searchVar->sortOrder = "a.first_name, a.last_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name', 'Please enter the last name');
        $validate->validateData('email', 'Please enter valid email address', 'email');
        
        $email = $fn->getPostParam('email', '', true);
        if ($email != ''){
            $rec = $fn->getRecordByCondition("staff", "email = '{$email}'");

            if (is_array($rec)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "This email address already exist";
            }
        }
        
        
        $fn->getValidateSiteFld($validate);

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Current';

        if (!isset($fa['user_group_id'])){
            $fa['user_group_id'] = $cpCfg['cp.superAdminUGId'];
        }

        $fa['user_group_id'] = $cpCfg['cp.superAdminUGId'];
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');

        if ($cpCfg['m.core.staff.hasPasswordSalt']) {
            $has_pwd = $fn->getPostParam('has_pwd');
            if ($has_pwd != 1) {
                $validate->validateData('pass_word', 'Please enter the password');
            }
        }
        $fn->getValidateSiteFld($validate);

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
        $validate = Zend_Registry::get('validate');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['m.core.staff.hasPasswordSalt']) {
            $pass_word = $fa['pass_word'];
            $email = $fa['email'];
            if ($pass_word != '') {
                $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            } else {
                //remove pass_word field from the fields array
                unset($fa['pass_word']);
            }
        }

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     * /admin/index.php?_spAction=updateMissingSaltPasswords&showHTML=0&module=core_staff
     */
    function getUpdateMissingSaltPasswords(){
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');

        if ($cpCfg['m.core.staff.hasPasswordSalt']) {
            $SQL = "
            SELECT s.*
            FROM staff s
            WHERE (salt IS NULL OR salt = '')
              AND email IS NOT NULL
              AND pass_word IS NOT NULL
            ORDER BY s.staff_id
            ";

            $dataArray = $dbUtil->getSQLResultAsArray($SQL);
            foreach ($dataArray as $row) {
                $fa = array();
                if ($row['email'] != '' && $row['pass_word'] != '') {
                    $arr = $cpUtil->getSaltAndPasswordArray($row['email'], $row['pass_word']);
                    $fa['salt'] = $arr['salt'];
                    $fa['pass_word'] = $arr['pass_word'];
                    $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'staff', "WHERE staff_id = {$row['staff_id']}");
                    $result = $db->sql_query($updateSQL);
                }
            }
            print "update completed!";
        }
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'user_group_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'staff_type');
        $fa = $fn->addToFieldsArray($fa, 'team');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'section_name');
        $fa = $fn->addToFieldsArray($fa, 'staff_rate');
        $fa = $fn->addToFieldsArray($fa, 'show_sensitive_details');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'site_id');
        $fa = $fn->addToFieldsArray($fa, 'developer', 0);

        $fa = $fn->addToFieldsArray($fa, 'zip_code');
        $fa = $fn->addToFieldsArray($fa, 'short_code');
        $fa = $fn->addToFieldsArray($fa, 'change_password_next_login');

        if($cpCfg['m.core.staff.hasCommissionDetails'] && $_SESSION['userGroupName'] == "Super Administrator"){
            $fa = $fn->addToFieldsArray($fa, 'staff_commission_rate');
            $fa = $fn->addToFieldsArray($fa, 'commission_type');
        }

        return $fa;
    }

    /**
     *
     */
    function getCoreStaffManPowerStaffCommissionLinkSQL($id) {
        return $SQL = "
        SELECT a.staff_commission_id
              ,p.project_code
              ,DATE_FORMAT(a.date, '%d-%m-%Y')
              ,a.amount
              ,a.status
        FROM `staff_commission` a
        LEFT JOIN (project p) ON (a.project_id = p.project_id)
          AND a.staff_id ={$id}
        ";
    }

    /**
     *
     */
    function getStaffStaffGroupLinkSQL($id) {
        $SQL = "
        SELECT a.staff_group_id
              ,a.title
        FROM `staff_group` a
            ,`staff_group_history` b
        WHERE a.staff_group_id = b.staff_group_id
          AND b.staff_id ={$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getStaffByGroupSQL($user_group_id = '') {
        $userGroupSQL = '';

        if ($user_group_id) {
            $userGroupSQL = "a.user_group_id = {$user_group_id}";
        }
        if ($userGroupSQL) {
            $userGroupSQL = "WHERE {$userGroupSQL}";
        }
        $sql = "
        SELECT a.staff_id, CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
        FROM staff a
        {$userGroupSQL}
        ORDER BY staff_name
        ";

        return $sql;
    }

    function getStaffCodeSQL() {
        $SQL = "
        SELECT s.staff_id
              ,s.short_code
        FROM staff s
        WHERE s.published = 1
        ";

        return $SQL;
    }
}
