<?
class CP_Admin_Modules_EzTrade_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT c.*
              ,cat.title AS category_title
              ,sc.title AS sub_category_title
              ,CONCAT_WS(' ', c.address_street, c.address_town) AS company_address
              ,r.region_name
              ,gc.name AS country_name
              
              ,(SELECT pt.description
                FROM payment_terms pt
                WHERE pt.company_id = c.company_id
                LIMIT 1) AS payment_terms

              ,(SELECT dt.description
                FROM delivery_terms dt
                WHERE dt.company_id = c.company_id
                LIMIT 1) AS delivery_terms
        FROM company c
        LEFT JOIN category cat    ON (c.category_id = cat.category_id)
        LEFT JOIN sub_category sc ON (c.sub_category_id = sc.sub_category_id)
        LEFT JOIN region r ON (r.region_id = c.region_id)
        LEFT JOIN geo_country gc ON (c.address_country = gc.country_code)
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
        $cpUtil = Zend_Registry::get('cpUtil');

        $status       = $fn->getReqParam('status');
        $category     = $fn->getReqParam('category');
        $region_id       = $fn->getReqParam('region_id');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.company_id');
    
            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($region_id != "") {
                $searchVar->sqlSearchVar[] = "c.region_id = '{$region_id}'";
            }

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
                    OR c.email LIKE '%{$tv['keyword']}%'
                    OR c.category LIKE '%{$tv['keyword']}%'
                    OR c.address_country LIKE '%{$tv['keyword']}%'
                    OR c.party_type LIKE '%{$tv['keyword']}%'
                    OR c.address_town LIKE '%{$tv['keyword']}%'
                    OR c.address_state LIKE '%{$tv['keyword']}%'
                    OR c.address_po_code LIKE '%{$tv['keyword']}%'
                )";
            }
    
            //------------------------------------------------------------------------//
            $fn->setSpecialSearch();
    
            $searchVar->sortOrder = "c.company_code";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('company_name', 'Please enter the company name');
        $validate->validateData('category', 'Please choose Party');

        $company_name = $fn->getReqParam('company_name');
        $SQL = "
        SELECT COUNT(*) AS count
        FROM company c
        WHERE c.company_name = '{$company_name}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $validate->errorArray['company_name']['name'] = 'company_name';
            $validate->errorArray['company_name']['msg']  = 'Company Name is not unique.';
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

        $company_code = 'C' . $fn->getSequenceFromSettings('m.trading.company.nextCode');
        $fa = $this->getFields();
        $fa['company_code'] = $company_code;
        $fa['status']       = 'active';
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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detail');
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
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'credit_insurance');
        $fa = $fn->addToFieldsArray($fa, 'preferred_currency');
        $fa = $fn->addToFieldsArray($fa, 'company_code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone_country_code');
        $fa = $fn->addToFieldsArray($fa, 'phone_area_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax_country_code');
        $fa = $fn->addToFieldsArray($fa, 'fax_area_code');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'sell_currency');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'consignee_name');
        $fa = $fn->addToFieldsArray($fa, 'consignee_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone_country_code');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone_area_code');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone');
        $fa = $fn->addToFieldsArray($fa, 'consignee_contact_person');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_company_address');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'company_type');
        $fa = $fn->addToFieldsArray($fa, 'party');
        $fa = $fn->addToFieldsArray($fa, 'party_type');
        $fa = $fn->addToFieldsArray($fa, 'region_id');
        $fa = $fn->addToFieldsArray($fa, 'sales_agent');
        $fa = $fn->addToFieldsArray($fa, 'port');
        $fa = $fn->addToFieldsArray($fa, 'pricing_type_id');

        return $fa;
    }

    /**
     *
     */
    function getEzTradeCompanyEzTradeContactLinkSQL($id) {

        return "
        SELECT con.contact_id
              ,con.salutation
              ,con.first_name
              ,con.last_name
              ,con.email
              ,CONCAT_WS(' - ', con.phone_country_code, con.phone_area_code, con.phone)
              ,CONCAT_WS(' - ', con.mobile_country_code, con.mobile_area_code, con.mobile)
              ,con.position
              ,con.department
        FROM company c
        JOIN contact con
        WHERE c.company_id = con.company_id
          AND c.company_id = {$id}
        ";

    }

    /**
     *
     */
    function getEzTradeCompanyEzTradeDeliveryAddressLinkSQL($id) {
        
        $SQL = "
        SELECT da.delivery_address_id
              ,da.address_flat
              ,da.address_street
              ,da.address_town
              ,da.address_state
              ,da.address_country
              ,da.address_po_code
        FROM company c
            ,delivery_address da
        WHERE da.company_id = c.company_id
          AND c.company_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEzTradeCompanyEzTradeDeliveryTermsLinkSQL($id) {
        
        $SQL = "
        SELECT d.delivery_terms_id
        	   ,d.description
        FROM delivery_terms d
        WHERE d.company_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEzTradeCompanyEzTradePaymentTermsLinkSQL($id) {
        
        $SQL = "
        SELECT pt.payment_terms_id
        	   ,pt.description
        FROM payment_terms pt
        WHERE pt.company_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getPaymentTermsSQL($company_id = 0) {
        $text = "
        SELECT a.description
        FROM payment_terms a
        WHERE a.company_id = {$company_id}
        ORDER BY company_id
        ";

        return $text;
    }

    /**
     *
     */
    function getShipmentToCompanySQL() {
        $text = "
        SELECT DISTINCT
               c.company_id
              ,c.company_name
        FROM company c
        JOIN shipment sm ON (sm.company_id = c.company_id)
        ORDER BY c.company_name
        ";

        return $text;
    }

    /**
     *
     */
    function getSupplierSQL() {
        $sql = "
        SELECT company_id
              ,company_name
        FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";
        return $sql;
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $fa = array(
             'title'            => $phpExcel->getImportFldObj('Company Name')
             ,'status'                => 'Current'
             ,'party_type'            => 'Customer'
             ,'address_flat'          => $phpExcel->getImportFldObj('Company Address')
        );

        /****************************************/
        $config = array(
             'module'              => 'ezTrade_company'
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
    }
    
}
