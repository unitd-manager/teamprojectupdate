<?
class CP_Admin_Modules_AgileIms_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT i.*
            ,co.contact_id
            ,co.first_name AS contact_name
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
                                        i.invoice_code LIKE '%{$tv['keyword']}%' OR
                                        co.first_name  LIKE '%{$tv['keyword']}%' OR
                                        i.receipt_code LIKE '%{$tv['keyword']}%' OR
                                        i.order_id     LIKE '%{$tv['keyword']}%' OR
                                        c.title        LIKE '%{$tv['keyword']}%'
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
        $searchVar->sortOrder = "i.invoice_id DESC";
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

        $this->getFetchInvoiceCode($id, $fa['project_id'], $invoice_sequence);

        $cpUtil->redirect("index.php?_topRm=finance&module={$tv['module']}&_action=detail&record_id={$id}");
    }

    /**
     *
     */
    function getFetchInvoiceCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

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
    function getCancelInvoice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $invoiceRec   = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");
        
        /* Finding of receipt record */
        $sqlIrh = "
        SELECT SUM(irh.amount) AS total_amount_paid
              ,i.invoice_id
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code = '{$invoice_code}'
          AND irh.amount > 0
        ";
        $resultIrh = $db->sql_query($sqlIrh);
        $rowIrh    = $db->sql_fetchrow($resultIrh);
        
        if ($rowIrh['total_amount_paid'] > 0) {
            return "Cannot cancel"; // Passing the value to jquery to give alert message
        } else {
            /* Updating of invoice record */
            $today = date('Y-m-d H:i:s');
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
               ,modification_date = '{$today}'
               ,modified_by = '{$fn->getSessionParam('userName')}'
            WHERE invoice_code = '{$invoice_code}'
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
    function getPrintAgeingReport() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        $media = Zend_Registry::get('media');
        
        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $enrollment_type    = $fn->getReqParam('enrollment_type');
        $company_contact_id = $fn->getReqParam('company_contact_id');

        if ($enrollment_type == 'Individual') {
            $sql = "
            SELECT c.first_name AS company_contact_name
                  ,c.address_flat AS address1
                  ,c.address_street AS address2
                  ,c.address_po_code AS po_code
                  ,gc.name AS country_name
            FROM contact c
            LEFT JOIN (`order` o)      ON (c.contact_id      = o.contact_id)
            LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
            WHERE c.contact_id = {$company_contact_id}
            ";
        } else {
            $sql = "
            SELECT c.title AS company_contact_name
                  ,c.address1
                  ,c.address2
                  ,c.address_po_code AS po_code
                  ,gc.name AS country_name
            FROM company c
            LEFT JOIN (`order` o)      ON (c.company_id           = o.company_id)
            LEFT JOIN (geo_country gc) ON (c.address_country_code = gc.country_code)
            WHERE c.company_id = {$company_contact_id}
            ";
        }
        $result = $db->sql_query($sql);
        $rowIntro = $db->sql_fetchrow($result);

        $template = 'Statement.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = $rowIntro['company_contact_name'] . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name.'.xlsx');

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;

        $current_date = date('Y-m-d');

        if ($enrollment_type == 'Individual') {
            $sqlJoin  = "LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)";
            $sqlWhere = " AND o.contact_id = {$company_contact_id}";
        } else {
            $sqlJoin  = "LEFT JOIN (company c) ON (o.company_id = c.company_id)";
            $sqlWhere = " AND o.company_id = {$company_contact_id}";
        }

        $SQL = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        {$sqlJoin}
        WHERE (i.status = 'Due' OR i.status = 'Partial Payment')
              {$sqlWhere}
          AND i.invoice_date <= '{$current_date}'
        ORDER BY i.invoice_date ASC
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $total_outstanding_amount = 0;
        $arr            = array();
        $blkMain        = array();
        
        $blkInvCode     = array();
        $blkInvDate     = array();
        $blkStudName    = array();
        $blkInvMonth    = array();
        $blkEnrollYear  = array();
        $blkAmount      = array();
        $blkAmountPaid  = array();
        
        $arr['parent_name']     = $rowIntro['company_contact_name'];
        $arr['address_flat']    = $rowIntro['address1'];
        $arr['address_street']  = $rowIntro['address2'];
        $arr['address_country'] = $rowIntro['country_name'] . " - " . $rowIntro['po_code'];

        while ($row = $db->sql_fetchrow($result)) {

            $invoice_id = $row['invoice_id'];

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$invoice_id}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $receipt_amount = $rowRec['total_invoice_amount_paid'];
            if ($rowRec['total_invoice_amount_paid'] == '') {
                $receipt_amount = 0;
            }
            $arrReceipt = array('amount_paid' => $receipt_amount);
            $blkAmountReceived[] = $arrReceipt;

            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD/MM/YYYY');

            $amount_payable = $row['invoice_amount'];

            //repeating rows of product values
            $arr11 = array('invoice_code' => $row['invoice_code']);
            $blkInvCode[] = $arr11;

            $arr2 = array('invoice_date' => $invoice_date);
            $blkInvDate[] = $arr2;

            $arr1 = array('invoice_amt' => number_format($row['invoice_amount'], 2));
            $blkInvAmt[] = $arr1;

            $arr6 = array('amount_payable' => $row['invoice_code']);
            $blkAmount[] = $arr6;

            $amount_payable_after_rec = $row['invoice_amount'] - $rowRec['total_invoice_amount_paid'];
            $amount_payable_after_rec = number_format($amount_payable_after_rec, 2);
            $arrAmtPayable = array('amount_payable_after_rec' => $amount_payable_after_rec);
            $blkAmtPayable[] = $arrAmtPayable;

            $total_outstanding_amount += $amount_payable_after_rec;

            $serialNo++;
        }

        $arr['total_outstanding_amount'] = number_format($total_outstanding_amount, 2);
        $arr['current_date']             = $fn->getCPDate($current_date, 'd M Y');;
        $arr['30days_due']               = number_format($this->getPastBalanceAmount($enrollment_type, $company_contact_id, $current_date, 30), 2);
        $arr['30moredays_due']           = number_format($this->getPastBalanceAmount($enrollment_type, $company_contact_id, $current_date, 61), 2);

        $blkMain[] = $arr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkInvCode', $blkInvCode);
        $TBS->MergeBlock('blkInvDate', $blkInvDate);
        $TBS->MergeBlock('blkInvAmt', $blkInvAmt);
        $TBS->MergeBlock('blkAmount', $blkAmount);
        $TBS->MergeBlock('blkAmountReceived', $blkAmountReceived);
        $TBS->MergeBlock('blkAmtPayable', $blkAmtPayable);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPastBalanceAmount($enrollment_type, $company_contact_id, $end_date, $no_of_days){
        $db = Zend_Registry::get('db');
        
        if ($no_of_days == 30) {
            $start_date = date('Y-m-d', strtotime($end_date . " -{$no_of_days} days"));;
            
            $sqlAppend  = "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y-m-d', strtotime($end_date . " -31 days"));;
            $sqlAppend = "< '{$start_date}'";
        }
        
        if ($enrollment_type == 'Individual') {
            $sqlJoin  = "LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)";
            $sqlWhere = " AND o.contact_id = {$company_contact_id}";
        } else {
            $sqlJoin  = "LEFT JOIN (company c) ON (o.company_id = c.company_id)";
            $sqlWhere = " AND o.company_id = {$company_contact_id}";
        }

        $sqlInvoice = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        {$sqlJoin}
        WHERE (i.status = 'Due' OR i.status = 'Partial Payment')
              {$sqlWhere}
          AND i.invoice_date {$sqlAppend}
        ORDER BY i.invoice_date ASC
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);

        $total_invoice_amount     = 0;
        $total_outstanding_amount = 0;
        
        while ($rowInvoice = $db->sql_fetchrow($resultInvoice)) {
            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$rowInvoice['invoice_id']}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $amount_payable_after_rec = $rowInvoice['invoice_amount'] - $rowRec['total_invoice_amount_paid'];
            $total_outstanding_amount += $amount_payable_after_rec;
        }
        
        $total_invoice_amount = number_format($total_outstanding_amount, 2);
        
        return $total_invoice_amount;
    }
}
