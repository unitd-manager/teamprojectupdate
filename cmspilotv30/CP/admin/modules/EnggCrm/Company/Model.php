<?
class CP_Admin_Modules_EnggCrm_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.*
        FROM company a
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

        $category     = $fn->getReqParam('category');
        $status       = $fn->getReqParam('status');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "a.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.company_id');
    
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
            }
    
    
            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "a.company_name LIKE '%{$company_name}%'";
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
    
            $searchVar->sqlSearchVar[] = "a.category = 'Client'";
            $searchVar->sortOrder = "a.company_name";
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
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');

        $fa = $fn->addToFieldsArray($fa, 'billing_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_street');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_country');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_address');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'client_code');

        return $fa;
    }

    /**
     *
     */
    function getEnggCrmCompanyEnggCrmContactLinkSQL($id) {

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
    function getEnggCrmCompanyEnggCrmCompanyAddressLinkSQL($id) {
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
        
             ,'billing_address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'billing_address_street'  => $phpExcel->getFldObj('Address Street')
             ,'billing_address_country' => $phpExcel->getFldObj('Address Country')
             ,'billing_address_po_code'   => $phpExcel->getFldObj('Postal Code')

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
        $db = Zend_Registry::get('db');

        $fa = array(
              'company_name'    => $phpExcel->getImportFldObj('Company Name')
             ,'billing_address_flat'    => $phpExcel->getImportFldObj('Address 1')
             ,'billing_address_street'  => $phpExcel->getImportFldObj('Address 2')
             ,'billing_address_country' => $phpExcel->getImportFldObj('Country')
             ,'billing_address_po_code' => $phpExcel->getImportFldObj('Postal Code')
             ,'salutation'      => $phpExcel->getImportFldObj('Contact Person Salutation')
             ,'first_name'      => $phpExcel->getImportFldObj('Contact Person')
             ,'phone_direct'    => $phpExcel->getImportFldObj('Contact Phone')
             ,'mobile'          => $phpExcel->getImportFldObj('Contact Mobile')
             ,'fax'             => $phpExcel->getImportFldObj('Contact fax')
             ,'email'           => $phpExcel->getImportFldObj('Contact Email')
        );
        
        $fa['category']['defaultValue'] = 'Client';
        $fa['salutation']['refOnly'] = true;
        $fa['first_name']['refOnly'] = true;
        $fa['phone_direct']['refOnly'] = true;
        $fa['mobile']['refOnly'] = true;
        $fa['fax']['refOnly'] = true;
        $fa['email']['refOnly'] = true;
        /****************************************/
        $config = array(
             'module'              => 'enggCrm_company'
            ,'matchFieldArr'       => array('company_name')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($company_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if ($company_id) {
            /* Inserting Contact for Company */
            $fa2 = array();
            $fa2['salutation']   = $fa['salutation'];
            $fa2['first_name']   = $fa['first_name'];
            $fa2['phone_direct'] = $fa['phone_direct'];
            $fa2['mobile']       = $fa['mobile'];
            $fa2['fax']          = $fa['fax'];
            $fa2['email']        = $fa['email'];
            $fa2['company_id']      = $company_id;
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'contact');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'contact');
            $result = $db->sql_query($SQL);
            $contact_id  = $db->sql_nextid();
        }
    }

    /**
     *
     */
    function getEnggCrmCompanyEnggCrmInvoiceLinkSQL($id) {
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
}
