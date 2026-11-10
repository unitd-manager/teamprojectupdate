<?
class CPL_Admin_Modules_Tradingsg_Booking_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL   = "
        SELECT b.*,
        c.company_name    AS c_company_name,
        c.email           AS c_email,
        c.address_flat    AS c_address_flat,
        c.address_street  AS c_address_street,
        c.address_town    AS c_address_town,
        c.address_state   AS c_address_state,
        c.address_country AS c_address_country,
        c.address_po_code AS c_address_po_code,
        c.phone           AS c_phone,
        c.fax             AS c_fax,
        c.status          AS c_status,
        c.website         AS c_website,
        c.category        AS c_category,
        CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM booking b
        LEFT JOIN employee e ON (e.employee_id = b.employee_id)
        LEFT JOIN (company c) ON ( c.company_id = b.customer_id )
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

        $booking_id        = $fn->getReqParam('booking_id');
        $special_search     = $fn->getReqParam('special_search');
        $employee_id = $fn->getReqParam('employee_id');
        $booking_date1 = $fn->getReqParam('booking_date_1');
        $booking_date2 = $fn->getReqParam('booking_date_2');

        if ($booking_id != "") {
            $searchVar->sqlSearchVar[] = "b.booking_id = '{$booking_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.booking_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.booking_id');

            if ($employee_id != '') {
                $searchVar->sqlSearchVar[] = "b.employee_id = '{$employee_id}'";
            }

            if ($booking_date1 != "" && $booking_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(b.booking_date BETWEEN '{$booking_date1}' AND '{$booking_date2}')";
            }
        
            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       e.first_name  LIKE '%{$tv['keyword']}%'
                    OR e.last_name        LIKE '%{$tv['keyword']}%'
                    OR c.company_name       LIKE '%{$tv['keyword']}%'
                )";
            }
            
            //$searchVar->sortOrder = "a.employee_name ASC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('customer_id', 'Please select the customer');

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
        $fa['status']  = 'Scheduled';
        $fa['booking_date'] = date('Y-m-d');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $validate->resetErrorArray();

        $validate->validateData('customer_id', 'Please select the customer');
        
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
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'customer_id');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'assign_time');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'booking_date');
        
        return $fa;
    }

    /**
     *
     */
    function getSearchClientName() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $customerName = $extractor[0];

        $SQL = "
        SELECT c.company_name AS value
              ,c.company_name AS label
              ,c.company_id AS id
              ,CONCAT_WS(' **** ', c.company_name) AS label
        FROM company c
        WHERE (c.company_name LIKE '{$customerName}%')
        ORDER BY c.company_name
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchEmployeeName() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $employeeName = $extractor[0];

        $SQL = "
        SELECT c.first_name AS value
              ,c.first_name AS label
              ,c.employee_id AS id
              ,CONCAT_WS(' **** ', c.first_name) AS label
        FROM employee c
        WHERE (c.first_name LIKE '{$employeeName}%')
        ORDER BY c.first_name
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getAddCustomer() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddCustomerValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_name  = $fn->getPostParam('company_name');
        $website    = $fn->getPostParam('website');
        $phone  = $fn->getPostParam('phone');
        $address_flat  = $fn->getPostParam('address_flat');
        $address_street  = $fn->getPostParam('address_street');
        $address_town  = $fn->getPostParam('address_town');
        $address_state  = $fn->getPostParam('address_state');
        $latitude  = $fn->getPostParam('latitude');
        $longitude  = $fn->getPostParam('longitude');

        $fa = array();
        $fa['company_name']   = $company_name;
        $fa['website']   = $website;
        $fa['phone'] = $phone;
        $fa['address_flat']   = $address_flat;
        $fa['address_street']   = $address_street;
        $fa['address_town']   = $address_town;
        $fa['address_state']   = $address_state;
        $fa['latitude']   = $latitude;
        $fa['longitude']   = $longitude;

        $company_id = $fn->addRecord($fa, 'company');

        return $validate->getSuccessMessageXML($company_id.'_'.$company_name);
    }

    /**
     *
     */
    function getAddCustomerValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the customer name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditCustomerSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditCustomerValidate()){
            return $validate->getErrorMessageXML();
        }

        $customer_id  = $fn->getPostParam('customer_id');
        $company_name  = $fn->getPostParam('company_name');
        $website    = $fn->getPostParam('website');
        $phone  = $fn->getPostParam('phone');
        $address_flat  = $fn->getPostParam('address_flat');
        $address_street  = $fn->getPostParam('address_street');
        $address_town  = $fn->getPostParam('address_town');
        $address_state  = $fn->getPostParam('address_state');
        $latitude  = $fn->getPostParam('latitude');
        $longitude  = $fn->getPostParam('longitude');

        $fa = array();
        $fa['company_name']   = $company_name;
        $fa['website']   = $website;
        $fa['phone'] = $phone;
        $fa['address_flat']   = $address_flat;
        $fa['address_street']   = $address_street;
        $fa['address_town']   = $address_town;
        $fa['address_state']   = $address_state;
        $fa['latitude']   = $latitude;
        $fa['longitude']   = $longitude;

        $fn->saveRecord($fa, 'company', 'company_id', $customer_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditCustomerValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the customer name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
