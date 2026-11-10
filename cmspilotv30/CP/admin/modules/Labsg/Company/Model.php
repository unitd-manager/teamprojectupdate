<?
class CP_Admin_Modules_Labsg_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT c.*
              ,gc.name AS country_name
        FROM company c
        LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
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
        $searchVar->mainTableAlias = 'c';

        $status       = $fn->getReqParam('status');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.company_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
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
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            $searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

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
        $fa['category'] = 'Client';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_street');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_town');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_state');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'customer_type');
        $fa = $fn->addToFieldsArray($fa, 'mark_up_percentage');
        $fa = $fn->addToFieldsArray($fa, 'cst_no');
        $fa = $fn->addToFieldsArray($fa, 'tin_no');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        return $fa;
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
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'company_name'          => $phpExcel->getImportFldObj('Company name')
             ,'billing_address_flat'  => $phpExcel->getImportFldObj('Address 1')
             ,'billing_address_street'=> $phpExcel->getImportFldObj('Address 2')
             ,'billing_address_state' => $phpExcel->getImportFldObj('Postal code')
             ,'phone'                 => $phpExcel->getImportFldObj('Phone')
             ,'fax'                   => $phpExcel->getImportFldObj('Fax')
             ,'mobile'                => $phpExcel->getImportFldObj('Mobile')
             ,'email'                 => $phpExcel->getImportFldObj('Email')
             ,'first_name'            => $phpExcel->getImportFldObj('Contact person')
             ,'test1'                 => $phpExcel->getImportFldObj('Test 1')
             ,'test2'                 => $phpExcel->getImportFldObj('Test 2')
             ,'test3'                 => $phpExcel->getImportFldObj('Test 3')
             ,'notes'                 => $phpExcel->getImportFldObj('Remark')
        );

        $fa['billing_address_country']['defaultValue'] = "SG";
        $fa['mobile']['refOnly']     = true;
        $fa['email']['refOnly']      = true;
        $fa['first_name']['refOnly'] = true;
        $fa['test1']['refOnly'] = true;
        $fa['test2']['refOnly'] = true;
        $fa['test3']['refOnly'] = true;
        /****************************************/
        $config = array(
             'module'              => 'labsg_company'
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($company_id, $fa) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $first_name = $fa['first_name'];
        $test1 = $fa['test1'];
        $test2 = $fa['test2'];
        $test3 = $fa['test3'];

        if ($first_name) {
            $fa1 = array();
            $fa1['mobile']     = $fa['mobile'];
            $fa1['email']      = $fa['email'];
            $fa1['first_name'] = $fa['first_name'];
            $fa1['company_id'] = $company_id;

            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa1, 'contact');
            $result = $db->sql_query($SQL);
            $contact_id = $db->sql_nextid();
        }

        if ($test1) {
            $ft1 = array();
            $ft1['company_id']   = $company_id;
            $ft1['treatment_id'] = 1;
            $ft1['amount']       = $test1;

            $SQLFt1    = $dbUtil->getInsertSQLStringFromArray($ft1, 'company_treatment');
            $resultFt1 = $db->sql_query($SQLFt1);
            $company_treatment_id = $db->sql_nextid();
        }

        if ($test2) {
            $ft2 = array();
            $ft2['company_id']   = $company_id;
            $ft2['treatment_id'] = 2;
            $ft2['amount']       = $test2;

            $SQLFt2    = $dbUtil->getInsertSQLStringFromArray($ft2, 'company_treatment');
            $resultFt2 = $db->sql_query($SQLFt2);
            $company_treatment_id = $db->sql_nextid();
        }

        if ($test3) {
            $ft3 = array();
            $ft3['company_id']   = $company_id;
            $ft3['treatment_id'] = 3;
            $ft3['amount']       = $test3;

            $SQLFt3    = $dbUtil->getInsertSQLStringFromArray($ft3, 'company_treatment');
            $resultFt3 = $db->sql_query($SQLFt3);
            $company_treatment_id = $db->sql_nextid();
        }
    }
    
    /**
     *
     */
    function getlabsgCompanylabsgContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
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
    function getlabsgCompanylabsgDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getlabsgCompanylabsgCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }
    /**
     *
     */
    function getTreatmentFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getTreatmentValidate()){
            return $validate->getErrorMessageXML();
        }

        $treatment_id = $fn->getPostParam('treatment_id');
        $company_id   = $fn->getPostParam('company_id');
        $title        = $fn->getPostParam('Treatment_Name');
        $amount       = $fn->getPostParam('amount');

        $fa = array();

        $fa['company_id']    = $company_id;
        $fa['treatment_id']  = $treatment_id;
        $fa['amount']        = $amount;

        $company_treatment_id = $fn->addRecord($fa, 'company_treatment');

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getEditTreatmentFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getTreatmentEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $amount               = $fn->getPostParam('amount');
        $company_treatment_id = $fn->getPostParam('company_treatment_id');

        /*
        $rowTreat = $fn->getRecordRowByID('company_treatment', 'company_treatment_id', $company_treatment_id);
        
        if($rowTreat['amount'] != $amount){
          $fa2 = array();

          $fa2['company_id']           = $rowTreat['company_id'];
          $fa2['treatment_id']         = $rowTreat['treatment_id'];
          $fa2['amount']               = $rowTreat['amount'];
          $fa2['company_treatment_id'] = $rowTreat['company_treatment_id'];

          $company_treatment_id = $fn->addRecord($fa2, 'company_treatment_history');
        }
        */

        $fa1 = array();
        $fa1['amount']           = $amount;
        $whereConditionTreatment = "WHERE company_treatment_id = {$company_treatment_id}" ;
        $sqlUpdateTreatment      = $dbUtil->getUpdateSQLStringFromArray($fa1, "company_treatment", $whereConditionTreatment);
        $resultUpdateTreatment   = $db->sql_query($sqlUpdateTreatment);

        return $validate->getSuccessMessageXML();
    }
    
    /**
     *
     */
    function getTreatmentValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('treatment_id', 'Please Select Treatment Name');
        $validate->validateData('amount', 'Please Enter Treatment Amount');

        $company_id   = $fn->getPostParam('company_id');
        $treatment_id = $fn->getPostParam('treatment_id');

        if($treatment_id != ''){
            $SQLCompanTreat = "
            SELECT treatment_id
            FROM company_treatment
            WHERE company_id = {$company_id}
            AND treatment_id = {$treatment_id}
            ";
            $resultCompanTreat  = $db->sql_query($SQLCompanTreat);
            $numRowsCompanTreat = $db->sql_numrows($resultCompanTreat);

            if($numRowsCompanTreat > 0){
                $validate->errorArray['treatment_id']['name'] = "treatment_id";
                $validate->errorArray['treatment_id']['msg']  = "Treatment Already Exist";
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
    function getTreatmentEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('amount', 'Please enter Treatment Amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getDeleteTreatment(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $treatment_id = $fn->getReqParam('treatment_id');
        $company_treatment_id = $fn->getReqParam('company_treatment_id');

        $SQL ="
               DELETE FROM company_treatment
               WHERE company_treatment_id = {$company_treatment_id}
               ";
        $result = $db->sql_query($SQL);

        $SQL ="
               DELETE FROM company_treatment_history
               WHERE company_treatment_id = {$company_treatment_id}
               AND treatment_id = {$treatment_id}
               ";
        $result = $db->sql_query($SQL);
    }



  }
