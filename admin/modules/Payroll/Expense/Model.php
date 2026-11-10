<?
class CPL_Admin_Modules_Payroll_Expense_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL ="
        SELECT e.*
        ,s.company_name
        FROM expense e
        LEFT JOIN (supplier s) ON (s.supplier_id = e.company_id)
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
        
        $searchVar->mainTableAlias = 'e';

        $month         = date('m');
        $year          = date('Y');
        $expense_id    = $fn->getReqParam('expense_id');
        $start_date    = $fn->getReqParam('expense_date_1');
        $end_date      = $fn->getReqParam('expense_date_2');
        $type          = $fn->getReqParam('type');
        $group         = $fn->getReqParam('group');
        $sub_group     = $fn->getReqParam('sub_group');
        $current_month = $fn->getReqParam('current_month');
        $yearVal       = $fn->getReqParam('year');
        $site_id       = $fn->getReqParam('site_id');
        $source        = $fn->getReqParam('source');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.expense_id = {$tv['record_id']}";
        }

        if ($expense_id != "") {
            $searchVar->sqlSearchVar[] = "e.expense_id = '{$expense_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.expense_id = '{$tv['record_id']}'";
        } else {

            if ($start_date != "" && $end_date != "") {
                $searchVar->sqlSearchVar[] = "e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else {

            /*if ($current_month != "") {
                $search_form = date('Y-'.$current_month.'-01');
                $search_to   = date("Y-m-t", strtotime($search_form));
                $searchVar->sqlSearchVar[] = "(e.date BETWEEN '{$search_form}' AND '{$search_to}')";
            }else {
                $search_form = date('Y-m-01');
                $search_to   = date('Y-m-t');
                $searchVar->sqlSearchVar[] = "(e.date BETWEEN '{$search_form}' AND '{$search_to}')";
            }*/

                /*if ($current_month == '' && $yearVal == ''){
                    $start_date = $year . '-' . $month . '-' . '01';
                    $end_date = $year . '-' . $month . '-' . '31';
                    $searchVar->sqlSearchVar[] = "e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
                }

                if ($current_month != '') {
                    $searchVar->sqlSearchVar[] = "DATE_FORMAT(e.date, '%m') = '{$current_month}'" ;
                }
                if ($yearVal != '') {
                    $searchVar->sqlSearchVar[] = "DATE_FORMAT(e.date, '%Y') = '{$yearVal}'" ;
                }*/
            }

            if ($type != "") {
                $searchVar->sqlSearchVar[] = "e.type = '{$type}'";
            }

            if ($group != "") {
                $searchVar->sqlSearchVar[] = "e.group = '{$group}'";
            }

            if ($sub_group != "") {
                $searchVar->sqlSearchVar[] = "e.sub_group = '{$sub_group}'";
            }

            if ($source != "") {
                $searchVar->sqlSearchVar[] = "e.source = '{$source}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    e.description LIKE '%{$tv['keyword']}%'
                 OR e.invoice_code LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "e.expense_id desc, e.date DESC";
        }

    }

    /**
    *
    */
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $group = '';
        $type = '';

        $new_company = $fn->getPostParam('new_company');
        $validate->resetErrorArray();
        $validate->validateData('group', 'Please select');
        $validate->validateData('amount', 'Please enter');
        $validate->validateData('description', 'Please enter');

        $invoice_code = $fn->getPostParam('invoice_code', '', true);
        $invoice_code_other = $fn->getPostParam('invoice_code_other', '', true);
        $group = $fn->getPostParam('group', '', true);
        if ($group) {
            $groupArr  = explode("_", $group);
            $group = $groupArr[0];
            $type = $groupArr[1];
        }

        if($new_company == 0 || $new_company == "") {
            $validate->validateData("existing_company_name", "Please Enter Company Name");
        } else {
            $validate->validateData("new_company_name", "Please Enter Company Name");
        }

        $new_company_name = $fn->getPostParam('new_company_name', '', true);
        $rec = $fn->getRecordByCondition('supplier', "company_name = '{$new_company_name}' AND company_name != ''");
        if (is_array($rec)) {
            $validate->errorArray['new_company_name']['name'] = "new_company_name";
            $validate->errorArray['new_company_name']['msg']  = "Already exist";
        }

        $sub_group = $fn->getPostParam('sub_group', '', true);
        //$type = $fn->getPostParam('type', '', true);
        $invoice_code_expense1112 = $fn->getPostParam('invoice_code_expense1112', '', true);
        $invoice_code_expense = $fn->getPostParam('invoice_code_expense', '', true);

        $rec = $fn->getRecordByCondition('expense', "invoice_code = '{$invoice_code}' AND invoice_code != '' AND type = '{$type}'");
        if (is_array($rec)) {
            $validate->errorArray['invoice_code']['name'] = "invoice_code";
            $validate->errorArray['invoice_code']['msg']  = "Already exist";
        }
        $rec = $fn->getRecordByCondition('expense', "invoice_code = '{$invoice_code_other}' AND invoice_code != '' AND type = '{$type}'");
        if (is_array($rec)) {
            $validate->errorArray['invoice_code']['name'] = "invoice_code_other";
            $validate->errorArray['invoice_code']['msg']  = "Already exist";
        }

        $rec1 = $fn->getRecordByCondition('expense', "invoice_code = '{$invoice_code_expense1112}' AND invoice_code != '' AND type = '{$type}'");
        if (is_array($rec1)) {
            $validate->errorArray['invoice_code_expense1112']['name'] = "invoice_code_expense1112";
            $validate->errorArray['invoice_code_expense1112']['msg']  = "Already exist";
        }

        $rec2 = $fn->getRecordByCondition('expense', "invoice_code = '{$invoice_code_expense}' AND invoice_code != '' AND type = '{$type}'");
        if (is_array($rec2)) {
            $validate->errorArray['invoice_code_expense']['name'] = "invoice_code_expense";
            $validate->errorArray['invoice_code_expense']['msg']  = "Already exist";
        }

        if ($type == 'Income') {
            $incomeGroup = $fn->getRecordRowByID('income_group', 'income_group_id', $group);
            $incomeSubGroup = $fn->getRecordRowByID('income_sub_group', 'income_sub_group_id', $sub_group);
            $group = $incomeGroup['title'];
            $subGroup = $incomeSubGroup['title'];

            if(($group == 'Revenue' && $subGroup == 'Sales') || ($group == 'REVENUE' && $subGroup == 'SALES')){
                $validate->validateData('invoice_date', 'Please select');
                $validate->validateData('invoice_code', 'Please enter');
                $validate->validateData('company_id', 'Please select');
            }
        } else {
            $current_date = date('Y-m-d');
            $po_date = $fn->getPostParam('po_date', '', true);

            $expenseGroup = $fn->getRecordRowByID('expense_group', 'expense_group_id', $group);
            $expenseSubGroup = $fn->getRecordRowByID('expense_sub_group', 'expense_sub_group_id', $sub_group);
            $group = $expenseGroup['title'];
            $subGroup = $expenseSubGroup['title'];

            if ($group == 'GENERAL EXPENSES' && $subGroup == 'SUPPLIER PAYMENT'){
                //$validate->validateData('po_date', 'Please select');
                //$validate->validateData('po_code', 'Please enter');
                $validate->validateData('supplier_id', 'Please select');
            }

            if ($po_date != '' && $po_date > $current_date) {
                $validate->errorArray['po_date']['name'] = "po_date";
                $validate->errorArray['po_date']['msg']  = 'Cannot be future date';
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');

        $fa = $this->getFields();

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id, 'list');
    }

    /**
     *
     */
    function getNewAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');

        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');
        $amount           = $fn->getPostParam('amount');
        $gst              = $fn->getPostParam('gst');
        $group_temp       = $fn->getPostParam('group', '', true);
        $new_company      = $fn->getPostParam('new_company');
        $new_company_name      = $fn->getPostParam('new_company_name');
        $supplier_id      = $fn->getPostParam('supplier_id');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        if($new_company_name != ''){
            $faSupplier = array();
            $faSupplier['company_name']    = $new_company_name;
            $faSupplier['supplier_type']   = 'Supplier Accounts';
            $faSupplier['creation_date']   = date("Y-m-d H:i:s");

            $insertSQL = $dbUtil->getInsertSQLStringFromArray($faSupplier, 'supplier');
            $resultSQL = $db->sql_query($insertSQL);
            $supplier_id = $db->sql_nextid();
        }

        $fa = $this->getFields();
        $groupArr    = explode("_", $group_temp);
        $group       = $groupArr[0];
        $type        = $groupArr[1];
        $fa['group'] = $groupArr[0];
        $fa['type']  = $groupArr[1];

        if ($gst > 0) {
            $fa['gst_percentage'] = $cpCfg['cp.gstPercentage'];
            $fa['gst_amount']     = round(($amount*$cpCfg['cp.gstPercentage'])/100, 2);
        }
        $fa['payment_status'] = 'Due';
        $fa['company_id'] = $supplier_id;

        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $expense_id = $fn->getReqParam('expense_id');
        $type = $fn->getReqParam('type');
        $invoice_code = $fn->getPostParam('invoice_code');

        $validate->resetErrorArray();
        if ($invoice_code != '') {
            $rec = $fn->getRecordByCondition('expense', "invoice_code = '{$invoice_code}' AND expense_id != '{$expense_id}' AND type = '{$type}'");
            if (is_array($rec)) {
                $validate->errorArray['invoice_code']['name'] = "invoice_code";
                $validate->errorArray['invoice_code']['msg']  = "Already exist";
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

        /*
        $type = $fn->getReqParam('type');
        $group = $fn->getReqParam('group');
        $sub_group = $fn->getReqParam('sub_group');
        $invoice_code_expense = $fn->getPostParam('invoice_code_expense1112');
        $invoice_date1112 = $fn->getPostParam('invoice_date1112');
        */

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        /*
        if ($type == 'Expense' && $group == '2') {
            if ($sub_group == '11' || $sub_group == '12' || $sub_group == '22') {
                $fa['invoice_code']   = $invoice_code_expense;
                $fa['invoice_date']   = $invoice_date1112;
            }
        }
        */

        //print $fa['company_id'].'aaa';
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'group');
        $fa = $fn->addToFieldsArray($fa, 'sub_group');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'service_charge');
        $fa = $fn->addToFieldsArray($fa, 'gst');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'invoice_code');
        $fa = $fn->addToFieldsArray($fa, 'supplier_name');
        $fa = $fn->addToFieldsArray($fa, 'payment_status');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_payment');
        $fa = $fn->addToFieldsArray($fa, 'cheque_no');
        $fa = $fn->addToFieldsArray($fa, 'issued_date');
        $fa = $fn->addToFieldsArray($fa, 'bank');
        $fa = $fn->addToFieldsArray($fa, 'payment_cleared_date');
        $fa = $fn->addToFieldsArray($fa, 'new_company');
        /*
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'invoice_date');
        $fa = $fn->addToFieldsArray($fa, 'received_date');
        $fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'po_date');
        $fa = $fn->addToFieldsArray($fa, 'issued_date');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'supplier_id');
        $fa = $fn->addToFieldsArray($fa, 'supplier_gst');
        $fa = $fn->addToFieldsArray($fa, 'customer_name');
        $fa = $fn->addToFieldsArray($fa, 'customer_gst');
        */

        return $fa;
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
        $expense_id     = $fn->getReqParam('expense_id');

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
        $valuelist_name = "Group";

        $fa['group'] = $valuelist_value;

        $whereCondition = "WHERE expense_id = {$expense_id}";
        $sqlUpdate      = $dbUtil->getUpdateSQLStringFromArray($fa, "expense", $whereCondition);
        $resultUpdate   = $db->sql_query($sqlUpdate);

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
    function getSubgroupByGroupJSON(){
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rows = "";

        $group_id_temp = $fn->getReqParam('group_id');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $json  = array();
        
        if ($group_id_temp == ""){
            $json[] = array("value" => "", "caption" => "Please Select");
            return json_encode($json);
        } else {
            $grouparr = explode("_", $group_id_temp);
            $group_id = $grouparr[0];
            $type = $grouparr[1];
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        if($type == 'Income'){
            $SQL = "
            SELECT income_sub_group_id AS sub_groupId
                  ,title
            FROM income_sub_group 
            WHERE income_group_id = '{$group_id}'
            {$appendSql}
            ORDER BY title
            ";
            $result   = $db->sql_query($SQL);  
        } else {
            $SQL = "
            SELECT expense_sub_group_id AS sub_groupId
                  ,title
            FROM expense_sub_group 
            WHERE expense_group_id = '{$group_id}'
            {$appendSql}
            ORDER BY title
            ";
            $result   = $db->sql_query($SQL);              
        }


        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['sub_groupId'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getExpSubgroupByGroupJSON(){
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rows = "";

        $expense_group_id = $fn->getReqParam('expense_group_id');
        $group            = $fn->getReqParam('group');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $json  = array();
        
        if ($expense_group_id == ""){
            return json_encode($json);
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT expense_sub_group_id
              ,title
        FROM expense_sub_group 
        WHERE expense_group_id = '{$expense_group_id}'
        {$appendSql}
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['expense_sub_group_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getIncomeSubgroupByGroupJSON(){
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rows = "";

        $income_group_id  = $fn->getReqParam('income_group_id');
        $group            = $fn->getReqParam('group');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $json  = array();
        
        if ($income_group_id == ""){
            return json_encode($json);
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT income_sub_group_id
              ,title
        FROM income_sub_group 
        WHERE income_group_id = '{$income_group_id}'
        {$appendSql}
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['income_sub_group_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getGroupByTypeJSON(){
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rows = "";

        $type = $fn->getReqParam('type');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $json  = array();
        
        if ($type == ""){
            $json[] = array("value" => "", "caption" => "Please Select");
            return json_encode($json);
        }

        if($type == 'Income'){
            $SQL = "
            SELECT income_group_id AS Id
                  ,title
            FROM income_group 
            ORDER BY title
            ";
            $result   = $db->sql_query($SQL);  
        } else {
            $SQL = "
            SELECT expense_group_id AS Id
                  ,title
            FROM expense_group 
            ORDER BY title
            ";
            $result   = $db->sql_query($SQL);              
        }

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['Id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getCalculateTotalAmt() {
        $fn       = Zend_Registry::get('fn');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');
        $cpCfg       = Zend_Registry::get('cpCfg');

        $amount = $fn->getReqParam('amount');
        $gst = $fn->getReqParam('gst');

        if($amount == ''){
            $amount = 0;
        }

        if($gst == 1){
            $gst_amount = round(($amount*$cpCfg['cp.gstPercentage'])/100, 2);
            /*
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);
                $fraction = substr($fraction, 0, 2);
                $gst_amount = $integer . "." . $fraction;
            }
            */
            
            $totalAmt = number_format($amount + $gst_amount, 2);
        } else {
            $totalAmt = number_format($amount, 2);
        }

        return $totalAmt;      
    }

    /**
     *
     */
    function getCalculateTotalAmtWithServiceCharge() {
        $fn       = Zend_Registry::get('fn');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');
        $cpCfg       = Zend_Registry::get('cpCfg');

        $serviceCharge = $fn->getReqParam('serviceCharge');
        $amount = $fn->getReqParam('amount');
        $gst = $fn->getReqParam('gst');

        if ($amount == '') {
            $amount = 0;
        }

        if($serviceCharge == '') {
            $serviceCharge = 0;
        }

        if($gst == 1){
            $gst_amount = round(($amount*$cpCfg['cp.gstPercentage'])/100,2);            
            $totalAmt = number_format($amount + $serviceCharge + $gst_amount, 2);
        } else {
            $totalAmt = number_format($amount + $serviceCharge, 2);
        }

        return $totalAmt;
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $amount          = $fn->getPostParam('receipt_amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $date            = $fn->getPostParam('receipt_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $expense_id      = $fn->getReqParam('expense_id');

        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $amountUnformatted = str_replace( ',', '', $amount);
        $fa = array();
        $fa['amount']         = $amountUnformatted;
        $fa['record_id']      = $expense_id;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = $date;
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();

        /* Update Expense Status */
        $row = $fn->getRecordRowByID('expense', 'expense_id', $expense_id);
        if($row['gst'] == 1){
            $gst_amount = round(($row['amount']*$row['gst_percentage'])/100, 2);
            /*
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);
                $fraction = substr($fraction, 0, 2);
                $gst_amount = $integer . "." . $fraction;
            }
            */

            $totalAmount = number_format($row['amount'] + $gst_amount, 2);
        } else {
            $totalAmount = number_format($row['amount'], 2);
        }

        $sqlReceipt = "
        SELECT SUM(amount) AS total_amount_paid FROM receipt
        WHERE record_id = '{$expense_id}'
          AND receipt_status = 'Paid'
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $rowReceipt = $db->sql_fetchrow($resultReceipt);
        $total_amount_paid = $rowReceipt['total_amount_paid'];
        
        $totalAmountUnFormatted = str_replace( ',', '', $totalAmount);
        $balance_amount_payable = number_format($totalAmountUnFormatted - $total_amount_paid, 2);

        if ($balance_amount_payable > 0) {
            $status = 'Partial Payment';
        } else {
            $status = 'Paid';
        }
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        /*
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($faExpense, 'expense', "WHERE expense_id = {$expense_id}");
        $result = $db->sql_query($updateSQL);
        */

        $updateSQL = "
        UPDATE expense
        SET payment_status = '{$status}'
           ,modification_date = '{$modification_date}'
           ,modified_by = '{$modified_by}'
        WHERE expense_id = {$expense_id}
        ";
        $result = $db->sql_query($updateSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        $max_receipt_amount = $fn->getReqParam('max_receipt_amount');
        $receipt_amount     = $fn->getPostParam('receipt_amount');
        $mode_of_payment    = $fn->getPostParam('mode_of_payment');
        $cheque_date        = $fn->getPostParam('cheque_date');
        $date               = $fn->getPostParam('receipt_date');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $current_date = date('Y-m-d');

        $validate->resetErrorArray();
        $validate->validateData('receipt_date' , 'Please select Date');
        $validate->validateData('receipt_amount' , 'Please enter Amount');
        $validate->validateData('mode_of_payment' , 'Please select Mode of Payment');

        if ($receipt_amount < '1') {
            $validate->errorArray['receipt_amount']['name'] = "receipt_amount";
            $validate->errorArray['receipt_amount']['msg']  = 'Enter Correct Amount';
        }

        if ($receipt_amount > $max_receipt_amount) {
            $validate->errorArray['receipt_amount']['name'] = "receipt_amount";
            $validate->errorArray['receipt_amount']['msg']  = 'Enter Amount not more than ' . $max_receipt_amount;
        }

        if ($date != '' && $date > $current_date) {
            $validate->errorArray['receipt_date']['name'] = "receipt_date";
            $validate->errorArray['receipt_date']['msg']  = 'Cannot be future date';
        }

        if ($mode_of_payment == 'Cheque') {
            $validate->validateData('cheque_no' , 'Please enter Cheque No');
            $validate->validateData('cheque_date' , 'Please select Cheque Date');
            $validate->validateData('bank_name' , 'Please select Bank');

            if ($cheque_date != '' && $cheque_date > $current_date) {
                $validate->errorArray['cheque_date']['name'] = "cheque_date";
                $validate->errorArray['cheque_date']['msg']  = 'Cannot be future date';
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
    function getGeneratePaymentFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $amount          = $fn->getPostParam('receipt_amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $date            = $fn->getPostParam('receipt_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $expense_id      = $fn->getReqParam('expense_id');

        if (!$this->getGeneratePaymentFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $amountUnformatted = str_replace( ',', '', $amount);
        $fa = array();
        $fa['amount']         = $amountUnformatted;
        $fa['record_id']      = $expense_id;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = $date;
        $fa['payment_status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'payment');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();

        /* Update Expense Status */
        $row = $fn->getRecordRowByID('expense', 'expense_id', $expense_id);
        if($row['gst'] == 1){
            $gst_amount = round(($row['amount']*$row['gst_percentage'])/100, 2);
            /*
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1));
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);
                $fraction = substr($fraction, 0, 2);
                $gst_amount = $integer . "." . $fraction;
            }
            */

            $totalAmount = number_format($row['amount'] + $gst_amount, 2);
        } else {
            $totalAmount = number_format($row['amount'], 2);
        }

        $sqlReceipt = "
        SELECT SUM(amount) AS total_amount_paid FROM payment
        WHERE record_id = '{$expense_id}'
          AND payment_status = 'Paid'
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $rowReceipt = $db->sql_fetchrow($resultReceipt);
        $total_amount_paid = $rowReceipt['total_amount_paid'];
        
        $totalAmountUnFormatted = str_replace( ',', '', $totalAmount);
        $balance_amount_payable = number_format($totalAmountUnFormatted - $total_amount_paid, 2);

        $faExpense = array();
        if ($balance_amount_payable > 0) {
            $faExpense['payment_status'] = 'Partial Payment';
        } else {
            $faExpense['payment_status'] = 'Paid';
        }
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($faExpense, 'expense', "WHERE expense_id = {$expense_id}");
        $result = $db->sql_query($updateSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGeneratePaymentFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $max_receipt_amount = $fn->getReqParam('max_receipt_amount');
        $receipt_amount     = $fn->getPostParam('receipt_amount');
        $mode_of_payment    = $fn->getPostParam('mode_of_payment');
        $cheque_date        = $fn->getPostParam('cheque_date');
        $date               = $fn->getPostParam('receipt_date');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $current_date = date('Y-m-d');

        $validate->resetErrorArray();
        $validate->validateData('receipt_date' , 'Please select Date');
        $validate->validateData('receipt_amount', 'Please enter Amount');
        $validate->validateData('mode_of_payment', 'Please select Mode of Payment');

        if ($receipt_amount < '1') {
            $validate->errorArray['receipt_amount']['name'] = "receipt_amount";
            $validate->errorArray['receipt_amount']['msg']  = 'Enter Correct Amount';
        }

        if ($receipt_amount > $max_receipt_amount) {
            $validate->errorArray['receipt_amount']['name'] = "receipt_amount";
            $validate->errorArray['receipt_amount']['msg']  = 'Enter Amount not more than ' . $max_receipt_amount;
        }

        if ($date != '' && $date > $current_date) {
            $validate->errorArray['receipt_date']['name'] = "receipt_date";
            $validate->errorArray['receipt_date']['msg']  = 'Cannot be future date';
        }

        if ($mode_of_payment == 'Cheque') {
            $validate->validateData('cheque_no' , 'Please enter Cheque No');
            $validate->validateData('cheque_date' , 'Please select Cheque Date');
            $validate->validateData('bank_name' , 'Please select Bank');

            if ($cheque_date != '' && $cheque_date > $current_date) {
                $validate->errorArray['cheque_date']['name'] = "cheque_date";
                $validate->errorArray['cheque_date']['msg']  = 'Cannot be future date';
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
    function getEditReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $remarks         = $fn->getPostParam('remarks');
        $date            = $fn->getPostParam('receipt_date');
        $receipt_id      = $fn->getReqParam('receipt_id');

        if (!$this->getEditReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['remarks']              = $remarks;
        $fa['date']                 = $date;
        $fa['modification_date']    = date("Y-m-d H:i:s");
        $fa['modified_by']          = $fn->getSessionParam('userName');

        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'receipt', "WHERE receipt_id = {$receipt_id}");
        $result = $db->sql_query($updateSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditReceiptFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('receipt_date', 'Please select Date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSearchSupplierName() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $companyName = $extractor[0];

        $SQL = "
        SELECT s.company_name AS value
              ,s.company_name AS label
              ,s.supplier_id AS id
        FROM supplier s
        WHERE s.company_name LIKE '%{$companyName}%'
          AND s.supplier_type = 'Supplier Accounts'
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }
}