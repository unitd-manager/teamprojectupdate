<?
class CPL_Admin_Modules_EnggCrm_Opportunity_Model extends CP_Admin_Modules_EnggCrm_Opportunity_Model
{
    /**
     *
     */
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $cpCfg = Zend_Registry::get('cpCfg');

        $extraTableNames = '';
        $joinTbls = '';
        $joinFlds = '';

        if ($tv['staff_id'] != "") {
            $extraTableNames .= "opportunity_staff os_hist,";
        }

        if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
            $flds = "
            SELECT GROUP_CONCAT(o.opportunity_id SEPARATOR ', ') AS record_ids
            ";
        } else {
            $flds = "
            SELECT o.*
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                  ,CONCAT_WS(' ', ref.first_name, ref.last_name) AS ref_contact_name
                  ,c.company_name
                  ,c.company_size
                  ,c.source
                  ,c.industry
                  ,e.team
                  ,p.project_code
                  ,q.quote_code
                  ,q.our_reference
                  ,q.intro_drawing_quote
                  ,q.total_amount AS quote_amount
                  ,q.revision
                  ,q.employee_id AS employeeId
                  ,ser.title AS service_title
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS project_manager_name
            ";
        }

        $SQL = "
        {$flds}
        FROM {$extraTableNames}
        opportunity o
        LEFT JOIN (quote q) ON (o.opportunity_id          = q.opportunity_id)
        LEFT JOIN (contact cont) ON (o.contact_id          = cont.contact_id)
        LEFT JOIN (contact ref)  ON (o.referrer_contact_id = ref.contact_id)
        LEFT JOIN (company c)    ON (o.company_id          = c.company_id)
        LEFT JOIN (employee e)   ON (o.employee_id         = e.employee_id)
        LEFT JOIN (service ser)  ON (o.service_id          = ser.service_id)
        LEFT JOIN (staff s)      ON (o.project_manager_id  = s.staff_id)
        LEFT JOIN (valuelist VL) ON (o.chance              = VL.value AND VL.key_text = 'opportunityChance')
        LEFT JOIN (project p)    ON (p.project_id          = o.project_id)
        {$joinTbls}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'o';

        $title              = $fn->getReqParam('title');
        $category           = $fn->getReqParam('category');
        $chance             = $fn->getReqParam('chance');
        $company_id         = $fn->getReqParam('company_id');
        $service_id         = $fn->getReqParam('service_id');
        $opportunity_id     = $fn->getReqParam('opportunity_id');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $session_user_group_id = isset($_SESSION['userGroupID']) ? $_SESSION['userGroupID']  : false;

        $SQLStaff = "
        SELECT e.team, e.employee_id
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";
        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        if ($opportunity_id != "") {
            $searchVar->sqlSearchVar[] = "o.opportunity_id   = {$opportunity_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.opportunity_id   = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'o.opportunity_id');

            if ($title != '') {
                $searchVar->sqlSearchVar[] = "o.title LIKE '%{$title}%'";
            }

            if ($category != '') {
                $searchVar->sqlSearchVar[] = "o.category = '{$category}'";
            }

            if ($project_manager_id != '') {
                $searchVar->sqlSearchVar[] = "o.project_manager_id  = {$project_manager_id}";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "o.company_id   = {$company_id}";
            }

            if ($service_id != "") {
                $searchVar->sqlSearchVar[] = "o.service_id   = {$service_id}";
            }

            if ($chance != "") {
                $searchVar->sqlSearchVar[] = "o.chance   = '{$chance}'";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "o.status   = '{$tv['status']}'";
            }

            if ($_SESSION['userGroupName'] == 'Super Administrator' || $_SESSION['userGroupName'] == 'Super Admin' ) {
            } else {
                if($rowStaff['team'] != ''){
                    $searchVar->sqlSearchVar[] = "o.employee_id IN (select employee_id FROM employee WHERE team = '{$rowStaff['team']}')";
                } else {
                    $searchVar->sqlSearchVar[] = "o.employee_id = '{$rowStaff['employee_id']}'";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.title            LIKE '%{$tv['keyword']}%'  OR
                    q.quote_code            LIKE '%{$tv['keyword']}%'  OR
                    o.description      LIKE '%{$tv['keyword']}%'  OR
                    o.notes            LIKE '%{$tv['keyword']}%'  OR
                    o.opportunity_code LIKE '%{$tv['keyword']}%'  OR
                    c.company_name LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($tv['staff_id'] != '') {
                $searchVar->sqlSearchVar[] = "o.opportunity_id = os_hist.opportunity_id";
                $searchVar->sqlSearchVar[] = "os_hist.staff_id = {$tv['staff_id']}";
            }

            //------------------------------------------------------------------------//
            $enquiry_date1         = $fn->getReqParam('enquiry_date_1');
            $enquiry_date2         = $fn->getReqParam('enquiry_date_2');
            $follow_up_date1       = $fn->getReqParam('follow_up_date_1');
            $follow_up_date2       = $fn->getReqParam('follow_up_date_2');
            $estimated_start_date1 = $fn->getReqParam('estimated_start_date_1');
            $estimated_start_date2 = $fn->getReqParam('estimated_start_date_2');

            if ($enquiry_date1 != "" && $enquiry_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.enquiry_date BETWEEN '{$enquiry_date1}' AND '{$enquiry_date2}')";
            } else if ($enquiry_date1 != "") {
                $searchVar->sqlSearchVar[] = "o.enquiry_date = '{$enquiry_date1}'";
            }

            if ($follow_up_date1 != "" && $follow_up_date2 != "" ){
                $searchVar->sqlSearchVar[] = "(o.follow_up_date BETWEEN '{$follow_up_date1}' AND '{$follow_up_date2}')";
            } else if ($follow_up_date1 != ""){
                $searchVar->sqlSearchVar[] = "o.follow_up_date = '{$follow_up_date1}'";
            }


            if ($estimated_start_date1 != "" && $estimated_start_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.estimated_start_date BETWEEN '{$estimated_start_date1}' AND '{$estimated_start_date2}')";
            } else if ($estimated_start_date1 != ""){
                $searchVar->sqlSearchVar[] = "o.estimated_start_date = '{$estimated_start_date1}'";
            }

            if ($yearMonthStart != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(o.enquiry_date, '%Y-%m') = '{$yearMonthStart}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "o.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(o.flag != 1 OR o.flag IS null)";
            }

            /*
            if ($tv['status'] == '' && $tv['searchDone'] == 0){
                $searchVar->sqlSearchVar[] = "(
                    LOWER(o.status) != 'cancelled'
                AND LOWER(o.status) != 'win'
                AND LOWER(o.status) != 'lost'
                AND LOWER(o.status) != 'follow up'
                AND o.status != ''
                )";
            }
            */
        }

        $searchVar->sortOrder = "o.opportunity_id DESC, o.opportunity_code DESC, o.enquiry_date DESC, o.status, c.company_name, VL.sort_order DESC";

        //print $searchVar->sortOrder . "<br>";
    }
    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $fa['company_id']);
        $site_id = $companyRec['site_id'];

        if ($cpCfg['m.enggCrm.hasQuotingModule'] == 1) {
            $fa['confirmed_quote_id'] = 0;
        }

        //-------------------------------------------------------//
       $fa['opportunity_code'] = $this->getUpdateAddOpportunityCode();
        
        //-------------------------------------------------------//
        $fa['office_ref_no'] = $fn->getSettingsValueByKey('officeCodePrefix') . $fn->getSettingsValueByKey('nextOfficeCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOfficeCode'";
        $result = $db->sql_query($SQL);

        if ($cpCfg['m.enggCrm.oppurtunity.hasSameCode'] == 1) {
            $nextOppCode = $fn->getSettingsValueByKey("nextOpportunityCode");
            $SQL = "UPDATE setting SET value = {$nextOppCode} WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);
        }

        $rowStaff  = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $fa['enquiry_date'] = date('Y-m-d');
        $fa['status'] = 'In Progress';
        $fa['currency'] = 'SG$';
        $fa['employee_id'] = $rowStaff['employee_id'];


        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);

        $rowStaff  = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $fa1 = array();
        $fa1['opportunity_id']   = $id;
        $fa1['employee_id']      = $rowStaff['employee_id'];
        // $fa1['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa1['quote_status']     = 'New';
        $fa1['quote_date']       = date('Y-m-d');
        $fa1['quote_code']       = $this->getUpdateAddQuoteCode($site_id);;
        $fa1['title']            = $fn->getSettingsValueByKey("cp.projectName");
        $fa1 = $fn->addModificationDetailsToFieldsArray($fa1, 'quote');

        $fn->addRecord($fa1, 'quote');
    }



    function getAddMultipleMaterialsSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $completed_by      = $fn->getReqParam('completed_by');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $store = $fn->getReqParam('store');
        $date = $fn->getReqParam('date');
        $time = $fn->getReqParam('time');
        $service_type = $fn->getReqParam('service_type');
                $contract_type = $fn->getReqParam('contract_type');

        
        $fa = array();
        $fa['completed_by']         = $completed_by;
        $fa['opportunity_id']    = $opportunity_id;
         $fa['store']    = $store;
        $fa['date']    = $date;
        $fa['time']    = $time;
        $fa['service_type']    = $service_type;
       $fa['contract_type']    = $contract_type;
       
        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'renewal');
        $result = $db->sql_query($insert);
                $renewal_id = $db->sql_nextid();


         $SQL="
        SELECT r.*
        FROM valuelist r
         WHERE  r.key_text = 'checklist'
        ";
        $result   = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {

            $fa2 = array();
            $fa2['title'] = $row['value'];
            $fa2['valuelist_id']        = $row['valuelist_id'];
            $fa2['renewal_id'] = $renewal_id;
            $fa2['creation_date']    = date("Y-m-d H:i:s");
            $fa2['created_by']       = $fn->getSessionParam('userName');


            $insertproductSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'renewal_chechlist_history');
            $resultproductSQL = $db->sql_query($insertproductSQL);

        }

          $oppRec = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);

         $SQL = "
        SELECT *
        FROM quote
        WHERE opportunity_id = {$oppRec['opportunity_id']}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

            $SQLRenewal = "UPDATE renewal SET quote_id = '{$row['quote_id']}' WHERE renewal_id = {$renewal_id}";
        $resultRenewal = $db->sql_query($SQLRenewal);
      return $validate->getSuccessMessageXML();
    }


    /**
     * Line Item Edit Form Submit
     */
    function getEditLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

       // if (!$this->getTimeSheetEditValidate()){
            //return $validate->getTimeSheetEditValidate();
       // }

        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $quote_items_id  = $fn->getReqParam('quote_items_id');
        $drawing_nos     = $fn->getReqParam('drawing_nos');
        $quote_item_rec = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);

        $drawing_number     = $fn->getPostParam('drawing_number');
        $drawing_title      = $fn->getPostParam('drawing_title');
        $drawing_revision   = $fn->getPostParam('drawing_revision');
        $title              = $fn->getPostParam('title');
        $description        = $fn->getPostParam('description');
        $quantity           = $fn->getPostParam('quantity');
        $unit               = $fn->getPostParam('unit');
        $unit_price         = $fn->getPostParam('unit_price');
        $amount             = $fn->getPostParam('amount');

        $quoteItemsRec = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);
        if ($drawing_number     != $quoteItemsRec['drawing_number']
        || $drawing_title       != $quoteItemsRec['drawing_title']
        || $drawing_revision    != $quoteItemsRec['drawing_revision']
        || $title               != $quoteItemsRec['title']
        || $description         != $quoteItemsRec['description']
        || $quantity            != $quoteItemsRec['quantity']
        || $unit                != $quoteItemsRec['unit']
        || $unit_price          != $quoteItemsRec['unit_price']
        || $amount              != $quoteItemsRec['amount']
        ) {
            $this->getSaveQuoteLog($quote_item_rec['quote_id']);
        }

        $fa = array();
        if($drawing_nos == 1) {
            $fa = $fn->addToFieldsArray($fa, 'drawing_number');
            $fa = $fn->addToFieldsArray($fa, 'drawing_title');
            $fa = $fn->addToFieldsArray($fa, 'drawing_revision');
        } else {          
            $fa = $fn->addToFieldsArray($fa, 'description');
            $fa = $fn->addToFieldsArray($fa, 'title');
            $fa = $fn->addToFieldsArray($fa, 'unit');
            $fa = $fn->addToFieldsArray($fa, 'quantity');
            $fa = $fn->addToFieldsArray($fa, 'unit_price');
            $fa = $fn->addToFieldsArray($fa, 'amount');
        }
        
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote_items');

        $whereCondition = "WHERE quote_items_id = {$quote_items_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote_items", $whereCondition);
        $db->sql_query($SQL);

        $quote_item_rec = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);
        $faQuote = array();
        $fa = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
        $whereCondition = "WHERE quote_id = {$quote_item_rec['quote_id']}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddLineItemForQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddLineItemForQuoteFormValidate()){
            return $validate->getErrorMessageXML();
        }
       
        $opportunity_id = $fn->getPostParam('opportunity_id');
        $quote_id       = $fn->getPostParam('quote_id');
        $description    = $fn->getPostParam('description');
        $unit_price         = $fn->getPostParam('unit_price');

        $fa = array();
       
        $fa['opportunity_id']      = $opportunity_id;
        $fa['quote_id']            = $quote_id;
        $fa['description']         = $description;
        $fa['unit_price']              = $unit_price;
        $fa['quantity']            = $fn->getPostParam('quantity');

        $fn->addRecord($fa, 'quote_items');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddMultipleDrawingLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $drawing_number_arr = $fn->getPostParam('drawing_number', array());
        $drawing_title_arr  = $fn->getPostParam('drawing_title', array());

        $validate->resetErrorArray();

        $filterArray1 = array_filter($drawing_number_arr);
        $filterArray2 = array_filter($drawing_title_arr);

        if (count($filterArray1) == 0 && $filterArray2){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter atleast one item";
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
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $opportunity_id  = $fn->getPostParam('opportunity_id');
        $quote_id        = $fn->getPostParam('quote_id');
        $drawing_nos     = $fn->getPostParam('drawing_nos');

        if($drawing_nos == 1) {
            $drawing_number_arr   = $fn->getPostParam('drawing_number', array());
            $drawing_title_arr    = $fn->getPostParam('drawing_title', array());
            $drawing_revision_arr = $fn->getPostParam('drawing_revision', array());

            if (!$this->getAddMultipleDrawingLineItemValidate()){
                return $validate->getErrorMessageXML();
            }

            $count = count($drawing_number_arr);

            for ($i= 0; $i < $count; $i++) {
                $drawing_number   = $drawing_number_arr[$i];
                $drawing_title    = $drawing_title_arr[$i];
                $drawing_revision = $drawing_revision_arr[$i];

                if($drawing_number != "") {
                    $fa = array();
                    $fa['opportunity_id']   = $opportunity_id;
                    $fa['quote_id']         = $quote_id;
                    $fa['drawing_number']   = $drawing_number;
                    $fa['drawing_title']    = $drawing_title;
                    $fa['drawing_revision'] = $drawing_revision;
                    $fa['creation_date']    = date('Y-m-d H:i:s');
                    $fa['created_by']       = $fn->getSessionParam('userName');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                    $result = $db->sql_query($insert);
                    $quote_items_id = $db->sql_nextid();
                }
            }

        } else {
            $description_arr = $fn->getPostParam('description', array());
            $title_arr       = $fn->getPostParam('title', array());
            $quantity_arr    = $fn->getPostParam('quantity', array());
            $unit_price_arr    = $fn->getPostParam('unit_price', array());
            $unit_arr        = $fn->getPostParam('unit', array());
            $amount_arr      = $fn->getPostParam('amount', array());

            $count = count($title_arr);
            for ($i= 0; $i < $count; $i++) {
                $description = $description_arr[$i];
                $title       = $title_arr[$i];
                $amount      = $amount_arr[$i];
                $unit        = $unit_arr[$i];
                $quantity    = $quantity_arr[$i];
                $unit_price    = $unit_price_arr[$i];

                if ($description) {
                    $fa = array();
                    $fa['opportunity_id']   = $opportunity_id;
                    $fa['quote_id']         = $quote_id;
                    $fa['description']      = $description;
                    $fa['title']            = $title;
                    $fa['unit']             = $unit;
                    $fa['quantity']         = $quantity;
                    $fa['amount']           = $amount;
                    $fa['unit_price']       = $unit_price;
                    $fa['creation_date']    = date('Y-m-d H:i:s');
                    $fa['created_by']       = $fn->getSessionParam('userName');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                    $result = $db->sql_query($insert);
                    $quote_items_id = $db->sql_nextid();

                    $faQuote = array();
                    $faQuote['discount'] = $fn->getPostParam('overallDiscount');
                    $faQuote = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
                    $whereCondition = "WHERE quote_id = {$quote_id}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($faQuote, 'quote', $whereCondition);
                    $db->sql_query($SQL);
                }
            }
        }

        /* Update Opportunity value in Opportunity table */
        $quoteRec = $fn->getRecordCount('quote', "opportunity_id = {$opportunity_id} AND quote_status != 'Cancelled'");
        if ($quoteRec == 1) {
            /* Updating Opp value to Opp if there is only one quote available */
            $total_quote_amount = $this->getFindTotalAmountOfQuote($opportunity_id, $quote_id = '');

            $faOpp = array();
            //$faOpp['estimated_value'] = $total_quote_amount;
            $faOpp = $fn->addModificationDetailsToFieldsArray($faOpp, 'opportunity');
            $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faOpp, 'opportunity', $whereCondition);
            $db->sql_query($SQL);
        } else if ($quoteRec > 1) {
            $sqlQuote = "
            SELECT quote_id
            FROM quote
            WHERE opportunity_id = {$opportunity_id}
              AND quote_status != 'Cancelled'
            ORDER BY modification_date DESC
            LIMIT 0,1
            ";
            $resultQuote = $db->sql_query($sqlQuote);
            $rowQuote = $db->sql_fetchrow($resultQuote);

            $quote_id = $rowQuote['quote_id'];

            /* Updating Opp value to Opp if there is only one quote available */
            $total_quote_amount = $this->getFindTotalAmountOfQuote($opportunity_id, $quote_id);

            $faOpp = array();
            //$faOpp['estimated_value'] = $total_quote_amount;
            $faOpp = $fn->addModificationDetailsToFieldsArray($faOpp, 'opportunity');
            $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faOpp, 'opportunity', $whereCondition);
            $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     * Quote Form Submit
     */
    function getEditForQuoteSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $opportunity_id                     = $fn->getReqParam('opportunity_id');
        $quote_id                           = $fn->getReqParam('quote_id');
        $title                              = $fn->getPostParam('title');
        $apply_digital_signature          = $fn->getPostParam('apply_digital_signature');
        $signature_name                              = $fn->getPostParam('signature_name');
        $employee_id                              = $fn->getPostParam('employee_id');
        $quote_code_user                    = $fn->getPostParam('quote_code_user');
        $quote_date                         = $fn->getPostParam('quote_date');
        $quote_status                       = $fn->getPostParam('quote_status');
        $condition                          = $fn->getPostParam('condition');
        $quote_intro_text_1                 = $fn->getPostParam('quote_intro_text_1');
        $invoices_payment_terms             = $fn->getPostParam('invoices_payment_terms');
        $responsibility                     = $fn->getPostParam('responsibility');
        $provision_by_client                = $fn->getPostParam('provision_by_client');
        $monday_to_friday_normal_timing     = $fn->getPostParam('monday_to_friday_normal_timing');
        $saturday_normal_timing             = $fn->getPostParam('saturday_normal_timing');
        $monday_to_friday_ot_timing         = $fn->getPostParam('monday_to_friday_ot_timing');
        $saturday_ot_timing                 = $fn->getPostParam('saturday_ot_timing');
        $sunday_and_publicholiday_ot_timing = $fn->getPostParam('sunday_and_publicholiday_ot_timing');
        $timesheet_type                     = $fn->getPostParam('timesheet_type');
        $project_location                   = $fn->getPostParam('project_location');
        $project_reference                  = $fn->getPostParam('project_reference');
        $gst                                = $fn->getPostParam('gst');
        $payment_method                     = $fn->getPostParam('payment_method');
        $discount                           = $fn->getPostParam('discount');
        $our_reference                      = $fn->getPostParam('our_reference');
        $intro_quote                        = $fn->getPostParam('intro_quote');
        $intro_drawing_quote                = $fn->getPostParam('intro_drawing_quote');
        $revision                           = $fn->getPostParam('revision');
        $ref_no_quote                       = $fn->getPostParam('ref_no_quote');
                $quote_revised                      = $fn->getPostParam('quote_revised');


        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        if ($title                             != $quoteRec['title']
        || $quote_code_user                    != $quoteRec['quote_code_user']
        || $quote_date                         != $quoteRec['quote_date']
        || $quote_status                       != $quoteRec['quote_status']
        || $condition                          != $quoteRec['condition']
        || $quote_intro_text_1                 != $quoteRec['quote_intro_text_1']
        || $invoices_payment_terms             != $quoteRec['invoices_payment_terms']
        || $responsibility                     != $quoteRec['responsibility']
        || $provision_by_client                != $quoteRec['provision_by_client']
        || $monday_to_friday_normal_timing     != $quoteRec['monday_to_friday_normal_timing']
        || $saturday_normal_timing             != $quoteRec['saturday_normal_timing']
        || $monday_to_friday_ot_timing         != $quoteRec['monday_to_friday_ot_timing']
        || $saturday_ot_timing                 != $quoteRec['saturday_ot_timing']
        || $sunday_and_publicholiday_ot_timing != $quoteRec['sunday_and_publicholiday_ot_timing']
        || $timesheet_type                     != $quoteRec['timesheet_type']
        || $project_location                   != $quoteRec['project_location']
        || $project_reference                  != $quoteRec['project_reference']
        || $gst                                != $quoteRec['gst']
        || $payment_method                     != $quoteRec['payment_method']
        || $discount                           != $quoteRec['discount']
        || $our_reference                      != $quoteRec['our_reference']
        || $intro_quote                        != $quoteRec['intro_quote']
        || $revision                           != $quoteRec['revision']
        || $ref_no_quote                       != $quoteRec['ref_no_quote']
        ) {
            $this->getSaveQuoteLog($quote_id);
        }

        $fa = array();
        $fa['title']                              = $title;
        $fa['quote_code_user']                    = $quote_code_user;
        $fa['quote_date']                         = $quote_date;
        $fa['quote_status']                       = $quote_status;
        $fa['condition']                          = $condition;
        $fa['quote_intro_text_1']                 = $quote_intro_text_1;
        $fa['invoices_payment_terms']             = $invoices_payment_terms;
        $fa['responsibility']                     = $responsibility;
        $fa['provision_by_client']                = $provision_by_client;
        $fa['monday_to_friday_normal_timing']     = $monday_to_friday_normal_timing;
        $fa['saturday_normal_timing']             = $saturday_normal_timing;
        $fa['monday_to_friday_ot_timing']         = $monday_to_friday_ot_timing;
        $fa['saturday_ot_timing']                 = $saturday_ot_timing;
        $fa['sunday_and_publicholiday_ot_timing'] = $sunday_and_publicholiday_ot_timing;
        $fa['timesheet_type']                     = $timesheet_type;
        $fa['project_location']                   = $project_location;
        $fa['project_reference']                  = $project_reference;
        $fa['gst']                                = $gst;
        $fa['discount']                           = $discount;
        $fa['payment_method']                     = $payment_method;
        $fa['our_reference']                      = $our_reference;
        $fa['intro_quote']                        = $intro_quote;
        $fa['payment_method']                     = $payment_method;
        $fa['revision']                           = $revision;
        $fa['ref_no_quote']                       = $ref_no_quote;
        $fa['intro_drawing_quote']                = $intro_drawing_quote;
        $fa['apply_digital_signature']            = $apply_digital_signature;
        $fa['signature_name']                     = $signature_name;
        $fa['employee_id']                        = $employee_id;

        $whereCondition = "WHERE quote_id = {$quote_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote", $whereCondition);
        $db->sql_query($SQL);


        
         $drawingrevision=$revision + 1;
        
        if ($quote_revised == 1){

        $SQLUpdateProduct = "
          UPDATE quote SET revision = '{$drawingrevision}'
          WHERE quote_id = '{$quote_id}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);


        }

        // Checking the quote status is confirmed before and changing a new quote record the status will be confirmed old one will be Updated to new
        if($quote_status == 'Awarded') {
            $sqlQuoteCondition = "
            SELECT *
            FROM quote
            WHERE quote_status = 'Awarded'
            AND quote_id != {$quote_id}
            ";
            $resultQuoteCondition  = $db->sql_query($sqlQuoteCondition);

            $SQL = "UPDATE project SET quote_id = '{$quote_id}' WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);
            while ($rowQuoteCondition  = $db->sql_fetchrow($resultQuoteCondition)){
                $sqlUpdateQuote = "
                UPDATE quote SET quote_status  = 'New' WHERE quote_id = {$rowQuoteCondition['quote_id']}
                ";
                $resultQuote  = $db->sql_query($sqlUpdateQuote);
            }

            $SQLOpportunity    = "UPDATE opportunity SET status = 'Awarded' WHERE opportunity_id = {$opportunity_id}";
            $resultOpportunity = $db->sql_query($SQLOpportunity);
        } else {
            /*$sqlOpportunity = "
            SELECT status
            FROM opportunity
            WHERE opportunity_id = '{$opportunity_id}'
            ";
            $resultOpportunity  = $db->sql_query($sqlOpportunity);
            $rowOpportunity     = $db->sql_fetchrow($resultOpportunity);

            if($rowOpportunity['status'] == "Awarded") {
                $SQLOpportunity    = "UPDATE opportunity SET status = 'In Progress' WHERE opportunity_id = {$opportunity_id}";
                $resultOpportunity = $db->sql_query($SQLOpportunity);
            } else {
                $SQLOpportunity    = "UPDATE opportunity SET status = '{$rowOpportunity['status']}' WHERE opportunity_id = {$opportunity_id}";
                $resultOpportunity = $db->sql_query($SQLOpportunity);
            }*/

            $SQL = "UPDATE project SET quote_id = '{$quote_id}' WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML($quote_status);

    }

    /**
     *
     */
    function getSaveQuoteLog($quote_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        $faQuoteLog = array();
        $faQuoteLog['quote_id']               = $quote_id;
        $faQuoteLog['opportunity_id']                     = $quoteRec['opportunity_id'];
        $faQuoteLog['project_id']                         = $quoteRec['project_id'];
        $faQuoteLog['title']                              = $quoteRec['title'];
        $faQuoteLog['quote_code']                         = $quoteRec['quote_code'];
        $faQuoteLog['quote_code_user']                    = $quoteRec['quote_code_user'];
        $faQuoteLog['quote_date']                         = $quoteRec['quote_date'];
        $faQuoteLog['quote_status']                       = $quoteRec['quote_status'];
        $faQuoteLog['condition']                          = $quoteRec['condition'];
        $faQuoteLog['quote_intro_text_1']                 = $quoteRec['quote_intro_text_1'];
        $faQuoteLog['invoices_payment_terms']             = $quoteRec['invoices_payment_terms'];
        $faQuoteLog['responsibility']                     = $quoteRec['responsibility'];
        $faQuoteLog['provision_by_client']                = $quoteRec['provision_by_client'];
        $faQuoteLog['monday_to_friday_normal_timing']     = $quoteRec['monday_to_friday_normal_timing'];
        $faQuoteLog['saturday_normal_timing']             = $quoteRec['saturday_normal_timing'];
        $faQuoteLog['monday_to_friday_ot_timing']         = $quoteRec['monday_to_friday_ot_timing'];
        $faQuoteLog['saturday_ot_timing']                 = $quoteRec['saturday_ot_timing'];
        $faQuoteLog['sunday_and_publicholiday_ot_timing'] = $quoteRec['sunday_and_publicholiday_ot_timing'];
        $faQuoteLog['timesheet_type']                     = $quoteRec['timesheet_type'];
        $faQuoteLog['project_location']                   = $quoteRec['project_location'];
        $faQuoteLog['project_reference']                  = $quoteRec['project_reference'];
        $faQuoteLog['gst']                                = $quoteRec['gst'];
        $faQuoteLog['discount']                           = $quoteRec['discount'];
        $faQuoteLog['drawing_nos']                        = $quoteRec['drawing_nos'];
        $faQuoteLog['our_reference']                      = $quoteRec['our_reference'];
        $faQuoteLog['intro_quote']                        = $quoteRec['intro_quote'];
        $faQuoteLog['total_amount']                       = $quoteRec['total_amount'];
        $faQuoteLog['payment_method']                     = $quoteRec['payment_method'];
        $faQuoteLog['revision']                           = $quoteRec['revision'];
        $faQuoteLog['creation_date']          = $quoteRec['creation_date'];
        $faQuoteLog['modification_date']      = $quoteRec['modification_date'];
        $faQuoteLog['created_by']             = $quoteRec['created_by'];
        $faQuoteLog['modified_by']            = $quoteRec['modified_by'];
        $faQuoteLog['employee_id']            = $quoteRec['employee_id'];
        $faQuoteLog['ref_no_quote']            = $quoteRec['ref_no_quote'];
        $faQuoteLog['intro_drawing_quote']            = $quoteRec['intro_drawing_quote'];

        $sqlQuoteLog = $dbUtil->getInsertSQLStringFromArray($faQuoteLog, 'quote_log');
        $resultQuoteLog = $db->sql_query($sqlQuoteLog);
        $quote_log_id  = $db->sql_nextid();

        $sql = "
        SELECT * FROM `quote_items`
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($sql);
        $count = 1;
        while ($quoteItemsRec = $db->sql_fetchrow($result)) {
            $faQuoteLog = array();
            $faQuoteLog['quote_items_id']      = $quoteItemsRec['quote_items_id'];
            $faQuoteLog['quote_log_id']          = $quote_log_id;
            $faQuoteLog['quote_id']          = $quoteItemsRec['quote_id'];
            $faQuoteLog['opportunity_id']      = $quoteItemsRec['opportunity_id'];
            $faQuoteLog['project_id']      = $quoteItemsRec['project_id'];
            $faQuoteLog['drawing_number']      = $quoteItemsRec['drawing_number'];
            $faQuoteLog['drawing_title']       = $quoteItemsRec['drawing_title'];
            $faQuoteLog['drawing_revision']    = $quoteItemsRec['drawing_revision'];
            $faQuoteLog['title']               = $quoteItemsRec['title'];
            $faQuoteLog['description']         = $quoteItemsRec['description'];
            $faQuoteLog['quantity']            = $quoteItemsRec['quantity'];
            $faQuoteLog['unit']                = $quoteItemsRec['unit'];
            $faQuoteLog['unit_price']          = $quoteItemsRec['unit_price'];
            $faQuoteLog['amount']              = $quoteItemsRec['amount'];
            $faQuoteLog['creation_date']       = $quoteItemsRec['creation_date'];
            $faQuoteLog['modification_date']   = $quoteItemsRec['modification_date'];
            $faQuoteLog['created_by']          = $quoteItemsRec['created_by'];
            $faQuoteLog['modified_by']         = $quoteItemsRec['modified_by'];

            $sqlQuoteLog = $dbUtil->getInsertSQLStringFromArray($faQuoteLog, 'quote_items_log');
            $resultQuoteLog = $db->sql_query($sqlQuoteLog);
        }
    }

    /**
     *
     */
    function getEnggCrmOpportunityEnggCrmEmployeeLinkSQL($id) {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

            return "
            SELECT a.employee_id
                  ,a.first_name AS title
                  ,''
                  ,a.status
            FROM `employee` a
            LEFT JOIN (opportunity_employee pe) ON (pe.employee_id = a.employee_id)
            WHERE pe.opportunity_id = {$id}
            ORDER BY title
           ";
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $quote_date       = $fn->getReqParam('quote_date');
        $quote_status       = $fn->getReqParam('quote_status');
        $total_amount       = $fn->getReqParam('total_amount');
        $project_reference       = $fn->getReqParam('project_reference');
        $our_reference       = $fn->getReqParam('our_reference');
        $payment_method       = $fn->getReqParam('payment_method');
        $apply_digital_signature       = $fn->getReqParam('apply_digital_signature');
        $signature_name       = $fn->getReqParam('signature_name');
        $condition       = $fn->getReqParam('condition');
        $note       = $fn->getReqParam('note');
        $revision       = $fn->getReqParam('revision');
        $intro_drawing_quote       = $fn->getReqParam('intro_drawing_quote');
        $subject       = $fn->getReqParam('subject');


        $fa1 = array();
        $fa1['quote_date']  = $quote_date;
        $fa1['quote_status']  = $quote_status;
        $fa1['total_amount']  = $total_amount;
        $fa1['project_reference']  = $project_reference;
        $fa1['our_reference']  = $our_reference;
        $fa1['payment_method']  = $payment_method;
        $fa1['apply_digital_signature']  = $apply_digital_signature;
        $fa1['signature_name']  = $signature_name;
        $fa1['condition']  = $condition;
        $fa1['note']  = $note;
        $fa1['revision']  = $revision;
        $fa1['intro_drawing_quote']  = $intro_drawing_quote;
        $fa1['subject']  = $subject;
   
        $fa1 = $fn->addModificationDetailsToFieldsArray($fa1, 'quote');

        $whereCondition = "WHERE opportunity_id = {$id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'quote', $whereCondition);
        $db->sql_query($SQL);

        $fn->returnAfterNewSave($id);
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
        ORDER BY company_name ASC
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
        */function getUpdateAddQuoteCode()
{
    $db = Zend_Registry::get('db');

    // Current Site ID
    $site_id = isset($_SESSION['cp_site_id']) ? $_SESSION['cp_site_id'] : 0;

    // Country Prefix
    switch ($site_id) {
        case 1:
            $prefix = 'KWT';
            break;

        case 2:
            $prefix = 'KSA';
            break;

        default:
            $prefix = 'QTN';
            break;
    }

    // Get Next Quote Number
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = 'nextQuoteCodeOpp'
        LIMIT 1
    ";

    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $nextQuoteCode = !empty($row['value']) ? $row['value'] : 1;

    // Generate Quote Code
    $quoteCode = $prefix . '-' . $nextQuoteCode . '-' . date('Y');

    // Increment Quote Number
    $SQL = "
        UPDATE setting
        SET value = value + 1
        WHERE key_text = 'nextQuoteCodeOpp'
    ";
    $db->sql_query($SQL);

    return $quoteCode;
}

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the project');
        //$validate->validateData('company_id', 'Please select the main con');

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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'office_ref_no');
        $fa = $fn->addToFieldsArray($fa, 'itq_ref_no');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_submission');
        $fa = $fn->addToFieldsArray($fa, 'services');
        $fa = $fn->addToFieldsArray($fa, 'site_show_date');
        $fa = $fn->addToFieldsArray($fa, 'site_show_time');
        $fa = $fn->addToFieldsArray($fa, 'site_show_attendee');
        $fa = $fn->addToFieldsArray($fa, 'actual_closing');
        $fa = $fn->addToFieldsArray($fa, 'actual_submission_date');
        $fa = $fn->addToFieldsArray($fa, 'pricing_done_by_acmv');
        $fa = $fn->addToFieldsArray($fa, 'pricing_done_by_elec');
        $fa = $fn->addToFieldsArray($fa, 'me_consultants');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'quote_ref');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'service_id');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_needed');
        $fa = $fn->addToFieldsArray($fa, 'estimated_start_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_value');
        $fa = $fn->addToFieldsArray($fa, 'estimated_value_base');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_code');
        $fa = $fn->addToFieldsArray($fa, 'other_cost');
        $fa = $fn->addToFieldsArray($fa, 'chance');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'client_type');
        $fa = $fn->addToFieldsArray($fa, 'difficulty');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'referrer_contact_id');
        $fa = $fn->addToFieldsArray($fa, 'source_channel');
        $fa = $fn->addToFieldsArray($fa, 'rating_1');
        $fa = $fn->addToFieldsArray($fa, 'rating_2');
        $fa = $fn->addToFieldsArray($fa, 'rating_3');
        $fa = $fn->addToFieldsArray($fa, 'rating_4');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'actual_closing_time');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');


        $rating = isset($fa['rating_1']) ? $fa['rating_1'] : 0;
        $rating += isset($fa['rating_2']) ? $fa['rating_2'] : 0;
        $rating += isset($fa['rating_3']) ? $fa['rating_3'] : 0;
        $rating += isset($fa['rating_4']) ? $fa['rating_4'] : 0;
        $rating = round($rating / 4);

        $optionArr = array(
             1 => 'Very Low'
            ,2 => 'Low'
            ,3 => 'Normal'
            ,4 => 'High'
            ,5 => 'Very High'
        );

        if($rating != 0){
            $fa['chance'] = $optionArr[$rating];
        }
        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $db      = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = array(
              'opportunity_code'              => $phpExcel->getFldObj('Opportunity Code')
              ,'title'        => $phpExcel->getFldObj('Project')
              ,'company_name'        => $phpExcel->getFldObj('Main Con')
            ,'office_ref_no'        => $phpExcel->getFldObj('Ref No')
            ,'revision'        => $phpExcel->getFldObj('Revision')
             ,'status'        => $phpExcel->getFldObj('Status')
             ,'quote_code'        => $phpExcel->getFldObj('Quote Code')
             ,'our_reference'        => $phpExcel->getFldObj('License')
             ,'intro_drawing_quote'        => $phpExcel->getFldObj('Job Description')
              ,'quote_amount'        => $phpExcel->getFldObj('Quote Amount')
          
        );

        //$dataArray = $dbUtil->getResultsetAsArray($result);

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     * QUOTE AND QUOTE ITEMS DUPLICATE
     */
    function getDuplicateQuote() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $quote_id        = $fn->getReqParam('quote_id');
        $add_line_item   = $fn->getReqParam('add_line_item');

        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        $fa = array();
        $fa['opportunity_id']   = $opportunity_id;
        $fa['drawing_nos']      = $quoteRec['drawing_nos'];
        $fa['our_reference']    = $quoteRec['our_reference'];
        $fa['intro_quote']      = $quoteRec['intro_quote'];
        $fa['total_amount']     = $quoteRec['total_amount'];
        $fa['revision']         = $quoteRec['revision'];
        $fa['title']            = $quoteRec['title'];
        $fa['discount']         = $quoteRec['discount'];
        $fa['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa['quote_status']     = 'New';
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode($site_id);;
        $id = $fn->addRecord($fa, 'quote');

        if ($add_line_item == 1) {
            $SQLQuoteItem = "
            SELECT *
            FROM quote_items
            WHERE quote_id = {$quote_id}
            ";
            $resultQuoteItem = $db->sql_query($SQLQuoteItem);
            while ($rowQuoteItem = $db->sql_fetchrow($resultQuoteItem)) {

                $fa1 = array();
                $fa1['opportunity_id']   = $opportunity_id;
                $fa1['quote_id']         = $id;
                $fa1['title']            = $rowQuoteItem['title'];
                $fa1['description']      = $rowQuoteItem['description'];
                $fa1['amount']           = $rowQuoteItem['amount'];
                $fa1['quantity']         = $rowQuoteItem['quantity'];
                $fa1['unit']             = $rowQuoteItem['unit'];
                $fa1['remarks']          = $rowQuoteItem['remarks'];
                $fa1['drawing_number']   = $rowQuoteItem['drawing_number'];
                $fa1['drawing_title']    = $rowQuoteItem['drawing_title'];
                $fa1['drawing_revision'] = $rowQuoteItem['drawing_revision'];
                $fa1['creation_date']    = date("Y-m-d H:i:s");
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $quote_items_id = $fn->addRecord($fa1, 'quote_items');
            }
        }
    }

    /**
     *
     */
    function getAddQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');

        $opportunity_id  = $fn->getReqParam('id');
        $rowStaff  = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $fa = array();
        $fa['opportunity_id']   = $opportunity_id;
        $fa['employee_id']      = $rowStaff['employee_id'];
        $fa['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa['quote_status']     = 'New';
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode($site_id);;
        $fa['title']            = $fn->getSettingsValueByKey("cp.projectName");
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote');

        $fn->addRecord($fa, 'quote');
    }

    /**
     *
     */

    function getUpdateAddOpportunityCode()
{
    $db = Zend_Registry::get('db');

    $site_id = isset($_SESSION['cp_site_id']) ? $_SESSION['cp_site_id'] : 0;

    // Site-wise Prefix & Counter Key
    switch ($site_id) {
        case 1:
            $prefix = 'KWT';
            $nextKey = 'nextOpportunityCode_KWT';
            break;

        case 2:
            $prefix = 'KSA';
            $nextKey = 'nextOpportunityCode_KWT';
            break;

       default: // No valid site 
       return '';
    }

    // Get Next Number
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = '{$nextKey}'
        LIMIT 1
    ";

    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $nextNo = !empty($row['value']) ? $row['value'] : 1;

    // Generate Opportunity Code
    $opportunityCode = $prefix . '-' . $nextNo;

    // Increment Counter
    $SQL = "
        UPDATE setting
        SET value = value + 1
        WHERE key_text = '{$nextKey}'
    ";
    $db->sql_query($SQL);

    return $opportunityCode;
}
}
