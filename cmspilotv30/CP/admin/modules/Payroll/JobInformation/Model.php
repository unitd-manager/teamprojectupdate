<?
class CP_Admin_Modules_Payroll_JobInformation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT j.*
        ,e.emp_code
        ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        ,e.first_name
        ,e.last_name
        ,e.phone
        ,e.email
        ,e.salary
        ,e.nric_no
        ,e.position
        ,e.date_of_expiry
        ,e.spass_no
        ,e.fin_no
        ,e.employee_work_type
        ,e.date_of_birth
        ,e.citizen
        FROM `job_information` j
        LEFT JOIN (employee e) ON (e.employee_id = j.employee_id)
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
        $searchVar->mainTableAlias = 'j';

        $employee_id        = $fn->getReqParam('employee_id');
        $job_information_id = $fn->getReqParam('job_information_id');
        $employee_status    = $fn->getReqParam('employee_status');
        $status             = $fn->getReqParam('status');

        if ($job_information_id != "") {
            $searchVar->sqlSearchVar[] = "j.job_information_id = '{$job_information_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "j.job_information_id = '{$tv['record_id']}'";
        } else {
            if ($employee_id != "") {
                $searchVar->sqlSearchVar[] = "j.employee_id = '{$employee_id}'";
            }

            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');

          /*  if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "j.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(j.flag != 1 OR j.flag IS null)";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "j.status = '{$status}'";
            } else {
                $searchVar->sqlSearchVar[] = "j.status = 'Current'";
            }

            if ($employee_status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$employee_status}'";
            }else{
                $searchVar->sqlSearchVar[] = "e.status = 'Current'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       e.first_name  LIKE '%{$tv['keyword']}%'
                    OR e.last_name   LIKE '%{$tv['keyword']}%'
                    OR e.phone       LIKE '%{$tv['keyword']}%'
                    OR e.nric_no     LIKE '%{$tv['keyword']}%'
                    OR e.fin_no      LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "e.first_name ASC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('employee_name', 'Please Select the employee');
        $validate->validateData('employee_id', 'Please Select the employee');
        $employee_id = $fn->getPostParam('employee_id');

        if($employee_id != ''){
            $employeeExist = $fn->getRecordCount('job_information', "employee_id = {$employee_id} AND status = 'Current'");
            if($employeeExist > 0){
                $validate->errorArray['employee_name']['name'] = "employee_name";
                $validate->errorArray['employee_name']['msg']  = "Employee already exists.";
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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Current';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $job_information_id = $fn->getReqParam('job_information_id');
        $jobInfoRec  = $fn->getRecordRowByID('job_information', 'job_information_id', $job_information_id);
        $employeeRec = $fn->getRecordRowByID('employee', 'employee_id', $jobInfoRec['employee_id']);

        $validate->resetErrorArray();
        $validate->validateData('basic_pay', 'Please enter basic pay');
        $validate->validateData('working_days', 'Please select working calendar');
        $validate->validateData('join_date', 'Please select first joined date');
        
        if ($employeeRec['citizen'] == 'Citizen' || $employeeRec['citizen'] == 'PR') {
            $validate->validateData('govt_donation', 'Please choose Govt Donation');
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $basic_pay                           = $fn->getReqParam('basic_pay');
        $job_information_id                  = $fn->getReqParam('job_information_id');
        $paid_annual_leave_per_year          = $fn->getPostParam('paid_annual_leave_per_year');
        $paid_outpatient_sick_leave_per_year = $fn->getPostParam('paid_outpatient_sick_leave_per_year');
        $working_days                        = $fn->getPostParam('working_days');

        $jobInfoRec = $fn->getRecordRowByID('job_information', 'job_information_id', $job_information_id);

        $fa = $this->getFields();
        if ($paid_annual_leave_per_year == '' || $paid_outpatient_sick_leave_per_year == '') {
        $arr = $this->getCalculateAnnualSickLeaves($jobInfoRec['employee_id'], $jobInfoRec['job_information_id']);
            if ($paid_annual_leave_per_year == '') {
                $fa['paid_annual_leave_per_year'] = $arr[0];
            }
            if ($paid_outpatient_sick_leave_per_year == '') {
                $fa['paid_outpatient_sick_leave_per_year'] = $arr[1];
            }
        }

        if ($working_days == 5) {
            $average_no_of_hours = 40;
        } else if ($working_days == 5.5) {
            $average_no_of_hours = 44;
        } else {
            $average_no_of_hours = 48;
        }

        $overtime_rate = ((12*$basic_pay)/(52*$average_no_of_hours)); //(12*monthlygross pay rate)/(52*average no of days employee to work in a week)
        $overtime_rate_final = ($overtime_rate*1.5); // 1.5 times the salary

        $fa['overtime_pay_rate'] = round($overtime_rate_final, 2);

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
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

        $fa = $fn->addToFieldsArray($fa, 'join_date');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'designation');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'probationary');
        $fa = $fn->addToFieldsArray($fa, 'emp_type');
        $fa = $fn->addToFieldsArray($fa, 'act_join_date');
        $fa = $fn->addToFieldsArray($fa, 'termination_date');
        $fa = $fn->addToFieldsArray($fa, 'termination_reason');
        $fa = $fn->addToFieldsArray($fa, 'payment_type');
        $fa = $fn->addToFieldsArray($fa, 'working_days');
        $fa = $fn->addToFieldsArray($fa, 'basic_pay');
        $fa = $fn->addToFieldsArray($fa, 'overtime');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_rate');
        $fa = $fn->addToFieldsArray($fa, 'cpf_applicable');
        $fa = $fn->addToFieldsArray($fa, 'cpf_account_no');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_id');
        $fa = $fn->addToFieldsArray($fa, 'pay_cdac');
        $fa = $fn->addToFieldsArray($fa, 'pay_sinda');
        $fa = $fn->addToFieldsArray($fa, 'pay_mbmf');
        $fa = $fn->addToFieldsArray($fa, 'pay_eucf');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_payment');
        $fa = $fn->addToFieldsArray($fa, 'account_no');
        $fa = $fn->addToFieldsArray($fa, 'bank_name');
        $fa = $fn->addToFieldsArray($fa, 'bank_code');
        $fa = $fn->addToFieldsArray($fa, 'branch_code');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_amount');
        $fa = $fn->addToFieldsArray($fa, 'allowance1');
        $fa = $fn->addToFieldsArray($fa, 'allowance2');
        $fa = $fn->addToFieldsArray($fa, 'allowance3');
        $fa = $fn->addToFieldsArray($fa, 'allowance4');
        $fa = $fn->addToFieldsArray($fa, 'allowance5');
        $fa = $fn->addToFieldsArray($fa, 'deduction1');
        $fa = $fn->addToFieldsArray($fa, 'deduction2');
        $fa = $fn->addToFieldsArray($fa, 'deduction3');
        $fa = $fn->addToFieldsArray($fa, 'govt_donation');

        $fa = $fn->addToFieldsArray($fa, 'departure_date');
        $fa = $fn->addToFieldsArray($fa, 'resignation_notice_date');

        $fa = $fn->addToFieldsArray($fa, 'duty_responsibility');
        $fa = $fn->addToFieldsArray($fa, 'duration_of_employment');
        $fa = $fn->addToFieldsArray($fa, 'place_of_work');
        $fa = $fn->addToFieldsArray($fa, 'work_hour_details');
        $fa = $fn->addToFieldsArray($fa, 'rest_day_per_week');
        $fa = $fn->addToFieldsArray($fa, 'salary_payment_dates');
        $fa = $fn->addToFieldsArray($fa, 'overtime_payment_dates');
        $fa = $fn->addToFieldsArray($fa, 'length_of_probation');
        $fa = $fn->addToFieldsArray($fa, 'probation_start_date');
        $fa = $fn->addToFieldsArray($fa, 'probation_end_date');
        $fa = $fn->addToFieldsArray($fa, 'notice_period_for_termination');

        $fa = $fn->addToFieldsArray($fa, 'paid_annual_leave_per_year');
        $fa = $fn->addToFieldsArray($fa, 'paid_outpatient_sick_leave_per_year');
        $fa = $fn->addToFieldsArray($fa, 'paid_hospitalisation_leave_per_year');
        $fa = $fn->addToFieldsArray($fa, 'other_type_of_leave');
        $fa = $fn->addToFieldsArray($fa, 'paid_medical_examination_fee');
        $fa = $fn->addToFieldsArray($fa, 'other_medical_benefits');

        return $fa;
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

        $file_name = "Contact_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Contact Id');
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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_id']);
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
    function getContactByCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_id   = $fn->getReqParam('company_id');

        $json  = array();

        if ($company_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name) AS contact_name
        FROM contact
        WHERE company_id = '{$company_id}'
        ORDER BY contact_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['contact_id'], "caption" => $row['contact_name']);
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
        $contact_id   = $fn->getReqParam('contact_id');
        $email  = trim($email);
        $append = "";

        if($contact_id != ""){
            $append = "AND contact_id != {$contact_id}";
        }

        $SQL = "
        SELECT email
        FROM   contact
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
              'contact_id'          => $phpExcel->getFldObj('Contact ID')
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
    function getSearchEmployeeDetails(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $employeeDetail = $extractor[0];

        $SQL = "
        SELECT  CONCAT_WS(' ', first_name, last_name) AS value
               ,CONCAT_WS(' ', first_name, last_name) AS label
               ,employee_id AS id
               ,CONCAT_WS(' ', first_name, last_name) AS Employee_Name
        FROM employee
        WHERE (employee_id LIKE '%{$employeeDetail}%'
        OR first_name LIKE '%{$employeeDetail}%'
        OR last_name LIKE '%{$employeeDetail}%'
        OR nric_no LIKE '%{$employeeDetail}%')
        AND status = 'Current'
        ORDER BY Employee_Name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getDuplicateJobInformation() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $job_information_id = $fn->getReqParam('job_information_id');
        $jobInfoRec = $fn->getRecordRowByID('job_information', 'job_information_id', $job_information_id);

        $current_date_time = date('Y-m-d H:i:s');
        $user_name = $fn->getSessionParam('userName');
        $current_date = date('Y-m-d');

        $arr = $this->getCalculateAnnualSickLeaves($jobInfoRec['employee_id'], $jobInfoRec['job_information_id']);
        $paid_annual_leave_per_year = $arr[0];
        $paid_outpatient_sick_leave_per_year = $arr[1];

        $current_date = date('Y-m-d');
        $fa = array();
        $fa['join_date']                           = $jobInfoRec['join_date'];
        $fa['employee_id']                         = $jobInfoRec['employee_id'];
        $fa['designation']                         = $jobInfoRec['designation'];
        $fa['department']                          = $jobInfoRec['department'];
        $fa['probationary']                        = $jobInfoRec['probationary'];
        $fa['emp_type']                            = $jobInfoRec['emp_type'];
        $fa['act_join_date']                       = $current_date;
        $fa['termination_date']                    = $jobInfoRec['termination_date'];
        $fa['termination_reason']                  = $jobInfoRec['termination_reason'];
        $fa['payment_type']                        = $jobInfoRec['payment_type'];
        $fa['working_days']                        = $jobInfoRec['working_days'];
        $fa['basic_pay']                           = $jobInfoRec['basic_pay'];
        $fa['overtime']                            = $jobInfoRec['overtime'];
        $fa['overtime_pay_rate']                   = $jobInfoRec['overtime_pay_rate'];
        $fa['cpf_applicable']                      = $jobInfoRec['cpf_applicable'];
        $fa['cpf_account_no']                      = $jobInfoRec['cpf_account_no'];
        $fa['income_tax_id']                       = $jobInfoRec['income_tax_id'];
        $fa['pay_cdac']                            = $jobInfoRec['pay_cdac'];
        $fa['pay_sinda']                           = $jobInfoRec['pay_sinda'];
        $fa['pay_mbmf']                            = $jobInfoRec['pay_mbmf'];
        $fa['pay_eucf']                            = $jobInfoRec['pay_eucf'];
        $fa['mode_of_payment']                     = $jobInfoRec['mode_of_payment'];
        $fa['account_no']                          = $jobInfoRec['account_no'];
        $fa['bank_name']                           = $jobInfoRec['bank_name'];
        $fa['bank_code']                           = $jobInfoRec['bank_code'];
        $fa['branch_code']                         = $jobInfoRec['branch_code'];
        $fa['status']                              = 'Current';
        $fa['income_tax_amount']                   = $jobInfoRec['income_tax_amount'];
        $fa['allowance1']                          = $jobInfoRec['allowance1'];
        $fa['allowance2']                          = $jobInfoRec['allowance2'];
        $fa['allowance3']                          = $jobInfoRec['allowance3'];
        $fa['allowance4']                          = $jobInfoRec['allowance4'];
        $fa['allowance5']                          = $jobInfoRec['allowance5'];
        $fa['deduction1']                          = $jobInfoRec['deduction1'];
        $fa['deduction2']                          = $jobInfoRec['deduction2'];
        $fa['deduction3']                          = $jobInfoRec['deduction3'];
        $fa['govt_donation']                       = $jobInfoRec['govt_donation'];
        $fa['duty_responsibility']                 = $jobInfoRec['duty_responsibility'];
        $fa['duration_of_employment']              = $jobInfoRec['duration_of_employment'];
        $fa['place_of_work']                       = $jobInfoRec['place_of_work'];
        $fa['work_hour_details']                   = $jobInfoRec['work_hour_details'];
        $fa['rest_day_per_week']                   = $jobInfoRec['rest_day_per_week'];
        $fa['salary_payment_dates']                = $jobInfoRec['salary_payment_dates'];
        $fa['overtime_payment_dates']              = $jobInfoRec['overtime_payment_dates'];
        $fa['length_of_probation']                 = $jobInfoRec['length_of_probation'];
        $fa['probation_start_date']                = $jobInfoRec['probation_start_date'];
        $fa['probation_end_date']                  = $jobInfoRec['probation_end_date'];
        $fa['notice_period_for_termination']       = $jobInfoRec['notice_period_for_termination'];
        $fa['paid_annual_leave_per_year']          = $paid_annual_leave_per_year;
        $fa['paid_outpatient_sick_leave_per_year'] = $paid_outpatient_sick_leave_per_year;
        $fa['paid_hospitalisation_leave_per_year'] = $jobInfoRec['paid_hospitalisation_leave_per_year'];
        $fa['other_type_of_leave']                 = $jobInfoRec['other_type_of_leave'];
        $fa['paid_medical_examination_fee']        = $jobInfoRec['paid_medical_examination_fee'];
        $fa['other_medical_benefits']              = $jobInfoRec['other_medical_benefits'];

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'job_information');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'job_information');
        $db->sql_query($SQL);
        $new_job_information_id = $db->sql_nextid();

        $previous_date = strftime("%Y-%m-%d", strtotime("$current_date -1 day"));
        $sqlUpdate = "
        UPDATE job_information
        SET status = 'Archive'
           ,modification_date = '{$current_date_time}'
           ,modified_by = '{$user_name}'
           ,termination_date = '{$previous_date}'
        WHERE job_information_id = {$job_information_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);

        return $new_job_information_id;

        /*
        $url = "index.php?_topRm=payroll&module=payroll_jobInformation&record_id={$new_job_information_id}&_action=edit";

        return $cpUtil->redirect($url);
        */
    }

    /**
     *
     */
    function getCalculateAnnualSickLeavesWithRecords($employee_id, $job_information_id) {
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');
        /* Find number of years worked by employee */
        $sqlEmp = "SELECT job_information_id, act_join_date, termination_date FROM job_information
        WHERE employee_id = {$employee_id} AND status != 'Cancel'
        ORDER BY act_join_date ASC";
        $resultEmp  = $db->sql_query($sqlEmp);
        $numRowsEmp = $db->sql_numrows($resultEmp);
        $counter    = 1;

        while ($rowEmp = $db->sql_fetchrow($resultEmp)) {
            if ($numRowsEmp == 1 && $rowEmp['termination_date'] == '') {
                $exact_start_date = $rowEmp['act_join_date'];
            } else {
                if ($rowEmp['termination_date']) {

                    if ($counter == 1) {
                        $exact_start_date = $rowEmp['act_join_date'];
                    }
                    $change_start_date = 1;
                    $termination_date = $rowEmp['termination_date'];

                    // Finding Next Job Information data
                    $sqlEmp2 = "
                    SELECT job_information_id, act_join_date, termination_date FROM job_information
                    WHERE employee_id = {$employee_id}
                      AND status != 'Cancel'
                      AND job_information_id > {$rowEmp['job_information_id']}
                    ORDER BY act_join_date ASC
                    LIMIT 0,1";
                    $resultEmp2  = $db->sql_query($sqlEmp2);
                    $numRowsEmp2 = $db->sql_numrows($resultEmp2);
                    $rowEmp2     = $db->sql_fetchrow($resultEmp2);
                    if ($numRowsEmp2) {
                        $next_join_date        = $rowEmp2['act_join_date'];
                        $next_join_date_calc   = strftime("%Y-%m-%d", strtotime("$termination_date +1 day"));
                        $termination_date_calc = strftime("%Y-%m-%d", strtotime("$next_join_date -1 day"));
                    }

                    $exact_start_date_temp = $rowEmp['act_join_date'];
                    if ($termination_date != $termination_date_calc && $next_join_date != $next_join_date_calc) {
                        $change_start_date = 1;
                        $exact_start_date_temp = $rowEmp2['act_join_date'];
                    } else {
                        $change_start_date = 0;
                        $exact_start_date_temp = $rowEmp2['act_join_date'];
                    }

                    if ($change_start_date == 1) {
                        $exact_start_date = $exact_start_date_temp;
                        $change_start_date = 0;
                    }
                }
                $counter++;
            }
        }

        $exact_start_date  = date_create($exact_start_date);
        $current_date      = date_create($current_date);
        $diff              = date_diff($exact_start_date, $current_date);
        $output_diff_year  = $diff->format('%y');
        $output_diff_month = $diff->format('%m');

        if ($output_diff_year == 0 && $output_diff_month < 3) {
            $paid_annual_leave_per_year = 0;
            $paid_outpatient_sick_leave_per_year = 0;
        } else if ($output_diff_year == 0 && $output_diff_month < 4) {
            $paid_annual_leave_per_year = 0;
            $paid_outpatient_sick_leave_per_year = 5;
        } else if ($output_diff_year == 0 && $output_diff_month < 5) {
            $paid_annual_leave_per_year = 0;
            $paid_outpatient_sick_leave_per_year = 8;
        } else if ($output_diff_year == 0 && $output_diff_month < 6) {
            $paid_annual_leave_per_year = 0;
            $paid_outpatient_sick_leave_per_year = 11;
        } else if ($output_diff_year == 0 && $output_diff_month < 12) {
            $paid_annual_leave_per_year = 0;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 1) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 2) {
            $paid_annual_leave_per_year = 8;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 3) {
            $paid_annual_leave_per_year = 9;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 4) {
            $paid_annual_leave_per_year = 10;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 5) {
            $paid_annual_leave_per_year = 11;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 6) {
            $paid_annual_leave_per_year = 12;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 7) {
            $paid_annual_leave_per_year = 13;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year > 7) {
            $paid_annual_leave_per_year = 14;
            $paid_outpatient_sick_leave_per_year = 14;
        }

        $arr = array();
        $arr[0] = $paid_annual_leave_per_year;
        $arr[1] = $paid_outpatient_sick_leave_per_year;

        return $arr;
    }

    /**
     *
     */
    function getCalculateAnnualSickLeaves($employee_id, $job_information_id) {
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');
        /* Find number of years worked by employee */
        $sqlEmp = "SELECT join_date FROM job_information
        WHERE employee_id = {$employee_id} AND job_information_id = {$job_information_id}";
        $resultEmp  = $db->sql_query($sqlEmp);
        $rowEmp = $db->sql_fetchrow($resultEmp);

        $exact_start_date  = date_create($rowEmp['join_date']);
        $current_date      = date_create($current_date);
        $diff              = date_diff($exact_start_date, $current_date);
        $output_diff_year  = $diff->format('%y');
        $output_diff_month = $diff->format('%m');

        if ($output_diff_year == 0 && $output_diff_month < 3) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 0 && $output_diff_month < 4) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 0 && $output_diff_month < 5) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 0 && $output_diff_month < 6) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 0 && $output_diff_month < 12) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 1) {
            $paid_annual_leave_per_year = 7;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 2) {
            $paid_annual_leave_per_year = 8;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 3) {
            $paid_annual_leave_per_year = 9;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 4) {
            $paid_annual_leave_per_year = 10;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 5) {
            $paid_annual_leave_per_year = 11;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 6) {
            $paid_annual_leave_per_year = 12;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year == 7) {
            $paid_annual_leave_per_year = 13;
            $paid_outpatient_sick_leave_per_year = 14;
        } else if ($output_diff_year > 7) {
            $paid_annual_leave_per_year = 14;
            $paid_outpatient_sick_leave_per_year = 14;
        }

        $arr = array();
        $arr[0] = $paid_annual_leave_per_year;
        $arr[1] = $paid_outpatient_sick_leave_per_year;

        return $arr;
    }

    /**
     *
     */
    function getKETPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot2.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // set margins
        /*
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        */
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0); //set footer margin to 0

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $job_information_id = $fn->getReqParam('job_information_id');
        $payroll_management_id = $fn->getReqParam('payroll_management_id');

        $SQL = "
        SELECT j.*
              ,CONCAT_WS(' ', e.first_name) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.fin_no
              ,e.nric_no
              ,e.date_of_birth  AS dob
              ,e.employee_id
              ,e.citizen
              ,e.spr_year
        FROM job_information j
        LEFT JOIN (employee e) ON (e.employee_id = j.employee_id)
        WHERE j.job_information_id = '{$job_information_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
        $pdf->SetFont('Arial','B',10);
        $today = date("d/m/Y");
        $act_join_date = $Row['act_join_date'];
        $act_join_date_formatted = $dateUtil->formatDate($act_join_date, 'DD/MM/YYYY');

        if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
            $finNo = $Row['nric_no'];
        }else {
            $finNo = $Row['fin_no'] ;
        }

        $tbl1 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td width="60%" style="color:#404040; font-size:25px;"><b>Key Employment Terms</b>
                <span style="color: #808080; font-size:10px;"><br/>All fields are mandatory, unless they are not applicable</span></td>
                <td width="39%">
                    <table cellpadding="2">
                        <tr>
                            <td style="color: #404040; align: right; font-size:14px; font-weight:bold; border-style: dotted dotted dotted dotted;">Issued on: '.$act_join_date_formatted .'
                            <br/><span style="color: #808080; font-size:10px;">All information accurate as of issuance date</span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        ';

        if($Row['emp_type'] == 'Full Time'){
            $full_time_emp = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $full_time_emp = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        if($Row['emp_type'] == 'Part Time'){
            $part_time_emp = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $part_time_emp = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        $act_join_date = $dateUtil->formatDate($Row['act_join_date'], 'DD/MM/YYYY');

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px; background-color: #E6E7E8;">
            <tr style="background-color:#414042; color:#FFFFFF; font-size:14px;">
                <td style="font-weight:bold;" colspan="2">Section A | Details of Employment</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Company Name<br/>'.$cpCfg['cp.companyName'].'</td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Job Title, Main Duties and Responsibilities<br/>'.strtoupper($Row['duty_responsibility']).'</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Employee Name<br/>'.$Row['employee_name'].'</td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">'.$full_time_emp.' Full-Time Employment<br>'.$part_time_emp.' Part-Time Employment</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Employee NRIC/FIN<br/>'.$finNo.'</td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Duration of Employment<br/>'.strtoupper($Row['duration_of_employment']).'</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Employment Start Date<br/>'.$act_join_date.'</td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Place of Work<br/>'.strtoupper($Row['place_of_work']).'</td>
            </tr>
        </table>
        ';

        if ($Row['working_days'] == '5.0') {
            $working_days = 5;
        } else if ($Row['working_days'] == '6.0') {
            $working_days = 6;
        } else {
            $working_days = $Row['working_days'];
        }

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px; background-color: #E6E7E8;">
            <tr style="background-color:#414042; color:#FFFFFF; font-size:14px;">
                <td style="font-weight:bold;" colspan="2">Section B | Working Hours and Rest Days</td>
            </tr>
            <tr>
                <td width="50%" rowspan="2" style="border-top: 3px solid #fff;">Details of Working Hours<br/>'.strtoupper(nl2br($Row['work_hour_details'])).'</td>
                <td width="50%" style="border-left: 2px solid #fff; border-bottom: 2px solid #fff; border-top: 3px solid #fff;">Number of Working Days Per Week : '.$working_days.' DAYS</td>
            </tr>
            <tr>
                <td width="50%" style="border-left: 2px solid #fff;">Rest Day Per Week : '.strtoupper($Row['rest_day_per_week']).'</td>
            </tr>
        </table>
        ';

        $total_allowance = $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'] + $Row['allowance4'] + $Row['allowance5'];
        $total_allowance = number_format($total_allowance, 2);
        $total_deduction = $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
        $total_deduction = number_format($total_deduction, 2);        

        if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
            $images = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $images = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        if($Row['payment_type'] == 'Monthly'){
            $Monthly = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $Monthly = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        if($Row['payment_type'] == 'Weekly'){
            $Weekly = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $Weekly = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        if($Row['payment_type'] == 'Fortnightly'){
            $Fortnightly = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $Fortnightly = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        if($Row['payment_type'] == 'Daily'){
            $Daily = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $Daily = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        if($Row['payment_type'] == 'Hourly'){
            $Hourly = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $Hourly = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        $Uncheckimage = '<img src="images/images.png" width="10px" height="10px"/>';

        $overtime_rate = '';
        if ($Row['overtime'] == 1) {
            $overtime_rate = 'S$ ' . number_format($Row['overtime_pay_rate'],2);
        }

        $tbl4 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px; background-color: #E6E7E8;">
            <tr style="background-color:#414042; color:#FFFFFF; font-size:14px;">
                <td style="font-weight:bold;" colspan="2">Section C | Salary</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff; ">Salary Period<br/>
                    <span style="font-size:11px;">'.$Hourly.' Hourly '.$Daily.' Daily '.$Weekly.' Weekly '.$Fortnightly.' Fortnightly '.$Monthly.' Monthly</span>
                </td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Date(s) of Salary Payment : &nbsp;&nbsp;&nbsp;&nbsp;' . strtoupper($Row['salary_payment_dates']) . '<br/>Date(s) of Overtime Payment : ' . strtoupper($Row['overtime_payment_dates']) . '</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Overtime Payment Period<br/>
                    <span style="font-size:11px;">'.$Uncheckimage.' Hourly '.$Uncheckimage.' Daily '.$Uncheckimage.' Weekly '.$Uncheckimage.' Fortnightly '.$Uncheckimage.' Monthly</span>
                </td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Basic Salary <span style="color: #808080; font-size:10px;">(Per Period)</span> : S$ '.number_format($Row['basic_pay'],2).'<br/>
                Overtime Rate of Pay : &nbsp;&nbsp;'. $overtime_rate .'</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Fixed Allowances Per Salary Period<br/>
                    <table border="0" cellpadding="4" style="background-color: #bfbfbf;">
                        <thead>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">Item</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">Allowance (S$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.allowance1Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['allowance1'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.allowance2Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['allowance2'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.allowance3Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['allowance3'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.allowance4Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['allowance4'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.allowance5Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['allowance5'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-right: 1px solid #fff;">Total Fixed Allowances</th>
                                <th width="49%">'.number_format($total_allowance,2).'</th>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Fixed Deductions Per Salary Period<br/>
                    <table border="0" cellpadding="4" style="background-color: #bfbfbf;">
                        <thead>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">Item</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">Deduction (S$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.deduction1Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['deduction1'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.deduction2Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['deduction2'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;">'.$cpCfg['m.jobInformation.deduction3Lbl'].'</th>
                                <th width="49%" style="border-bottom: 1px solid #fff;">'.number_format($Row['deduction3'],2).'</th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;"></th>
                                <th width="49%" style="border-bottom: 1px solid #fff;"></th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-bottom: 1px solid #fff;  border-right: 1px solid #fff;"></th>
                                <th width="49%" style="border-bottom: 1px solid #fff;"></th>
                            </tr>
                            <tr>
                                <th width="50%" style="border-right: 1px solid #fff;">Total Fixed Deductions</th>
                                <th width="49%">'.number_format($total_deduction,2).'</th>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff;">Other Salary-Related Components</td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">'.$images.' CPF Contributions Payable<br/>
                <span style="color:#808080; font-size:10px;">(subject to prevailing CPF contribution rates)</span></td>
            </tr>
        </table>
        ';

        if ($Row['paid_annual_leave_per_year'] != '') {
            $paid_annual_leave = '<img src="images/images1.png" width="10px" height="10px"/>';
            $paid_annual_leave_txt = $Row['paid_annual_leave_per_year'];
        }else {
            $paid_annual_leave = '<img src="images/images.png" width="10px" height="10px"/>';
            $paid_annual_leave_txt = '___';
        }

        if ($Row['paid_outpatient_sick_leave_per_year'] != '') {
            $paid_outpatient_sick_leave = '<img src="images/images1.png" width="10px" height="10px"/>';
            $paid_outpatient_sick_leave_txt = $Row['paid_outpatient_sick_leave_per_year'];
        }else {
            $paid_outpatient_sick_leave = '<img src="images/images.png" width="10px" height="10px"/>';
            $paid_outpatient_sick_leave_txt = '___';
        }

        if ($Row['paid_hospitalisation_leave_per_year'] != '') {
            $paid_hospitalisation_leave = '<img src="images/images1.png" width="10px" height="10px"/>';
            $paid_hospitalisation_leave_txt = $Row['paid_hospitalisation_leave_per_year'];
        }else {
            $paid_hospitalisation_leave = '<img src="images/images.png" width="10px" height="10px"/>';
            $paid_hospitalisation_leave_txt = '___';
        }

        if ($Row['paid_medical_examination_fee'] == 1) {
            $paid_medical_examination_fee = '<img src="images/images1.png" width="10px" height="10px"/>';
        }else {
            $paid_medical_examination_fee = '<img src="images/images.png" width="10px" height="10px"/>';
        }

        $tbl5 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px; background-color: #E6E7E8;">
            <tr style="background-color:#414042; color:#FFFFFF; font-size:14px;">
                <td style="font-weight:bold;" colspan="2">Section D | Leave and Medical Benefits</td>
            </tr>
            <tr>
                <td width="50%" rowspan="2" style="border-top: 3px solid #fff; ">Types of Leave<br/>
                    <span style="color: #808080; font-size:10px;">(applicable if service is at least 3 months)<br/></span>
                    <span style="font-size:10px;">'.$paid_annual_leave.' Paid Annual Leave Per Year:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '. $paid_annual_leave_txt .' days<br/>
                    <span style="color: #808080; font-size:10px;">(for 1st year of service)</span><br/>
                    '.$paid_outpatient_sick_leave.' Paid Outpatient Sick Leave Per Year:&nbsp;&nbsp;&nbsp;'. $paid_outpatient_sick_leave_txt .' days<br/>
                    <span style="color: #808080; font-size:10px;">(after 6 months of service)</span><br/>
                    '.$paid_hospitalisation_leave.' Paid Hospitalisation Leave Per Year:&nbsp;&nbsp;&nbsp;&nbsp;'. $paid_hospitalisation_leave_txt .' days</span><br/>
                    <span style="color: #808080; font-size:10px;">(Note that paid hospitalisation per year is inclusive of paid outpatient sick leave. Leave entitlement for part-time employees may be pro-rated based on hours.)</span>
                </td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Other Types of Leave<br/>
                <span style="color: #808080; font-size:10px;">(e.g Paid Maternity Leave)</span><br/>'. nl2br($Row['other_type_of_leave']) .'</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">'.$paid_medical_examination_fee.' Paid Medical Examination Fee<br/><br/>
                Other Medical Benefits<span style="color: #808080; font-size:10px;"> (optional,to specify)</span><br/>'. nl2br($Row['other_medical_benefits']) .'</td>
            </tr>
        </table>
        ';

        $length_of_probation = '';
        $probation_end_date = '';
        $probation_start_date = '';
        if ($Row['probationary'] == 1) {
            $length_of_probation = strtoupper($Row['length_of_probation']);
            $probation_start_date = $dateUtil->formatDate($Row['probation_start_date'], 'DD/MM/YYYY');
            $probation_end_date = $dateUtil->formatDate($Row['probation_end_date'], 'DD/MM/YYYY');
            //$lineimage = '<img src="images/img.jpg" width="100px" height="2px"/>';
        }

        $tbl6 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px; background-color: #E6E7E8;">
            <tr style="background-color:#414042; color:#FFFFFF; font-size:14px;">
                <td style="font-weight:bold;" colspan="2">Section E | Others</td>
            </tr>
            <tr>
                <td width="50%" style="border-top: 3px solid #fff; ">Length of Probation:  &nbsp;'.$length_of_probation.'<br/><br/>
                    Probation Start Date:&nbsp;'.$probation_start_date.'<br/><br/>
                    Probation End Date:   &nbsp;&nbsp;'.$probation_end_date.'
                </td>
                <td width="50%" style="border-top: 3px solid #fff; border-left: 2px solid #fff;">Notice Period for Termination of Employment<br/>
                    <span style="color: #808080; font-size:10px;">(initiated by either party whereby the length shall be the same)</span><br/>
                    <span>'. strtoupper($Row['notice_period_for_termination']) .'</span>                    
                </td>
            </tr>
        </table>
        ';

        $pdf->ln(-6);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-7);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl6, true, false, false, false, '');
        $pdf->Output();
    }

    /**
     *
     */
    function getPrintEmploymentContract() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $dateUtil = Zend_Registry::get('dateUtil');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $job_information_id = $fn->getReqParam('job_information_id');
        $jiRec = $fn->getRecordRowByID('job_information', 'job_information_id', $job_information_id);
        $employeeRec = $fn->getRecordRowByID('employee', 'employee_id', $jiRec['employee_id']);

        $template = 'Employment Contract.docx';
        $templatePath = $cpCfg['cp.masterPath'].'modules/Payroll/Lib/templates/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Employee Contract_' . $job_information_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        if ($jiRec['act_join_date']) {
            $todayMonth = $dateUtil->formatDate($jiRec['act_join_date'], 'DD MMMM YYYY');
        } else {
            $todayMonth =  date('dS F Y');
        }
        $todayWithDay =  date('l, d F Y');

        $company_address = strtoupper($cpCfg['cp.addressPdf1']) . ' ' . strtoupper($cpCfg['cp.addressPdf2']) . ' ' . strtoupper($cpCfg['cp.addressPdf4']);
        if ($jiRec['place_of_work']) {
            $place_of_work = strtoupper($jiRec['place_of_work']);
        } else {
            $place_of_work = $company_address;
        }
        
        if ($employeeRec['citizen'] == 'Citizen' || $employeeRec['citizen'] == 'PR') {
            $id_no = "NRIC No: {$employeeRec['nric_no']}";
        } else {
            $id_no = "FIN No: {$employeeRec['fin_no']}";
        }

        $valArr = array();
        /* Contact Details */
        $valArr['current_date']         = strtoupper($todayMonth);
        $valArr['company_name']         = strtoupper($cpCfg['cp.companyName']);
        $valArr['company_address']      = $company_address;
        $valArr['place_of_work']        = $place_of_work;
        $valArr['employee_name']        = strtoupper($employeeRec['first_name']);
        $valArr['id_no']                = strtoupper($id_no);
        $valArr['designation']          = strtoupper($jiRec['designation']);
        $valArr['duty_responsibility']  = strtoupper($jiRec['duty_responsibility']);
        $valArr['basic_pay']            = number_format($jiRec['basic_pay']);
        $valArr['working_days']         = $jiRec['working_days'];

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);

        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }
}
