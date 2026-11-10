<?
class CP_Admin_Modules_AceIms_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT i.*
            ,co.contact_id
            ,CONCAT_WS(' ', co.first_name, co.last_name) AS contact_name
            ,c.company_id
            ,c.title
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'i';

        $invoice_id = $fn->getReqParam('invoice_id');
        $record_id  = $fn->getReqParam('record_id');
        $company_id = $fn->getReqParam('company_id');
        $status     = $fn->getReqParam('status');
        $date1      = $fn->getReqParam('date1');
        $date2      = $fn->getReqParam('date2');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$tv['record_id']}'";
        } else if ($invoice_id != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$invoice_id}'";
        } else {
            if ($status != "") {
                if ($status == "Due" ) {
                    $searchVar->sqlSearchVar[] = "(i.status =  'Due' || i.status  =  'Late')" ;
                } else {
                    $searchVar->sqlSearchVar[] = "i.status   = '{$status}'";
                }
            }

            if ($date1 != "" && $date2 != "") {
                $searchVar->sqlSearchVar[] = "(i.invoice_date BETWEEN '{$date1}' AND '{$date2}')";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "c.company_id   = '{$company_id}'";
            }

            if ($invoice_id != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_id   = '{$invoice_id}'";
            }

            if ($record_id != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_id   = '{$record_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        i.invoice_code   LIKE '%{$tv['keyword']}%' OR
                                        co.first_name  LIKE '%{$tv['keyword']}%' OR
                                        co.last_name  LIKE '%{$tv['keyword']}%' OR
                                        i.receipt_code   LIKE '%{$tv['keyword']}%' OR
                                        i.order_id   LIKE '%{$tv['keyword']}%' OR
                                        c.title  LIKE '%{$tv['keyword']}%'
                                       )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('project_id', 'Please choose the project');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'invoice_code');
        $fa = $fn->addToFieldsArray($fa, 'invoice_date');
        $fa = $fn->addToFieldsArray($fa, 'invoice_amount');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        return $fa;
    }
    /**
     *
     */
    function getInvoiceSQLForPager() {

        $SQL = "
        SELECT count(*)
        FROM invoice i
        LEFT JOIN (project p)    ON (p.project_id = i.project_id    )
        LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id )
        LEFT JOIN (company c)    ON (c.company_id = p.company_id    )
        ";

        return $SQL;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Invoice_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Currency Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Contact Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        //============================================================================= //
        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;

            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_amount']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getRaiseInvoice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $project_id = $fn->getReqParam('project_id');

        $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $compRec = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $totalPriorInvSQL = "
		SELECT SUM(invoice_amount) AS total_pi_amount
		FROM invoice
		WHERE project_id = {$project_id}
		AND status != LOWER('Cancelled')
       	";
        $resultPI = $db->sql_query($totalPriorInvSQL);
        $rowPI    = $db->sql_fetchrow($resultPI);

        $invoice_sequence = $this->getNextInvoiceSeq($project_id);

        $fa = array();
        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['invoice_date']      = date("Y-m-d");
        $fa['project_id']        = $project_id;
        $fa['invoice_amount']    = ($rowPI['total_pi_amount'] > 0) ?  ($projRec['project_value'] - $rowPI['total_pi_amount']) : $projRec['project_value'];
        $fa['status']            = "Due";
        $fa['invoice_sequence']  = $invoice_sequence;
        $fa['inv_currency']      = $projRec['currency'];

        $id = $fn->addRecord($fa);

        $this->getUpdateInvoiceCode($id, $fa['project_id'], $invoice_sequence);

        $cpUtil->redirect("index.php?_topRm=finance&module={$tv['module']}&_action=detail&record_id={$id}");
    }

    /**
     *
     */
    function getNextInvoiceSeq($project_id) {
        $db = Zend_Registry::get('db');

        $SQL    = "
        SELECT MAX(invoice_sequence)
        FROM invoice
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row[0]+1;
    }

    /**
     *
     */
    function getUpdateInvoiceCode($invoice_id, $project_id, $sequence) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

        if ($project_id != "") {
            $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
            $project_code = $projRec['project_code'];

            $invoice_prefix = $fn->getSettingsValueByKey("invoiceCodePrefix");
            $project_prefix = $fn->getSettingsValueByKey("projectCodePrefix");
            $invCodeStartIndex = strlen($project_prefix) + 1;

            if ($cpCfg['m.project.invoice.hasAutoAffix'] == 0){
                $SQL = "
                UPDATE invoice
                SET invoice_code = CONCAT_WS('', '{$invoice_prefix}'
                                                ,SUBSTRING('{$projRec['project_code']}' FROM {$invCodeStartIndex})
                                                ,'-'
                                                , '{$sequence}'
                                            )
                WHERE invoice_id = {$invoice_id}
                ";
                $result = $db->sql_query($SQL);
            } else {
                $prefixZeros    = "000000";
                $invoiceSerial  = $fn->getSettingsValueByKey("nextInvoiceCode");

                if ($site_id) {
                    $SQL     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
                } else {
                    $SQL     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
                }
                $result  = $db->sql_query($SQL);
                $project_code = substr($project_code, strlen($project_prefix));

                $invoiceCode = $invoice_prefix. $project_code ."-".  substr ($prefixZeros, 0, 6 - strlen($invoiceSerial)) . $invoiceSerial;

                $SQL = "
                UPDATE invoice
                SET invoice_code = '{$invoiceCode}'
                WHERE invoice_id = {$invoice_id}
                ";
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function getGenerateInvoiceForCurrentMonth() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $site_id        = $fn->getSessionParam('cp_site_id');

        $month          = $fn->getReqParam('current_month');
        $current_year   = date('Y');
        $rowDate        = $fn->getRecordByCondition('setting', "key_text = 'invoiceDate'");
        //$rowAmount      = $fn->getRecordByCondition('setting', "key_text = 'invoiceAmount'");

        $sql = "
        SELECT DISTINCT cc.contact_id
              ,cc.order_id
        FROM course_contact cc
        LEFT JOIN (parent_contact pc) ON (cc.contact_id = pc.contact_id)
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (`order` o) ON (p.parent_id = o.parent_id)
        WHERE cc.year_of_enrollment = '{$current_year}'
           AND p.mode_of_payment = 'Giro'
           OR p.mode_of_payment = 'Cash'
        ";
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result)) {
            $contact_id = $row['contact_id'];
            $order_id   = $row['order_id'];

            # Checking whether the invoice is created for the contact already
            $sqlInv = "
            SELECT invoice_id FROM invoice
            WHERE contact_id = '{$row['contact_id']}'
              AND invoice_month = '{$month}'
            ";
            $resultInv    = $db->sql_query($sqlInv);
            $numRowsInv   = $db->sql_numrows($resultInv);

            if ($numRowsInv == 0) {

                $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");

                if($nextInvoiceCode < 10) {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '000' . $nextInvoiceCode;
                } else if($nextInvoiceCode < 99) {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '00' . $nextInvoiceCode;
                } else if($nextInvoiceCode < 999) {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '0' . $nextInvoiceCode;
                } else {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
                }

                $fa = array();
                $fa['order_id']         = $order_id;
                $fa['contact_id']       = $contact_id;
                $fa['invoice_code']     = $nextInvoiceCode;
                $fa['invoice_month']    = $month;
                $fa['invoice_date']     = $current_year . '-' . $month . '-' . '1';
                $fa['invoice_amount']   = "60";
                $fa['status']           = 'Due';
                $fa['creation_date']    = date("Y-m-d H:i:s");
                $invoice_id             = $fn->addRecord($fa, 'invoice');

                if ($site_id) {
                    $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
                } else {
                    $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
                }
                $resultUpdate    = $db->sql_query($SQLUpdate);

                /*
                $modObj = getCPModuleObj('aceIms_order');
                $modObj->model->getGenerateInvoiceForEntMedia($invoice_id);
                */
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFetchInvoiceCode($order_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        /*
        $sqlCount = "
        SELECT invoice_id
        FROM invoice
        WHERE year_of_enrollment = {$orderRec['year_of_enrollment']}
        ";
        $resultCount  = $db->sql_query($sqlCount);
        $numRowsCount = $db->sql_numrows($resultCount);

        $invCodeNo = $numRowsCount + 1;
        */

        /* Setting of Invoice code */
        $invCodeNo = $fn->getSettingsValueByKey("nextInvoiceCode");   //  eg: 123

        if($invCodeNo < 10) {
            $invoice_code = '000' . $invCodeNo;
        } else if($invCodeNo < 99) {
            $invoice_code = '00' . $invCodeNo;
        } else if($invCodeNo < 999) {
            $invoice_code = '0' . $invCodeNo;
        } else {
            $invoice_code = $invCodeNo;
        }

        return $invoice_code;
    }

    /**
     *
     */
    function getEditInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $invoice_id       = $fn->getReqParam('invoice_id');
        $invoice_date     = $fn->getPostParam('invoice_date');
        $invoice_due_date = $fn->getPostParam('invoice_due_date');
        $invoice_terms    = $fn->getPostParam('invoice_terms');
        $notes            = $fn->getPostParam('notes');

        if (!$this->getEditInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $faInv = array();
        $faInv['invoice_date']     = $invoice_date;
        $faInv['invoice_due_date'] = $invoice_due_date;
        $faInv['invoice_terms']    = $invoice_terms;
        $faInv['notes']            = $notes;

        $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditInvoiceFormValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $invoice_date     = $fn->getPostParam('invoice_date');
        $invoice_due_date = $fn->getPostParam('invoice_due_date');

        $validate->resetErrorArray();
        $validate->validateData('invoice_date' , 'Please enter invoice date');

        if ($invoice_due_date != '' && ($invoice_date > $invoice_due_date)) {
            $validate->errorArray['invoice_due_date']['name'] = "invoice_due_date";
            $validate->errorArray['invoice_due_date']['msg']  = "Due date should not be less than invoice date.";
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
    function getGenerateInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $trainee_id_arr = $fn->getPostParam('traineeId', array());
        $order_id       = $fn->getReqParam('order_id');

        $total_amount_payable = 0;
        $count = count($trainee_id_arr);
        for ($i= 0; $i< $count; $i++) {
            $trainee_id = $trainee_id_arr[$i];

            $sqlOiSubject = "
            SELECT order_item_id
            FROM order_item
            WHERE order_id = {$order_id}
              AND contact_id = {$trainee_id}
              AND module = 'aceIms_subject'
            ";
            $resultOiSubject  = $db->sql_query($sqlOiSubject);
            $numRowsOiSubject = $db->sql_numrows($resultOiSubject);

            if ($numRowsOiSubject) {
                $sqlOiSum = "
                SELECT SUM(unit_price) AS total_amount_payable
                FROM order_item
                WHERE order_id = {$order_id}
                  AND contact_id = {$trainee_id}
                  AND module != 'aceIms_course'
                ";
                $resultOiSum = $db->sql_query($sqlOiSum);
                $rowOiSum    = $db->sql_fetchrow($resultOiSum);
            } else {
                $sqlOiSum = "
                SELECT SUM(unit_price) AS total_amount_payable
                FROM order_item
                WHERE order_id = {$order_id}
                  AND contact_id = {$trainee_id}
                ";
                $resultOiSum = $db->sql_query($sqlOiSum);
                $rowOiSum    = $db->sql_fetchrow($resultOiSum);
            }
            $total_amount_payable += $rowOiSum['total_amount_payable'];
        }

        $invoice_code = $this->getFetchInvoiceCode($order_id);

        /* Creating a new invoice */
        $faInv = array();
        $faInv['invoice_date']     = date('Y-m-d');
        $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+30 days"));
        $faInv['status']           = 'Due';
        $faInv['invoice_amount']   = $total_amount_payable;
        $faInv['created_by']       = $fn->getSessionParam('userName');
        $faInv['creation_date']    = date('Y-m-d H:i:s');
        $faInv['invoice_code']     = $invoice_code;
        $faInv['inv_currency']     = 'SGD';
        $faInv['order_id']         = $order_id;

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $faInv['year_of_enrollment']         = $orderRec['year_of_enrollment'];
        $faInv['company_contact_salutation'] = $orderRec['company_contact_salutation'];
        $faInv['company_contact_name']       = $orderRec['company_contact_name'];
        $faInv['cust_first_name']            = $orderRec['cust_first_name'];
        $faInv['cust_email']                 = $orderRec['cust_email'];
        $faInv['cust_address1']              = $orderRec['cust_address1'];
        $faInv['cust_address2']              = $orderRec['cust_address2'];
        $faInv['cust_address_po_code']       = $orderRec['cust_address_po_code'];
        $faInv['cust_address_country_code']  = $orderRec['cust_address_country_code'];
        $faInv['contact_reg_no']             = $orderRec['contact_reg_no'];

        $gst_percentage = $fn->getSettingsValueByKey("gstPercentage");
        if ($gst_percentage) {
            $faInv['gst_percentage']         = $gst_percentage;
        }

        $invoice_id                = $fn->addRecord($faInv, 'invoice');

        /* Increment of Invoice Code */
        $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $selectContactIds = join(',', $trainee_id_arr);

        $sqlOi = "
        SELECT * FROM order_item
        WHERE order_id = {$order_id}
          AND contact_id IN ($selectContactIds)
        ";
        $resultOi = $db->sql_query($sqlOi);
        while ($rowOi = $db->sql_fetchrow($resultOi)) {
            /* Creating a invoice item records */
            $faInvItem = array();
            $faInvItem['record_id']         = $rowOi['record_id'];
            $faInvItem['qty']               = $rowOi['qty'];
            $faInvItem['unit_price']        = $rowOi['unit_price'];
            $faInvItem['item_title']        = $rowOi['item_title'];
            $faInvItem['module']            = $rowOi['module'];
            $faInvItem['contact_id']        = $rowOi['contact_id'];
            $faInvItem['contact_name']      = $rowOi['contact_name'];
            $faInvItem['subsidy_paid']      = $rowOi['subsidy_paid'];
            $faInvItem['invoice_id']        = $invoice_id;
            $faInvItem['course_start_date'] = $rowOi['course_start_date'];
            $faInvItem['course_end_date']   = $rowOi['course_end_date'];
            $faInvItem['course_code']       = $rowOi['course_code'];
            $invoice_item_id                = $fn->addRecord($faInvItem, 'invoice_item');

            /* Updating Invoice Id to Order Item Table */
            $faOi = array();
            $faOi['invoice_id'] = $invoice_id;
            $fn->saveRecord($faOi, 'order_item', 'order_item_id', $rowOi['order_item_id']);
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCancelInvoice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $invoiceRec   = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");

        /* Finding of receipt record */
        $SQLIrh = "
        SELECT SUM(irh.amount) AS total_amount_paid
              ,i.invoice_code
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code = {$invoice_code}
          AND irh.amount > 0
        ";
        $resultIrh = $db->sql_query($SQLIrh);
        $numRowsIrh = $db->sql_numrows($resultIrh);
        $rowIrh = $db->sql_fetchrow($resultIrh);

        if ($rowIrh['total_amount_paid'] > 0) {
            return "Cannot cancel"; // Passing the value to jquery to give alert message
        } else {
            /* Updating of invoice record */
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
            WHERE invoice_code = {$invoice_code}
            ";
            $resultInv = $db->sql_query($sqlInv);

            /* Setting of Invoice Item to NULL to create invoice again */
            $sqlOrderItem = "
            UPDATE `order_item` oi
            LEFT JOIN (invoice i) ON (oi.invoice_id = i.invoice_id)
            SET oi.invoice_id = NULL
            WHERE oi.invoice_id = {$invoiceRec['invoice_id']}
            ";
            $resultOrderItem = $db->sql_query($sqlOrderItem);
        }

        return;
    }
}
