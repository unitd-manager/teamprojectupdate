<?
class CPL_Admin_Widgets_EnggCrm_ProjectQuote_Model extends CP_Common_Lib_WidgetModelAbstract
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
     *
     */
    function getAddQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $project_id  = $fn->getReqParam('project_id');
        $rowProject  = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowCompany  = $fn->getRecordRowByID('company', 'company_id', $rowProject['company_id']);
        $rowStaff  = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $fa = array();
        $fa['project_id']       = $project_id;
        $fa['employee_id']      = $rowStaff['employee_id'];
        $fa['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa['quote_status']     = 'New';
        //$fa['gst']              = 1;
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode();
        $fa['title']            = $fn->getSettingsValueByKey("cp.projectName");
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote');
        $quote_id = $fn->addRecord($fa, 'quote');

        $quoteRows = $fn->getRecordCount('quote', "project_id = {$project_id}");

        return $quoteRows."_".$quote_id;   
    }

    /**
     *
     */
    function getUpdateAddQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextQuoteCode = $fn->getSettingsValueByKey("nextQuoteCode");

        if($nextQuoteCode < 10){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode . '-' . date("Y");
        }
        else if($nextQuoteCode < 99){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix'). $nextQuoteCode . '-' . date("Y");
        }
        else if($nextQuoteCode > 99 || $nextOppCode < 999){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode . '-' . date("Y");
        }
        else{
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix')  . $nextQuoteCode . '-' . date("Y");
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextQuoteCode'";
        $result = $db->sql_query($SQL);

        return $quoteCode;
    }
    
    /**
     *
     */
    function getAddMultipleLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $title_arr       = $fn->getPostParam('title', array());
        $nationality_arr = $fn->getPostParam('nationality', array());
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

        $project_id        = $fn->getPostParam('project_id');
        $quote_id          = $fn->getPostParam('quote_id');
        $drawing_nos       = $fn->getPostParam('drawing_nos');

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
                    $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

                    $fa = array();
                    $fa['opportunity_id']   = $projectRec['opportunity_id'];
                    $fa['project_id']       = $project_id;
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
            //$partno_arr        = $fn->getPostParam('partno', array());
            $title_arr         = $fn->getPostParam('title', array());
            $description_arr   = $fn->getPostParam('description', array());
            $unit_price_arr    = $fn->getPostParam('unit_price', array());
            $unit_arr          = $fn->getPostParam('unit', array());
            $amount_arr        = $fn->getPostParam('amount', array());
            $quantity_arr      = $fn->getPostParam('quantity', array());
            //$remarks_arr       = $fn->getPostParam('remarks', array());
            //$nationality_arr   = $fn->getPostParam('nationality', array());
            //$ot_rate_arr       = $fn->getPostParam('ot_rate', array());
            //$ph_rate_arr       = $fn->getPostParam('ph_rate', array());
            //$scaffold_code_arr = $fn->getPostParam('scaffold_code', array());
            //$erection_arr      = $fn->getPostParam('erection', array());
            //$dismantle_arr     = $fn->getPostParam('dismantle', array());

            if (!$this->getAddMultipleLineItemValidate()){
                return $validate->getErrorMessageXML();
            }

            $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);

            $count = count($description_arr);

            for ($i= 0; $i < $count; $i++) {
                $partno        = '';
                $description   = '';
                $unit          = '';
                //$quantity      = '';
                //$nationality   = '';
                //$ot_rate       = '';
                //$ph_rate       = '';
                //$scaffold_code = '';
                //$erection      = '';
                //$dismantle     = '';

                $title       = $title_arr[$i];
                //$title         = '';
                $description   = $description_arr[$i];
                $unit          = $unit_arr[$i];
                $amount        = $amount_arr[$i];
                $unit_price    = $unit_price_arr[$i];
                $quantity      = $quantity_arr[$i];
                //$remarks     = $remarks_arr[$i];
                //$scaffold_code = $scaffold_code_arr[$i];
                //$erection      = $erection_arr[$i];
                //$dismantle     = $dismantle_arr[$i];

                $chkField      = $description;

                if ($chkField) {
                    $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

                    $fa = array();
                    $fa['opportunity_id']   = $projectRec['opportunity_id'];
                    $fa['project_id']       = $project_id;
                    $fa['quote_id']         = $quote_id;
                    //$fa['part_no']          = $partno;
                    $fa['title']            = $title;
                    $fa['amount']           = $amount;
                    $fa['unit_price']       = $unit_price;
                    $fa['quantity']         = $quantity;
                    $fa['description']      = $description;
                    $fa['unit']             = $unit;
                    //$fa['remarks']        = $remarks;
                    //$fa['nationality']      = $nationality;
                    //$fa['ot_rate']          = $ot_rate;
                    //$fa['ph_rate']          = $ph_rate;
                    //$fa['scaffold_code']    = $scaffold_code;
                    //$fa['erection']         = $erection;
                    //$fa['dismantle']        = $dismantle;
                    $fa['creation_date']    = date('Y-m-d H:i:s');
                    $fa['created_by']       = $fn->getSessionParam('userName');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                    $result = $db->sql_query($insert);
                    $quote_items_id = $db->sql_nextid();

                    $faQuote = array();
                    $faQuote['discount'] = $fn->getPostParam('overallDiscount');
                    $fa = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
                    $whereCondition = "WHERE quote_id = {$quote_id}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote', $whereCondition);
                    $db->sql_query($SQL);
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     * 
     */
    function getEditLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $project_category = $fn->getPostParam('project_category');

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
    function getEditLineItemSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getEditLineItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $quote_items_id = $fn->getReqParam('quote_items_id');
        $drawing_nos    = $fn->getReqParam('drawing_nos');
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
            //$fa = $fn->addToFieldsArray($fa, 'part_no');
            $fa = $fn->addToFieldsArray($fa, 'title');
            $fa = $fn->addToFieldsArray($fa, 'description');
            $fa = $fn->addToFieldsArray($fa, 'quantity');
            $fa = $fn->addToFieldsArray($fa, 'unit');
            $fa = $fn->addToFieldsArray($fa, 'unit_price');
            $fa = $fn->addToFieldsArray($fa, 'amount');
            //$fa = $fn->addToFieldsArray($fa, 'remarks');
            //$fa = $fn->addToFieldsArray($fa, 'nationality');
            //$fa = $fn->addToFieldsArray($fa, 'ot_rate');
            //$fa = $fn->addToFieldsArray($fa, 'ph_rate');
            //$fa = $fn->addToFieldsArray($fa, 'scaffold_code');
            //$fa = $fn->addToFieldsArray($fa, 'erection');
            //$fa = $fn->addToFieldsArray($fa, 'dismantle');
        }

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote_items');

        $whereCondition = "WHERE quote_items_id = {$quote_items_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote_items", $whereCondition);
        $db->sql_query($SQL);

        $faQuote = array();
        $faQuote = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
        $whereCondition = "WHERE quote_id = {$quote_item_rec['quote_id']}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($faQuote, 'quote', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();

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
     * Line Item Edit Form Submit
     */
    function getEditForQuoteSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $project_id                         = $fn->getReqParam('project_id');
        $quote_id                           = $fn->getReqParam('quote_id');
        $title                              = $fn->getPostParam('title');
        $apply_digital_signature          = $fn->getPostParam('apply_digital_signature');
        $signature_name                              = $fn->getPostParam('signature_name');
        $quote_code_user                    = $fn->getPostParam('quote_code_user');
        $quote_date                         = $fn->getPostParam('quote_date');
        $quote_status                       = $fn->getPostParam('quote_status');
        $condition                          = $fn->getPostParam('condition');
        $note                          = $fn->getPostParam('note');
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
        // $total_amount                       = $fn->getPostParam('total_amount');
        $revision                           = $fn->getPostParam('revision');
        $quote_revised                      = $fn->getPostParam('quote_revised');

        if (!$this->getEditForQuoteValidate()){
            return $validate->getErrorMessageXML();
        }
        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        if ($title                             != $quoteRec['title']
        || $quote_code_user                    != $quoteRec['quote_code_user']
        || $quote_date                         != $quoteRec['quote_date']
        || $quote_status                       != $quoteRec['quote_status']
        || $condition                          != $quoteRec['condition']
        || $note                          != $quoteRec['note']
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
        // || $total_amount                       != $quoteRec['total_amount']
        || $revision                           != $quoteRec['revision']
        ) {
            $this->getSaveQuoteLog($quote_id);
        }

        

        $fa = array();
        $fa['title']                              = $title;
        $fa['quote_code_user']                    = $quote_code_user;
        $fa['quote_date']                         = $quote_date;
        $fa['quote_status']                       = $quote_status;
        $fa['condition']                          = $condition;
        $fa['note']                          = $note;
        $fa['apply_digital_signature']                          = $apply_digital_signature;
        $fa['signature_name']                          = $signature_name;
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
        $fa['our_reference']                      = $our_reference;
        $fa['intro_quote']                        = $intro_quote;
        // $fa['total_amount']                       = $total_amount;
        $fa['payment_method']                     = $payment_method;
        $fa['revision']                           = $revision;
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote');

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
        $sqlQuote = "
        SELECT quote_id FROM quote
        WHERE project_id = {$project_id}
        ";
        $resultQuote  = $db->sql_query($sqlQuote);
        $numRowsQuote = $db->sql_numrows($resultQuote);

        // if($quote_status == 'Awarded') {
        //     $SQL = "UPDATE project SET quote_id = '{$quote_id}' WHERE project_id = {$project_id}";
        //     $result = $db->sql_query($SQL);

        //     $this->getGenerateOrderRecords($quote_id, $project_id);

        //     /* Checking whether opp has more than one quote to update other quotes to new except confirmed quote */
        //     /*if ($numRowsQuote > 1) {
        //         $sqlQuoteCondition = "
        //         SELECT * FROM quote 
        //         WHERE quote_status = 'Awarded'
        //           AND project_id = {$project_id}
        //           AND quote_id != {$quote_id}
        //         ";
        //         $resultQuoteCondition  = $db->sql_query($sqlQuoteCondition);
        //         while ($rowQuoteCondition  = $db->sql_fetchrow($resultQuoteCondition)){
        //             $sqlUpdateQuote = "
        //             UPDATE quote SET quote_status  = 'New' WHERE quote_id = {$rowQuoteCondition['quote_id']}
        //             ";
        //             $resultQuote  = $db->sql_query($sqlUpdateQuote); 
        //         }
        //     }*/
        // }

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
        // $faQuoteLog['drawing_nos']                        = $quoteRec['drawing_nos'];
        $faQuoteLog['our_reference']                      = $quoteRec['our_reference'];
        $faQuoteLog['intro_quote']                        = $quoteRec['intro_quote'];
        // $faQuoteLog['total_amount']                       = $quoteRec['total_amount'];
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
     * Generate order records from Project
     */
    function getGenerateOrderRecords($quote_id, $project_id){
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
        $faQuote['quote_status']      = 'Awarded';
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by']       = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'quote_id', $quote_id);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $projRec    = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['quote_id']             = $quote_id;
        $faOrder['project_id']           = $project_id;
        $faOrder['company_id']           = $projRec['company_id'];
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
        $orderRec = $fn->getRecordByCondition('order', "project_id = '{$project_id}'");
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
        WHERE qi.quote_id = {$quote_id}
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
            $faOi['quote_id']         = $quote_id;
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
    }

    /**
     *
     */
    function getCreationModificationQuote() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $dateUtil  = Zend_Registry::get('dateUtil');

        $quote_id = $fn->getReqParam('quote_id');

        $header = "
        <thead>
            <tr>
                <td>Created By/Creation Date</td>
                <td>Modified By/Modification Date</td>
            </tr>
        </thead>
        ";

        $SQLPO ="
        SELECT q.creation_date
              ,q.created_by
              ,q.modification_date
              ,q.modified_by
        FROM quote q
        WHERE q.quote_id = {$quote_id}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $row    = $db->sql_fetchrow($resultPo);

        if($row['modified_by'] != ""){
            $modified_by = "{$row['modified_by']} - <br/>". $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
        }else{
            $modified_by = "";
        }

        if($row['created_by'] != ""){
            $created_by = "{$row['created_by']} - <br/>". $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
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
        <form id='creationModificationPo' class='creationModificationPo' method='post'>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getDeleteLineItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $quote_items_id    = $fn->getReqParam('quote_items_id');

        $deleteSQL    = "
        DELETE FROM quote_items
        WHERE quote_items_id = '{$quote_items_id}'
        ";
        $result = $db->sql_query($deleteSQL);
    }
}