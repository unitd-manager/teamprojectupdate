<?
class CPL_Admin_Modules_Payroll_Employee_Model extends CP_Admin_Modules_Payroll_Employee_Model
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $event_id       = $fn->getReqParam('event_id');
        $broadcast_id   = $fn->getReqParam('broadcast_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $project_id     = $fn->getReqParam('project_id');
        $task_id        = $fn->getReqParam('task_id');

        $extraTableNames = "";
        if ($event_id != "") {
            $extraTableNames .= "event_employee eventemployee,";
        }

        if ($broadcast_id != "") {
            $extraTableNames .= "broadcast_employee hist1,";
        }

        if ($opportunity_id != "") {
            $extraTableNames .= "opportunity opp,";
        }

        if ($project_id != "") {
            $extraTableNames .= "project proj,";
        }

        if ($task_id != "") {
            $extraTableNames .= "task task,";
        }

        /*
        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
            $SQL   = "
            SELECT a.*
                   ,b.company_name    AS c_company_name
                   ,b.email           AS c_email
                   ,b.address_flat    AS c_address_flat
                   ,b.address_street  AS c_address_street
                   ,b.address_town    AS c_address_town
                   ,b.address_state   AS c_address_state
                   ,b.address_country AS c_address_country
                   ,b.address_po_code AS c_address_po_code
                   ,b.phone           AS c_phone
                   ,b.fax             AS c_fax
                   ,b.status          AS c_status
                   ,b.website         AS c_website
                   ,b.category        AS c_category
                   ,d.address_flat    AS comp_mul_address_flat
                   ,d.address_street  AS comp_mul_address_street
                   ,d.address_town    AS comp_mul_address_town
                   ,d.address_state   AS comp_mul_address_state
                   ,d.address_country AS comp_mul_address_country
                  ,gc.name AS country_name
                  ,IF(a.employee_work_type = 'Part time', add_hourly_rate, salary) AS employee_amt
            FROM {$extraTableNames}
            employee a
            LEFT JOIN geo_country gc ON (a.address_country = gc.country_code)
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            LEFT JOIN (company_address d) ON ( a.company_address_id = d.company_address_id )
                    ";
        } else {
            $SQL   = "
            SELECT a.*,
            b.company_name    AS c_company_name,
            b.email           AS c_email,
            b.address_flat    AS c_address_flat,
            b.address_street  AS c_address_street,
            b.address_town    AS c_address_town,
            b.address_state   AS c_address_state,
            b.address_country AS c_address_country,
            b.address_po_code AS c_address_po_code,
            b.phone           AS c_phone,
            b.fax             AS c_fax,
            b.status          AS c_status,
            b.website         AS c_website,
            b.category        AS c_category,
            gc.name AS country_name
            FROM {$extraTableNames}
            employee a
            LEFT JOIN geo_country gc ON (a.address_country = gc.country_code)
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            ";
        }
        */
        $SQL   = "
        SELECT DISTINCT a.employee_id AS employee_id_duplicate
              ,a.*
              ,gc.name AS country_name
        FROM {$extraTableNames}
        employee a
        LEFT JOIN geo_country gc ON (a.address_country = gc.country_code)
        LEFT JOIN job_information j ON (a.employee_id = j.employee_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $searchVar->mainTableAlias = 'a';

        $employee_id        = $fn->getReqParam('employee_id');
        $special_search     = $fn->getReqParam('special_search');
        $employee_work_type = $fn->getReqParam('employee_work_type');
        $employee_status    = $fn->getReqParam('employee_status');
        $pass_type          = $fn->getReqParam('pass_type');
        $employee_type      = $fn->getReqParam('employee_type');

        if ($employee_id != "") {
            $searchVar->sqlSearchVar[] = "a.employee_id = '{$employee_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.employee_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.employee_id');

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "a.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(a.subscribe != 1 OR a.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "a.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "a.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "a.published = 0 OR a.published IS NULL OR a.published = ''";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       a.first_name  LIKE '%{$tv['keyword']}%'
                    OR a.last_name   LIKE '%{$tv['keyword']}%'
                    OR a.phone       LIKE '%{$tv['keyword']}%'
                    OR a.nric_no     LIKE '%{$tv['keyword']}%'
                    OR a.fin_no      LIKE '%{$tv['keyword']}%'
                    OR a.passport    LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($employee_type != "") {
                $searchVar->sqlSearchVar[] = "a.employee_type = '{$employee_type}'";
            }

            if ($employee_work_type != "") {
                $searchVar->sqlSearchVar[] = "j.emp_type = '{$employee_work_type}'";
            }

            if ($pass_type != "") {
                $searchVar->sqlSearchVar[] = "a.citizen = '{$pass_type}'";
            }

            if ($employee_status != "") {
                $searchVar->sqlSearchVar[] = "a.status = '{$employee_status}'";
            }else{
                $searchVar->sqlSearchVar[] = "a.status = 'Current'";
            }

            $searchVar->sortOrder = "a.first_name ASC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $is_citizen = $fn->getReqParam('is_citizen');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter full name');
        $validate->validateData('citizen', 'Please choose pass type');
        $citizen = $fn->getPostParam('citizen', '', true);

        if($citizen == 'PR' || $citizen == 'Citizen'){
            $validate->validateData('nric_no' , 'Please enter a valid NRIC');
            $nric_no = $fn->getPostParam('nric_no', '', true);

            if ($nric_no != ''){
                $rec = $fn->getRecordByCondition('employee', "nric_no = '{$nric_no}'");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['nric_no']['name'] = "nric_no";
                    $validate->errorArray['nric_no']['msg']  = "NRIC No already exists. '{$NRIClink}'";

                }
            }
        } else if ($citizen == 'EP' || $citizen == 'SP' || $citizen == 'DP') {
            $validate->validateData('fin_no' , 'Please enter a valid Fin No');
            $fin_no = $fn->getPostParam('fin_no', '', true);

            if ($fin_no != ''){
                $rec = $fn->getRecordByCondition('employee', "fin_no = '{$fin_no}'");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['fin_no']['name'] = "fin_no";
                    $validate->errorArray['fin_no']['msg']  = "Fin/Work Permit No already exists. '{$NRIClink}'";

                }
            }
        } else if ($citizen == 'WP') {
            $validate->validateData('fin_no' , 'Please enter a valid Fin No');
            $validate->validateData('work_permit' , 'Please enter a valid Work Permit No');

            $fin_no = $fn->getPostParam('fin_no', '', true);

            if ($fin_no != ''){
                $rec = $fn->getRecordByCondition('employee', "fin_no = '{$fin_no}'");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['fin_no']['name'] = "fin_no";
                    $validate->errorArray['fin_no']['msg']  = "Fin/Work Permit No already exists. '{$NRIClink}'";

                }
            }
        }

        if ($is_citizen == 1) {
            $validate->validateData('nric_no' , 'Please enter a valid NRIC');
            $nric_no = $fn->getPostParam('nric_no', '', true);

            if ($nric_no != ''){
                $rec = $fn->getRecordByCondition('employee', "nric_no = '{$nric_no}'");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['nric_no']['name'] = "nric_no";
                    $validate->errorArray['nric_no']['msg']  = "NRIC No already exists. '{$NRIClink}'";

                }
            }
        } else {
            //$validate->validateData('fin_no' , 'Please enter a valid Fin/Work Permit No');
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
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $emp_code = $fn->getSettingsValueByKey("nextEmployeeCode");
        $fa = $this->getFields();
        $fa['emp_code'] = $emp_code;
        $fa['address_country'] = 'SG';

        $id = $fn->addRecord($fa);
        //To update employee code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextEmployeeCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('date_of_birth' , 'Please select the date of birth');
        //$validate->validateData('gender' , 'Please select Gender');
        $validate->validateData('nationality' , 'Please select Nationality');

        $validate->validateData('citizen', 'Please choose the citizenship');
        $citizen = $fn->getPostParam('citizen', '', true);
        $employee_id = $fn->getPostParam('employee_id', '', true);

        if($citizen == 'PR'){
            $validate->validateData('spr_year' , 'Please select the SPR year');
        }

        if($citizen == 'PR' || $citizen == 'Citizen'){
            $validate->validateData('nric_no' , 'Please enter a valid NRIC');
            $nric_no = $fn->getPostParam('nric_no', '', true);

            /*
            if ($nric_no != ''){
                $rec = $fn->getRecordByCondition('employee', "nric_no = '{$nric_no}' AND employee_id != {$employee_id}");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['nric_no']['name'] = "nric_no";
                    $validate->errorArray['nric_no']['msg']  = "NRIC No already exists. '{$NRIClink}'";

                }
            }
            */
        } else if ($citizen == 'EP' || $citizen == 'SP') {
            $validate->validateData('fin_no' , 'Please enter a valid Fin No');
            $fin_no = $fn->getPostParam('fin_no', '', true);

            /*
            if ($fin_no != ''){
                $rec = $fn->getRecordByCondition('employee', "fin_no = '{$fin_no}' AND employee_id != {$employee_id}");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['fin_no']['name'] = "fin_no";
                    $validate->errorArray['fin_no']['msg']  = "Fin/Work Permit No already exists. '{$NRIClink}'";

                }
            }
            */
        } else if ($citizen == 'WP') {
            $validate->validateData('fin_no', 'Please enter a valid Fin No');
            $validate->validateData('work_permit', 'Please enter a valid Work Permit No');

            $fin_no = $fn->getPostParam('fin_no', '', true);

            /*
            if ($fin_no != ''){
                $rec = $fn->getRecordByCondition('employee', "fin_no = '{$fin_no}'");
                $expNRIC = array('displayText' => 'Go to this record', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['fin_no']['name'] = "fin_no";
                    $validate->errorArray['fin_no']['msg']  = "Fin/Work Permit No already exists. '{$NRIClink}'";
                }
            }
            */
        }

        $passport    = $fn->getReqParam('passport');
        $expNoEdit   = array('isEditable' => 0);
        $employee_id = $fn->getReqParam('employee_id');

        $passport = $fn->getPostParam('passport', '', true);

        /*
        if ($passport != ''){
            $rec = $fn->getRecordByCondition('employee', "passport = '{$passport}' AND employee_id != {$employee_id}");
            $expPassport = array('displayText' => 'Go to this record', 'target' => '_blank');
            $Passportlink = $fn->getRecordDetailLink('payroll_employee', 'record_id', $rec['employee_id'], $expPassport);

            if (is_array($rec)){
                $validate->errorArray['passport']['name'] = "passport";
                $validate->errorArray['passport']['msg']  = "Passport No. already exists. '{$Passportlink}'";
            }
        }
        */

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        //$fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'emp_code');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'fin_no');
        $fa = $fn->addToFieldsArray($fa, 'nric_no');
        $fa = $fn->addToFieldsArray($fa, 'passport');
        $fa = $fn->addToFieldsArray($fa, 'religion');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'employee_group');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_address_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        $fa = $fn->addToFieldsArray($fa, 'degree1');
        $fa = $fn->addToFieldsArray($fa, 'educational_qualitifcation1');
        $fa = $fn->addToFieldsArray($fa, 'year_of_completion1');
        $fa = $fn->addToFieldsArray($fa, 'degree2');
        $fa = $fn->addToFieldsArray($fa, 'educational_qualitifcation2');
        $fa = $fn->addToFieldsArray($fa, 'year_of_completion2');
        $fa = $fn->addToFieldsArray($fa, 'degree3');
        $fa = $fn->addToFieldsArray($fa, 'educational_qualitifcation3');
        $fa = $fn->addToFieldsArray($fa, 'year_of_completion3');

        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_name');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_phone');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_phone2');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_address');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'foreign_addrs_area');
        $fa = $fn->addToFieldsArray($fa, 'foreign_addrs_street');
        $fa = $fn->addToFieldsArray($fa, 'foreign_addrs_country');
        $fa = $fn->addToFieldsArray($fa, 'foreign_addrs_postal_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'foreign_mobile');
        $fa = $fn->addToFieldsArray($fa, 'foreign_email');

        $fa = $fn->addToFieldsArray($fa, 'salary');
        $fa = $fn->addToFieldsArray($fa, 'day_rate');
        $fa = $fn->addToFieldsArray($fa, 'add_hourly_rate');
        $fa = $fn->addToFieldsArray($fa, 'overtime_rate');
        $fa = $fn->addToFieldsArray($fa, 'employee_work_type');
        $fa = $fn->addToFieldsArray($fa, 'spass_no');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'date_of_expiry');
        $fa = $fn->addToFieldsArray($fa, 'spr_year');
        $fa = $fn->addToFieldsArray($fa, 'citizen');
        $fa = $fn->addToFieldsArray($fa, 'work_permit');
        $fa = $fn->addToFieldsArray($fa, 'fin_no_expiry_date');
        $fa = $fn->addToFieldsArray($fa, 'work_permit_expiry_date');       
        $fa = $fn->addToFieldsArray($fa, 'dormitory_id');       
        $fa = $fn->addToFieldsArray($fa, 'room_no');       
        $fa = $fn->addToFieldsArray($fa, 'employee_type');
        $fa = $fn->addToFieldsArray($fa, 'admin_staff');

        return $fa;
    }

    /**
     *
     */
    function getEmployeeCategorySubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getEmployeeCategoryValidate()){
            return $validate->getErrorMessageXML();
        }

        $employee_id      = $fn->getReqParam('employee_id');
        $category         = $fn->getPostParam('category');

        $fa = array();
        $fa['category']        = $category;
        $fa['employee_id']     = $employee_id;
        $fa['creation_date']   = date("Y-m-d H:i:s");

        $insertSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_category');
        $resultSQL = $db->sql_query($insertSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEmployeeCategoryValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('category', 'Please select category');

        $category = $fn->getPostParam('category', '', true);
        $employee_id = $fn->getPostParam('employee_id');

        if ($category != ''){
            $rec = $fn->getRecordByCondition('employee_category', "category = '{$category}' AND employee_id = {$employee_id}");
            if (is_array($rec)){
                $validate->errorArray['category']['name'] = "category";
                $validate->errorArray['category']['msg']  = "Category already added";
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
    function getDeleteEmployeeCategory(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_category_id = $fn->getReqParam('employee_category_id');

        $SQL ="
               DELETE FROM employee_category
               WHERE employee_category_id = {$employee_category_id}
               ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getExportData1($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');


        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Employee_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Id');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Salutation');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'First Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Last Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Position');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fax');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Subscribed');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Website');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Fax');

        if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Country');

        } else {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Category');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }
        //============================================================================= //
        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['salutation']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['last_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['position']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone_direct']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['fax']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['subscribe']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_website']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_fax']);

            if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_country']);

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_country']);
            } else {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_country']);
            }
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_category']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getEmployeeByCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_id   = $fn->getReqParam('company_id');

        $json  = array();

        if ($company_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT employee_id
              ,employee_name
        FROM employee
        WHERE company_id = '{$company_id}'
        ORDER BY employee_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['employee_id'], "caption" => $row['employee_name']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getMultipleAddress(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $company_id   = $fn->getReqParam('company_id');
        $json  = array();

        if($company_id == ""){
            return json_encode($json);
        }


        $SQL    = "
        SELECT   company_address_id
                 , CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
        FROM     company_address a
        WHERE    company_id = {$company_id}
        ORDER BY company_address_id
        ";

        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['company_address_id'], "caption" => $row['address']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCompanyAddress(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $company_id   = $fn->getReqParam('company_id');

        $SQL = "
        SELECT *
        FROM company
        WHERE company_id = {$company_id}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $json = array("address_street" => $row['address_street'], "address_flat" => $row['address_flat'],
                "address_town" => $row['address_town'], "address_state" => $row['address_state'],
                "address_country" => $row['address_country']
        );

        return json_encode($json);
    }

    /**
     *
     */
    function getEmailValidation(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $email   = $fn->getReqParam('email');
        $employee_id   = $fn->getReqParam('employee_id');
        $email  = trim($email);
        $append = "";

        if($employee_id != ""){
            $append = "AND employee_id != {$employee_id}";
        }

        $SQL = "
        SELECT email
        FROM   employee
        WHERE  email = '{$email}'
               AND email != ''
               {$append}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $check = ($numRows >= 1) ? 1 : 0;

        return $check;

    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'employee_id'          => $phpExcel->getFldObj('employee ID')
             ,'salutation'          => $phpExcel->getFldObj('Salutation')
             ,'first_name'          => $phpExcel->getFldObj('First Name')
             ,'last_name'           => $phpExcel->getFldObj('Last Name')
             ,'email'               => $phpExcel->getFldObj('Email')
             ,'position'            => $phpExcel->getFldObj('Position')
             ,'phone_direct'        => $phpExcel->getFldObj('Phone')
             ,'fax'                 => $phpExcel->getFldObj('Fax')
             ,'mobile'              => $phpExcel->getFldObj('Mobile')
             ,'subscribe'           => $phpExcel->getFldObj('Subscribed')
             ,'c_company_name'      => $phpExcel->getFldObj('Company Name')
             ,'c_website'           => $phpExcel->getFldObj('Company Website')
             ,'c_phone'             => $phpExcel->getFldObj('Company Phone')
             ,'c_fax'               => $phpExcel->getFldObj('Company Fax')

             ,'c_address_flat'      => $phpExcel->getFldObj('Flat')
             ,'c_address_street'    => $phpExcel->getFldObj('Street')
             ,'c_address_town'      => $phpExcel->getFldObj('Town')
             ,'c_address_state'     => $phpExcel->getFldObj('State')
             ,'c_address_country'   => $phpExcel->getFldObj('Country')

             ,'c_category'           => $phpExcel->getFldObj('Category')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        $fa = array(
              'first_name'              => $phpExcel->getImportFldObj('Name')
             ,'salutation'              => $phpExcel->getImportFldObj('Salutation')
             ,'gender'                  => $phpExcel->getImportFldObj('Gender')
             ,'nric_no'                 => $phpExcel->getImportFldObj('Nric No')
             ,'fin_no'                  => $phpExcel->getImportFldObj('Fin No')
             ,'work_permit'             => $phpExcel->getImportFldObj('Wp No')
             ,'basic_pay'               => $phpExcel->getImportFldObj('Basic Pay')
             ,'hourly_pay_rate'         => $phpExcel->getImportFldObj('Hourly Charge')
             ,'fin_no_expiry_date'      => $phpExcel->getImportFldObj('Fin Expiry')
             ,'work_permit_expiry_date' => $phpExcel->getImportFldObj('Wp Expiry')
             ,'date_of_birth'           => $phpExcel->getImportFldObj('Dob')
             ,'nationality'             => $phpExcel->getImportFldObj('Nationality')
             ,'race'                    => $phpExcel->getImportFldObj('Race')
             ,'spr_year'                => $phpExcel->getImportFldObj('Year of PR')
             ,'designation'             => $phpExcel->getImportFldObj('Occupation')
             ,'mobile'                  => $phpExcel->getImportFldObj('Handphone No')
             ,'passport'                => $phpExcel->getImportFldObj('Passport No')
             ,'date_of_expiry'          => $phpExcel->getImportFldObj('Passport Expiry')
             ,'citizen'                 => $phpExcel->getImportFldObj('Pass Type')
             ,'act_join_date'           => $phpExcel->getImportFldObj('Employment Start Date')
             ,'duty_responsibility'     => $phpExcel->getImportFldObj('Duties & Responsibilities')
             ,'work_hour_details'       => $phpExcel->getImportFldObj('Details of working hours')
             ,'working_days'            => $phpExcel->getImportFldObj('No of working days')
        );

        /* Default Start */
        $fa['status']['defaultValue'] = 'Current';
        $fa['employee_work_type']['defaultValue'] = 'Full Time';
        $fa['salutation']['defaultValue'] = 'Mr';
        /* Default End */
        /* Reference Start */
        $fa['basic_pay']['refOnly']  = true;
        $fa['hourly_pay_rate']['refOnly']  = true;
        $fa['designation']['refOnly']  = true;

        $fa['act_join_date']['refOnly']  = true;
        $fa['duty_responsibility']['refOnly']  = true;
        $fa['work_hour_details']['refOnly']  = true;
        $fa['working_days']['refOnly']  = true;
        /* Reference End */
        /****************************************/
        $config = array(
             'module'               => 'payroll_employee'
            //,'matchFieldArr'      => array('first_name')
            ,'fldsArr'              => $fa
            ,'callbackAfterInsert'  => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($employee_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $employeeRec = $fn->getRecordRowById('employee', 'employee_id', $employee_id);

        if ($employee_id) {

            if ($fa['hourly_pay_rate']) {
                $overtime_rate_final = ($fa['hourly_pay_rate']*1.5); // 1.5 times the salary
                $overtime_pay_rate   = round($overtime_rate_final, 2);

                $sun_ph_rate_final = ($fa['hourly_pay_rate']*2); // 1.5 times the salary
                $sun_ph_pay_rate   = round($sun_ph_rate_final, 2);
            } else {
                $overtime_rate = ((12*$fa['basic_pay'])/(52*44)); //(12*monthlygross pay rate)/(52*average no of days employee to work in a week)
                $overtime_rate_final = ($overtime_rate*1.5); // 1.5 times the salary
                $overtime_pay_rate   = round($overtime_rate_final, 2);

                $sun_ph_rate_final = ($overtime_rate*2); // 2 times the salary
                $sun_ph_pay_rate   = round($sun_ph_rate_final, 2);
            }

            /* Inserting Job Information for Employee */
            $fa2 = array();
            $fa2['basic_pay']           = $fa['basic_pay'];
            $fa2['hourly_pay_rate']     = $fa['hourly_pay_rate'];
            $fa2['overtime']            = "1";
            $fa2['overtime_pay_rate']   = $overtime_pay_rate;
            $fa2['sun_ph_pay_rate']     = $sun_ph_pay_rate;
            $fa2['designation']         = $fa['designation'];
            $fa2['status']              = 'Current';
            $fa2['employee_id']         = $employee_id;
            $fa2['act_join_date']       = $fa['act_join_date'];
            $fa2['duty_responsibility'] = $fa['duty_responsibility'];
            $fa2['work_hour_details']   = $fa['work_hour_details'];
            $fa2['working_days']        = $fa['working_days'];

            if ($employeeRec['citizen'] == 'Citizen' || $employeeRec['citizen'] == 'PR') {
                $fa2['cpf_applicable']  = "1";
            }
            
            $fa2['emp_type']             = "Full Time";
            $fa2['payment_type']         = "Monthly";
            $fa2['salary_payment_dates'] = "First week of every month";

            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'job_information');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'job_information');
            $result = $db->sql_query($SQL);
            $job_information_id  = $db->sql_nextid();
        }
    }

    /**
     *
     */
    function getValueByValuelistJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $valuelist_name = $fn->getReqParam('valuelist_name');

        $json  = array();

        if ($valuelist_name == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT v.value
              ,v.value
        FROM valuelist v
        WHERE v.key_text = '{$valuelist_name}'
        ORDER BY v.value
        ";
        $result = $db->sql_query($SQL);
        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['value'], "caption" => $row['value']);
        }

        return json_encode($json);
    }


    /**
     *
     */
    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $valuelist_value = $fn->getPostParam('valuelist_value');
        $valuelist_name  = $fn->getReqParam('valuelist_name');
        $employee_id     = $fn->getReqParam('employee_id');

        if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();

        $fa = array();
        $valuelist_name = "employeeGroup" ;

        $fa['employee_group'] = $valuelist_value;

        $whereCondition = "WHERE employee_id = {$employee_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "employee", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getAddNewValuelistFormValidate($valuelist_name, $valuelist_value) {
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('valuelist_value', 'Please enter value');

        if ($valuelist_value) {
            $sql = "
            SELECT value FROM valuelist
            WHERE key_text = '{$valuelist_name}'
              AND value = '{$valuelist_value}'
            ";
            $result  = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $validate->errorArray['valuelist_value']['name'] = "valuelist_value";
                $validate->errorArray['valuelist_value']['msg']  = "Entered value already exists";
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
     * @param type $contact_id
     * @param type $fa
     */
    function importDataRowCallbackForPayslipOld($employee_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $basic_pay = $fa['basic_pay'];

        /* Job information data calculation - START */
        /* Checking basic pay is same or not for employee */
        $sqlJi = "SELECT job_information_id FROM job_information
                  WHERE employee_id = '{$employee_id}'
                    AND basic_pay = '{$basic_pay}'
                    AND status = 'Current'
                 ";
        $resultJi  = $db->sql_query($sqlJi);
        $numRowsJi = $db->sql_numrows($resultJi);
        if ($numRowsJi == 0) {
            /* Update old basic pay to archive */
            $sqlJiUpdate = "UPDATE job_information SET status = 'Archive'
                                                      ,modification_date = NOW()
                            WHERE employee_id = '{$employee_id}'
                              AND status = 'Current'
                            ";
            $resultJiUpdate = $db->sql_query($sqlJiUpdate);

            /* Create new basic pay for employee */
            $fa2 = array();
            $fa2['basic_pay']       = $basic_pay;
            $fa2['working_days']    = "6.0";
            $fa2['mode_of_payment'] = $fa['mode_of_payment'];
            $fa2['status']          = "Current";
            $fa2['employee_id']     = $employee_id;
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'job_information');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'job_information');
            $result = $db->sql_query($SQL);
        }
        /* Job information data calculation - END */

        /* Payroll Management data calculation - START */
        $payslip_start_fulldate = $fa['payslip_start_date']; //2017-05-01
        $payslip_end_fulldate   = $fa['payslip_end_date']; //2017-05-31

        $payslip_start_month = substr($payslip_start_fulldate, 5, 2);
        $payslip_end_month = substr($payslip_end_fulldate, 5, 2);
        $payslip_start_year = substr($payslip_start_fulldate, 0, 4);
        $payslip_end_year = substr($payslip_end_fulldate, 0, 4);

        if (
            ($payslip_start_fulldate != '') && ($payslip_end_fulldate != '')
         && ($payslip_start_month == $payslip_end_month)
         && ($payslip_start_year == $payslip_end_year)
         && ($basic_pay != '') && ($fa['generated_date'] != '')
        ) {
            /* Checking payroll management data is available or not for employee */
            $sqlPm = "SELECT payroll_management_id FROM payroll_management
                      WHERE employee_id = '{$employee_id}'
                        AND payroll_month = '{$payslip_start_month}'
                        AND payroll_year = '{$payslip_start_year}'
                     ";
            $resultPm  = $db->sql_query($sqlPm);
            $numRowsPm = $db->sql_numrows($resultPm);
            if ($numRowsPm == 0) {
                /* Create new payroll management data for employee */
                $fa3 = array();
                $fa3['employee_id']        = $employee_id;
                $fa3['payroll_month']      = $payslip_start_month;
                $fa3['payroll_year']       = $payslip_start_year;
                $fa3['status']             = "Generated";
                $fa3['basic_pay']          = $basic_pay;
                $fa3['ot_hours']           = $fa['ot_hours'];
                $fa3['overtime_pay_rate']  = $fa['overtime_pay_rate'];
                $fa3['allowance1']         = $fa['allowance1'];
                $fa3['allowance2']         = $fa['allowance2'];
                $fa3['allowance3']         = $fa['allowance3'];
                $fa3['generated_date']     = $fa['generated_date'];
                $fa3['deduction1']         = $fa['deduction1'];
                $fa3['deduction2']         = $fa['deduction2'];
                $fa3['deduction3']         = $fa['deduction3'];
                $fa3['loan_amount']        = $fa['loan_amount'];
                $fa3['payslip_start_date'] = $fa['payslip_start_date'];
                $fa3['payslip_end_date']   = $fa['payslip_end_date'];
                $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'payroll_management');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'payroll_management');
                $result = $db->sql_query($SQL);
            }
        }
        /* Payroll Management data calculation - END */
    }

    /**
     *
     * @param type $contact_id
     * @param type $fa
     */
    function importDataRowCallbackForPayslip($employee_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $EmployeeRec = $fn->getRecordRowById('employee', 'employee_id', $employee_id);

        $payslip_start_fulldate = $fa['payslip_start_date']; //2017-05-01
        $payslip_end_fulldate   = $fa['payslip_end_date']; //2017-05-31

        $final_pay       = $fa['final_pay'];
        $reimbursement   = $fa['reimbursement'];
        $basic_pay       = $fa['basic_pay'];
        $working_days    = $fa['working_days'];
        $no_working_days = $fa['no_working_days'];

        /* Job information data calculation - START */
        /* Calculate OT rate per hour - START */
        $rate_per_hour = ((12*$basic_pay/2288)); // 52*44
        $overtime_per_hour = ($rate_per_hour * 1.5);
        $overtime_per_hour_rounded = round($overtime_per_hour, 2);
        /* Calculate OT rate per hour - END */

        /* Checking basic pay is same or not for employee */
        $sqlJi = "
        SELECT job_information_id FROM job_information
        WHERE employee_id = '{$employee_id}'
          AND basic_pay = '{$basic_pay}'
          AND status = 'Current'
        ";
        $resultJi  = $db->sql_query($sqlJi);
        $numRowsJi = $db->sql_numrows($resultJi);
        if ($numRowsJi == 0) {
            /* Update old basic pay to archive */
            $previous_month_unformatted = date('d-m-Y', strtotime('-1 months', strtotime($payslip_end_fulldate)));
            $previous_month_last_date = date("Y-m-d", strtotime($previous_month_unformatted));

            $sqlJiUpdate = "
            UPDATE job_information SET status = 'Archive'
                                      ,modification_date = NOW()
                                      ,resignation_notice_date = '{$previous_month_last_date}'
                                      ,termination_date = '{$previous_month_last_date}'
            WHERE employee_id = '{$employee_id}'
              AND status = 'Current'
            ";
            $resultJiUpdate = $db->sql_query($sqlJiUpdate);

            /* Create new basic pay for employee */
            $fa2 = array();
            $fa2['basic_pay']         = $basic_pay;
            $fa2['overtime_pay_rate'] = $overtime_per_hour_rounded;
            $fa2['working_days']      = $working_days;
            $fa2['mode_of_payment']   = "cash";
            $fa2['status']            = "Current";
            $fa2['employee_id']       = $employee_id;
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'job_information');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'job_information');
            $result = $db->sql_query($SQL);
        }
        /* Job information data calculation - END */

        /* Payroll Management data calculation - START */
        $payslip_start_month = substr($payslip_start_fulldate, 5, 2);
        $payslip_end_month   = substr($payslip_end_fulldate, 5, 2);
        $payslip_start_year  = substr($payslip_start_fulldate, 0, 4);
        $payslip_end_year    = substr($payslip_end_fulldate, 0, 4);

        if (
            ($payslip_start_fulldate != '') && ($payslip_end_fulldate != '')
         && ($payslip_start_month == $payslip_end_month)
         && ($payslip_start_year == $payslip_end_year)
         && ($basic_pay != '') && ($fa['generated_date'] != '')
        ) {
            /* Checking payroll management data is available or not for employee */
            $sqlPm = "
            SELECT payroll_management_id FROM payroll_management
            WHERE employee_id = '{$employee_id}'
              AND payroll_month = '{$payslip_start_month}'
              AND payroll_year = '{$payslip_start_year}'
              AND status != 'Cancelled'
            ";
            $resultPm  = $db->sql_query($sqlPm);
            $numRowsPm = $db->sql_numrows($resultPm);
            if ($numRowsPm == 0) {
                /* Create new payroll management data for employee */
                $fa3 = array();
                $fa3['employee_id']        = $employee_id;
                $fa3['payroll_month']      = $payslip_start_month;
                $fa3['payroll_year']       = $payslip_start_year;
                $fa3['payslip_start_date'] = $fa['payslip_start_date'];
                $fa3['payslip_end_date']   = $fa['payslip_end_date'];
                $fa3['status']             = "Generated";
                $fa3['generated_date']     = $fa['generated_date'];
                $fa3['basic_pay']          = $basic_pay;
                $fa3['total_basic_pay_for_month'] = $basic_pay;
                $fa3['reimbursement']      = $reimbursement;

                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'][$payslip_start_month];
                $fa3['actual_working_days']   = $working_days_in_month;
                $fa3['working_days_in_month'] = $working_days_in_month;

                /* if the final amount greater than basic pay, manipulate OT and allowance */
                $ot_amount_rounded = 0;
                if ($final_pay > $basic_pay) {
                    $balance_amount = $final_pay - $basic_pay;

                    /* Auto calculation of OT hours */
                    $ot_hours_needed = ($balance_amount/$overtime_per_hour_rounded);
                    if ($ot_hours_needed > 70) {
                        $random_number_for_ot = rand(65,70); // find random number for OT hours
                        $ot_amount = $random_number_for_ot * $overtime_per_hour_rounded; // find total OT amount
                        $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                        $fa3['ot_hours']            = $random_number_for_ot;
                        $fa3['overtime_pay_rate']   = $overtime_per_hour_rounded;
                        $fa3['ot_amount']           = $ot_amount_rounded;
                    } else if (($ot_hours_needed > 0 && $ot_hours_needed < 15)){
                        $random_number_for_ot = rand(0, 14); // find random number for OT hours

                        $ot_amount = $random_number_for_ot * $overtime_per_hour_rounded; // find total OT amount
                        $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                        /* Check whether OT and balance amount are same */
                        if ($balance_amount >= $ot_amount_rounded) {
                            $fa3['ot_hours']          = $random_number_for_ot;
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        } else {
                            $ot_amount = floor($ot_hours_needed) * $overtime_per_hour_rounded; // find total OT amount
                            $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                            $fa3['ot_hours']          = floor($ot_hours_needed); // removes decimal points
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        }
                    } else if (($ot_hours_needed >= 15 && $ot_hours_needed < 30)){
                        $random_number_for_ot = rand(15, 29); // find random number for OT hours

                        $ot_amount = $random_number_for_ot * $overtime_per_hour_rounded; // find total OT amount
                        $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                        /* Check whether OT and balance amount are same */
                        if ($balance_amount >= $ot_amount_rounded) {
                            $fa3['ot_hours']          = $random_number_for_ot;
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        } else {
                            $ot_amount = floor($ot_hours_needed) * $overtime_per_hour_rounded; // find total OT amount
                            $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                            $fa3['ot_hours']          = floor($ot_hours_needed);
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        }
                    } else if (($ot_hours_needed >= 30 && $ot_hours_needed < 45)){
                        $random_number_for_ot = rand(30, 44); // find random number for OT hours

                        $ot_amount = $random_number_for_ot * $overtime_per_hour_rounded; // find total OT amount
                        $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                        /* Check whether OT and balance amount are same */
                        if ($balance_amount >= $ot_amount_rounded) {
                            $fa3['ot_hours']          = $random_number_for_ot;
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        } else {
                            $ot_amount = floor($ot_hours_needed) * $overtime_per_hour_rounded; // find total OT amount
                            $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                            $fa3['ot_hours']          = floor($ot_hours_needed);
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        }
                    } else if (($ot_hours_needed >= 45 && $ot_hours_needed < 60)){
                        $random_number_for_ot = rand(45, 59); // find random number for OT hours

                        $ot_amount = $random_number_for_ot * $overtime_per_hour_rounded; // find total OT amount
                        $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                        /* Check whether OT and balance amount are same */
                        if ($balance_amount >= $ot_amount_rounded) {
                            $fa3['ot_hours']          = $random_number_for_ot;
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        } else {
                            $ot_amount = floor($ot_hours_needed) * $overtime_per_hour_rounded; // find total OT amount
                            $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                            $fa3['ot_hours']          = floor($ot_hours_needed);
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        }
                    } else if (($ot_hours_needed >= 60 && $ot_hours_needed < 70)){
                        $random_number_for_ot = rand(60, 69); // find random number for OT hours

                        $ot_amount = $random_number_for_ot * $overtime_per_hour_rounded; // find total OT amount
                        $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                        /* Check whether OT and balance amount are same */
                        if ($balance_amount >= $ot_amount_rounded) {
                            $fa3['ot_hours']          = $random_number_for_ot;
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        } else {
                            $ot_amount = floor($ot_hours_needed) * $overtime_per_hour_rounded; // find total OT amount
                            $ot_amount_rounded = round($ot_amount, 2); // round OT amount to 2 decimal places

                            $fa3['ot_hours']          = floor($ot_hours_needed);
                            $fa3['overtime_pay_rate'] = $overtime_per_hour_rounded;
                            $fa3['ot_amount']         = $ot_amount_rounded;
                        }
                    }

                    $balance_amount_after_ot = $balance_amount - $ot_amount_rounded;

                    $fa3['allowance1'] = $balance_amount_after_ot;
                } else if ($final_pay < $basic_pay) {
                /* if the final amount lesser than basic pay, give deduction */
                    $balance_amount = $basic_pay - $final_pay;
                    $fa3['deduction1']   = $balance_amount;
                    //$fa3['loan_amount']= $fa['loan_amount'];
                }

                /* If employee is citizen, create CPF records - START */
                if ($EmployeeRec['citizen'] == 'Citizen' || $EmployeeRec['citizen'] == 'PR') {
                    /* Find difference of age - START */
                    $dob_for_age = $dateUtil->formatDate($EmployeeRec['date_of_birth'], 'DD-MM-YYYY');
                    $dob_for_age = "01-" . substr($dob_for_age, 3);

                    $payslip_generated_year = substr($fa['generated_date'], 0, 4);
                    $payslip_generated_month = substr($fa['generated_date'], 5, 2);
    
                    $payslipdate_for_age = "01-" . $payslip_generated_month . '-' . $payslip_generated_year;
                    $modObj = getCPModuleObj('payroll_payrollManagement');
                    $age = $modObj->model->getFindage($dob_for_age, $payslipdate_for_age);
                    /* Find difference of age - END */

                    $cpf = 0;
                    $cpfE = 0;
                    $total_contribution_amount = 0;
                    if($EmployeeRec['citizen'] == 'PR' || $EmployeeRec['citizen'] == 'Citizen'){
                        $sprCondition = '';
                        if($EmployeeRec['spr_year'] != '' && $EmployeeRec['citizen'] == 'PR'){
                            if ($EmployeeRec['spr_year'] > 2) {
                                $spr_year = "3";
                            } else {
                                $spr_year = $EmployeeRec['spr_year'];
                            }
                            $sprCondition = "AND spr_year = {$spr_year}";
                        } else {
                            $sprCondition = "AND spr_year = 3";
                        }

                        $gross_pay = $final_pay; // for CPF calculation
                        $SQLPercentageCPF = "
                        SELECT by_employer
                              ,by_employee
                              ,cap_amount_employer
                              ,cap_amount_employee
                        FROM cpf_calculator
                        WHERE {$age} BETWEEN from_age AND to_age
                          AND {$gross_pay} BETWEEN from_salary AND to_salary
                          AND year = {$payslip_generated_year}
                          {$sprCondition}
                        ";
                        $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                        $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                        if ($gross_pay >= 501 && $gross_pay <= 749) {
                            if ($age >= 0 && $age <= 55) {
                                $cpf_employee = (0.6*($gross_pay - 500)); // 0.6(Total wage - 500)
                            } else if ($age >= 56 && $age <= 60) {
                                $cpf_employee = (0.39*($gross_pay - 500)); // 0.39(Total wage - 500)
                            } else if ($age >= 61 && $age <= 65) {
                                $cpf_employee = (0.225*($gross_pay - 500)); // 0.225(Total wage - 500)
                            } else if ($age >= 66 && $age <= 200) {
                                $cpf_employee = (0.15*($gross_pay - 500)); // 0.225(Total wage - 500)
                            }

                            $cpf_employer = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                            $total_contribution = $cpf_employer + $cpf_employee;
                            $total_contribution_amount = round($total_contribution); // Total CPF contribution

                            // CPF Employee contribution
                            list($int, $dec) = explode('.', $cpf_employee);
                            $cpfE = $int;

                            // CPF Employer contribution
                            $cpf = $total_contribution_amount - $cpfE;
                        } else {

                            /* CPF Total Calculation */
                            $total_cpf_percent = $rowPercentageCPF['by_employee'] + $rowPercentageCPF['by_employer'];
                            $total_contribution = (($gross_pay) * $total_cpf_percent)/100;
                            $total_contribution_amount = round($total_contribution); // Total CPF contribution

                            /*
                            $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                            $cpf = round($cpf, 2);
                            */

                            // CPF Employee contribution
                            $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                            list($int, $dec) = explode('.', $cpfE);
                            $cpfE = $int;

                            // CPF Employer contribution
                            $cpf = $total_contribution_amount - $cpfE;
                            if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                                $rowPercentageCPF['cap_amount_employer'] != 0){
                                $cpf = $rowPercentageCPF['cap_amount_employer'];
                            }

                            if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                                $rowPercentageCPF['cap_amount_employee'] != 0){
                                $cpfE = $rowPercentageCPF['cap_amount_employee'];
                            }
                        }
                    }

                    $fa3['total_cpf_contribution'] = $total_contribution_amount;
                    $fa3['cpf_employee']      = $cpfE;
                    $fa3['cpf_employer']      = $cpf;
                }
                /* If employee is citizen, create CPF records - END */
                
                $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'payroll_management');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'payroll_management');
                $result = $db->sql_query($SQL);
            }
        }
        /* Payroll Management data calculation - END */
    }

    /**
     *
     */
    function getPayrollEmployeePayrollTrainingLinkSQL($id) {

        $SQL = "
        SELECT ts.training_staff_id
              ,t.title
              ,DATE_FORMAT(ts.from_date, '%d-%m-%Y') AS training_from_date
              ,DATE_FORMAT(ts.to_date, '%d-%m-%Y') AS training_to_date
        FROM training_staff ts
        LEFT JOIN training t ON (ts.training_id = t.training_id)
        WHERE ts.staff_id = '{$id}'
        ORDER BY ts.to_date DESC
        ";

        return $SQL;
    }

    /**
     *
     */
    function getPayrollEmployeePayrollJobInformationLinkSQL($id) {

        $SQL = "
        SELECT j.job_information_id
              ,j.basic_pay
              ,DATE_FORMAT(j.act_join_date, '%d-%m-%Y') AS start_date
              ,DATE_FORMAT(j.termination_date, '%d-%m-%Y') AS end_date
        FROM job_information j
        WHERE j.employee_id = '{$id}'
        ORDER BY j.job_information_id DESC
        ";

        return $SQL;
    }
}
