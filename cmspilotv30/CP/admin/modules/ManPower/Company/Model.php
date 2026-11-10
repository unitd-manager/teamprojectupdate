<?
class CP_Admin_Modules_Manpower_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.* 
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
	          ,gc.name AS country_name
        FROM company a
        LEFT JOIN (staff s) ON (a.staff_id = s.staff_id) 
        LEFT JOIN (geo_country gc) ON (a.address_country_code = gc.country_code)
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

        $category       =  $fn->getReqParam('category');
        $status         = $fn->getReqParam('status');
        $company_id     = $fn->getReqParam('company_id');
        $company_name   = $fn->getReqParam('company_name');
        $company_type   = $fn->getReqParam('company_type');

        $searchVar->sqlSearchVar[] = "a.company_type != 'Referral'";
        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "a.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.company_id');
    
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
            }
    
            if ($category != "") {
                $searchVar->sqlSearchVar[] = "a.category = '{$category}'";
            }
    

            if ($company_type != "") {
                $searchVar->sqlSearchVar[] = "a.company_type = '{$company_type}'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.company_name  LIKE '%{$tv['keyword']}%'
                    OR a.group_name LIKE '%{$tv['keyword']}%'  
                    OR a.email      LIKE '%{$tv['keyword']}%'
                )";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "a.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
            }

            if ($_SESSION['userGroupType'] == 'User') {
                $searchVar->sqlSearchVar[] =  "a.staff_id = {$_SESSION['staff_id']}";
            }
    
            $searchVar->sortOrder = "a.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');
        $validate->validateData('company_type', 'Please select the company type');

        $company_name = $fn->getPostParam('company_name', '', true);
        $company_type = $fn->getPostParam('company_type', '', true);

        if ($company_name != ''){
            $rec = $fn->getRecordByCondition('company', "company_name = '{$company_name}'");
            $expCompany = array('displayText'=> 'Goto Existing Company Record');
            $compLink = $fn->getRecordDetailLink('manPower_company', 'record_id', $rec['company_id'], $expCompany);
        
            if (is_array($rec)){
                $validate->errorArray['company_name']['name'] = "company_name";
                $validate->errorArray['company_name']['msg']  = "Company already exists. '{$compLink}'";
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
        $fa['client_code']   = $this->getUpdateClientCode();
        $fa['address_country_code'] = 'US';
        $fa['staff_id']      = $_SESSION['staff_id'];

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }


    /**
     *
     */
    function getUpdateClientCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* Updation of Client Code */
        $site_id        = $fn->getSessionParam('cp_site_id');
        $nextClientCode = $fn->getSettingsValueByKey("nextClientCode");


        $clientCode = $fn->getSettingsValueByKey('clientPrefix') . $nextClientCode;

        //$SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextClientCode' AND site_id = {$site_id}";
        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextClientCode'";
        $result = $db->sql_query($SQL);

        return $clientCode;

    }

    /**
     *
     */

    function getCompanyDocumentSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $company_id = $fn->getReqParam('company_id');
        $documents_id = $fn->getReqParam('documents_id');
        $documents = $fn->getReqParam('documents');

        $fa = array();
        $fa['company_id'] = $company_id;
        $fa['documents_id'] = $documents_id;
        $fa['site_id'] = $_SESSION['cp_site_id'];
        if($documents == 1){
            print 'aaaaa';
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'company_documents');
            $db->sql_query($SQL);
            $company_documents_id = $db->sql_nextid();
            return 'yes';
        } else{
            $sql = "
            DELETE FROM company_documents
            WHERE company_id = {$company_id}
              AND documents_id = {$documents_id}
            ";
            $result = $db->sql_query($sql);
        }        
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $userGroupType = $fn->getSessionParam('userGroupType');

        $validate->resetErrorArray();

        $validate->validateData('company_name', 'Please enter the Company Name');
        $validate->validateData('company_type', 'Please enter the Company Type');

        
        $email      = $fn->getPostParam('email', '', true);
        $record_id  = $fn->getReqParam('company_id');

        if ($email != ''){
            if(!$validate->isEmail($email)){            
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Please enter valid email";
            }
            else{
                $rec = $fn->getRecordByCondition('company', "email = '{$email}' AND company_id != {$record_id}");
                if (is_array($rec)){
                    $expEmail = array('displayText'=> 'Goto Existing Company Record');
                    $emailLink = $fn->getRecordDetailLink('manPower_company', 'record_id', $rec['company_id'], $expEmail);
                    
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
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'remarks');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        //$fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        //$fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'company_type');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_address');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'annual_turn_over');
        $fa = $fn->addToFieldsArray($fa, 'uen');
        $fa = $fn->addToFieldsArray($fa, 'company_type');
        $fa = $fn->addToFieldsArray($fa, 'commission_percentage');

        return $fa;
    }

    /**
     *
     */
    function getProjectCompanyProjectContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";

    }

    /**
     *
     */
    function getProjectCompanyProjectOpportunityLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('opp_status');
        
        $extraFields = "";

        if ($status != "") {
            $whereSQL = " AND a.status = '{$status}'";
        } else {
            $whereSQL = " AND (a.status != 'Cancelled')";
        }

        $SQL = "
        SELECT a.opportunity_id
              ,a.opportunity_code
              ,a.title AS title
              ,FORMAT(a.estimated_value,0)  AS estimated_value
              ,a.status {$extraFields}
        FROM company b
            ,opportunity a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
          {$whereSQL}
        ORDER BY title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyProjectInvoiceLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('invoice_status');

        $whereSQL = '';

        if ($status != "") {
            $whereSQL .= " AND a.status = '{$status}'";
        } else {
            $whereSQL .= " AND (a.status = 'Due' OR a.status = 'Late')";
        }

        $SQL = "
        SELECT a.invoice_id
              ,a.invoice_code
              ,b.project_code
              ,a.invoice_type AS title
              ,a.invoice_date
              ,a.invoice_due_date
              ,FORMAT(a.invoice_amount, 0) AS invoice_amount
              ,a.status
        FROM invoice a
        LEFT JOIN (project b) ON (a.project_id = b.project_id)
        LEFT JOIN (company d) ON (b.company_id = d.company_id)
        WHERE d.company_id = {$id}
              {$whereSQL}
        ORDER BY title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyProjectProjectLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $still_to_bill_sql = "(
        SELECT FORMAT(sum(invoice_amount), 0) AS total_cost
        FROM invoice c
        WHERE c.project_id = a.project_id
        )
        ";

        $status      = $fn->getReqParam('project_status', 'WIP');
        
        $whereSQL    = "";
        $extraFields = "";

        if ($status != "") {
            $whereSQL .= " AND a.status = '{$status}'";
        }

        $SQL = "
        SELECT a.project_id
              ,a.project_code
              ,a.title AS title
              ,FORMAT(a.project_value, 0) AS project_value
              ,a.status
        FROM company b
            ,project a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
          {$whereSQL}
        ORDER BY title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectCompanyProjectCompanyAddressLinkSQL($id) {
        $SQL = "
        SELECT a.company_address_id
              ,a.address_flat
              ,a.address_street
              ,a.address_town
              ,a.address_state
              ,a.address_country
              ,a.address_po_code
        FROM company b
            ,company_address a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getExportData1(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Company_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company ID");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company Name");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Category");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Company Size");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Industry");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Source");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Website");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Phone");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Fax");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Flat");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Street");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Town");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address State");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Address Country");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Status");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Comment By");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Notes");
        
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array( 'bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }
        
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['category']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_size']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['industry']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['source']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['website']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['fax']);

            if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
                $sqlAdd = "
                SELECT * 
                FROM company_address
                WHERE company_id = {$row['company_id']}
                LIMIT 0, 1
                ";
                $resultAdd = $db->sql_query($sqlAdd);
                $rowAdd    = $db->sql_fetchrow($resultAdd);

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAdd['address_country']);
            } else {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_country']);
            }    

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comment_by']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['notes']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')
        
             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
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
    function getManPowerCompanyManPowerContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.contact_priority
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ORDER BY a.contact_priority ASC
        ";

    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'company_name'       => $phpExcel->getFldObj('Company Name')
             ,'group_name'         => $phpExcel->getFldObj('Group Name')
             ,'website'            => $phpExcel->getFldObj('Website')
             ,'email'              => $phpExcel->getFldObj('Email')
             ,'phone'              => $phpExcel->getFldObj('Phone')
             ,'fax'                => $phpExcel->getFldObj('Fax')
             ,'address_flat'       => $phpExcel->getFldObj('Address Flat')
             ,'address_street'     => $phpExcel->getFldObj('Address Street')
             ,'address_town'       => $phpExcel->getFldObj('Address Town')   
             ,'address_state'      => $phpExcel->getFldObj('Address State')   
             ,'address_country'    => $phpExcel->getFldObj('Address Country')
             
        );

        $config = array(
             'module'        => 'common_geoRegion'
            ,'matchFieldArr' => array('region_code')
            ,'fldsArr'       => $fa
        );

        return $phpExcel->importData($config);
    }
}
