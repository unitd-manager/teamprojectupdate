<?
class CPL_Admin_Modules_EnggCrm_Renewal_Model extends CP_Common_Lib_ModuleModelAbstract
{
   function getSQL() {
        $SQL = "
       SELECT 
    v.*, 
    c.first_name AS contact_names, 
    b.company_name, 
    c.phone, 
    (
        SELECT sr.shop
        FROM shop_renewal sr
        WHERE sr.renewal_id = v.renewal_id
        LIMIT 1
    ) AS renewal_shop,
    (
        SELECT sr.location
        FROM shop_renewal sr
        WHERE sr.renewal_id = v.renewal_id
        LIMIT 1
    ) AS renewal_location
FROM renewal v
LEFT JOIN company b ON v.company_id = b.company_id
LEFT JOIN contact c ON v.contact_id = c.contact_id

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

        $renewal_id = $fn->getReqParam('renewal_id');
        $company_id         = $fn->getReqParam('company_id');

        if ($renewal_id != "") {
            $searchVar->sqlSearchVar[] = "v.renewal_id = '{$renewal_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "v.renewal_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'v.renewal_id');

        	if ($company_id != "") {
            	$searchVar->sqlSearchVar[] = "v.company_id  = {$company_id}";
        	}
        
    		if ($tv['keyword'] != "") {
        		$searchVar->sqlSearchVar[] = "(
                	v.ref_no LIKE '%{$tv['keyword']}%'
                	OR v.ref_no  LIKE '%{$tv['keyword']}%'
                	OR b.company_name  LIKE '%{$tv['keyword']}%'                
            	)";
        	}

        	$searchVar->sortOrder = "v.renewal_id DESC";
        }  

    }

    /**
     *
     */
     function getNewValidate() {
       $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('start_date', 'Please enter the date');
      
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
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
        $renewal_id     = $fn->getReqParam('renewal_id');

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
        $valuelist_name = "checklist" ;

        $fa['service_included'] = $valuelist_value;

        /*$whereCondition = "WHERE renewal_id = {$renewal_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "renewal", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);*/

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
     */
    function getAddServiceMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $renewal_id        = $fn->getPostParam('renewal_id');

      
            //$partno_arr        = $fn->getPostParam('partno', array());
            $schedule_arr         = $fn->getPostParam('schedule', array());
            $schedule_date_arr   = $fn->getPostParam('schedule_date', array());
            $actual_date_arr    = $fn->getPostParam('actual_date', array());
            $service_due_arr          = $fn->getPostParam('service_due', array());
            $remarks_arr        = $fn->getPostParam('remarks', array());
           
            if (!$this->getAddMultipleLineItemValidate()){
                return $validate->getErrorMessageXML();
            }

            $rowProject = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);

            $count = count($schedule_arr);

            for ($i = 0; $i < $count; $i++) {
                $schedule = isset($schedule_arr[$i]) ? $schedule_arr[$i] : null;
                $schedule_date = isset($schedule_date_arr[$i]) ? $schedule_date_arr[$i] : null;
                $actual_date = isset($actual_date_arr[$i]) ? $actual_date_arr[$i] : null;
                $service_due = isset($service_due_arr[$i]) ? $service_due_arr[$i] : null;
                $remarks = isset($remarks_arr[$i]) ? $remarks_arr[$i] : null;
            
                if (is_null($schedule) || is_null($service_due)) {
                    // Skip this iteration if required values are missing
                    continue;
                }
            
                $chkField = $schedule;
                if ($chkField) {
                    $fa = array(
                        'renewal_id' => $renewal_id,
                        'schedule' => $schedule,
                        'schedule_date' => $schedule_date,
                        'actual_date' => $actual_date,
                        'service_due' => $service_due,
                        'remarks' => $remarks,
                        'creation_date' => date('Y-m-d H:i:s')
                    );
            
                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'service_renewal');
                    $result = $db->sql_query($insert);
                    $service_renewal_id = $db->sql_nextid();
                }
            }
            
        

        return $validate->getSuccessMessageXML();
    }

  /**
     *
     */
    function getAddMultipleLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $schedule_arr       = $fn->getPostParam('schedule', array());
    
        $validate->resetErrorArray();

        $filterArray3 = array_filter($schedule_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter schedule";
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
    function getAddShopMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $renewal_id        = $fn->getPostParam('renewal_id');

      
            //$partno_arr        = $fn->getPostParam('partno', array());
            $shop_arr         = $fn->getPostParam('shop', array());
          
            $location_arr        = $fn->getPostParam('location', array());
           
            if (!$this->getAddShopMultipleLineItemValidate()){
                return $validate->getErrorMessageXML();
            }

            $rowProject = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);

            $count = count($shop_arr);

            for ($i = 0; $i < $count; $i++) {
                $shop = isset($shop_arr[$i]) ? $shop_arr[$i] : null;
           
                $location = isset($location_arr[$i]) ? $location_arr[$i] : null;
            
            
                $chkField = $shop;
                if ($chkField) {
                    $fa = array(
                        'renewal_id' => $renewal_id,
                        'shop' => $shop,
                      
                        'location' => $location,
                        'creation_date' => date('Y-m-d H:i:s')
                    );
            
                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'shop_renewal');
                    $result = $db->sql_query($insert);
                    $shop_renewal_id = $db->sql_nextid();
                }
            }
            
        

        return $validate->getSuccessMessageXML();
    }

  /**
     *
     */
    function getAddShopMultipleLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $shop_arr       = $fn->getPostParam('shop', array());
    
        $validate->resetErrorArray();

        $filterArray3 = array_filter($shop_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter shop";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');


        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $fa['ref_no'] = $this->getUpdateAddQuoteCode();
        $fa['article_three'] = "This Maintenance Contractor covers for the area occupied by the shops mentioned in contract. Others areas of the entire
        building are not included in this contract";
        $fa['article_six'] = "This contract is in 2 originals. Both parties shall hold an original respectively.Any changes to contract shall be mutually agreed and
        signed in 2 copies as addendums.";
        $fa['article_seven'] = "The second party shall submit invoice for 100% Advance payment after signing of contract. First party shall make payments
        with 7 days from submission of invoice.";

        
        $id = $fn->addRecord($fa);
        $projectRec = $fn->getRecordRowByID('renewal', 'renewal_id', $id);
        $quoteRec = $fn->getRecordByCondition('quote', "quote_id = '{$projectRec['quote_id']}'");

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
        $faQuote['quote_status']      = 'Awarded';
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by']       = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'quote_id', $quoteRec['quote_id']);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'quote_id', $quoteRec['quote_id']);
        $projRec    = $fn->getRecordRowByID('renewal', 'renewal_id', $id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['quote_id']             = $quoteRec['quote_id'];
        $faOrder['renewal_id']           = $id;
        $faOrder['company_id']           = $projRec['company_id'];
        $faOrder['contact_id']           = $projRec['contact_id'];
        $faOrder['project_type']         = $projRec['contract_type'];
        $faOrder['quote_title']          = $quoteRec['title'];
        $faOrder['cust_company_name']    = $companyRow['company_name'];
        $faOrder['cust_address1']        = $companyRow['address_flat'];
        $faOrder['cust_address2']        = $companyRow['address_street'];
        $faOrder['cust_address_country'] = $companyRow['address_country'];
        $faOrder['cust_address_po_code'] = $companyRow['address_po_code'];
        $faOrder['cust_email']           = $companyRow['email'];
        $faOrder['cust_phone']           = $companyRow['phone'];
        $faOrder['cust_fax']             = $companyRow['fax'];
        $faOrder['record_type']          = $projRec['contract_type'];

        if ($companyRow['address_flat'] != '') {
            $faOrder['shipping_first_name']      = $companyRow['company_name'];
            $faOrder['shipping_address1']        = $companyRow['address_flat'];
            $faOrder['shipping_address2']        = $companyRow['address_street'];
            $faOrder['shipping_address_country'] = $companyRow['address_country'];
            $faOrder['shipping_address_po_code'] = $companyRow['address_po_code'];
            $faOrder['shipping_email']           = $companyRow['email'];
            $faOrder['shipping_phone']           = $companyRow['phone'];
            $faOrder['shipping_fax']             = $companyRow['fax'];
        } else {
            $faOrder['shipping_first_name']      = $companyRow['company_name'];
            $faOrder['shipping_address1']        = $companyRow['billing_address_flat'];
            $faOrder['shipping_address2']        = $companyRow['billing_address_street'];
            $faOrder['shipping_address_country'] = $companyRow['billing_address_country'];
            $faOrder['shipping_address_po_code'] = $companyRow['billing_address_po_code'];
            $faOrder['shipping_email']           = $companyRow['billing_email'];
            $faOrder['shipping_phone']           = $companyRow['billing_phone'];
            $faOrder['shipping_fax']             = $companyRow['billing_fax'];
        }

        $faOrder['creation_date']             = date('Y-m-d H:i:s');
        $faOrder['created_by']                = $fn->getSessionParam('userName');
        $faOrder['order_status']              = 'New';
        $faOrder['order_date']                = date('Y-m-d');

            $faOrder['start_date']            = $projRec['start_date'];
            $faOrder['end_date']              = $projRec['end_date'];
    

        //check if the order record already exist or not
        $orderRec = $fn->getRecordByCondition('order', "renewal_id = '{$id}'");
        if(is_array($orderRec)){
            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($faOrder, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($faOrder, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

        /* Creation of Order Item record */
        $SQLSelect = "
        SELECT qi.*
        FROM quote_items qi
        WHERE qi.quote_id = '{$quoteRec['quote_id']}'
        ORDER BY qi.quote_items_id ASC
        ";
        $resultSelect = $db->sql_query($SQLSelect);
        while ($row = $db->sql_fetchrow($resultSelect)) {
            $faOi = array();
            $faOi['part_no']          = $row['part_no'];
            $faOi['item_title']       = $row['title'];
            $faOi['qty']              = $row['quantity'];
            $faOi['unit']             = $row['unit'];
            $faOi['unit_price']       = $row['amount'];
            $faOi['description']      = $row['description'];
            $faOi['remarks']          = $row['remarks'];
            $faOi['record_id']        = $row['quote_items_id'];
            $faOi['order_id']         = $order_id;
            $faOi['quote_id']         = $quoteRec['quote_id'];
            $faOi['drawing_number']   = $row['drawing_number'];
            $faOi['drawing_title']    = $row['drawing_title'];
            $faOi['drawing_revision'] = $row['drawing_revision'];

            $orderItemRec = $fn->getRecordByCondition('order_item', "record_id = '{$row['quote_items_id']}' AND order_id = {$order_id}");
            if(is_array($orderItemRec)){
                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($faOi, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $SQLOI = $dbUtil->getInsertSQLStringFromArray($faOi, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
        }

        $fn->returnAfterNewSave($id);
    }


    /**
     *
     */
    function getAddQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $renewal_id  = $fn->getReqParam('renewal_id');
        $rowProject  = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        $rowCompany  = $fn->getRecordRowByID('company', 'company_id', $rowProject['company_id']);
        $rowStaff  = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $fa = array();
        $fa['renewal_id']       = $renewal_id;
        $fa['schedule']         = 'New';
        $fa['schedule_date']       = date('Y-m-d');
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'service_renewal');
        $service_renewal_id = $fn->addRecord($fa, 'service_renewal');

        $quoteRows = $fn->getRecordCount('service_renewal', "renewal_id = {$renewal_id}");

        return $quoteRows."_".$service_renewal_id;   
    }

    function getEditForQuoteSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $renewal_id                         = $fn->getReqParam('renewal_id');
        $service_renewal_id                         = $fn->getReqParam('service_renewal_id');

        $service_due                           = $fn->getReqParam('service_due');
        $remarks                              = $fn->getPostParam('remarks');
        $schedule_date                    = $fn->getPostParam('schedule_date');
        $schedule                    = $fn->getPostParam('schedule');
        $actual_date                    = $fn->getPostParam('actual_date');


        if (!$this->getEditForQuoteValidate()){
            return $validate->getErrorMessageXML();
        }
   

        $fa = array();
        $fa['renewal_id']                    = $renewal_id;
        $fa['service_due']                    = $service_due;
        $fa['schedule_date']                         = $schedule_date;
        $fa['actual_date']                         = $actual_date;
        $fa['schedule']                         = $schedule;
        $fa['remarks']                          = $remarks;

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'service_renewal');

        $whereCondition = "WHERE service_renewal_id = {$service_renewal_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "service_renewal", $whereCondition);
        $db->sql_query($SQL);

   
        return $validate->getSuccessMessageXML($service_due);
    }


    function getEditForShopSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $renewal_id                         = $fn->getReqParam('renewal_id');
        $shop_renewal_id                         = $fn->getReqParam('shop_renewal_id');

        $shop                           = $fn->getReqParam('shop');
        $location                              = $fn->getPostParam('location');
      


        if (!$this->getEditForQuoteValidate()){
            return $validate->getErrorMessageXML();
        }
   

        $fa = array();
        $fa['renewal_id']                    = $renewal_id;
        $fa['shop']                    = $shop;
      
        $fa['location']                          = $location;

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'shop_renewal');

        $whereCondition = "WHERE shop_renewal_id = {$shop_renewal_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "shop_renewal", $whereCondition);
        $db->sql_query($SQL);

   
        return $validate->getSuccessMessageXML($shop);
    }

     /**
     *
     */
    function getEditForQuoteValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');


        $validate->resetErrorArray();
        //$validate->validateData('timesheet_type', 'Please select Timesheet Type');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
     /**
     *
     */
    function getUpdateAddQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextQuoteCode = $fn->getSettingsValueByKey("nextREFCode");

        if($nextQuoteCode < 10){
            $quoteCode = $fn->getSettingsValueByKey('refCodePrefix') . $nextQuoteCode . '-' . date("Y");
        }
        else if($nextQuoteCode < 99){
            $quoteCode = $fn->getSettingsValueByKey('refCodePrefix'). $nextQuoteCode . '-' . date("Y");
        }
        else if($nextQuoteCode > 99 || $nextOppCode < 999){
            $quoteCode = $fn->getSettingsValueByKey('refCodePrefix') . $nextQuoteCode . '-' . date("Y");
        }
        else{
            $quoteCode = $fn->getSettingsValueByKey('refCodePrefix')  . $nextQuoteCode . '-' . date("Y");
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRefCode'";
        $result = $db->sql_query($SQL);

        return $quoteCode;
    }

     /**
     *
     */
    function getNewCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $json  = array();

        $SQL = "
        SELECT company_id
              ,company_name
        FROM company
        ORDER BY company_id DESC
        ";
        $result   = $db->sql_query($SQL);

        //$json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['company_id'], "caption" => $row['company_name']);
        }

        return json_encode($json);
    }
    /**
     *
     */
    function getEditValidate() {
		 $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

       // $validate->validateData('project_id', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
      
    }

     //function getEditPortalValidate() {
       // return $this->getNewValidate();
   // }

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
    function getaddMonthly() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');

        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET monthly = '1'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

     /**
     *
     */
    function getdeleteMonthly() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');

        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET monthly = '0'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

     /**
     *
     */
    function getaddQuaterly() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');

        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET quaterly = '1'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

     /**
     *
     */
    function getdeleteQuaterly() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');

        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET quaterly = '0'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

    /**
     *
     */
    function getaddAnually() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');

        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET annually = '1'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

     /**
     *
     */
    function getdeleteAnually() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');

        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET annually = '0'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }


     /**
     *
     */
    function getaddRemarks() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $renewal_chechlist_history_id     = $fn->getReqParam('renewal_chechlist_history_id');
        $renewal_id     = $fn->getReqParam('renewal_id');
                $remarks     = $fn->getReqParam('remarks');


        $sqlUpdate = "
        UPDATE `renewal_chechlist_history` SET remarks = '{$remarks}'
        WHERE renewal_chechlist_history_id = {$renewal_chechlist_history_id}
        AND renewal_id = {$renewal_id}

        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

       
        $fa = $fn->addToFieldsArray($fa, 'renewal_id');
        $fa = $fn->addToFieldsArray($fa, 'date');
       
        $fa = $fn->addToFieldsArray($fa, 'completed_by');
        $fa = $fn->addToFieldsArray($fa, 'time');
              $fa = $fn->addToFieldsArray($fa, 'store');
  				$fa = $fn->addToFieldsArray($fa, 'service_included');
               $fa = $fn->addToFieldsArray($fa, 'service_type');
               $fa = $fn->addToFieldsArray($fa, 'contract_type');
               $fa = $fn->addToFieldsArray($fa, 'ref_no');
               $fa = $fn->addToFieldsArray($fa, 'start_date');
               $fa = $fn->addToFieldsArray($fa, 'end_date');
               $fa = $fn->addToFieldsArray($fa, 'shop_mention');
               $fa = $fn->addToFieldsArray($fa, 'article_five_content');
               $fa = $fn->addToFieldsArray($fa, 'behalf_party');
               $fa = $fn->addToFieldsArray($fa, 'contact_name');
               $fa = $fn->addToFieldsArray($fa, 'mobile');
               $fa = $fn->addToFieldsArray($fa, 'contact_name2');
               $fa = $fn->addToFieldsArray($fa, 'mobile2');
               $fa = $fn->addToFieldsArray($fa, 'contact_name3');
               $fa = $fn->addToFieldsArray($fa, 'mobile3');
               $fa = $fn->addToFieldsArray($fa, 'willing_to_maintain');
               $fa = $fn->addToFieldsArray($fa, 'apply_digital_signature');
               $fa = $fn->addToFieldsArray($fa, 'signature_name');
               $fa = $fn->addToFieldsArray($fa, 'company_id');
                $fa = $fn->addToFieldsArray($fa, 'price_visit');
                $fa = $fn->addToFieldsArray($fa, 'mall');
                $fa = $fn->addToFieldsArray($fa, 'shop');
                $fa = $fn->addToFieldsArray($fa, 'valid_period');
                $fa = $fn->addToFieldsArray($fa, 'pay_machine');
                  $fa = $fn->addToFieldsArray($fa, 'service');     
                  $fa = $fn->addToFieldsArray($fa, 'contact_id');  
                  $fa = $fn->addToFieldsArray($fa, 'location');  
                  $fa = $fn->addToFieldsArray($fa, 'article_six'); 
                  $fa = $fn->addToFieldsArray($fa, 'article_seven'); 
                  $fa = $fn->addToFieldsArray($fa, 'article_three'); 
                  $fa = $fn->addToFieldsArray($fa, 'renewal_due');
                  $fa = $fn->addToFieldsArray($fa, 'value');
                  $fa = $fn->addToFieldsArray($fa, 'service_due');
                  $fa = $fn->addToFieldsArray($fa, 'contract_value');
                  $fa = $fn->addToFieldsArray($fa, 'email');
                  $fa = $fn->addToFieldsArray($fa, 'reference_quotation');
                  $fa = $fn->addToFieldsArray($fa, 'terms_of_payment');
                  $fa = $fn->addToFieldsArray($fa, 'payment_status');
                  
        return $fa;
    }

   /**
     *
     */
    function getActualChargeSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $vehicle_id = $fn->getPostParam('vehicle_id');
       $vehicle_fuel_id = $fn->getPostParam('vehicle_fuel_id');
        $date = $fn->getPostParam('date');
        $amount = $fn->getPostParam('amount');
        $liters = $fn->getPostParam('liters');

        if (!$this->getActualChargeValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['vehicle_fuel_id']    = $vehicle_fuel_id;
        $fa['vehicle_id']    = $vehicle_id;
        $fa['date']    = $date;
        $fa['amount']    = $amount;
        $fa['liters']    = $liters;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'vehicle_fuel');

        $fn->addRecord($fa, 'vehicle_fuel');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getActualChargeValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $amount = $fn->getPostParam('amount');

        $validate->resetErrorArray();

        if ($amount == 0 || $amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Amount";
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
    function getRenewalDateSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $vehicle_id = $fn->getPostParam('vehicle_id');
       $vehicle_insurance_id = $fn->getPostParam('vehicle_insurance_id');
        $insurance_date = $fn->getPostParam('insurance_date');
        $insurance_amount = $fn->getPostParam('insurance_amount');
        $renewal_date = $fn->getPostParam('renewal_date');

        if (!$this->getRenewalDateValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['vehicle_insurance_id']    = $vehicle_insurance_id;
        $fa['vehicle_id']    = $vehicle_id;
        $fa['insurance_date']    = $insurance_date;
        $fa['insurance_amount']    = $insurance_amount;
        $fa['renewal_date']    = $renewal_date;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'vehicle_insurance');

        $fn->addRecord($fa, 'vehicle_insurance');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getRenewalDateValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

         $insurance_amount = $fn->getPostParam('insurance_amount');

        $validate->resetErrorArray();

        if ($insurance_amount == 0 || $insurance_amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Insurance Amount";
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
    function getServiceSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $vehicle_id = $fn->getPostParam('vehicle_id');
       $vehicle_service_id = $fn->getPostParam('vehicle_service_id');
        $date = $fn->getPostParam('date');
        $amount = $fn->getPostParam('amount');
        $description = $fn->getPostParam('description');

        if (!$this->getServiceValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['vehicle_service_id']    = $vehicle_service_id;
        $fa['vehicle_id']    = $vehicle_id;
        $fa['date']    = $date;
        $fa['amount']    = $amount;
        $fa['description']    = $description;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'vehicle_service');

        $fn->addRecord($fa, 'vehicle_service');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getServiceValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $amount = $fn->getPostParam('amount');

        $validate->resetErrorArray();

        if ($amount == 0 || $amount == ''){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Amount";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
   /* function getprojectJsonByComId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $project_id = $fn->getReqParam('project_id', '', true);

        $json  = array();

        if ($project_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getContactsByCompanySQL($project_id);
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['project_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }*/

    /**
     *
     */
    function getDeleteShopRenewal() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $renewal_id = $fn->getReqParam('renewal_id');
        $shop_renewal_id = $fn->getReqParam('shop_renewal_id');

        $SQLDeletePOProduct = "
        DELETE FROM shop_renewal
        WHERE shop_renewal_id = '{$shop_renewal_id}'
        AND renewal_id = '{$renewal_id}'
        ";
        $resultDeletePOProduct  = $db->sql_query($SQLDeletePOProduct);
    }	

}
