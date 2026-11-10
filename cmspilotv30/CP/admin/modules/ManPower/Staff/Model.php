<?
class CP_Admin_Modules_ManPower_Staff_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames  = '';
        $countryAppendSQL = '';
        $countryJoinSQL   = '';

        if ($cpCfg['m.manPower.staff.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL = "LEFT JOIN (country co) ON (a.country_id = co.country_id)";
        }

        $staff_group_id = $fn->getReqParam('staff_group_id');

        if($staff_group_id != "") {
            $extraTableNames .= "staff_group_history sg_hist,";
        }

        $staffGroupSQL = "";

        if($cpCfg['cp.hasProjectMg'] == 1 && $cpCfg['m.manPower.hasStaffGroup'] == 1) {
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
              ,b.user_group_type
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
        $searchVar->sqlSearchVar[] = "b.user_group_type != 'Agent'";

        if ($cpCfg['m.manPower.staff.showCountry'] == 1) {
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

            if ($_SESSION['userGroupType'] != 'Super Administrator') {
                $searchVar->sqlSearchVar[] = "a.staff_id = '{$_SESSION['staff_id']}'";
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
        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('email', 'Please enter valid Email');

        $email = $fn->getPostParam('email', '', true);
        if ($email != ''){
            if(!$validate->isEmail($email)){            
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Please enter valid email";
            }
            else{
                $rec = $fn->getRecordByCondition('staff', "email = '{$email}'");
                if (is_array($rec)){
                    $expEmail = array('displayText'=> 'Goto Existing Staff Record');
                    $emailLink = $fn->getRecordDetailLink('manPower_staff', 'record_id', $rec['staff_id'], $expEmail);
                    
                    $validate->errorArray['email']['name'] = "email";
                    $validate->errorArray['email']['msg']  = "Email already exists. '{$emailLink}'";
                }
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $site_id = 0;
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $site_id = $_SESSION['cp_site_id']; 
        }

        $fa = $this->getFields();
        $fa['status'] = 'Current';
        $fa['staff_login_type'] = 'Staff';
        $fa['site_id'] = $site_id;

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
        $validate->validateData('pass_word', 'Please enter the Password');

        /*$phone = $fn->getPostParam('phone', '', true);
        if (!ctype_digit($phone)) {
            $validate->errorArray['phone']['name'] = "phone";
            $validate->errorArray['phone']['msg']  = "Please enter valid phone no only eg: 654";
        }*/

        if ($_SESSION['userGroupType'] == 'Super Administrator') {
            $email_address = $fn->getPostParam('email', '', true);
            $record_id     = $fn->getReqParam('staff_id');
            
            $validate->validateData('email' , 'Please enter valid Email');
            if ($email_address != ''){
                if(!$validate->isEmail($email_address)){            
                    $validate->errorArray['email']['name'] = "email";
                    $validate->errorArray['email']['msg']  = "Please enter valid email";
                }
                else{
                    $recStaff = $fn->getRecordByCondition('staff', "email = '{$email_address}' AND staff_id != {$record_id}");
                    if (is_array($recStaff)){
                        $expEmail = array('displayText'=> 'Goto Existing Staff Record');
                        $emailLink = $fn->getRecordDetailLink('manPower_staff', 'staff_id', $recStaff['staff_id'], $expEmail);
                        $validate->errorArray['email']['name'] = "email";
                        $validate->errorArray['email']['msg']  = "Email already exists. '{$emailLink}'";
                    }
                }
            }
        }   

        if ($cpCfg['m.manPower.staff.hasPasswordSalt']) {
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

        if ($cpCfg['m.manPower.staff.hasPasswordSalt']) {
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
     * /admin/index.php?_spAction=updateMissingSaltPasswords&showHTML=0&module=manPower_staff
     */
    function getUpdateMissingSaltPasswords(){
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');

        if ($cpCfg['m.manPower.staff.hasPasswordSalt']) {
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
        $fa = $fn->addToFieldsArray($fa, 'designation');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'fin_no');
        $fa = $fn->addToFieldsArray($fa, 'phone');
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
        $fa = $fn->addToFieldsArray($fa, 'native_address_street');
        $fa = $fn->addToFieldsArray($fa, 'native_address_town');
        $fa = $fn->addToFieldsArray($fa, 'native_address_state');
        $fa = $fn->addToFieldsArray($fa, 'native_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'contract_date');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'site_id');
        $fa = $fn->addToFieldsArray($fa, 'developer', 0);
        $fa = $fn->addToFieldsArray($fa, 'staff_salary');
        $fa = $fn->addToFieldsArray($fa, 'passport_no');
        $fa = $fn->addToFieldsArray($fa, 'zip_code');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'date_of_expiry');

        $fa = $fn->addToFieldsArray($fa, 'zip_code');
        $fa = $fn->addToFieldsArray($fa, 'short_code');
        $fa = $fn->addToFieldsArray($fa, 'change_password_next_login');

        if($cpCfg['m.manPower.staff.hasCommissionDetails'] && $_SESSION['userGroupName'] == "Super Administrator"){
            $fa = $fn->addToFieldsArray($fa, 'staff_commission_rate');
            $fa = $fn->addToFieldsArray($fa, 'commission_type');
        }

        return $fa;
    }

    /**
     *
     */
    function getManPowerStaffManPowerStaffCommissionLinkSQL($id) {
        return $SQL = "
        SELECT a.staff_commission_id
              ,p.title
              ,DATE_FORMAT(a.date, '%d-%m-%Y')
              ,a.amount
              ,a.status
        FROM `staff_commission` a
        LEFT JOIN (project p) ON (a.project_id = p.project_id)
        WHERE a.staff_id ={$id}
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

    /**
     *
     */
	    function getPrintStaffContractAbuDhabi() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $staff_id = $fn->getReqParam('staff_id');
        $staffRec  = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);
        
       
        $template = 'Employment Contract - Abu Dhabi.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Employment Agreement_' . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        
        //$contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$staffRec['native_address_country']}'");

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $staffRec['native_address_country']);

		$staffName = strtoupper($staffRec['first_name'] . ' ' .$staffRec['last_name']);
		$contract_date = $staffRec['contract_date'];
        $contractDate = $fn->getCPDate($contract_date, 'd/m/Y');
        $contractDated = $fn->getCPDate($contract_date, 'dS	 F Y');
        $contractDay = $fn->getCPDate($contract_date, 'l');
		$salWords = $staffRec['staff_salary'];
		$salarywords = $this->numberToWords($salWords);
		$salaryWords = strtoupper($salarywords);
								
        $valArr = array();
        /* Staff Details */
        $valArr['staff_name']           = $staffName;
        $valArr['contract_date']        = $contractDate;
        $valArr['contract_day']         = $contractDay;
        $valArr['contract_dated']       = $contractDated;
        $valArr['passport_no']          = $staffRec['passport_no'];
        $valArr['address_street']       = $staffRec['native_address_street'];
        $valArr['address_town']         = $staffRec['native_address_town'];
        $valArr['address_state']        = $staffRec['native_address_state'];
        $valArr['zip_code']             = $staffRec['zip_code'];
        $valArr['address_country']      = $countryNameRec['name'];
        $valArr['designation']          = $staffRec['designation'];
        $valArr['department']           = $staffRec['department'];
        $valArr['salary']               = $staffRec['staff_salary'];
        $valArr['salarywords']          = $salaryWords;

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
	    function getPrintStaffContract() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $staff_id = $fn->getReqParam('staff_id');
        $staffRec  = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);
        
       
        $template = 'Employment Agreement.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Employment Agreement_' . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        
        //$contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$staffRec['native_address_country']}'");

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $staffRec['native_address_country']);

		$staffName = strtoupper($staffRec['first_name'] . ' ' .$staffRec['last_name']);
		$contract_date = $staffRec['contract_date'];
        $contractDate = $fn->getCPDate($contract_date, 'd/m/Y');
        $contractDated = $fn->getCPDate($contract_date, 'dS	 F Y');
        $contractDay = $fn->getCPDate($contract_date, 'l');
		$salWords = $staffRec['staff_salary'];
		$salarywords = $this->numberToWords($salWords);
		$salaryWords = strtoupper($salarywords);
								
        $valArr = array();
        /* Staff Details */
        $valArr['staff_name']           = $staffName;
        $valArr['contract_date']        = $contractDate;
        $valArr['contract_day']         = $contractDay;
        $valArr['contract_dated']       = $contractDated;
        $valArr['passport_no']          = $staffRec['passport_no'];
        $valArr['address_street']       = $staffRec['native_address_street'];
        $valArr['address_town']         = $staffRec['native_address_town'];
        $valArr['address_state']        = $staffRec['native_address_state'];
        $valArr['zip_code']             = $staffRec['zip_code'];
        $valArr['address_country']      = $countryNameRec['name'];
        $valArr['designation']          = $staffRec['designation'];
        $valArr['department']           = $staffRec['department'];
        $valArr['salary']               = $staffRec['staff_salary'];
        $valArr['salarywords']          = $salaryWords;

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
	function numberToWords($number){
	    if (($number < 0) || ($number > 999999999))
	    {
	       throw new Exception("Number is out of range");
	    }
	
	    $Gn = floor($number / 1000000);  /* Millions (giga) */
	    $number -= $Gn * 1000000;
	    $kn = floor($number / 1000);     /* Thousands (kilo) */
	    $number -= $kn * 1000;
	    $Hn = floor($number / 100);      /* Hundreds (hecto) */
	    $number -= $Hn * 100;
	    $Dn = floor($number / 10);       /* Tens (deca) */
	    $n = $number % 10;               /* Ones */ 
	
	    $result = ""; 
	
	    if ($Gn)
	    {  $result .= $this->numberToWords($Gn) . " Million";  } 
	
	    if ($kn)
	    {  $result .= (empty($result) ? "" : " ") . $this->numberToWords($kn) . " Thousand"; } 
	
	    if ($Hn)
	    {  $result .= (empty($result) ? "" : " ") . $this->numberToWords($Hn) . " Hundred";  } 
	
	    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
	        "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
	        "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",
	        "Nineteen");
	    $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty",
	        "Seventy", "Eigthy", "Ninety"); 
	
	    if ($Dn || $n)
	    {
	       if (!empty($result))
	       {  $result .= " and ";
	       } 
	
	       if ($Dn < 2)
	       {  $result .= $ones[$Dn * 10 + $n];
	       }
	       else
	       {  $result .= $tens[$Dn];
	          if ($n)
	          {  $result .= "-" . $ones[$n];
	          }
	       }
	    }
	
	    if (empty($result))
	    {  $result = "zero"; } 
	
	    return $result;
	} 
    /**
     *
     */
    function getStaffDocumentSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $staff_id = $fn->getReqParam('staff_id');
        $documents_id = $fn->getReqParam('documents_id');
        $documents = $fn->getReqParam('documents');

        $fa = array();
        $fa['staff_id'] = $staff_id;
        $fa['documents_id'] = $documents_id;
        $fa['site_id'] = $_SESSION['cp_site_id'];
        if($documents == 1){
            print 'aaaaa';
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'staff_documents');
            $db->sql_query($SQL);
            $staff_documents_id = $db->sql_nextid();
            return 'yes';
        } else{
            $sql = "
            DELETE FROM staff_documents
            WHERE staff_id = {$staff_id}
              AND documents_id = {$documents_id}
            ";
            $result = $db->sql_query($sql);
        }        
    }

    /**
     *
     */
    function getPrintDeclarationWord() {
	    $cpCfg = Zend_Registry::get('cpCfg');
	    $ln = Zend_Registry::get('ln');
	    $fn = Zend_Registry::get('fn');
	    $db = Zend_Registry::get('db');
	    $media = Zend_Registry::get('media');
	
	    //-----------------------------------------------------------------//
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');
	
	    $TBS = new clsTinyButStrong;
	    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
	    
	    $staff_id = $fn->getReqParam('staff_id');
	    $staffRec  = $fn->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], 'staff_id', $staff_id);
	    	   
		$staffName             = strtoupper($staffRec['first_name'] . ' ' .$staffRec['last_name']);

	    $template = 'Staff_Declaration.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_Declaration_' . $staffName . '.docx';
	    //$file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
	    
		$dob = $staffRec['date_of_birth'];
        $DOB = $fn->getCPDate($dob, 'd-m-Y');
		$doe = $staffRec['date_of_expiry'];
        $DOE = $fn->getCPDate($doe, 'd-m-Y');


	    $valArr = array();
	    /* Staff Details */
        $valArr['staff_name']          = $staffName;
        $valArr['staff_dob']           = $DOB;
        $valArr['staff_dateExpiry']    = $DOE;
        $valArr['staff_travelDocNo']   = $staffRec['passport_no'];

	    $blkMain   = array();
	    $blkMain[] = $valArr;
	
	    $TBS->MergeBlock('blkMain', $blkMain);
	    
	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintNoDueWord() {
	    $cpCfg = Zend_Registry::get('cpCfg');
	    $ln = Zend_Registry::get('ln');
	    $fn = Zend_Registry::get('fn');
	    $db = Zend_Registry::get('db');
	    $media = Zend_Registry::get('media');
	
	    //-----------------------------------------------------------------//
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');
	
	    $TBS = new clsTinyButStrong;
	    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
	    
	    $staff_id = $fn->getReqParam('staff_id');
	    $staffRec  = $fn->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], 'staff_id', $staff_id);	    
	   
		$staffName             = strtoupper($staffRec['first_name'] . ' ' .$staffRec['last_name']);

	    $template = 'Staff_No_Due.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_NoDue_' . $staffName . '_' . '.docx';
	    $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

		$fin_no = $staffRec['fin_no'];
	    
	    $valArr = array();
	    /* Staff Details */
        $valArr['staff_name'] = $staffName;
        $valArr['fin_no'] = $fin_no;

	    $blkMain   = array();
	    $blkMain[] = $valArr;
	
	    $TBS->MergeBlock('blkMain', $blkMain);
	    
	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintCancelWord() {
	    $cpCfg = Zend_Registry::get('cpCfg');
	    $ln = Zend_Registry::get('ln');
	    $fn = Zend_Registry::get('fn');
	    $db = Zend_Registry::get('db');
	    $media = Zend_Registry::get('media');
	
	    //-----------------------------------------------------------------//
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');
	
	    $TBS = new clsTinyButStrong;
	    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
	    
	    $staff_id = $fn->getReqParam('staff_id');
	    $staffRec  = $fn->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], 'staff_id', $staff_id);	    
	   
		$staffName             = strtoupper($staffRec['first_name'] . ' ' .$staffRec['last_name']);

	    $template = 'Staff_Cancel.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_Cancel_' . $staffName . '_' . '.docx';
	    $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

		$fin_no = $staffRec['fin_no'];
	    
	    $valArr = array();
	    /* Staff Details */
        $valArr['staff_name'] = $staffName;
        $valArr['fin_no'] = $fin_no;

	    $blkMain   = array();
	    $blkMain[] = $valArr;
	
	    $TBS->MergeBlock('blkMain', $blkMain);
	    
	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintResignationWord() {
	    $cpCfg = Zend_Registry::get('cpCfg');
	    $ln = Zend_Registry::get('ln');
	    $fn = Zend_Registry::get('fn');
	    $db = Zend_Registry::get('db');
	    $media = Zend_Registry::get('media');
	
	    //-----------------------------------------------------------------//
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
	    include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');
	
	    $TBS = new clsTinyButStrong;
	    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
	    
	    $staff_id = $fn->getReqParam('staff_id');
	    $staffRec  = $fn->getRecordRowByID($cpCfg['cp.modAccessStaffTable'], 'staff_id', $staff_id);	    
	   
		$staffName             = strtoupper($staffRec['first_name'] . ' ' .$staffRec['last_name']);

	    $template = 'Staff_Resignation.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_Resignation_' . $staffName . '_' . '.docx';
	    $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

		$fin_no = $staffRec['fin_no'];
		$designation = $staffRec['designation'];
	    
	    $valArr = array();
	    /* Staff Details */
        $valArr['staff_name'] = $staffName;
        $valArr['fin_no'] = $fin_no;
        $valArr['designation'] = $designation;

	    $blkMain   = array();
	    $blkMain[] = $valArr;
	
	    $TBS->MergeBlock('blkMain', $blkMain);
	    
	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

}

