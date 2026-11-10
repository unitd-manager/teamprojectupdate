<?
class CP_Admin_Modules_Hms_Employee_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";

        if ($cpCfg['m.hms.hasMultipleCompanyAddress'] == 1) {
            $SQL   = "
            SELECT a.*
                   ,CONCAT_WS(' ', a.first_name, a.middle_name, a.last_name) AS employee_name
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
            b.category        AS c_category
            FROM {$extraTableNames}
            employee a
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            ";
        }

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

        $company_id     = $fn->getReqParam('company_id');
        $employee_id    = $fn->getReqParam('employee_id');
        $task_id        = $fn->getReqParam('task_id');
        $employee_name  = $fn->getReqParam('employee_name');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $position       = $fn->getReqParam('position');
        $status         = $fn->getReqParam('status');

        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

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
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "a.company_id = {$company_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       a.first_name     LIKE '%{$tv['keyword']}%'
                    OR a.middle_name    LIKE '%{$tv['keyword']}%'
                    OR a.last_name      LIKE '%{$tv['keyword']}%'
                    OR a.user_name      LIKE '%{$tv['keyword']}%'
                    OR a.company_name   LIKE '%{$tv['keyword']}%'
                    OR a.email          LIKE '%{$tv['keyword']}%'
                    OR b.company_name   LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($position != "") {
                $searchVar->sqlSearchVar[] = "a.position = '{$position}'";
            }

            if ($employee_name != "") {
                $searchVar->sqlSearchVar[] = "a.employee_name = '{$employee_name}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "a.subscribe = 1";
            }

            $searchVar->sortOrder = "employee_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');

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
        $fa['color']  = '#3A87AD';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('passport', 'Please enter the passport no');
        $validate->validateData('nric_no' , 'Please enter the nric no');
        $validate->validateData('employee_work_type' , 'Please select the work type');

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
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
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

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'middle_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_address_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');

        $fa = $fn->addToFieldsArray($fa, 'salary');
        $fa = $fn->addToFieldsArray($fa, 'add_hourly_rate');
        $fa = $fn->addToFieldsArray($fa, 'employee_work_type');
        $fa = $fn->addToFieldsArray($fa, 'passport');
        $fa = $fn->addToFieldsArray($fa, 'nric_no');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'consultation_fees');
        $fa = $fn->addToFieldsArray($fa, 'add_in_payroll');
        $fa = $fn->addToFieldsArray($fa, 'status');

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

        if($cpCfg['m.hms.hasMultipleCompanyAddress'] == 1) {
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

            if($cpCfg['m.hms.hasMultipleCompanyAddress'] == 1) {
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
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
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
        $valuelist_name = "positionType" ;

        $fa['position'] = $valuelist_value;

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

}
