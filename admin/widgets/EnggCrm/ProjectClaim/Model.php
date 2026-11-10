<?
class CPL_Admin_Widgets_EnggCrm_ProjectClaim_Model extends CP_Common_Lib_WidgetModelAbstract
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
    function getAddClaimFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $project_id = $fn->getReqParam('project_id');
        $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowCompany = $fn->getRecordRowByID('company', 'company_id', $rowProject['company_id']);

        $fa = array();
        $fa['claim_date']    = date("Y-m-d");
        $fa['project_id']    = $project_id;
        $fa['client_id']     = $rowProject['company_id'];
        $fa['project_title'] = $rowProject['title'];
        $fa['status']        = "In Progress";
        $fa['claim_code']    = $this->getUpdateAddClaimCode();
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'project_claim');

        $fn->addRecord($fa, 'project_claim');           
    }

    /**
     *
     */
    function getEditForClaimValidate() {
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
    function getEditForClaimSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $claim_date                  = $fn->getPostParam('claim_date');
        $status                      = $fn->getPostParam('status');
        $project_title               = $fn->getPostParam('project_title');
        $po_quote_no                 = $fn->getPostParam('po_quote_no');
        $ref_no                      = $fn->getPostParam('ref_no');
        $amount                      = $fn->getPostParam('amount');
        $description                 = $fn->getPostParam('description');
        $variation_order_submission  = $fn->getPostParam('variation_order_submission');
        $value_of_contract_work_done = $fn->getPostParam('value_of_contract_work_done');
        $less_previous_retention     = $fn->getPostParam('less_previous_retention');
        $vo_claim_work_done          = $fn->getPostParam('vo_claim_work_done');
        $project_id                  = $fn->getReqParam('project_id');
        $project_claim_id            = $fn->getReqParam('project_claim_id');

        if (!$this->getEditForClaimValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa['claim_date']                  = $claim_date;
        $fa['status']                      = $status;
        $fa['project_title']               = $project_title;
        $fa['po_quote_no']                 = $po_quote_no;
        $fa['ref_no']                      = $ref_no;
        $fa['amount']                      = $amount;
        $fa['description']                 = $description;
        $fa['variation_order_submission']  = $variation_order_submission;
        $fa['value_of_contract_work_done'] = $value_of_contract_work_done;
        $fa['vo_claim_work_done']          = $vo_claim_work_done;
        $fa['less_previous_retention']     = $less_previous_retention;
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'project_claim');

        $whereCondition = "WHERE project_claim_id = {$project_claim_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "project_claim", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML($status);
    }

    /**
     *
     */
    function getAddMultipleClaimItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $title_arr       = $fn->getPostParam('title', array());
        $description_arr = $fn->getPostParam('description', array());


        $validate->resetErrorArray();

        $filterArray1 = array_filter($title_arr);
        $filterArray2 = array_filter($description_arr);
        if (count($filterArray1) == 0 && count($filterArray2) == 0) {
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter details in atlease 1 item";
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
    function getAddMultipleClaimItemFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id       = $fn->getPostParam('project_id');
        $project_claim_id = $fn->getPostParam('project_claim_id');
        $claim_date       = $fn->getPostParam('claim_date');
        $project_title    = $fn->getPostParam('project_title');
        $claim_seq        = $fn->getPostParam('claim_seq');

        $title_arr                = $fn->getPostParam('title', array());
        $description_arr          = $fn->getPostParam('description', array());
        $amount_arr               = $fn->getPostParam('amount', array());
        $current_month_amount_arr = $fn->getPostParam('current_month_amount', array());
        $remarks_arr              = $fn->getPostParam('remarks', array());

        if (!$this->getAddMultipleClaimItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($description_arr);

        for ($i= 0; $i < $count; $i++) {
            $title                =  $title_arr[$i];
            $description          =  $description_arr[$i];
            $amount               =  $amount_arr[$i];
            $current_month_amount =  $current_month_amount_arr[$i];
            $remarks              =  $remarks_arr[$i];

            $faPC = array();
            $faPC['claim_date']    = $claim_date;
            $faPC['project_title'] = $project_title;
            $faPC = $fn->addModificationDetailsToFieldsArray($faPC, 'project_claim');
            $whereConditionPC = "WHERE project_claim_id = {$project_claim_id}";
            $SQLPC = $dbUtil->getUpdateSQLStringFromArray($faPC, "project_claim", $whereConditionPC);
            $db->sql_query($SQLPC);

            if ($description) {
                $fa = array();
                $fa['project_claim_id'] = $project_claim_id;
                $fa['project_id']       = $project_id;
                $fa['title']            = $title;
                $fa['description']      = $description;
                $fa['status']           = "In Progress";
                $fa['amount']           = $amount;
                $fa['remarks']          = $remarks;
                $fa['created_by']       = $fn->getSessionParam('userName');
                $fa['creation_date']    = date('Y-m-d H:i:s');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'claim_line_items');
                $result = $db->sql_query($insert);
                $claim_line_items_id = $db->sql_nextid();

                if($current_month_amount > 0) {
                    $faPmt = array();
                    $faPmt['date']                = $claim_date;
                    $faPmt['amount']              = $current_month_amount;
                    $faPmt['claim_line_items_id'] = $claim_line_items_id;
                    $faPmt['project_claim_id']    = $project_claim_id;
                    $faPmt['claim_seq']           = $claim_seq;
                    $faPmt['gst']                 = $cpCfg['cp.gstPercentage'];
                    $faPmt['total_amount']        = round(($current_month_amount * $cpCfg['cp.gstPercentage']) / 100, 2);
                    $faPmt['status']              = "In Progress";
                    $faPmt['project_id']          = $project_id;
                    $faPmt['created_by']          = $fn->getSessionParam('userName');
                    $faPmt['creation_date']       = date('Y-m-d H:i:s');

                    $insertPmt = $dbUtil->getInsertSQLStringFromArray($faPmt, 'claim_payment');
                    $resultPmt = $db->sql_query($insertPmt);
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditMultipleClaimItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $title_arr       = $fn->getPostParam('title', array());
        $description_arr = $fn->getPostParam('description', array());

        $validate->resetErrorArray();

        $filterArray1 = array_filter($title_arr);
        $filterArray2 = array_filter($description_arr);
        if (count($filterArray1) == 0 && count($filterArray2) == 0) {
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter details in atlease 1 item";
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
    function getEditMultipleClaimItemFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id       = $fn->getPostParam('project_id');
        $project_claim_id = $fn->getPostParam('project_claim_id');
        $claim_date       = $fn->getPostParam('claim_date');
        $project_title    = $fn->getPostParam('project_title');
        //$claim_seq        = $fn->getPostParam('claim_seq');

        $title_arr               = $fn->getPostParam('title', array());
        $description_arr         = $fn->getPostParam('description', array());
        $amount_arr              = $fn->getPostParam('amount', array());
        $claim_line_items_id_arr = $fn->getPostParam('claim_line_items_id', array());
        $claim_status_arr        = $fn->getPostParam('claim_status', array());

        if (!$this->getEditMultipleClaimItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($description_arr);
        for ($i = 0; $i < $count; $i++) {
            $title               = $title_arr[$i];
            $description         = $description_arr[$i];
            $amount              = $amount_arr[$i];
            $claim_line_items_id = $claim_line_items_id_arr[$i];
            $status              = $claim_status_arr[$i];

            $faPC = array();
            $faPC['claim_date']    = $claim_date;
            $faPC['project_title'] = $project_title;
            $faPC = $fn->addModificationDetailsToFieldsArray($faPC, 'project_claim');
            $whereConditionPC = "WHERE project_claim_id = {$project_claim_id}";
            $SQLPC = $dbUtil->getUpdateSQLStringFromArray($faPC, "project_claim", $whereConditionPC);
            $db->sql_query($SQLPC);

            if ($description) {
                $previousDescription = "";
                $previousAmount      = "";
                if($claim_line_items_id) {
                    $sqlClaimItems ="
                    SELECT ct.*
                    FROM claim_line_items ct
                    WHERE claim_line_items_id = '{$claim_line_items_id}'
                    ";
                    $resultClaimItems = $db->sql_query($sqlClaimItems);
                    $rowClaimItems    = $db->sql_fetchrow($resultClaimItems);

                    if($rowClaimItems['description'] != "") {
                        $previousDescription = $rowClaimItems['description'];
                    }

                    if($rowClaimItems['amount'] != "") {
                        $previousAmount = $rowClaimItems['amount'];
                    }

                    $fa = array();
                    $fa['project_claim_id']  = $project_claim_id;
                    $fa['project_id']        = $project_id;
                    $fa['title']             = $title;
                    $fa['description']       = $description;
                    $fa['amount']            = $amount;
                    $fa['status']            = $status;
                    $fa['modified_by']       = $fn->getSessionParam('userName');
                    $fa['modification_date'] = date('Y-m-d H:i:s');

                    $whereCondition = "WHERE claim_line_items_id = {$claim_line_items_id}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "claim_line_items", $whereCondition);
                    $db->sql_query($SQL);

                    $SQLPaymentCheck = "
                    SELECT *
                    FROM claim_payment
                    WHERE claim_line_items_id = '{$claim_line_items_id}'
                      ORDER BY claim_payment_id DESC
                    ";
                    $resultPaymentCheck  = $db->sql_query($SQLPaymentCheck);
                    $numRowsPaymentCheck = $db->sql_numrows($resultPaymentCheck);

                    if($numRowsPaymentCheck > 0) {
                        $rowPaymentCheck = $db->sql_fetchrow($resultPaymentCheck);

                        $faPmt = array();
                        $faPmt['description']       = $previousDescription;
                        $faPmt['claim_amount']      = $previousAmount;
                        $faPmt['modified_by']       = $fn->getSessionParam('userName');
                        $faPmt['modification_date'] = date('Y-m-d H:i:s');

                        $whereConditionPmt = "WHERE claim_payment_id = '{$rowPaymentCheck['claim_payment_id']}'";
                        $SQLPmt = $dbUtil->getUpdateSQLStringFromArray($faPmt, "claim_payment", $whereConditionPmt);
                        $db->sql_query($SQLPmt);
                    }
                } else {
                    $fa = array();
                    $fa['project_claim_id'] = $project_claim_id;
                    $fa['project_id']       = $project_id;
                    $fa['title']            = $title;
                    $fa['status']           = $status;
                    $fa['description']      = $description;
                    $fa['amount']           = $amount;
                    $fa['created_by']       = $fn->getSessionParam('userName');
                    $fa['creation_date']    = date('Y-m-d H:i:s');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'claim_line_items');
                    $result = $db->sql_query($insert);
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateAddClaimCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextClaimCode = $fn->getSettingsValueByKey("nextClaimCode");

        if($nextClaimCode < 10){
            $claimCode = $fn->getSettingsValueByKey('claimCodePrefix') . '000' . $nextClaimCode;
        }
        else if($nextClaimCode < 99){
            $claimCode = $fn->getSettingsValueByKey('claimCodePrefix') . '00' . $nextClaimCode;
        }
        else if($nextClaimCode > 99 || $nextClaimCode < 999){
            $claimCode = $fn->getSettingsValueByKey('claimCodePrefix') . '0' . $nextClaimCode;
        }
        else{
            $claimCode = $fn->getSettingsValueByKey('claimCodePrefix')  . $nextClaimCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextClaimCode'";
        $result = $db->sql_query($SQL);

        return $claimCode;
    } 

    /**
     *
     */
    function getAddMultipleClaimPaymentItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $current_month_amount_arr = $fn->getPostParam('current_month_amount', array());
        $validate->resetErrorArray();

        $filterArray1 = array_filter($current_month_amount_arr);
        if (count($filterArray1) == 0) {
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter current month amount in atlease 1 item";
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
    function getAddMultipleClaimPaymentItemFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id       = $fn->getPostParam('project_id');
        $project_claim_id = $fn->getPostParam('project_claim_id');
        $claim_date       = $fn->getPostParam('claim_date');
        $project_title    = $fn->getPostParam('project_title');
        $claim_seq        = $fn->getPostParam('claim_seq');

        $remarks_arr              = $fn->getPostParam('remarks', array());
        $claim_line_items_id_arr  = $fn->getPostParam('claim_line_items_id', array());
        $current_month_amount_arr = $fn->getPostParam('current_month_amount', array());

        if (!$this->getAddMultipleClaimPaymentItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($current_month_amount_arr);
        for ($i = 0; $i < $count; $i++) {
            $remarks              = $remarks_arr[$i];
            $claim_line_items_id  = $claim_line_items_id_arr[$i];
            $current_month_amount = $current_month_amount_arr[$i];

            $sqlClaimItems ="
            SELECT ct.title
                  ,ct.description
                  ,ct.amount
            FROM claim_line_items ct
            WHERE ct.claim_line_items_id = {$claim_line_items_id}
              AND ct.project_claim_id    = {$project_claim_id}
            ";
            $resultClaimItems = $db->sql_query($sqlClaimItems);
            $rowClaimItems    = $db->sql_fetchrow($resultClaimItems);

            $title       = $rowClaimItems['title'];
            $description = $rowClaimItems['description'];
            $amount      = $rowClaimItems['amount'];

            if ($description) {
                $previousDescription = "";
                $previousAmount      = "";
                if($claim_line_items_id) {
                    if($rowClaimItems['description'] != "") {
                        $previousDescription = $rowClaimItems['description'];
                    }

                    if($rowClaimItems['amount'] != "") {
                        $previousAmount = $rowClaimItems['amount'];
                    }

                    $fa = array();
                    $fa['project_claim_id']  = $project_claim_id;
                    $fa['project_id']        = $project_id;
                    $fa['title']             = $title;
                    $fa['description']       = $description;
                    $fa['amount']            = $amount;
                    $fa['remarks']           = $remarks;
                    $fa['modified_by']       = $fn->getSessionParam('userName');
                    $fa['modification_date'] = date('Y-m-d H:i:s');

                    $whereCondition = "WHERE claim_line_items_id = {$claim_line_items_id}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "claim_line_items", $whereCondition);
                    $db->sql_query($SQL);

                    if($current_month_amount > 0) {
                        $faPmt = array();
                        $faPmt['date']                = $claim_date;
                        $faPmt['amount']              = $current_month_amount;
                        $faPmt['claim_line_items_id'] = $claim_line_items_id;
                        $faPmt['project_claim_id']    = $project_claim_id;
                        $faPmt['claim_seq']           = $claim_seq;
                        $faPmt['status']              = "In Progress";
                        $faPmt['project_id']          = $project_id;
                        $faPmt['gst']                 = $cpCfg['cp.gstPercentage'];
                        $faPmt['total_amount']        = round(($current_month_amount * $cpCfg['cp.gstPercentage']) / 100, 2);
                        $faPmt['created_by']          = $fn->getSessionParam('userName');
                        $faPmt['creation_date']       = date('Y-m-d H:i:s');

                        $insertPmt = $dbUtil->getInsertSQLStringFromArray($faPmt, 'claim_payment');
                        $resultPmt = $db->sql_query($insertPmt);
                    }
                } else {
                    $fa = array();
                    $fa['project_claim_id'] = $project_claim_id;
                    $fa['project_id']       = $project_id;
                    $fa['title']            = $title;
                    $fa['description']      = $description;
                    $fa['amount']           = $amount;
                    $fa['created_by']       = $fn->getSessionParam('userName');
                    $fa['creation_date']    = date('Y-m-d H:i:s');

                    $insert              = $dbUtil->getInsertSQLStringFromArray($fa, 'claim_line_items');
                    $result              = $db->sql_query($insert);
                    $claim_line_items_id = $db->sql_nextid();

                    if($current_month_amount > 0) {
                        $faPmt = array();
                        $faPmt['date']                = $claim_date;
                        $faPmt['amount']              = $current_month_amount;
                        $faPmt['claim_line_items_id'] = $claim_line_items_id;
                        $faPmt['project_claim_id']    = $project_claim_id;
                        $faPmt['claim_seq']           = $claim_seq;
                        $faPmt['status']              = "In Progress";
                        $faPmt['project_id']          = $project_id;
                        $faPmt['gst']                 = $cpCfg['cp.gstPercentage'];
                        $faPmt['total_amount']        = round(($current_month_amount * $cpCfg['cp.gstPercentage']) / 100, 2);
                        $faPmt['created_by']          = $fn->getSessionParam('userName');
                        $faPmt['creation_date']       = date('Y-m-d H:i:s');

                        $insertPmt = $dbUtil->getInsertSQLStringFromArray($faPmt, 'claim_payment');
                        $resultPmt = $db->sql_query($insertPmt);
                    }
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditMultipleClaimPaymentItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        /*$title_arr       = $fn->getPostParam('title', array());
        $description_arr = $fn->getPostParam('description', array());*/


        $validate->resetErrorArray();

        /*$filterArray1 = array_filter($title_arr);
        $filterArray2 = array_filter($description_arr);
        if (count($filterArray1) == 0 && count($filterArray2) == 0) {
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter details in atlease 1 item";
        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditMultipleClaimPaymentItemFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id       = $fn->getPostParam('project_id');
        $project_claim_id = $fn->getPostParam('project_claim_id');
        $claim_date       = $fn->getPostParam('claim_date');
        $claim_seq        = $fn->getPostParam('claim_seq');

        $claim_payment_id_arr     = $fn->getPostParam('claim_payment_id', array());
        $claim_status_arr         = $fn->getPostParam('claim_status', array());
        $current_month_amount_arr = $fn->getPostParam('current_month_amount', array());

        if (!$this->getEditMultipleClaimPaymentItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($current_month_amount_arr);
        for ($i = 0; $i < $count; $i++) {
            $claim_payment_id     = $claim_payment_id_arr[$i];
            $claim_status         = $claim_status_arr[$i];
            $current_month_amount = $current_month_amount_arr[$i];

            $faPmt = array();
            $faPmt['amount']            = $current_month_amount;
            $faPmt['status']            = $claim_status;
            $faPmt['date']              = $claim_date;
            $faPmt['claim_seq']         = $claim_seq;
            $faPmt['gst']               = $cpCfg['cp.gstPercentage'];
            $faPmt['total_amount']      = round(($current_month_amount * $cpCfg['cp.gstPercentage']) / 100, 2);
            $faPmt['modified_by']       = $fn->getSessionParam('userName');
            $faPmt['modification_date'] = date('Y-m-d H:i:s');

            $whereConditionPmt = "WHERE claim_payment_id = '{$claim_payment_id}'";
            $SQLPmt = $dbUtil->getUpdateSQLStringFromArray($faPmt, "claim_payment", $whereConditionPmt);
            $db->sql_query($SQLPmt);
        }

        return $validate->getSuccessMessageXML();
    }

}