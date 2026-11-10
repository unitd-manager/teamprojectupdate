<?
class CPL_Admin_Widgets_EnggCrm_ProjectJobCompletion_Model extends CP_Common_Lib_WidgetModelAbstract
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectJobCompletion');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */

    function getAddJobFormSubmit() {

        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');

        $project_id  = $fn->getReqParam('project_id');
      
        $fa = array();
        $fa['project_id']       = $project_id;
        $fa['job_completion_date']  = date('Y-m-d');
      
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'job_completion');

        $job_completion_id = $fn->addRecord($fa, 'job_completion');       

     
    }




     /**
     *
     */

    function getUpdateDeliveryOrderSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $project_id  = $fn->getReqParam('project_id');
        $delivery_order_id  = $fn->getReqParam('delivery_order_id');
 $job_order_id  = $fn->getReqParam('job_order_id');
        $rowProject       = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowCompany       = $fn->getRecordRowByID('company', 'company_id', $rowProject['company_id']);
        $quoteRec = $fn->getRecordByCondition('job_order', "project_id = {$rowProject['project_id']}");
         $rowCompanyjob       = $fn->getRecordRowByID('job_order_hist', 'job_order_id', $quoteRec['job_order_id']);

      $SQL="
       SELECT jh.product_id
       ,jh.update_to_doreport,jh.serial_no
        FROM job_order_hist jh
        LEFT JOIN job_order q ON (q.job_order_id = jh.job_order_id)
        WHERE q.project_id = '{$project_id}'
        ";
        $result   = $db->sql_query($SQL);
        while($row = $db->sql_fetchrow($result)){
            
            if($row['update_to_doreport'] == ''){
                $fa1 = array();
                $fa1['delivery_order_id']     = $delivery_order_id;                    
                $fa1['product_id']        = $row['product_id'];
                $fa1['serial_no']        = $row['serial_no'];

                $fa1['creation_date']    = date('Y-m-d H:i:s');
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa1, 'delivery_order_hist');
                $resultInsert = $db->sql_query($insert);
                $delivery_order_hist_id = $db->sql_nextid();
            }                
        }
        $SQL    = "UPDATE job_order_hist SET update_to_doreport = 1 WHERE job_order_id = {$rowCompanyjob['job_order_id']}";
        $result = $db->sql_query($SQL);        
    }

    /**
     *
     */
    function getUpdateJobOrderCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $poCode = $fn->getSettingsValueByKey("nextJobCompletionCode");

        $POCode = $fn->getSettingsValueByKey('JobCompletionCodePrefix') . ' '. $poCode . ' - ' . date("y");

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextdeliveryOrderNoteCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }


    /**
     *
     */
    function getSearchTitle() {
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
              ,p.model
              ,p.nomenclature
              ,p.manufacture
              ,p.serial_no
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


    /**
     *
     */
    function getUpdateAddQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextQuoteCode = $fn->getSettingsValueByKey("nextQuoteCode");

        if($nextQuoteCode < 10){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode . '/' . date("Y");
        }
        else if($nextQuoteCode < 99){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix'). $nextQuoteCode . '/' . date("Y");
        }
        else if($nextQuoteCode > 99 || $nextOppCode < 999){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode . '/' . date("Y");
        }
        else{
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix')  . $nextQuoteCode . '/' . date("Y");
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextQuoteCode'";
        $result = $db->sql_query($SQL);

        return $quoteCode;
    }

    
    /**
     *
     */

    function getAddMultipleJobLineItemValidate1() {


        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

       
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

    function getAddMultipleJobLineItemSubmit1() {

        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id        = $fn->getPostParam('project_id');
        $delivery_order_id          = $fn->getPostParam('delivery_order_id');
            $remarks_arr   = $fn->getPostParam('remarks', array());
            $accessories_arr       = $fn->getPostParam('accessories', array());
            $service_arr       = $fn->getPostParam('service', array());
                    $delivery_note_items_id_arr   = $fn->getPostParam('delivery_note_items_id', array());


            if (!$this->getAddMultipleJobLineItemValidate1()){

                return $validate->getErrorMessageXML();
            }

            $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $count = count($delivery_note_items_id_arr);


            for ($i= 0; $i < $count; $i++) {
                $delivery_note_items_id     = $delivery_note_items_id_arr[$i];
                $accessories = $accessories_arr[$i];
                $service      = $service_arr[$i];
                $remarks     = $remarks_arr[$i];


                    $fa = array();
                    $fa['delivery_order_id']       = $delivery_order_id;
                      $fa['delivery_note_items_id']        = $delivery_note_items_id;

                    $fa['remarks']        = $remarks;
                    $fa['accessories']      = $accessories;
                    $fa['service']          = $service;
                    //$fa['ph_rate']          = $ph_rate;
                    //$fa['scaffold_code']    = $scaffold_code;
                    //$fa['erection']         = $erection;
                    //$fa['dismantle']        = $dismantle;
                    $fa['creation_date']    = date('Y-m-d H:i:s');
                    $fa['created_by']       = $fn->getSessionParam('userName');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'delivery_order_hist');
                    $result = $db->sql_query($insert);
                $delivery_order_hist_id = $db->sql_nextid();


                  
                
            }
        

        return $validate->getSuccessMessageXML();
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
        $delivery_note_id          = $fn->getPostParam('delivery_note_id');
       
            //$partno_arr        = $fn->getPostParam('partno', array());
            $delivery_title_arr         = $fn->getPostParam('delivery_title', array());
            $description_arr   = $fn->getPostParam('description', array());
            $unit_arr          = $fn->getPostParam('unit', array());
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
                $delivery_title        = '';
                $description   = '';
                $unit          = '';
                $quantity      = '';
                //$nationality   = '';
                //$ot_rate       = '';
                //$ph_rate       = '';
                //$scaffold_code = '';
                //$erection      = '';
                //$dismantle     = '';

                $delivery_title       = $delivery_title_arr[$i];
                //$title         = '';
                $description   = $description_arr[$i];
                $unit          = $unit_arr[$i];
             
                $quantity      = $quantity_arr[$i];
                //$remarks     = $remarks_arr[$i];
                //$scaffold_code = $scaffold_code_arr[$i];
                //$erection      = $erection_arr[$i];
                //$dismantle     = $dismantle_arr[$i];

                $chkField      = $description;

                if ($chkField) {
                    $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

                    $fa = array();
                    $fa['project_id']       = $project_id;
                    $fa['delivery_note_id']         = $delivery_note_id;
                    //$fa['part_no']          = $partno;
                    $fa['delivery_title']            = $delivery_title;
                  
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

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'delivery_note_items');
                    $result = $db->sql_query($insert);
                    $delivery_note_items_id = $db->sql_nextid();

                
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

        $delivery_title_arr       = $fn->getPostParam('delivery_title', array());
      
        $validate->resetErrorArray();

        $filterArray3 = array_filter($delivery_title_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Title";
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

        $delivery_note_items_id = $fn->getReqParam('delivery_note_items_id');
 
        $delivery_title        = $fn->getPostParam('delivery_title');
       $description        = $fn->getPostParam('description');
        $unit        = $fn->getPostParam('unit');
        $quantity        = $fn->getPostParam('quantity');
       
            $fa = array();
            $fa = $fn->addToFieldsArray($fa, 'delivery_title');
            $fa = $fn->addToFieldsArray($fa, 'description');
            $fa = $fn->addToFieldsArray($fa, 'quantity');
            $fa = $fn->addToFieldsArray($fa, 'unit');
          

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'delivery_note_items');

        $whereCondition = "WHERE delivery_note_items_id = {$delivery_note_items_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "delivery_note_items", $whereCondition);
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

     function getEditForJobSubmit() {

        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $project_id            = $fn->getReqParam('project_id');
        $job_completion_id = $fn->getReqParam('job_completion_id');
      
        $job_completion_date       = $fn->getPostParam('job_completion_date');   
        $job_title       = $fn->getPostParam('job_title');
        $job_status       = $fn->getPostParam('job_status');
        $company_id       = $fn->getPostParam('company_id');   
        $description       = $fn->getPostParam('description'); 
                $report_no       = $fn->getPostParam('report_no');       
              $job_no       = $fn->getPostParam('job_no');      

       if (!$this->getEditForQuoteValidate()){
            return $validate->getErrorMessageXML();
        }


        $fa = array();
        $fa['job_completion_date']     = $job_completion_date;        
        $fa['job_title']     = $job_title;    
        $fa['description']     = $description;
        $fa['job_status']     = $job_status;
        $fa['company_id']     = $company_id;
        $fa['report_no']     = $report_no;
        $fa['job_no']     = $job_no;



        $whereCondition = "WHERE job_completion_id = {$job_completion_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "job_completion", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }


    /**
     * Line Item Edit Form Submit
     */
    function getEditForQuoteSubmit12() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $project_id                         = $fn->getReqParam('project_id');
        $delivery_note_id                           = $fn->getReqParam('delivery_note_id');
        $title                              = $fn->getPostParam('title');
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
        $drawing_nos                        = $fn->getPostParam('drawing_nos');
        $our_reference                      = $fn->getPostParam('our_reference');
        $intro_quote                        = $fn->getPostParam('intro_quote');
        $total_amount                       = $fn->getPostParam('total_amount');
        $revision                           = $fn->getPostParam('revision');
        $quote_manual_code                  = $fn->getPostParam('quote_manual_code');
        $alternate_email                    = $fn->getPostParam('alternate_email');
        $alternate_name                     = $fn->getPostParam('alternate_name');

        if (!$this->getEditForQuoteValidate()){
            return $validate->getErrorMessageXML();
        }

        $quoteRec = $fn->getRecordRowByID('quote', 'delivery_note_id', $delivery_note_id);
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
        || $drawing_nos                        != $quoteRec['drawing_nos']
        || $our_reference                      != $quoteRec['our_reference']
        || $intro_quote                        != $quoteRec['intro_quote']
        || $total_amount                       != $quoteRec['total_amount']
        || $revision                           != $quoteRec['revision']
        || $quote_manual_code                  != $quoteRec['quote_manual_code']
        || $alternate_email                    != $quoteRec['alternate_email']
        || $alternate_name                     != $quoteRec['alternate_name']
        ) {
            $this->getSaveQuoteLog($delivery_note_id);
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
        $fa['drawing_nos']                        = $drawing_nos;
        $fa['our_reference']                      = $our_reference;
        $fa['intro_quote']                        = $intro_quote;
        $fa['total_amount']                       = $total_amount;
        $fa['payment_method']                     = $payment_method;
        $fa['revision']                           = $revision;
        $fa['quote_manual_code']                  = $quote_manual_code;
        $fa['alternate_email']                    = $alternate_email; 
        $fa['alternate_name']                     = $alternate_name; 
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote');

        $whereCondition = "WHERE delivery_note_id = {$delivery_note_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote", $whereCondition);
        $db->sql_query($SQL);

        // Checking the quote status is confirmed before and changing a new quote record the status will be confirmed old one will be Updated to new
        $sqlQuote = "
        SELECT delivery_note_id FROM quote
        WHERE project_id = {$project_id}
        ";
        $resultQuote  = $db->sql_query($sqlQuote);
        $numRowsQuote = $db->sql_numrows($resultQuote);

        if($quote_status == 'Awarded') {
            $SQL = "UPDATE project SET delivery_note_id = '{$delivery_note_id}' WHERE project_id = {$project_id}";
            $result = $db->sql_query($SQL);

            $this->getGenerateOrderRecords($delivery_note_id, $project_id);

            /* Checking whether opp has more than one quote to update other quotes to new except confirmed quote */
            /*if ($numRowsQuote > 1) {
                $sqlQuoteCondition = "
                SELECT * FROM quote 
                WHERE quote_status = 'Awarded'
                  AND project_id = {$project_id}
                  AND delivery_note_id != {$delivery_note_id}
                ";
                $resultQuoteCondition  = $db->sql_query($sqlQuoteCondition);
                while ($rowQuoteCondition  = $db->sql_fetchrow($resultQuoteCondition)){
                    $sqlUpdateQuote = "
                    UPDATE quote SET quote_status  = 'New' WHERE delivery_note_id = {$rowQuoteCondition['delivery_note_id']}
                    ";
                    $resultQuote  = $db->sql_query($sqlUpdateQuote); 
                }
            }*/
        }

        return $validate->getSuccessMessageXML($quote_status);
    }

    /**
     *
     */
    function getSaveQuoteLog($delivery_note_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $quoteRec = $fn->getRecordRowByID('quote', 'delivery_note_id', $delivery_note_id);

        $faQuoteLog = array();
        $faQuoteLog['delivery_note_id']               = $delivery_note_id;
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
        SELECT * FROM `delivery_note_items`
        WHERE delivery_note_id = {$delivery_note_id}
        ";
        $result = $db->sql_query($sql);
        $count = 1;
        while ($quoteItemsRec = $db->sql_fetchrow($result)) {
            $faQuoteLog = array();
            $faQuoteLog['delivery_note_items_id']      = $quoteItemsRec['delivery_note_items_id'];
            $faQuoteLog['quote_log_id']          = $quote_log_id;
            $faQuoteLog['delivery_note_id']          = $quoteItemsRec['delivery_note_id'];
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

            $sqlQuoteLog = $dbUtil->getInsertSQLStringFromArray($faQuoteLog, 'delivery_note_items_log');
            $resultQuoteLog = $db->sql_query($sqlQuoteLog);
        }
    }

    /**
     * Generate order records from Project
     */
    function getGenerateOrderRecords($delivery_note_id, $project_id){
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
        $faQuote['quote_status']      = 'Awarded';
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by']       = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'delivery_note_id', $delivery_note_id);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'delivery_note_id', $delivery_note_id);
        $projRec    = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['delivery_note_id']             = $delivery_note_id;
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
        FROM delivery_note_items qi
        WHERE qi.delivery_note_id = {$delivery_note_id}
        ORDER BY qi.delivery_note_items_id ASC
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
            $faOi['record_id']        = $row['delivery_note_items_id'];
            $faOi['order_id']         = $order_id;
            $faOi['delivery_note_id']         = $delivery_note_id;
            $faOi['drawing_number']   = $row['drawing_number'];
            $faOi['drawing_title']    = $row['drawing_title'];
            $faOi['drawing_revision'] = $row['drawing_revision'];

            $orderItemRec = $fn->getRecordByCondition('order_item', "record_id = '{$row['delivery_note_items_id']}' AND order_id = {$order_id}");
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

        $delivery_note_id = $fn->getReqParam('delivery_note_id');

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
        WHERE q.delivery_note_id = {$delivery_note_id}
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

    function getDeleteJobLineItem(){

        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $delivery_order_hist_id    = $fn->getReqParam('delivery_order_hist_id');

        $deleteSQL    = "
        DELETE FROM delivery_order_hist
        WHERE delivery_order_hist_id = '{$delivery_order_hist_id}'
        ";
        $result = $db->sql_query($deleteSQL);
    }
}