<?
class CPL_Admin_Widgets_EnggCrm_ProjectWorkOrder_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;        
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectQuote');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     * Line Item Edit Form Submit
     */
    function getEditForWorkOrderSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $project_id            = $fn->getReqParam('project_id');
        $sub_con_work_order_id = $fn->getReqParam('sub_con_work_order_id');
        $work_order            = $fn->getPostParam('work_order');
        $work_order_date       = $fn->getPostParam('work_order_date');
        $work_order_due_date   = $fn->getPostParam('work_order_due_date');
        $completed_date        = $fn->getPostParam('completed_date');
        $status                = $fn->getPostParam('status');
        $project_location      = $fn->getPostParam('project_location');
        $project_reference     = $fn->getPostParam('project_reference');
        $sub_con_id     = $fn->getPostParam('sub_con_id');
        $condition             = $fn->getPostParam('condition');
        $quote_date     = $fn->getPostParam('quote_date');
        $quote_reference     = $fn->getPostParam('quote_reference');

        if (!$this->getEditForWorkOrderValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['work_order']          = $work_order;
        $fa['work_order_date']     = $work_order_date;
        $fa['work_order_due_date'] = $work_order_due_date;
        $fa['completed_date']      = $completed_date;
        $fa['status']              = $status;
        $fa['project_location']    = $project_location;
        $fa['project_reference']   = $project_reference;
        $fa['quote_date']   = $quote_date;
        $fa['quote_reference']   = $quote_reference;
        $fa['sub_con_id']   = $sub_con_id;
        $fa['condition']   = $condition;

        $whereCondition = "WHERE sub_con_work_order_id = {$sub_con_work_order_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "sub_con_work_order", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditForWorkOrderValidate() {
        $fn = Zend_Registry::get('fn');
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
    function getAddWOFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $project_id  = $fn->getReqParam('project_id');
        $rowProject       = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowCompany       = $fn->getRecordRowByID('company', 'company_id', $rowProject['company_id']);

        $fa = array();
        $fa['project_id']       = $project_id;
        $fa['condition']        = $fn->getSettingsValueByKey("workOrderTermsAndCondition");
        $fa['status']           = 'New';
        $fa['work_order_date']  = date('Y-m-d');
        $fa['sub_con_worker_code'] = $this->getUpdateAddWOCode();
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'sub_con_work_order');

        $fn->addRecord($fa, 'sub_con_work_order');           
    }

    /**
     * 
     */
    function getEditWOLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Line Item Edit Form Submit
     */
    function getEditWOLineItemSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getEditWOLineItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $work_order_line_items_id  = $fn->getReqParam('work_order_line_items_id');
                         
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'unit_rate');
        $fa = $fn->addToFieldsArray($fa, 'quantity');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'work_order_line_items');

        $whereCondition = "WHERE work_order_line_items_id = {$work_order_line_items_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "work_order_line_items", $whereCondition);
        $db->sql_query($SQL);

        $quote_item_rec = $fn->getRecordRowByID('work_order_line_items', 'work_order_line_items_id', $work_order_line_items_id);
        $faQuote = array();
        $faQuote = $fn->addModificationDetailsToFieldsArray($faQuote, 'sub_con_work_order');
        $whereCondition = "WHERE sub_con_work_order_id = {$quote_item_rec['sub_con_work_order_id']}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($faQuote, 'sub_con_work_order', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getDeleteWOLineItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $work_order_line_items_id    = $fn->getReqParam('work_order_line_items_id');

        $deleteSQL    = "
        DELETE FROM work_order_line_items
        WHERE work_order_line_items_id = '{$work_order_line_items_id}'
        ";
        $result = $db->sql_query($deleteSQL);

    }

    /**
     *
     */
    function getUpdateAddWOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextWOCode = $fn->getSettingsValueByKey("nextWOCode");

        if($nextWOCode < 10){
            $wOCode = $fn->getSettingsValueByKey('wOCodePrefix') . '000' . $nextWOCode.'/'.date('y');
        }
        else if($nextWOCode < 99){
            $wOCode = $fn->getSettingsValueByKey('wOCodePrefix') . '00' . $nextWOCode.'/'.date('y');
        }
        else if($nextWOCode > 99 || $nextOppCode < 999){
            $wOCode = $fn->getSettingsValueByKey('wOCodePrefix') . '0' . $nextWOCode.'/'.date('y');
        }
        else{
            $wOCode = $fn->getSettingsValueByKey('wOCodePrefix')  . $nextWOCode.'/'.date('y');
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextWOCode'";
        $result = $db->sql_query($SQL);

        return $wOCode;
    }    

    /**
     *
     */
    function getAddMultipleWOItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $amount_arr      = $fn->getPostParam('amount', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($amount_arr);
        if (count($filterArray3) == 0){
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
    function getAddMultipleWOItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id        = $fn->getPostParam('project_id');
        $sub_con_work_order_id = $fn->getPostParam('sub_con_work_order_id');
        $description_arr   = $fn->getPostParam('description', array());
        $amount_arr        = $fn->getPostParam('amount', array());
        $unit_rate_arr        = $fn->getPostParam('unit_rate', array());
        $quantity_arr      = $fn->getPostParam('quantity', array());

        if (!$this->getAddMultipleWOItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $count = count($description_arr);

        for ($i= 0; $i < $count; $i++) {
            $description   = $description_arr[$i];
            $amount        = $amount_arr[$i];
            $unit_rate        = $unit_rate_arr[$i];
            $quantity      = $quantity_arr[$i];

            $chkField      = $description;

            if ($chkField) {
                $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

                $fa = array();
                $fa['project_id']       = $project_id;
                $fa['sub_con_work_order_id'] = $sub_con_work_order_id;
                $fa['unit_rate']           = $unit_rate;
                $fa['amount']           = $amount;
                $fa['quantity']         = $quantity;
                $fa['description']      = $description;
                $fa['creation_date']    = date('Y-m-d H:i:s');
                $fa['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'work_order_line_items');
                $result = $db->sql_query($insert);
                $quote_items_id = $db->sql_nextid();

                $faQuote = array();
                $fa = $fn->addModificationDetailsToFieldsArray($faQuote, 'sub_con_work_order');
                $whereCondition = "WHERE sub_con_work_order_id = {$sub_con_work_order_id}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'sub_con_work_order', $whereCondition);
                $db->sql_query($SQL);
            }
        }

        return $validate->getSuccessMessageXML();
    }
}