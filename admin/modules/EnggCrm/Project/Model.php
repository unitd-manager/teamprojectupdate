<?
class CPL_Admin_Modules_EnggCrm_Project_Model extends CP_Admin_Modules_EnggCrm_Project_Model
{
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'p';

        $title              = $fn->getReqParam('title');
        $category           = $fn->getReqParam('category');
        $company_id         = $fn->getReqParam('company_id');
        $contact_id         = $fn->getReqParam('contact_id');
        $project_id         = $fn->getReqParam('project_id');
        $service_id         = $fn->getReqParam('service_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $yearMonthFinish    = $fn->getReqParam('yearMonthFinish');
        $project_month      = $fn->getReqParam('project_month');
        $start_date1        = $fn->getReqParam('start_date_1');
        $start_date2        = $fn->getReqParam('start_date_2');
        $end_date1          = $fn->getReqParam('end_date_1');
        $end_date2          = $fn->getReqParam('end_date_2');
        $client_type        = $fn->getReqParam('client_type');

        if ($project_id != "") {
            $searchVar->sqlSearchVar[] = "p.project_id = {$project_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.project_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.project_id');

            /*if ($_SESSION['userGroupName'] == 'Super Administrator' || $_SESSION['userGroupName'] == 'Administrator' ) {
            } else {
                $searchVar->sqlSearchVar[] = "p.project_id IN (SELECT ps.project_id FROM project_staff ps WHERE ps.staff_id = {$_SESSION['staff_id']})";
            }*/

            if ($title != '') {
                $searchVar->sqlSearchVar[] = "p.title LIKE '%{$title}%'";
            }

            if ($category != '') {
                $searchVar->sqlSearchVar[] = "p.category = '{$category}'";
            }

            if ($project_month != "") {
                $searchVar->sqlSearchVar[] = "p.paid_on  = '{$project_month}'";
            }

            if ($contact_id != "") {
                $searchVar->sqlSearchVar[] = "p.contact_id   = {$contact_id}";
            }

            if ($service_id != "") {
                $searchVar->sqlSearchVar[] = "p.service_id   = {$service_id}";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "p.status   = '{$tv['status']}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "p.company_id  = {$company_id}";
            }

            if ($client_type != '') {
                $searchVar->sqlSearchVar[] = "p.client_type = '{$client_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title          LIKE '%{$tv['keyword']}%'  OR
                    p.project_code   LIKE '%{$tv['keyword']}%'  OR
                    p.project_id     LIKE '%{$tv['keyword']}%'  OR
                    p.description    LIKE '%{$tv['keyword']}%'  OR
                    c.company_name   LIKE '%{$tv['keyword']}%'  OR
                    p.notes          LIKE '%{$tv['keyword']}%'  OR
                    p.quote_ref      LIKE '%{$tv['keyword']}%'  OR
                    ser.service_code LIKE '%{$tv['keyword']}%'  OR
                    ser.title        LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($yearMonthStart != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(start_date, '%Y-%m') = '{$yearMonthStart}'";
            }

            if ($yearMonthFinish != '' ) {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(estimated_finish_date, '%Y-%m') = '{$yearMonthFinish}'";
            }

            if ($start_date1 != "" && $start_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(p.start_date BETWEEN '{$start_date1}' AND '{$start_date2}')";
            }

            if ($end_date1 != "" && $end_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(p.estimated_finish_date BETWEEN '{$end_date1}' AND '{$end_date2}')";
            }
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            if ($tv['special_search'] == "Overrun") {
                $searchVar->sqlSearchVar[] = "(
                    ROUND(
                     (
                      (IF(ISNULL(p.used_inhouse),0, p.used_inhouse))  / ( p.project_value - IF(ISNULL(p.budget_third_party),0, p.budget_third_party) )
                     ) *100
                    )
                ) > 100
                ";
            }

            //------------------------------------------------------------------------//
            if ($cpCfg['m.enggCrm.project.defaultSQL'] != "" && $tv['searchDone'] == 0) {
                $searchVar->sqlSearchVar[] = $cpCfg['m.enggCrm.project.defaultSQL'];
            }

            if ($tv['status'] == '' && $tv['searchDone'] == 0){
                $searchVar->sqlSearchVar[] = $cpCfg['m.enggCrm.project.defaultSQL'];
            }
        }

        $searchVar->sortOrder = "p.project_code DESC";
    }

    /**
     * Generate order records from Project
     */
    function getGenerateOrderManpowerRecords(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id = $fn->getReqParam('project_id');
        $quote_id   = $fn->getReqParam('quote_id');

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
        $faQuote['quote_status']   = 'Order Raised';
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'quote_id', $quote_id);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $projRec    = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['quote_id']                  = $quote_id;
        $faOrder['project_id']                = $project_id;
        $faOrder['company_id']                = $projRec['company_id'];
        $faOrder['contact_id']                = $projRec['contact_id'];
        $faOrder['project_type']              = $projRec['category'];
        $faOrder['quote_title']               = $quoteRec['title'];
        $faOrder['cust_company_name']         = $companyRow['company_name'];
        $faOrder['cust_address1']             = $companyRow['billing_address_flat'];
        $faOrder['cust_address2']             = $companyRow['billing_address_street'];
        $faOrder['cust_address_country']      = $companyRow['billing_address_country'];
        $faOrder['cust_address_po_code']      = $companyRow['billing_address_po_code'];
        $faOrder['record_type']               = $projRec['category'];

        if ($companyRow['address_flat'] != '') {
            $faOrder['shipping_first_name']       = $companyRow['company_name'];
            $faOrder['shipping_address1']         = $companyRow['address_flat'];
            $faOrder['shipping_address2']         = $companyRow['address_street'];
            $faOrder['shipping_address_country']  = $companyRow['address_country'];
            $faOrder['shipping_address_po_code']  = $companyRow['address_po_code'];
        } else {
            $faOrder['shipping_first_name']       = $companyRow['company_name'];
            $faOrder['shipping_address1']         = $companyRow['billing_address_flat'];
            $faOrder['shipping_address2']         = $companyRow['billing_address_street'];
            $faOrder['shipping_address_country']  = $companyRow['billing_address_country'];
            $faOrder['shipping_address_po_code']  = $companyRow['billing_address_po_code'];
        }

        if ($projRec['category'] == 'Maintenance') {
            $faOrder['start_date']            = $projRec['start_date'];
            $faOrder['end_date']              = $projRec['estimated_finish_date'];
        }

        //check if the order record already exist or not
        $orderRec = $fn->getRecordByCondition('order', "project_id = '{$project_id}'");
        if(is_array($orderRec)){
            $faOrder['modification_date'] = date('Y-m-d H:i:s');
            $faOrder['modified_by']       = $fn->getSessionParam('userName');

            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($faOrder, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $faOrder['creation_date'] = date('Y-m-d H:i:s');
            $faOrder['created_by']    = $fn->getSessionParam('userName');
            $faOrder['order_status']  = 'New';
            $faOrder['order_date']    = date('Y-m-d');

            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($faOrder, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

        $employee_timesheet_financeRec = $fn->getRecordByCondition('employee_timesheet_finance',"project_id = {$project_id}");
        if(is_array($employee_timesheet_financeRec)){
            $SQLtimesheet_financeRec = "
            DELETE FROM employee_timesheet_finance
            WHERE project_id = {$project_id}
            ";
            $resulttimesheet_financeRec = $db->sql_query($SQLtimesheet_financeRec);
        }

        /* select and insert on Employee timesheet finance record */
        $SQLtimesheetFinance = "
        INSERT INTO employee_timesheet_finance (employee_timesheet_id, 
            project_id,
            employee_id,
            creation_date,
            modification_date,
            employee_hours,
            date,
            hourly_rate,
            month,
            year,
            description,
            employee_ot_hours,
            employee_ph_hours,
            ot_hourly_rate,
            ph_hourly_rate,
            admin_charges,
            transport_charges)
        SELECT *
        FROM employee_timesheet WHERE project_id = {$project_id}
        ";
        $resultTimesheetFinance = $db->sql_query($SQLtimesheetFinance);

        $SQLUPDATEtimesheet_financeRec = "
        UPDATE employee_timesheet_finance SET order_id = {$order_id}
        WHERE project_id = {$project_id}
        ";
        $resultUPDATEtimesheet_financeRec = $db->sql_query($SQLUPDATEtimesheet_financeRec);
        
        $orderItemRec = $fn->getRecordByCondition('order_item',"order_id = {$order_id}");
        if(is_array($orderItemRec)){
            $SQLDeleteOrderItem = "
            DELETE FROM order_item
            WHERE order_id = {$order_id}
            ";
            $resultDeleteOrderItem = $db->sql_query($SQLDeleteOrderItem);
        }

        $quoteItemsRec   = $fn->getRecordRowByID('quote_items', 'quote_id', $quoteRec['quote_id']);

        $SQL = "
        SELECT   SUM(et.employee_hours) AS totalHours
                ,et.hourly_rate
                ,et.ot_hourly_rate
                ,et.ph_hourly_rate
                ,et.employee_id
                ,et.admin_charges
                ,et.transport_charges
                ,SUM(et.employee_ot_hours) AS totalOTHours
                ,SUM(et.employee_ph_hours) AS totalPHHours
                ,DATE_FORMAT(et.date, '%M') AS Month
                ,DATE_FORMAT(et.date, '%m') AS month_req
                ,DATE_FORMAT(et.date, '%Y') AS year_req
                ,DATE_FORMAT(et.date, '%Y-%m') AS year_Months
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        GROUP BY DATE_FORMAT(et.date, '%Y-%m'), et.employee_id
        ";
        $result  = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $faOi = array();
            $faOi['item_title']        = $row['Month'];
            $faOi['qty']               = $row['hourly_rate'];
            $faOi['unit_price']        = $row['totalHours'];
            $faOi['month']             = $row['month_req'];
            $faOi['year']              = $row['year_req'];
            $faOi['record_id']         = $row['employee_id'];
            $faOi['ot_hourly_rate']    = $row['ot_hourly_rate'];
            $faOi['ph_hourly_rate']    = $row['ph_hourly_rate'];
            $faOi['employee_ot_hours'] = $row['totalOTHours'];
            $faOi['employee_ph_hours'] = $row['totalPHHours'];
            $faOi['admin_charges']     = $row['admin_charges'];
            $faOi['transport_charges'] = $row['transport_charges'];
            $faOi['order_id']          = $order_id;

            $SQLOI = $dbUtil->getInsertSQLStringFromArray($faOi, 'order_item');
            $resultOI = $db->sql_query($SQLOI);
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('company_id', 'Please select company');
        $validate->validateData('category', 'Please select category');

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status']                = "WIP";
        $fa['start_date']            = date("Y-m-d");
        $fa['estimated_finish_date'] = date('Y-m-d', strtotime("+30 days"));

        //-------------------------------------------------------//
        $fa['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
        $result = $db->sql_query($SQL);

        $fa['currency'] = $cpCfg['m.enggCrm.baseCurrency'];
                    
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('title', 'Please enter the title');
        //$validate->validateData('company_id', 'Please select company name');
        //$validate->validateData('category', 'Please select category');
        //$validate->validateData('start_date', 'Please select start date');
        //$validate->validateData('estimated_finish_date', 'Please select estimated finish date');
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

     /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'project_code');
        $fa = $fn->addToFieldsArray($fa, 'per_completed');
        $fa = $fn->addToFieldsArray($fa, 'actual_finish_date');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'quote_ref');
        $fa = $fn->addToFieldsArray($fa, 'paid_on');
        $fa = $fn->addToFieldsArray($fa, 'deposit_inv_ref');
        $fa = $fn->addToFieldsArray($fa, 'invoice');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'project_value');
        $fa = $fn->addToFieldsArray($fa, 'project_value_base');
        $fa = $fn->addToFieldsArray($fa, 'project_value_ref');
        $fa = $fn->addToFieldsArray($fa, 'project_commission');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'client_type');
        $fa = $fn->addToFieldsArray($fa, 'difficulty');
        $fa = $fn->addToFieldsArray($fa, 'target_left');
        $fa = $fn->addToFieldsArray($fa, 'estimated_finish_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'stage');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'product_id');

        $fa = $fn->addToFieldsArray($fa, 'date_of_incorporation');
        $fa = $fn->addToFieldsArray($fa, 'last_agm_date');
        $fa = $fn->addToFieldsArray($fa, 'last_ar_date');
        $fa = $fn->addToFieldsArray($fa, 'date_of_accounts_laid');
        $fa = $fn->addToFieldsArray($fa, 'next_agm_due_date');
        $fa = $fn->addToFieldsArray($fa, 'form_c_filed_date');
        $fa = $fn->addToFieldsArray($fa, 'form_cs');
        $fa = $fn->addToFieldsArray($fa, 'eci');
        $fa = $fn->addToFieldsArray($fa, 'client_po');
        $fa = $fn->addToFieldsArray($fa, 'po_ref_no');
        $fa = $fn->addToFieldsArray($fa, 'po_amount');
        

        return $fa;
    }


    /**
     * Add Timesheet Record submit in new window
     */
    function getAddMultipleTimesheetRecordsSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddMultipleTimesheetValidate()){
            return $validate->getErrorMessageXML();
        }

        $project_id                = $fn->getPostParam('project_id');
        $employee_id_arr           = $fn->getPostParam('TimesheetEmployee_id', array());
        $TimesheetRatePerHr_arr    = $fn->getPostParam('TimesheetRatePerHr', array());
        $TimesheetOTRatePerHr_arr  = $fn->getPostParam('TimesheetOTRatePerHr', array());
        $TimesheetPHRatePerHr_arr  = $fn->getPostParam('TimesheetPHRatePerHr', array());
        $yearSelected              = $fn->getPostParam('project_Time_year');
        $monthSelected             = $fn->getPostParam('project_Time_Month');
        $admin_charges_arr         = $fn->getPostParam('adminChargesEmployee', array());
        $transport_charges_arr     = $fn->getPostParam('transportChargesEmployee', array());

        $count = count($employee_id_arr);
        for ($i= 0; $i < $count; $i++) {

            $SQLInvoiceCheck = "
            SELECT i.start_date
                  ,i.end_date
            FROM `invoice` i
            LEFT JOIN `order` o ON(o.project_id = {$project_id})
            WHERE i.status != 'Cancelled'
            AND i.order_id = o.order_id
            AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$yearSelected}-{$monthSelected}'
            AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$yearSelected}-{$monthSelected}'
            ";
            $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
            $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
            if($numRowsInvoiceCheck > 0){
                $TimesheetRatePerHr_arr    = $fn->getReqParam('TimesheetRatePerHr', array());
                $TimesheetOTRatePerHr_arr  = $fn->getPostParam('TimesheetOTRatePerHr', array());
                $TimesheetPHRatePerHr_arr  = $fn->getPostParam('TimesheetPHRatePerHr', array());

            }

            $employee_id       = $employee_id_arr[$i];
            $RatePerHR         = $TimesheetRatePerHr_arr[$i];
            $OTRatePerHR       = $TimesheetOTRatePerHr_arr[$i];
            $PHRatePerHR       = $TimesheetPHRatePerHr_arr[$i];
            $admin_charges     = $admin_charges_arr[$i];
            $transport_charges = $transport_charges_arr[$i];
            
            $count2 = cal_days_in_month(CAL_GREGORIAN, $monthSelected, $yearSelected);
            $dayCount = 1;
            for ($j= 0; $j < $count2; $j++) {
                $timesheetDate = $yearSelected.'-'.$monthSelected.'-'.$dayCount;
                
                $SQLInvoice = "
                SELECT i.start_date
                      ,i.end_date
                FROM `invoice` i
                LEFT JOIN `order` o ON(o.project_id = {$project_id})
                WHERE i.status != 'Cancelled'
                AND i.order_id = o.order_id
                AND '{$timesheetDate}' BETWEEN i.start_date AND i.end_date
                ";
                $resultInvoice   = $db->sql_query($SQLInvoice);
                $numRowsInvoice  = $db->sql_numrows($resultInvoice);

                $disabledInput = '';
                if($numRowsInvoice > 0){

                }else{
                    ${"day$dayCount"."_arr"} = $fn->getPostParam("TimesheetDaysProject"."$dayCount", array());
                    ${"day$dayCount"} = ${"day$dayCount"."_arr"}[$i];

                    ${"dayOT$dayCount"."_arr"} = $fn->getPostParam("TimesheetDaysProjectOT"."$dayCount", array());
                    ${"dayOT$dayCount"} = ${"dayOT$dayCount"."_arr"}[$i];

                    ${"dayPH$dayCount"."_arr"} = $fn->getPostParam("TimesheetDaysProjectPH"."$dayCount", array());
                    ${"dayPH$dayCount"} = ${"dayPH$dayCount"."_arr"}[$i];

                    if ($employee_id != '') {
                        //if (${"day$dayCount"} != '' || ${"dayOT$dayCount"} != '' || ${"dayPH$dayCount"} != ''){

                            $SQLTimesheetExist ="
                            SELECT employee_hours
                                   ,employee_ot_hours
                                   ,employee_ph_hours
                                   ,hourly_rate
                                   ,ot_hourly_rate
                                   ,ph_hourly_rate
                                   ,admin_charges
                                   ,transport_charges
                            FROM `employee_timesheet`
                            WHERE project_id = {$project_id}
                            AND employee_id = {$employee_id}
                            AND date= '{$timesheetDate}'
                            "; 
                            $resultTimesheetExist  = $db->sql_query($SQLTimesheetExist);
                            $numRowsTimesheetExist = $db->sql_numrows($resultTimesheetExist);
                            $rowTimesheetExist = $db->sql_fetchrow($resultTimesheetExist);

                            if($numRowsTimesheetExist > 0){
                                if(${"day$dayCount"} != $rowTimesheetExist['employee_hours'] || ${"dayOT$dayCount"} != $rowTimesheetExist['employee_ot_hours'] || ${"dayPH$dayCount"} != $rowTimesheetExist['employee_ph_hours'] || $RatePerHR != $rowTimesheetExist['hourly_rate'] || $OTRatePerHR != $rowTimesheetExist['ot_hourly_rate'] || $PHRatePerHR != $rowTimesheetExist['ph_hourly_rate'] || $admin_charges != $rowTimesheetExist['admin_charges'] || $transport_charges != $rowTimesheetExist['transport_charges']){

                                    $fa1 = array();
                                    $fa1['date']              = $timesheetDate;

                                    //if (${"day$dayCount"} != ''){
                                        $fa1['employee_hours']    = ${"day$dayCount"};
                                    //}

                                    //if(${"dayOT$dayCount"} != ''){
                                        $fa1['employee_ot_hours'] = ${"dayOT$dayCount"};
                                    //}

                                    //if(${"dayPH$dayCount"} != ''){
                                        $fa1['employee_ph_hours'] = ${"dayPH$dayCount"};
                                    //}

                                    $fa1['employee_id']       = $employee_id;
                                    $fa1['hourly_rate']       = $RatePerHR;
                                    $fa1['ot_hourly_rate']    = $OTRatePerHR;
                                    $fa1['ph_hourly_rate']    = $PHRatePerHR;
                                    $fa1['admin_charges']     = $admin_charges; 
                                    $fa1['transport_charges'] = $transport_charges;
                                    $fa1['modification_date'] = date('Y-m-d H:i:s');
                                     
                                    $whereCondition = "WHERE project_id = {$project_id} AND employee_id = {$employee_id} AND date= '{$timesheetDate}'";
                                    $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'employee_timesheet', $whereCondition);
                                    $resultSQL =$db->sql_query($updateSQL);
                                }
                            }else{

                                if (${"day$dayCount"} != '' || ${"dayOT$dayCount"} != '' || ${"dayPH$dayCount"} != ''){
                                     $fa = array();
                                     $fa['project_id']        = $project_id;
                                     $fa['employee_id']       = $employee_id;

                                     if (${"day$dayCount"} != ''){
                                        $fa['employee_hours']    = ${"day$dayCount"};
                                     }

                                     if(${"dayOT$dayCount"} != ''){
                                         $fa['employee_ot_hours'] = ${"dayOT$dayCount"};
                                     }
                                    
                                     if(${"dayPH$dayCount"} != ''){
                                         $fa['employee_ph_hours'] = ${"dayPH$dayCount"};
                                     }

                                     $fa['date']              = $timesheetDate;
                                     $fa['hourly_rate']       = $RatePerHR;
                                     $fa['ot_hourly_rate']    = $OTRatePerHR;
                                     $fa['ph_hourly_rate']    = $PHRatePerHR;
                                     $fa['month']             = $monthSelected;
                                     $fa['creation_date']     = date('Y-m-d H:i:s');
                                     $fa['year']              = $yearSelected;
                                     $fa['admin_charges']     = $admin_charges; 
                                     $fa['transport_charges'] = $transport_charges;

                                     $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_timesheet');
                                     $result = $db->sql_query($insert);
                                }
                            }
                    }
                }
                $dayCount++;
            }

        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddQuoteColumn() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $title      = $fn->getReqParam('title');
        $project_id = $fn->getReqParam('project_id');

        $fa = array();
        $fa['title']         = $title;
        $fa['project_id']    = $project_id;

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_columns');
        $result = $db->sql_query($insert);

    }
    /**
     *
     */
    function getDeleteQuoteColumn() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $title      = $fn->getReqParam('title');
        $project_id = $fn->getReqParam('project_id');

        $deleteQuoteColumn = "
        DELETE FROM quote_columns
        WHERE project_id ={$project_id}
        AND title ='{$title}'";

        $result = $db->sql_query($deleteQuoteColumn);
    }

    /**
     *
     */
    function getAddRemoveEmployeeToProject() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $employee_id        = $fn->getReqParam('employee_id');
        $project_id         = $fn->getReqParam('project_id');
        $active_in_project  = $fn->getReqParam('active_in_project');

        $SQLUpdateEmployee = "
        UPDATE project_employee SET active_in_project = {$active_in_project}
        WHERE employee_id = {$employee_id}
        AND project_id = {$project_id}
        ";
        $resultUpdateEmployee = $db->sql_query($SQLUpdateEmployee);
    }

    /**
     *
     */
    function getEnggCrmProjectEnggCrmEmployeeLinkSQL($id) {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $projectRec = $fn->getRecordRowByID('project', 'project_id', $id);

        if ($projectRec['category'] == "Hourly Charge") {
            $SQLQuoteItem = "
            SELECT qi.title
            FROM quote_items qi
            WHERE qi.project_id = '{$id}'
            AND (qi.title != '' OR qi.title IS NOT NULL)
            ";
            $resultQuoteItem  = $db->sql_query($SQLQuoteItem);
            $numRowsQuoteItem = $db->sql_numrows($resultQuoteItem);

            if ($numRowsQuoteItem == 0) {
                $appendSQLDD = ",'Please Add Category For Quote' AS EmployeeCategory";
            } else {
                $appendSQLDD = ",CONCAT_WS('', '<select name=category_type project_employee_id=',pe.project_employee_id,' category_type=',REPLACE(pe.category_type, ' ', '_'),'><option value=>Select Category</option>{$dbUtil->getDropDownFromSQLCols1($db, $SQLQuoteItem, '')}</select>') AS EmployeeCategory";

                $appendSQLDD = ",CONCAT_WS(' ', '<div class=addChangeCategory project_employee_id=',pe.project_employee_id,'>choose</div><br/>', pe.category_type) AS EmployeeCategory";
            }

            return "
            SELECT a.employee_id
                  ,a.first_name AS title
                  {$appendSQLDD}
                  ,a.status
                  ,IF(pe.active_in_project = '1'
                    ,CONCAT_WS('', '<input type=checkbox checked class=project_employee_in name=active_in_project value=',a.employee_id,'>')
                    ,CONCAT_WS('', '<input type=checkbox class=project_employee_in name=active_in_project value=',a.employee_id,'>')
                    ) AS project_Select
            FROM `employee` a
            LEFT JOIN (project_employee pe) ON (pe.employee_id = a.employee_id)
            WHERE pe.project_id = {$id}
            ORDER BY title
           ";
        } else {
            return "
            SELECT a.employee_id
                  ,a.first_name AS title
                  ,''
                  ,a.status
                  ,IF(pe.active_in_project = '1'
                    ,CONCAT_WS('', '<input type=checkbox checked class=project_employee_in name=active_in_project value=',a.employee_id,'>')
                    ,CONCAT_WS('', '<input type=checkbox class=project_employee_in name=active_in_project value=',a.employee_id,'>')
                    ) AS project_Select
            FROM `employee` a
            LEFT JOIN (project_employee pe) ON (pe.employee_id = a.employee_id)
            WHERE pe.project_id = {$id}
            ORDER BY title
           ";
        }
    }

    /**
     * 
     */
    function getUpdateEmployeeCategoryType() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
    
        $project_employee_id = $fn->getReqParam('project_employee_id');
        $category_type       = $fn->getReqParam('category_type');

        $SQLUpdate = "
        UPDATE `project_employee` SET category_type = '{$category_type}'
        WHERE project_employee_id = '{$project_employee_id}'
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);
    }

    /**
     *
     */
    function getCreationModificationDetailsPopup() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $record_id  = $fn->getReqParam('record_id');
        $table_name = $fn->getReqParam('table_name');
        $field_name = $fn->getReqParam('field_name');

        $header = "
        <thead>
            <tr>
                <td>Created By/Creation Date</td>
                <td>Modified By/Modification Date</td>
            </tr>
        </thead>
        ";

        $SQL ="
        SELECT creation_date
              ,created_by
              ,modification_date
              ,modified_by
        FROM {$table_name}
        WHERE {$field_name} = {$record_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        if($row['modified_by'] != ""){
            $modified_by = "{$row['modified_by']}/{$row['modification_date']}";
        }else{
            $modified_by = "";
        }

        if($row['created_by'] != ""){
            $created_by = "{$row['created_by']}/{$row['creation_date']}";
        }else{
            $created_by = "";
        }

        $rows = "
        <tbody>
            <tr>
                <td>{$created_by}</td>
                <td>{$modified_by}</td>
            </tr>
        </tbody>
        ";

        $text = "
        <form id='creationModificationDetails' class='creationModificationDetails' method='post'>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *This function is working for convert to project
     */
    function getConvertToProject() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $site_id = $fn->getSessionParam('cp_site_id');

                $opportunity_id = $fn->getReqParam('opportunity_id');


         $SQlForQuote ="
        SELECT *
        FROM quote
        WHERE opportunity_id = {$opportunity_id}
     
        ";
        $resultForQuote = $db->sql_query($SQlForQuote);
        $rowForQuote = $db->sql_fetchrow($resultForQuote);



        $SQL = "
        SELECT *
        FROM opportunity
        WHERE opportunity_id = {$opportunity_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            if (!$site_id && isset($row['site_id'])) {
                $site_id = $row['site_id'];
            }
        if ($row['category'] == 'Contract') {
            $SQLRenewal = "UPDATE renewal SET quote_id = '{$rowForQuote['quote_id']}', site_id = '{$site_id}' WHERE opportunity_id = {$opportunity_id}";
        $resultRenewal = $db->sql_query($SQLRenewal);

        $SQL3 = "UPDATE quote SET quote_status = 'New', site_id = '{$site_id}' WHERE opportunity_id = '{$opportunity_id}' AND project_id = '{$id}'";
        $result3 = $db->sql_query($SQL3);

        $SQLOpportunity    = "UPDATE opportunity SET status = 'Converted to Project' WHERE opportunity_id = {$opportunity_id}";
        $resultOpportunity = $db->sql_query($SQLOpportunity);

        $cpUtil->redirect("index.php?_topRm=project&module=enggCrm_opportunity&_action=edit&opportunity_id={$opportunity_id}");

        }

    }

        $opportunity_id = $fn->getReqParam('opportunity_id');

         $SQL = "
        SELECT *
        FROM opportunity
        WHERE opportunity_id = {$opportunity_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $company = $db->sql_fetchrow($result);

        if ($company['category'] == 'Installation'){


        $SQL = "
        SELECT *
        FROM opportunity
        WHERE opportunity_id = {$opportunity_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);

            $this->fieldsArray = array();
            $fa = & $this->fieldsArray;
            $fa['creation_date']      = date("Y-m-d H:i:s");
            $fa['title']              = $row['title'];
            $fa['contact_id']         = $row['contact_id'];
            $fa['status']             = "WIP";
            $fa['company_id']         = $row['company_id'];
            $fa['project_manager_id'] = $row['project_manager_id'];
            $fa['category']           = $row['category'];
            $fa['notes']              = $row['notes'];
            $fa['opportunity_id']     = $row['opportunity_id'];
            $fa['per_completed']      = 0;
            $fa['start_date']         = date("Y-m-d");
            $fa['site_id']            = $site_id;
        }

        $fa['project_code'] = $this->getProjectCodeOnConvFromOpp($row['opportunity_id']);
        $id = $fn->addRecord($this->fieldsArray);
              
        //---------------------------------------//
        $SQL1    = "UPDATE quote SET project_id = {$id}, site_id = '{$site_id}' WHERE opportunity_id = {$opportunity_id}";
        $result1 = $db->sql_query($SQL1);

        $faOpp = array();
        $faOpp['status']   = 'Win';
        $faOpp['modification_date'] = date('Y-m-d H:i:s');
        $faOpp['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faOpp, 'opportunity', 'opportunity_id', $opportunity_id);

        $SQlOE ="
        SELECT *
        FROM opportunity_employee
        WHERE opportunity_id = {$opportunity_id}
        ";
        $resultOE = $db->sql_query($SQlOE);
        while ($rowOE = $db->sql_fetchrow($resultOE)) {
            $faPe = array();
            $faPe['project_id']         = $id;
            $faPe['employee_id']        = $rowOE['employee_id'];
            $faPe['active_in_project']  = $rowOE['active_in_project'];
            $faPe['site_id']            = $site_id;
            $faPe['creation_date']      = date('Y-m-d H:i:s');

            $SQLPE = $dbUtil->getInsertSQLStringFromArray($faPe, 'project_employee');
            $resultPE = $db->sql_query($SQLPE);

            $rec = $fn->getRecordByCondition('employee', "project_manager = '1' AND employee_id = {$rowOE['employee_id']}");
            if($rec['employee_id'] != ''){
                $SQLPro = "UPDATE project SET project_manager_id = '{$rec['employee_id']}' WHERE project_id = {$id}";
                $resultPro = $db->sql_query($SQLPro);                
            }
        }

        $SQlOCS ="
        SELECT *
        FROM opportunity_costing_summary
        WHERE opportunity_id = {$opportunity_id}
        ";
        $resultOCS = $db->sql_query($SQlOCS);
        while ($rowOCS = $db->sql_fetchrow($resultOCS)) {
            $facs = array();
            $facs['po_code']                        = $rowOCS['po_code'];
            $facs['invoice_code']                   = $rowOCS['invoice_code'];
            $facs['delivery_date']                  = $rowOCS['delivery_date'];
            $facs['no_of_worker_used']              = $rowOCS['no_of_worker_used'];
            $facs['no_of_days_worked']              = $rowOCS['no_of_days_worked'];
            $facs['labour_rates_per_day']           = $rowOCS['labour_rates_per_day'];
            $facs['po_price']                       = $rowOCS['po_price'];
            $facs['po_price_with_gst']              = $rowOCS['po_price_with_gst'];
            $facs['invoiced_price']                 = $rowOCS['invoiced_price'];
            $facs['invoiced_price_with_gst']        = $rowOCS['invoiced_price_with_gst'];
            $facs['profit_percentage']              = $rowOCS['profit_percentage'];
            $facs['profit']                         = $rowOCS['profit'];
            $facs['total_material_price']           = $rowOCS['total_material_price'];
            $facs['transport_charges']              = $rowOCS['transport_charges'];
            $facs['total_labour_charges']           = $rowOCS['total_labour_charges'];
            $facs['salesman_commission']            = $rowOCS['salesman_commission'];
            $facs['finance_charges']                = $rowOCS['finance_charges'];
            $facs['office_overheads']               = $rowOCS['office_overheads'];
            $facs['other_charges']                  = $rowOCS['other_charges'];
            $facs['total_cost']                     = $rowOCS['total_cost'];
            $facs['project_id']                     = $id;
            $facs['salesman_commission_percentage'] = $rowOCS['salesman_commission_percentage'];
            $facs['finance_charges_percentage']     = $rowOCS['finance_charges_percentage'];
            $facs['office_overheads_percentage']    = $rowOCS['office_overheads_percentage'];
            $facs['transport_charges_percentage']   = $rowOCS['transport_charges_percentage'];
            $facs['site_id']                        = $site_id;
            $facs['creation_date']                  = date('Y-m-d H:i:s');
            $facs['created_by']                     = $fn->getSessionParam('userName');
            
            $insertCs  = $dbUtil->getInsertSQLStringFromArray($facs, 'costing_summary');
            $resultCs  = $db->sql_query($insertCs);
            $costing_summary_id = $db->sql_nextid();

            $SQlOCSH ="
            SELECT *
            FROM opportunity_costing_summary_history
            WHERE opportunity_id = {$opportunity_id}
              AND opportunity_costing_summary_id = {$rowOCS['opportunity_costing_summary_id']}
            ";
            $resultOCSH = $db->sql_query($SQlOCSH);
            while ($rowOCSH = $db->sql_fetchrow($resultOCSH)) {
                $faIi = array();
                $faIi['project_id']         = $id;
                $faIi['costing_summary_id'] = $costing_summary_id;
                $faIi['supplier_id']        = $rowOCSH['supplier_id'];
                $faIi['site_id']            = $site_id;
                $faIi['sub_con_id']         = $rowOCSH['sub_con_id'];
                $faIi['quantity']           = $rowOCSH['quantity'];
                $faIi['unit_price']         = $rowOCSH['unit_price'];
                $faIi['unit']               = $rowOCSH['unit'];
                $faIi['amount']             = $rowOCSH['amount'];
                $faIi['creation_date']      = date('Y-m-d H:i:s');
                
                $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'costing_summary_history');
                $result = $db->sql_query($insert);
            }
        }

        $SQlMedia ="
        SELECT *
        FROM media
        WHERE record_id = {$opportunity_id}
          AND room_name = 'enggCrm_opportunity'
          AND record_type = 'attachment'
        ";
        $resultMedia = $db->sql_query($SQlMedia);
        while ($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $faM = array();
            $faM['record_id']         = $id;
            $faM['site_id']           = $site_id;
            $faM['media_type']        = $rowMedia['media_type'];
            $faM['actual_file_name']  = $rowMedia['actual_file_name'];
            $faM['content_type']      = $rowMedia['content_type'];
            $faM['media_size']        = $rowMedia['media_size'];
            $faM['record_type']       = $rowMedia['record_type'];
            $faM['lang']              = $rowMedia['lang'];
            $faM['alt_tag_data']      = $rowMedia['alt_tag_data'];
            $faM['sort_order']        = $rowMedia['sort_order'];
            $faM['room_name']         = 'enggCrm_project';
            $faM['creation_date']     = date('Y-m-d H:i:s');
            
            $insertMedia = $dbUtil->getInsertSQLStringFromArray($faM, 'media');
            $resultMedia = $db->sql_query($insertMedia);
            $media_id = $db->sql_nextid();

            $file_name = $media_id.'_'.$rowMedia['actual_file_name'];

            $SQLMediaUpdate = "UPDATE media SET file_name = '{$file_name}' WHERE media_id = {$media_id}";
            $resultMediaUpdate = $db->sql_query($SQLMediaUpdate);

            $file = "{$cpCfg['cp.siteRoot']}media/normal/{$rowMedia['file_name']}";
            $newfile = "{$cpCfg['cp.siteRoot']}media/normal/{$file_name}";
            copy($file, $newfile);
        }

        $SQlForQuote ="
        SELECT *
        FROM quote
        WHERE opportunity_id = {$opportunity_id}
       
        ";
        $resultForQuote = $db->sql_query($SQlForQuote);
        $rowForQuote = $db->sql_fetchrow($resultForQuote);

        $SQL2 = "UPDATE project SET quote_id = '{$rowForQuote['quote_id']}' WHERE opportunity_id = {$opportunity_id}";
        $result2 = $db->sql_query($SQL2);

        $SQL3 = "UPDATE quote SET quote_status = 'New' WHERE opportunity_id = '{$opportunity_id}' AND project_id = '{$id}'";
        $result3 = $db->sql_query($SQL3);

        $SQLOpportunity    = "UPDATE opportunity SET status = 'Converted to Project' WHERE opportunity_id = {$opportunity_id}";
        $resultOpportunity = $db->sql_query($SQLOpportunity);

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
      
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by']       = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'quote_id', $rowForQuote['quote_id']);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'quote_id', $rowForQuote['quote_id']);
        $projRec    = $fn->getRecordRowByID('project', 'project_id', $id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['quote_id']             = $rowForQuote['quote_id'];
        $faOrder['project_id']           = $id;
        $faOrder['company_id']           = $projRec['company_id'];
        $faOrder['site_id']              = $site_id;
        $faOrder['contact_id']           = $projRec['contact_id'];
        $faOrder['project_type']         = $projRec['category'];
        $faOrder['quote_title']          = $quoteRec['title'];
        $faOrder['cust_company_name']    = $companyRow['company_name'];
        $faOrder['cust_address1']        = $companyRow['address_flat'];
        $faOrder['cust_address2']        = $companyRow['address_street'];
        $faOrder['cust_address_country'] = $companyRow['address_country'];
        $faOrder['cust_address_po_code'] = $companyRow['address_po_code'];
        $faOrder['cust_email']           = $companyRow['email'];
        $faOrder['cust_phone']           = $companyRow['phone'];
        $faOrder['cust_fax']             = $companyRow['fax'];
        $faOrder['record_type']          = $projRec['category'];

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

        if ($projRec['category'] == 'Maintenance') {
            $faOrder['start_date']            = $projRec['start_date'];
            $faOrder['end_date']              = $projRec['estimated_finish_date'];
        }

        //check if the order record already exist or not
        $orderRec = $fn->getRecordByCondition('order', "project_id = '{$id}'");
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
        WHERE qi.quote_id = {$rowForQuote['quote_id']}
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
            $faOi['quote_id']         = $rowForQuote['quote_id'];
            $faOi['site_id']          = $site_id;
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

        $cpUtil->redirect("index.php?_topRm=project&module=enggCrm_opportunity&_action=edit&opportunity_id={$opportunity_id}");

    }
    }

    /**
     *
     */
    function getProjectCodeOnConvFromOpp($opportunity_id) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        if ($cpCfg['m.enggCrm.oppurtunity.hasSameCode'] == 1) {
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
            $project_code =  "P-" . substr($oppRec['opportunity_code'], 2);
        } else {
            $project_code = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
            $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);
        }

        return $project_code;
    }
    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        if (strtolower($fa['status']) == 'cancelled'){
            $SQL = "
            UPDATE opportunity
            SET status = 'Cancelled'
            WHERE project_id = {$id}
            ";

            $db->sql_query($SQL);
        }
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
              ,p.category_id AS category
              ,p.product_type
              ,(SELECT i.actual_stock
                FROM inventory i
                WHERE i.product_id = p.product_id) AS stock
        FROM product p
        WHERE (p.title LIKE '{$productTitle}%')
          AND p.published = 1
        ORDER BY p.title
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }
}
