<?
class CPL_Admin_Modules_EnggCrm_Invoice_Model extends CP_Admin_Modules_EnggCrm_Invoice_Model
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $sqlMaster = Zend_Registry::get('sqlMaster');

        $currentDate  = date("Y-m-d");

        $joinTbls = '';
        $joinFlds = '';
        
        $totalPriorInvSQL = "(
        SELECT FORMAT(SUM(invoice_amount), 0)
        FROM invoice
        WHERE project_id = i.project_id
          AND invoice_code < i.invoice_code
          AND status != LOWER('Cancelled')
        )";
        
        if ($cpCfg['m.enggCrm.hasMultiBranches'] == 1){
            $joinTbls .= "LEFT JOIN branch b ON(p.branch_id = b.branch_id)";
            $joinFlds .= ",b.title AS branch_name";
        }

        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
            if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
                $flds = "
                SELECT GROUP_CONCAT(i.invoice_id SEPARATOR ',') AS record_ids
                ";
            } else {
                $flds = "
                SELECT i.*
                ,cont.contact_id
                ,o.order_id
                ,c.company_id
                ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                ,cont.position as position
                ,cont.company_address_flat
                ,cont.company_address_street
                ,cont.company_address_town
                ,cont.company_address_state
                ,cont.company_address_country
                ,c.company_name
                ,p.title AS project_title
                ,p.project_value AS project_value
                ,p.currency AS project_currency
                ,p.description AS project_description
                ,p.project_code as project_code
                ,ca.address_flat    AS comp_mul_address_flat
                ,ca.address_street  AS comp_mul_address_street
                ,ca.address_town    AS comp_mul_address_town
                ,ca.address_state   AS comp_mul_address_state
                ,ca.address_country AS comp_mul_address_country
                ,DATEDIFF(Now() ,i.invoice_due_date) AS age
                ,(IF(ISNULL({$totalPriorInvSQL}), 0, {$totalPriorInvSQL})) AS prior_invoice_billed
                {$joinFlds}
                ";
            }

            $SQL = "
            {$flds}
            FROM invoice i
            LEFT JOIN (`order` o)    ON (i.order_id = o.order_id    )
            LEFT JOIN (project p)         ON (i.project_id = p.project_id)
            LEFT JOIN (contact cont)      ON (p.contact_id = cont.contact_id)
            LEFT JOIN (company c)         ON (p.company_id = c.company_id)
            LEFT JOIN (company_address ca)ON (cont.company_address_id = ca.company_address_id)
            {$joinTbls}
            ";

        } else {
            if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
                $flds = "
                SELECT GROUP_CONCAT(i.invoice_id SEPARATOR ',') AS record_ids
                ";
            } else {
                $flds = "
                SELECT i.*
                ,cont.contact_id
               
                ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                ,cont.position AS position
                ,c.company_id
                ,c.company_name
                ,c.address_flat
                ,c.address_street
                ,c.address_town
                ,c.address_country
                ,c.address_state
                ,p.title            AS project_title
                ,p.project_value    AS project_value
                ,p.currency AS project_currency
                ,p.description      AS project_description
                ,p.project_code     AS project_code
                ,DATEDIFF(Now() ,i.invoice_due_date) AS age
                ,(SELECT SUM(invoice_amount)
                  FROM invoice
                  WHERE project_id = i.project_id
                ) AS prior_invoice_billed
                ";
            }

            $SQL = "
            {$flds}
            FROM invoice i
            LEFT JOIN (`order` o)    ON (i.order_id = o.order_id    )
            LEFT JOIN (project p)    ON (o.project_id = p.project_id    )
            LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id )
            LEFT JOIN (company c)    ON (c.company_id = o.company_id    )
            ";
        }

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

        $invoice_id    = $fn->getReqParam('invoice_id');
        $record_id     = $fn->getReqParam('record_id');
        $project_id    = $fn->getReqParam('project_id');
        $company_id    = $fn->getReqParam('company_id');
        $company_name  = $fn->getReqParam('company_name');
        $title         = $fn->getReqParam('title');
        $status        = $fn->getReqParam('status');
        $invoice_type  = $fn->getReqParam('invoice_type');
        $invoice_date1 = $fn->getReqParam('invoice_date_1');
        $invoice_date2 = $fn->getReqParam('invoice_date_2');
        $due_date1     = $fn->getReqParam('due_date_1');
        $due_date2     = $fn->getReqParam('due_date_2');
        $paid_date1    = $fn->getReqParam('paid_date_1');
        $paid_date2    = $fn->getReqParam('paid_date_2');
        $yearMonth     = $fn->getReqParam('yearMonth');
        $branch_id     = $fn->getReqParam('branch_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$tv['record_id']}'";
        } else if ($invoice_id != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$invoice_id}'";
        } else {
    
            if ($invoice_date1 != "" && $invoice_date2 != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_date BETWEEN  '{$invoice_date1}' AND '{$invoice_date2}'
                )";
            }
    
            if ($due_date1 != "" && $due_date2 != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_due_date BETWEEN  '{$due_date1}' AND '{$due_date2}'
                )";
            }
    
            if ($paid_date1 != "" && $paid_date2 != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_paid_date BETWEEN  '{$paid_date1}' AND '{$paid_date2}'
                )";
            }

            if ($branch_id != "") {
                $searchVar->sqlSearchVar[] = "p.branch_id = '{$branch_id}'";
            }

            if ($status != "") {
                if ($status == "Due" ) {
                    $searchVar->sqlSearchVar[] = "(i.status =  'Due' || i.status  =  'Late')" ;
                } else {
                    $searchVar->sqlSearchVar[] = "i.status   = '{$status}'";
                }
            }    
    
            if ($project_id != "") {
                $searchVar->sqlSearchVar[] = "i.project_id   = '{$project_id}'";
            }
    
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "c.company_id   = '{$company_id}'";
            }
        
            if ($invoice_id != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_id   = '{$invoice_id}'";
            }
    
            if ($invoice_type != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_type   = '{$invoice_type}'";
            }
    
            if ($record_id != "") {
                $searchVar->sqlSearchVar[] = "i.invoice_id   = '{$record_id}'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        i.invoice_code      LIKE '%{$tv['keyword']}%' OR
                                        i.invoice_amount    LIKE '%{$tv['keyword']}%' OR
                                        p.title             LIKE '%{$tv['keyword']}%' OR
                                        i.project_id        LIKE '%{$tv['keyword']}%' OR
                                        i.invoice_code_user LIKE '%{$tv['keyword']}%' OR
                                        i.po_number         LIKE '%{$tv['keyword']}%' OR
                                        i.project_reference         LIKE '%{$tv['keyword']}%' OR
                                        c.company_name      LIKE '%{$tv['keyword']}%'
                                       )";
            }
                   
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }

            if ($yearMonth != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$yearMonth}'";
            }
            
            //------------------------------------------------------------------------//    
            /*$searchVar->sortOrder = "
            CASE
            WHEN (i.status = 'Late' ) THEN 1
            WHEN (i.invoice_due_date != '' AND i.invoice_due_date IS NOT NULL AND i.invoice_due_date != '0000-00-00' ) THEN 2
            ELSE 3
            END, i.invoice_due_date
            ";
            */
            $searchVar->sortOrder = "i.invoice_code DESC";
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
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $project_id             = $fa['project_id'];
        $invoice_sequence       = $this->getNextInvoiceSeq($project_id);
        $fa['invoice_sequence'] = $invoice_sequence;

        $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $fa['inv_currency'] = $projRec['currency'];

        $id = $fn->addRecord($fa);
        $this->getUpdateInvoiceCode($id, $project_id, $invoice_sequence);
        $this->getUpdateProjectStatus($project_id);

        $fn->returnAfterNewSave($id);
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

        if ($project_id != "") {
            $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
            $project_code = $projRec['project_code'];

            $invoice_prefix = $fn->getSettingsValueByKey("invoiceCodePrefix");
            $project_prefix = $fn->getSettingsValueByKey("projectCodePrefix");
            $invCodeStartIndex = strlen($project_prefix) + 1;

            if ($cpCfg['m.enggCrm.invoice.hasAutoAffix'] == 0){
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
                
                $SQL     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
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
    function getUpdateProjectStatus($project_id) {
        $db = Zend_Registry::get('db');

        $SQL     = "
        SELECT *
        FROM project
        WHERE project_id = {$project_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $still_to_bill =  ($row['project_value']) - ($this->getInvoiceAmount($row['project_id']));
            if ($still_to_bill == 0) {
                $SQL2    = "
                UPDATE project
                SET status = 'Billed'
                WHERE status = 'Delivered & Billable'
                  AND project_id ='{$row['project_id']}'
                ";
                $result2 = $db->sql_query($SQL2);
            }
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('invoice_type', 'Please choose the invoice type');
        $validate->validateData('invoice_due_date', 'Please enter the invoice due date');
        $validate->validateData('invoice_terms', 'Please set the invoice terms');

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

        $project_id = $this->getProjectIdForInvoice($id);
        $this->getUpdateProjectStatus($project_id);

        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getProjectIdForInvoice($invoice_id) {
        $db = Zend_Registry::get('db');

        $SQL    = "
        SELECT project_id
        FROM invoice
        WHERE invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row['project_id'];
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------------//
        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'invoice_code');
        $fa = $fn->addToFieldsArray($fa, 'inv_currency');
        $fa = $fn->addToFieldsArray($fa, 'invoice_type');
        $fa = $fn->addToFieldsArray($fa, 'invoice_amount');
        $fa = $fn->addToFieldsArray($fa, 'invoice_due_date');
        $fa = $fn->addToFieldsArray($fa, 'invoice_date');
        $fa = $fn->addToFieldsArray($fa, 'invoice_sent_out');
        $fa = $fn->addToFieldsArray($fa, 'invoice_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'invoice_paid_date');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'disbursment');
        $fa = $fn->addToFieldsArray($fa, 'disbursement_amount');

        return $fa;
    }

    /**
     *
     */
    function getInvoiceSQLForPager() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
            $SQL = "
            SELECT count(*)
            FROM invoice i
            LEFT JOIN (project p)         ON (i.project_id = p.project_id)
            LEFT JOIN (contact cont)      ON (p.contact_id = cont.contact_id)
            LEFT JOIN (company c)         ON (p.company_id = c.company_id)
            LEFT JOIN (company_address ca)ON (cont.company_address_id = ca.company_address_id)
            ";

        } else {
            $SQL = "
            SELECT count(*)
            FROM invoice i
            LEFT JOIN (project p)    ON (p.project_id = i.project_id    )
            LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id )
            LEFT JOIN (company c)    ON (c.company_id = p.company_id    )
            ";
        }

        return $SQL;
    }


    /**
     *
     */
    function getExportData1(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');


        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Invoice-" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Contact');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Company');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Value');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Still to Bill');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Currency');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Sent Out');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Due Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Paid Date');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array( 'bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $still_to_bill = $row['project_value'] - $this->getInvoiceAmount($row['project_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_title']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_value']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $still_to_bill);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_amount']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['inv_currency']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getYesNo($row['invoice_sent_out']));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_due_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_paid_date']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
 
    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        //$still_to_bill = $row['project_value'] - $this->getInvoiceAmount($row['project_id']);
        $fa = array(
              'invoice_code'        => $phpExcel->getFldObj('Invoice Code')
              ,'invoice_type'        => $phpExcel->getFldObj('Invoice Type')
              ,'age'        => $phpExcel->getFldObj('Age')
             ,'company_name'        => $phpExcel->getFldObj('Client Company')
             ,'project_reference'       => $phpExcel->getFldObj('Project Name')
             ,'po_number'       => $phpExcel->getFldObj('PO Number')
            //  ,'project_value'       => $phpExcel->getFldObj('Project Value')
             
            //  ,'still_to_bill'       => $phpExcel->getFldObj('Still to Bill')
             
             ,'invoice_amount'      => $phpExcel->getFldObj('Invoice Amount')
            ,'status'              => $phpExcel->getFldObj('Status')        
            // ,'inv_currency'        => $phpExcel->getFldObj('Currency')
             ,'invoice_date'        => $phpExcel->getFldObj('Invoice Date')
             
            // ,'invoice_sent_out'    => $phpExcel->getFldObj('Invoice Sent Out')
             
             ,'invoice_due_date'    => $phpExcel->getFldObj('Invoice Due Date')
             //,'invoice_paid_date'   => $phpExcel->getFldObj('Invoice Paid Date')
        );
        
        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }       

    /**
     *
     */
    function getInvoiceAmount($project_id) {
        $db = Zend_Registry::get('db');

        $SQL    = "
        SELECT sum(invoice_amount) as total_invoice_amount
        FROM invoice
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $total_invoice_amount  = $row['total_invoice_amount'];

        return $total_invoice_amount;
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

        $cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module={$tv['module']}&_action=edit&record_id={$id}");
    }

    /**
     *
     */
    function getPrevInvoiceAmount($project_id, $invoice_sequence) {
        $db = Zend_Registry::get('db');

        $SQL1    = "
        SELECT invoice_amount
        FROM invoice
        WHERE project_id = {$project_id}
        ORDER BY invoice_sequence
        ";
        $result1 = $db->sql_query($SQL1);
        $rowcount = 1;
        $invoice_sequence = $invoice_sequence -1;
        $invoice_amount = 0;

        while($row1    = $db->sql_fetchrow($result1)) {
            if ($rowcount <  $invoice_sequence|| $invoice_sequence == $rowcount ) {
                $invoice_amount  = $invoice_amount + $row1['invoice_amount'];
            }
            $rowcount++;
        }

        return $invoice_amount;
    }

    /**
     *
     */
    function getProjectCodeForProjectID($project_id) {
        $db = Zend_Registry::get('db');

        $SQL    = "
        SELECT project_code
        FROM project
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row['project_code'];
    }

    /**
     *
     */
    function getRaiseInvoiceForAllProjectsTemp() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        exit();
        
        $SQL = "
        SELECT project_id
        FROM project
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $project_id = $row['project_id'];
    
            $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
            $compRec = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);
    
            $invoice_sequence = $this->getNextInvoiceSeq($project_id);
    
            $fa = array();
            $fa['creation_date']     = date("Y-m-d H:i:s");
            $fa['invoice_date']      = date("Y-m-d");
            $fa['project_id']        = $project_id;
            $fa['invoice_amount']    = $projRec['project_value'];
            $fa['status']            = "Due";
            $fa['invoice_sequence']  = $invoice_sequence;
    
            $id = $fn->addRecord($fa);
    
            $this->getUpdateInvoiceCode($id, $fa['project_id'], $invoice_sequence);
        }
    }
     /**
     *
     */

    function getInvoiceWOQuotePrintToFpdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);
        //$pdf->Cell(width, height,'text', border, lnbreak, align, fill);
        //$header = array('Term', 'Subject', 'Grade', 'Marks');


		$pdf=new PDF_MC_Table();
		$pdf->AddPage();
		$pdf->SetFont('Arial','',14);
		//Table with 20 rows and 4 columns
		//$pdf->SetWidths(array(30,50,30,40));
		//$pdf->Row(array('Ahmad','Syed','bs','muhyideen'));
		//$pdf->Output();
		//return;

        $record_id     = $fn->getReqParam('record_id');
		$invoice_terms = '';
		$notes  = '';
		$SQL = "
		SELECT DATE_FORMAT(i.invoice_date, '%d %b %Y') AS invoice_date
			  ,i.notes
			  ,i.invoice_code
			  ,i.invoice_amount
			  ,i.invoice_terms
			  ,i.invoice_type
			  ,i.inv_currency
			  ,p.project_id
			  ,p.contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
			  ,cont.position as position
			  ,cont.salutation
			  ,cont.company_address_flat
			  ,cont.company_address_street
			  ,cont.company_address_town
			  ,cont.company_address_state
			  ,cont.company_address_country
			  ,c.company_name
			  ,c.phone
              ,c.address_flat    AS comp_mul_address_flat
              ,c.address_street  AS comp_mul_address_street
              ,c.address_state   AS comp_mul_address_state
              ,c.address_country AS comp_mul_address_country
              ,c.address_po_code AS comp_mul_address_po
			  ,qc.quote_category_id
			  ,qc.valuelist_id
			  ,qc.category_type
			  ,qi.quote_items_id
			  ,qi.title AS item_title
			  ,qi.amount
			  ,qi.amount_other
			  ,(
				  SELECT SUM(qi.amount)
				  FROM quote_items qi
				  WHERE qi.quote_category_id = qc.quote_category_id
			   ) AS amount_sum
			  ,(
				  SELECT SUM(qi.amount_other)
				  FROM quote_items qi
				  WHERE qi.quote_category_id = qc.quote_category_id
			   ) AS amount_other_sum
			  ,qi.item_type
			  ,v.value AS quote_cat_title
			  ,(
				  SELECT SUM(qi.amount)
				  FROM quote_items qi
				  WHERE p.confirmed_quote_id = qi.quote_id
			   ) AS total
			  ,(
				  SELECT SUM(qi.amount_other)
				  FROM quote_items qi
				  WHERE p.confirmed_quote_id = qi.quote_id
			   ) AS total_other
			  ,qc.sort_order AS quote_category_sort
			  ,qi.sort_order AS quote_items_sort

		FROM invoice i
		JOIN project p ON (i.project_id = p.project_id)
        LEFT JOIN contact cont ON (p.contact_id = cont.contact_id)
        LEFT JOIN company c    ON (p.company_id = c.company_id)
        LEFT JOIN company_address ca ON (cont.company_id = ca.company_id)
		JOIN quote_category qc ON (p.confirmed_quote_id = qc.quote_id)
		JOIN quote_items qi    ON (qc.quote_category_id = qi.quote_category_id)
		LEFT JOIN valuelist v  ON (qc.valuelist_id      = v.valuelist_id)
		WHERE i.invoice_id = $record_id
		ORDER BY quote_category_sort
				,qc.quote_category_id
				,quote_items_sort
		";
		
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                // Framed 
                $date = $fn->getCPDate($row['invoice_date'], 'd m Y');
                $code = 'Invoice # : '. $row['invoice_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);
                $pdf->Image('images/logo-print.jpg',10,5,45);
                
                // Company Name
                $pdf->SetXY(68,5);
                $pdf->Cell(40, 20, $company_name);
                $pdf->Ln(10);
                
                // Invoice Date & Code
                $pdf->SetFont('Arial','B',9);
                $pdf->SetXY(165, 25);
                $pdf->Cell(50, 20, "$code" );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "$date");

                $pdf->SetFont('Arial','',10);

                // To Address
                $pdf->SetXY(10, 30);
				$pdf->SetFillColor(131,181,231);
                $pdf->Rect(10 , 35, 85, 40, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(10);
                $pdf->Cell(20, 20, "Invoice To :");
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['company_name']);
                $pdf->Ln(5);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(15,20,"Attn : " . $row['salutation']);
                $pdf->Cell(40,20,$row['contact_name']);
                $pdf->Ln(5);

				if ($row['comp_mul_address_flat']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_flat']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_street']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_street']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_state']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_state']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_country']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_country']);
                    $pdf->Ln(5);
                }
				
                $pdf->Ln(30);

                // Table Heading
                $pdf->SetFont('Arial','B',10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(155,8,"Description",1 ,0, 'L', 1);
                $pdf->Cell(30,8,"Amount (SG$)",1 ,0, 'R', 1);
                $pdf->Ln();
            }
             //Table Content
            $pdf->SetFont('Arial','',10);
            $pdf->SetX(10);
            $pdf->Cell(155,10, " " . $row['item_title'] , 1);
			$amount = number_format($row['amount']);
            if ($amount == 0){
                $amount = '';
            }
            $pdf->Cell(30,10, " " . $amount, 1,  0, 'R');
            $total = $row['total'];
			$total = number_format($total);
            $pdf->Ln();
            $invoice_terms = $row['invoice_terms'];
            $notes = $row['notes'];
            $count++;
        }
            //Final Values
            $pdf->SetFont('Arial','B',10);
            //$pdf->SetFillColor(219,255,140);
			$pdf->SetFillColor(131,181,231);
            $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
            $pdf->Cell(30,8,$total,1,  0, 'R', 1);
            $pdf->Ln(20);
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(150, 8, 'Terms :');
            $pdf->Ln(5);
            $pdf->WordWrap($invoice_terms,200);
            $pdf->Write(5, $invoice_terms);
            //$pdf->Cell(150, 8, $invoice_terms);
            $pdf->Ln(8);
            $pdf->Cell(155, 8,'Note : ');
            $pdf->Ln(5);
            $pdf->WordWrap($notes,200);
            //$pdf->Cell(155, 8, $notes);
            $pdf->Write(5, $notes);
            //$pdf->Cell(0,10, $printFooter ,0,0,'C');

			//$pdf->SetY(-15);
			//$pdf->SetFont('Arial','',6);
			//$pdf->Cell(0, 10,'10 Jalan Besar #17-02 ,Sim Lim Tower, Singapore 208787, Tel : +65 6396 7554, Email : razik@usoftsolutions.com, Web: www.usoftsolutions.com', 0 , 0, 'C');
        $pdf->Output();
    }

    /**
     *
    */

    function getInvoiceNoItemsPrintToFpdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
    

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();
        $pdf->SetFont('Arial','B',14);

		$pdf->AddPage();
		$pdf->SetFont('Arial','',14);
		//$pdf->Cell(50, 20, "Testing Invoice Print to PDF" );  
		//Table with 20 rows and 4 columns
		//$pdf->SetWidths(array(30,50,30,40));
		//$pdf->Row(array('Ahmad','Syed','bs','muhyideen'));
		//$pdf->Output();
		//return;

        $record_id     = $fn->getReqParam('record_id');
		$invoice_terms = '';
		$notes  = '';
        $total = '';
        
		$SQL = "
		SELECT DATE_FORMAT(i.invoice_date, '%d %b %Y') AS invoice_date
			  ,i.notes
			  ,i.invoice_code
			  ,i.invoice_amount
			  ,i.invoice_terms
			  ,i.invoice_type
			  ,i.inv_currency
			  ,p.project_id
			  ,p.contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
			  ,cont.position as position
			  ,cont.salutation
			  ,cont.company_address_flat
			  ,cont.company_address_street
			  ,cont.company_address_town
			  ,cont.company_address_state
			  ,cont.company_address_country
			  ,c.company_name
			  ,c.phone
              ,c.address_flat    AS comp_mul_address_flat
              ,c.address_street  AS comp_mul_address_street
              ,c.address_state   AS comp_mul_address_state
              ,c.address_country AS comp_mul_address_country
              ,c.address_po_code AS comp_mul_address_po
			  ,it.description AS invoice_items_description
			  ,it.amount AS invoice_items_amount
			  ,(
				  SELECT SUM(it.amount)
				  FROM invoice_items it
				  WHERE i.invoice_id = it.invoice_id
			   ) AS invoice_item_sum

		FROM invoice i
		JOIN project p ON (i.project_id = p.project_id)
        LEFT JOIN contact cont ON (p.contact_id = cont.contact_id)
        LEFT JOIN company c    ON (p.company_id = c.company_id)
        LEFT JOIN company_address ca ON (cont.company_id = ca.company_id)
		JOIN invoice_items it    ON (i.invoice_id = it.invoice_id)
		WHERE i.invoice_id = $record_id
		ORDER BY it.invoice_items_id
		";
		
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $rows = "";
        $columns = array();      
   
        // header col
        $col = array();

        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                // Framed 
                $date = $fn->getCPDate($row['invoice_date'], 'd m Y');
                $code = 'Invoice # : '. $row['invoice_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);
                $pdf->Image('images/logo-print.jpg',10,5,45);
                
                // Company Name
                $pdf->SetXY(68,5);
                $pdf->Cell(40, 20, $company_name);
                $pdf->Ln(10);
                
                // Invoice Date & Code
                $pdf->SetFont('Arial','B',9);
                $pdf->SetXY(165, 25);
                $pdf->Cell(50, 20, "$code" );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "$date");

                $pdf->SetFont('Arial','',10);

                // To Address
                $pdf->SetXY(10, 30);
				$pdf->SetFillColor(131,181,231);
                $pdf->Rect(10 , 35, 85, 40, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(10);
                $pdf->Cell(20, 20, "Invoice To :");
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['company_name']);
                $pdf->Ln(5);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(15,20,"Attn : " . $row['salutation']);
                $pdf->Cell(40,20,$row['contact_name']);
                $pdf->Ln(5);

				if ($row['comp_mul_address_flat']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_flat']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_street']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_street']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_state']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_state']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_country']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_country']);
                    $pdf->Ln(5);
                }
				
                $pdf->Ln(30);

                // Table Heading
                $pdf->SetFont('Arial','B',10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $pdf->SetFillColor(142,182,212);
                //header
                $col[] = array('text' => 'Description', 'width' => '165', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => '', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
                $col[] = array('text' => 'Amount', 'width' => '20', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => '', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');   
                $columns[] = $col;
                $total = $row['invoice_item_sum'];
                $total = number_format($total);
            }
             //Table Content
            $pdf->SetFont('Arial','',10);
			$amount = number_format($row['invoice_items_amount']);

            if ($amount == 0){
                $amount = '';
            }
            $col = array();
            $col[] = array('text' => $row['invoice_items_description'], 'width' => '165', 'height' => '5', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $col[] = array('text' => $amount, 'width' => '20', 'height' => '5', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');   
            $columns[] = $col;

            $invoice_terms = $row['invoice_terms'];
            $notes = $row['notes'];
            $count++;
        }
        $col = array();
        $col[] = array('text' => 'Total', 'width' => '165', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => '', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
        $col[] = array('text' => $total, 'width' => '20', 'height' => '5', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '8', 'font_style' => '', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');   
        $columns[] = $col;

        $pdf->WriteTable($columns);
        $printFooter = $cpCfg['printFooter'];
        
        //Final Values
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, 'Terms :');
        $pdf->Ln(5);
        $pdf->WordWrap($invoice_terms,200);
        $pdf->Write(5, $invoice_terms);
        $pdf->Ln(8);
        $pdf->Cell(155, 8,'Note : ');
        $pdf->Ln(5);
        $pdf->WordWrap($notes,200);
        $pdf->Write(5, $notes);
        /*$pdf->SetXY(10,266);
        $pdf->SetFont('Arial','',6);
        //Page number*/
        //$pdf->Cell(0,10, $printFooter ,0,0,'C');
        $pdf->Output();
    }

    /**
     *
     */
    function getProjectInvoiceProjectInvoiceItemSQL($id) {

        return "
        SELECT a.invoice_items_id
              ,a.description
              ,a.amount
        FROM invoice b
            ,invoice_items a
        WHERE a.invoice_id = b.invoice_id
          AND b.invoice_id = {$id}
        ORDER BY a.creation_date DESC
        ";
              
    }

    //==================================================================//
    function getSendReminderEmailSubmit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoice_id   = $fn->getReqParam('invoice_id');
        
        $SQL = "
        SELECT i.invoice_id
              ,invoice_code
              ,c.email
              ,p.title AS project_title             
        FROM invoice i
        LEFT JOIN project p ON (p.project_id = i.project_id)
        LEFT JOIN contact c ON (c.contact_id = p.contact_id)
        WHERE i.invoice_id = '$invoice_id'
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        $currentDate = date('M d Y');
        $message     = $cpCfg['invoiceReminderEmailBody'];
        $message     = str_replace('[[project_title]]', $row['project_title'], $message);
        
        $subject     = $cpCfg['invoiceReminderEmailSubject'];
        $subject     = str_replace('[[currentDate]]', $currentDate, $subject);

        $fromName    = 'Admin';
        $fromEmail   = $cpCfg['cp.adminEmail'];
        $toName      = "Client";
        $toEmail     = $row['email'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $order_id           = $fn->getReqParam('order_id');
        $record_id_arr      = $fn->getReqParam('orderItem_record_id', array());
        $record_Type        = $fn->getReqParam('record_type');
        $invoice_type       = $fn->getPostParam('invoice_type');
        $title_arr          = $fn->getPostParam('title', array());
        $description_arr    = $fn->getPostParam('description', array());
        $unit_price_arr     = $fn->getPostParam('unit_price', array());
        $amount_arr         = $fn->getPostParam('amount', array());
        $unit_arr           = $fn->getPostParam('unit', array());
        $quantity_arr       = $fn->getPostParam('quantity', array());
        $description_arr    = $fn->getPostParam('description', array());
        $remarks_arr        = $fn->getPostParam('remarks', array());
        $add_minus_arr      = $fn->getPostParam('add_minus', array());
        $rate_text          = $fn->getPostParam('rate_text');
        $qty_text           = $fn->getPostParam('qty_text');
        $notes              = $fn->getPostParam('notes');
        $invoice_start_date = $fn->getPostParam('invoice_start_date');
        $invoice_end_date   = $fn->getPostParam('invoice_end_date');
        $month_req_arr      = $fn->getReqParam('month_req', array());
        $year_req_arr       = $fn->getReqParam('year_req', array());
        $total_cost_arr     = $fn->getReqParam('total_cost_input', array());
        $quote_code         = $fn->getPostParam('quote_code');
        $project_location   = $fn->getPostParam('project_location');
        $project_reference  = $fn->getPostParam('project_reference');
        $payment_terms      = $fn->getPostParam('payment_terms');
$discount = number_format((float)$fn->getPostParam('discount'), 2, '.', '');
    $po_number          = $fn->getPostParam('po_number');
        $invoice_terms      = $fn->getPostParam('invoice_terms');
        $apply_digital_signature      = $fn->getPostParam('apply_digital_signature');
        $signature_name      = $fn->getPostParam('signature_name');
        $employee_id      = $fn->getPostParam('employee_id');
        $subject      = $fn->getPostParam('subject');

        if (!$this->getGenerateInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $orderRec   = $fn->getRecordRowByID('order', 'order_id', $order_id);
        
        /* Generation of Invoice record */
        $faInv = array();

        $faInv['title']             = $orderRec['quote_title'];
       $faInv['invoice_code'] = $this->getUpdateAddInvoiceCode();
        $faInv['invoice_date']      = date('Y-m-d');
        $faInv['apply_digital_signature']      = $apply_digital_signature;
        $faInv['signature_name']      = $signature_name;
        $faInv['employee_id']      = $employee_id;
        $faInv['start_date']        = $invoice_start_date;
        $faInv['end_date']          = $invoice_end_date;
        $faInv['status']            = 'Due';
        $faInv['order_id']          = $order_id;
        $faInv['invoice_due_date']  = date('Y-m-d', strtotime("+30 days"));
        $faInv['creation_date']     = date('Y-m-d H:i:s');
        $faInv['notes']             = $notes;
        $faInv['discount']          = $discount;
        $faInv['quote_code']        = $quote_code;
        $faInv['project_location']  = $project_location;
        $faInv['project_reference'] = $project_reference;
        $faInv['payment_terms']     = $payment_terms;
        $faInv['subject']     = $subject;
        $faInv['po_number']         = $po_number;
        $faInv['invoice_terms']     = $invoice_terms;
        $faInv['created_by']        = $fn->getSessionParam('userName');
        
        $gst_percentage = $fn->getSettingsValueByKey("cp.gstPercentage");
        if ($gst_percentage) {
            $faInv['gst_percentage'] = $gst_percentage;
        }
        $faInv['site_id'] = $orderRec['site_id'];

        $insertInv  = $dbUtil->getInsertSQLStringFromArray($faInv, 'invoice');
        $resultInv  = $db->sql_query($insertInv);
        $invoice_id = $db->sql_nextid();

    

        $total_cost = 0;
        $count = count($description_arr);
        for ($i= 0; $i < $count; $i++) {
            $title       = isset($title_arr[$i]) ? $title_arr[$i] : '';
            $description = isset($description_arr[$i]) ? $description_arr[$i] : '';
            $amount      = isset($amount_arr[$i]) ? $amount_arr[$i] : 0;
            $unit_price  = isset($unit_price_arr[$i]) ? $unit_price_arr[$i] : 0;
            $unit        = isset($unit_arr[$i]) ? $unit_arr[$i] : '';
            $quantity    = isset($quantity_arr[$i]) ? $quantity_arr[$i] : 0;
            $add_minus   = isset($add_minus_arr[$i]) ? $add_minus_arr[$i] : '';

            if($description != "") {
                $faIi = array();
                $faIi['invoice_id']  = $invoice_id;
                $faIi['site_id']     = $orderRec['site_id'];   // Add this
                $faIi['description'] = $description;
                $faIi['item_title']  = $title;
                $faIi['unit_price']  = $unit_price;
                $faIi['amount']      = $amount;
                $faIi['unit']        = $unit;
                $faIi['qty']         = $quantity;
                $faIi['add_minus']   = $add_minus;
                
                $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'invoice_item');
                $result = $db->sql_query($insert);
                $invoice_item_id = $db->sql_nextid();
            }                
        }

        $total_invoice_amount = 0;
        $total_invoice_amount_minus = 0;
        $sqlIi = "
        SELECT *
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultIi  = $db->sql_query($sqlIi);
        while ($rowIi = $db->sql_fetchrow($resultIi)) {
            if($rowIi['add_minus'] == 'Minus'){
                if($rowIi['amount'] != "") {
                    $total_invoice_amount_minus += $rowIi['amount'];
                } else if($rowIi['unit_price'] > 0 && $rowIi['qty'] > 0) {
                    $total_invoice_amount_minus += $rowIi['unit_price'] * $rowIi['qty'];
                } else if($rowIi['unit_price'] > 0 && ($rowIi['qty'] == "" || $rowIi['qty'] == 0)) {
                    $total_invoice_amount_minus += $rowIi['unit_price'];
                }
            } else {
                if($rowIi['amount'] != "") {
                    $total_invoice_amount += $rowIi['amount'];
                } else if($rowIi['unit_price'] > 0 && $rowIi['qty'] > 0) {
                    $total_invoice_amount += $rowIi['unit_price'] * $rowIi['qty'];
                } else if($rowIi['unit_price'] > 0 && ($rowIi['qty'] == "" || $rowIi['qty'] == 0)) {
                    $total_invoice_amount += $rowIi['unit_price'];
                }
            }
        }

        $total_cost = round($total_invoice_amount - $total_invoice_amount_minus, 3);

        /* Update quote status */
        $faInvoice = array();
        $faInvoice['invoice_amount'] = $total_cost;
        $fn->saveRecord($faInvoice, 'invoice', 'invoice_id', $invoice_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateInvoiceFormValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $order_id           = $fn->getReqParam('order_id');
        $record_type        = $fn->getReqParam('record_type');
        $invoice_start_date = $fn->getPostParam('invoice_start_date');
        $invoice_end_date   = $fn->getPostParam('invoice_end_date');
        $title_arr          = $fn->getPostParam('title', array());
        $description_arr    = $fn->getPostParam('description', array());
        $amount_arr         = $fn->getPostParam('amount', array());
        $quantity_arr       = $fn->getPostParam('quantity', array());
        $po_number          = $fn->getPostParam('po_number');

        $validate->resetErrorArray();

        if($record_type == 'Manpower Supply'){ 
            $validate->validateData('invoice_start_date', 'Please select the start date');
            $validate->validateData('invoice_end_date', 'Please select the end date');
        }

        $SQLINV = "
        SELECT start_date
               ,end_date
        FROM invoice
        WHERE order_id = {$order_id}
        AND ('{$invoice_start_date}' BETWEEN start_date AND end_date
        OR '{$invoice_end_date}' BETWEEN start_date AND end_date)
        AND status != 'Cancelled'
        ";
        $resultINV  = $db->sql_query($SQLINV);
        $numRowsINV = $db->sql_numrows($resultINV);
        $rowINV     = $db->sql_fetchrow($resultINV);

        if($numRowsINV > 0){
            $invoice_start_date_formatted = $fn->getCPDate($invoice_start_date, 'd/n/Y');
            $invoice_end_date_formatted   = $fn->getCPDate($invoice_end_date, 'd/n/Y');
            $validate->errorArray['invoice_start_date']['name'] = "invoice_start_date";
            $validate->errorArray['invoice_start_date']['msg']  = "Invoice already created for the dates between {$invoice_start_date_formatted} and {$invoice_end_date_formatted}";
        }

        $SQLINVPO = "
        SELECT po_number
        FROM invoice
        WHERE po_number = '{$po_number}'
        AND po_number != ''
        AND status != 'Cancelled'
        ";
        $resultINVPO  = $db->sql_query($SQLINVPO);
        $numRowsINVPO = $db->sql_numrows($resultINVPO);
        $rowINVPO     = $db->sql_fetchrow($resultINVPO);

        if($numRowsINVPO > 0){
            $validate->errorArray['po_number']['name'] = "po_number";
            $validate->errorArray['po_number']['msg']  = "PO number already created";
        }


        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
              ,SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
              ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
              ,SUM(admin_charges) AS admin_charges
              ,SUM(transport_charges) AS transport_charges 
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);
        $rowOi['total_order_amt'] = $rowOi['total_order_amt'] + $rowOi['totalOTAmount'] + $rowOi['totalPHAmount'] + $rowOi['admin_charges'] + $rowOi['transport_charges'];

        /* Total Invoice Amount generated earlier */
        $sqlInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amt_generated
        FROM invoice
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($sqlInv);
        $rowInv     = $db->sql_fetchrow($resultInv);

        $total_order_amount_due = $rowOi['total_order_amt'] - $rowInv['total_invoice_amt_generated'];
        //==================================================================//
        $total_entered_amount = 0;
        //$count = count($amount_arr);

        $filterTitleArray       = array_filter($title_arr);
        $filterDescriptionArray = array_filter($description_arr);
        if (count($filterTitleArray) == 0 && count($filterDescriptionArray) == 0){
            $msg = 'Please enter atleast 1 item';
            $validate->validateData('error_box', $msg);
        }

        /*for ($i= 0; $i < $count; $i++) {
            $amount   = $amount_arr[$i];
            //$quantity = $quantity_arr[$i];

            /*
            if ($i == 0 && ($amount == '' || $quantity == '')) {
                $msg = 'Please enter amount/quantity for atleast 1 item.';
                $validate->validateData('error_box', $msg);
            }
            */

            /*if ($i == 0 && $amount == '') {
                $msg = 'Please enter amount for atleast 1 item.';
                $validate->validateData('error_box', $msg);
            }

            if ($amount > 0) {
                $total_entered_amount += $amount;
            }
        }

        if ($total_entered_amount == $total_order_amount_due) {
            //return true;
        }*/

        /*$orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && round($total_entered_amount, 2) > round($total_order_amount_due, 2)) {
            //$msg = "<div class='invoiceAlert'>Please enter the total amount not greater than " . number_format($total_order_amount_due,2) . ". You have entered a total of " . number_format($total_entered_amount, 2) . "</div>";
            $msg = "<div class='invoiceAlert'>Please enter the total amount not greater than " . $total_order_amount_due . ". You have entered a total of " . $total_entered_amount . "</div>";
            $validate->validateData('error_box', $msg);
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
    function getAutoGenerateMaintenanceInvoice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        // http://greencitycrm.localhost/admin/index.php?_topRm=finance&module=enggCrm_order&_action=autoGenerateMaintenanceInvoice

        $current_date = date('Y-m-d');

        /* Find the list of orders for which invoice has to be generated */
        $sql = "
        SELECT * FROM `order`
        WHERE auto_create_invoice = 1
          AND project_type = 'Maintenance'
          AND start_date != ''
          AND start_date <= '{$current_date}'
          AND end_date >= '{$current_date}'";
        $result = $db->sql_query($sql);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $invoiceCount = $fn->getRecordCount('invoice', "order_id = {$row['order_id']}");

            /* Check whether any invoice is available for order. If yes, duplicate it */
            if ($invoiceCount > 0) {
                $invoiceRow = $fn->getRecordByCondition('invoice', "order_id = {$row['order_id']}", 'invoice_date DESC');

                $faInv = array();
                $faInv['invoice_code']     = $fn->getSettingsValueByKey('invoiceCodePrefix') . $fn->getSettingsValueByKey('nextInvoiceCode');
                $faInv['invoice_date']     = date('Y-m-d');
                $faInv['status']           = 'Due';
                $faInv['order_id']         = $invoiceRow['order_id'];
                $faInv['title']            = $invoiceRow['title'];
                $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+30 days"));
                $faInv['creation_date']    = date('Y-m-d H:i:s');
                $faInv['created_by']       = $fn->getSessionParam('userName');
                
                $gst_percentage = $fn->getSettingsValueByKey("cp.gstPercentage");
                if ($gst_percentage) {
                    $faInv['gst_percentage'] = $gst_percentage;
                }

                $insertInv  = $dbUtil->getInsertSQLStringFromArray($faInv, 'invoice');
                $resultInv  = $db->sql_query($insertInv);
                $invoice_id = $db->sql_nextid();

                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
                $resultUpdate = $db->sql_query($SQLUpdate);

                $sqlIi = "
                SELECT * FROM `invoice_item`
                WHERE invoice_id = {$invoiceRow['invoice_id']}";
                $resultIi = $db->sql_query($sqlIi);
                while ($rowIi = $db->sql_fetchrow($resultIi)) {
                    if ($rowIi['description']) {
                        $faIi = array();
                        $faIi['invoice_id']   = $invoice_id;
                        $faIi['item_title']   = $rowIi['item_title'];
                        $faIi['description']  = $rowIi['description'];
                        $faIi['unit_price']   = $rowIi['unit_price'];
                        $faIi['unit']         = $rowIi['unit'];
                        $faIi['qty']          = $rowIi['qty'];
                        $faIi['remarks']      = $rowIi['remarks'];

                        $insertInvItem = $dbUtil->getInsertSQLStringFromArray($faIi, 'invoice_item');
                        $resultInvItem = $db->sql_query($insertInvItem);
                        $invoice_item_id = $db->sql_nextid();
                    }
                }

                $total_invoice_amount = 0;
                $sqlInvItem = "
                SELECT *
                FROM invoice_item
                WHERE invoice_id = {$invoice_id}
                ";
                $resultInvItem = $db->sql_query($sqlInvItem);
                while ($rowInvItem = $db->sql_fetchrow($resultInvItem)) {
                    if ($rowInvItem['qty'] > 0) {
                        $total_invoice_amount += $rowInvItem['unit_price'] * $rowInvItem['qty'];
                    } else {
                        $total_invoice_amount += $rowInvItem['unit_price'];
                    }
                }

                /* Update quote status */
                $faInvoice = array();
                $faInvoice['invoice_amount'] = round($total_invoice_amount, 3);
                $fn->saveRecord($faInvoice, 'invoice', 'invoice_id', $invoice_id);

                $count++;
            }
        }
    }

    /**
     *
     */
    function getEditInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoice_id             = $fn->getReqParam('invoice_id');
        $invoiceRec             = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $order_id               = $invoiceRec['order_id'];
        $invoice_date           = $fn->getPostParam('invoice_date');
        $invoice_due_date       = $fn->getPostParam('invoice_due_date');
        $invoice_title          = $fn->getPostParam('invoice_title');
        $subject                = $fn->getPostParam('subject');

        $payment_terms          = $fn->getPostParam('payment_terms');
        $po_number              = $fn->getPostParam('po_number');
        $invoice_sent_out_date  = $fn->getPostParam('invoice_sent_out_date');
        $invoice_code_user      = $fn->getPostParam('invoice_code_user');
        $discount               = $fn->getPostParam('discount');
        $invoice_terms          = $fn->getPostParam('invoice_terms');
        $project_reference          = $fn->getPostParam('project_reference');
        $apply_digital_signature          = $fn->getPostParam('apply_digital_signature');
        $signature_name                              = $fn->getPostParam('signature_name');
        $employee_id                              = $fn->getPostParam('employee_id');

        $faInv = array();
        $faInv['invoice_date']          = $invoice_date;
        $faInv['invoice_due_date']      = $invoice_due_date;
        $faInv['title']                 = $invoice_title;
        $faInv['subject']                 = $subject;
        $faInv['apply_digital_signature']                          = $apply_digital_signature;
        $faInv['signature_name']                          = $signature_name;
        $faInv['employee_id']                          = $employee_id;
        $faInv['payment_terms']         = $payment_terms;
        $faInv['po_number']             = $po_number;
        $faInv['invoice_sent_out_date'] = $invoice_sent_out_date;
        $faInv['invoice_code_user']     = $invoice_code_user;
        $faInv['discount']              = $discount;
        $faInv['invoice_terms']         = $invoice_terms;
        $faInv['project_reference']     = $project_reference;
        
        if ($cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm']) {
            $faInv['invoice_terms'] = "{$fn->getPostParam('invoice_terms')}";
        }

        if ($cpCfg['m.enggCrm.invoice.showLocationInEditForm']) {
            $faInv['title'] = $fn->getPostParam('invoice_title');
        }
        $faInv = $fn->addModificationDetailsToFieldsArray($faInv, 'invoice');
        $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $new_titles = isset($_POST['new_title']) ? $_POST['new_title'] : array();
        $new_descriptions = isset($_POST['new_description']) ? $_POST['new_description'] : array();
        $new_units = isset($_POST['new_unit']) ? $_POST['new_unit'] : array();
        $new_quantities = isset($_POST['new_qty']) ? $_POST['new_qty'] : array();
        $new_unitprices = isset($_POST['new_unitprice']) ? $_POST['new_unitprice'] : array();
        $new_amounts = isset($_POST['new_amount']) ? $_POST['new_amount'] : array();
        $new_addminus = isset($_POST['new_addminus']) ? $_POST['new_addminus'] : array();
        $new_item_count = count($new_titles);
        for ($i = 0; $i < $new_item_count; $i++) {
            $new_title = isset($new_titles[$i]) ? trim($new_titles[$i]) : '';
            $new_description = isset($new_descriptions[$i]) ? trim($new_descriptions[$i]) : '';
            if ($new_title !== '' || $new_description !== '') {
                $new_item = array(
                    'invoice_id' => $invoice_id,
                    'site_id' => $orderRec['site_id'],
                    'item_title' => $new_title,
                    'description' => $new_description,
                    'unit' => isset($new_units[$i]) ? $new_units[$i] : '',
                    'qty' => isset($new_quantities[$i]) && trim((string) $new_quantities[$i]) !== '' ? $new_quantities[$i] : 0,
                    'unit_price' => isset($new_unitprices[$i]) && trim((string) $new_unitprices[$i]) !== '' ? $new_unitprices[$i] : 0,
                    'amount' => isset($new_amounts[$i]) && trim((string) $new_amounts[$i]) !== '' ? $new_amounts[$i] : 0,
                    'add_minus' => isset($new_addminus[$i]) ? $new_addminus[$i] : 'Add'
                );
                $insert = $dbUtil->getInsertSQLStringFromArray($new_item, 'invoice_item');
                $db->sql_query($insert);
            }
        }

                $removed_invoice_item_ids = isset($_POST['removed_invoice_item_ids']) ? $_POST['removed_invoice_item_ids'] : array();
                foreach ($removed_invoice_item_ids as $removed_invoice_item_id) {
                        $removed_invoice_item_id = (int) $removed_invoice_item_id;
                        if ($removed_invoice_item_id > 0) {
                                $SQL = "
                                DELETE FROM invoice_item
                                WHERE invoice_item_id = {$removed_invoice_item_id}
                                    AND invoice_id = {$invoice_id}
                                ";
                                $db->sql_query($SQL);
                        }
                }

        foreach($_POST AS $key => $val){
            if (substr($key, 0, 5) == 'title'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $title = $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['item_title'] != $title) {
                    $faIi = array();
                    $faIi['item_title'] = $title;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }


            if (substr($key, 0, 9) == 'unitprice'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $unitprice = trim((string) $val) === '' ? 0 : $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['unit_price'] != $unitprice) {
                    $faIi = array();
                    $faIi['unit_price'] = $unitprice;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }

            if (substr($key, 0, 5) == 'unit_'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $unit = $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['unit'] != $unit) {
                    $faIi = array();
                    $faIi['unit'] = $unit;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }


            if (substr($key, 0, 3) == 'qty'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $qty = trim((string) $val) === '' ? 0 : $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['qty'] != $qty) {
                    $faIi = array();
                    $faIi['qty'] = $qty;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }

            if (substr($key, 0, 8) == 'addminus'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $add_minus = $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['add_minus'] != $add_minus) {
                    $faIi = array();
                    $faIi['add_minus'] = $add_minus;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }

            if (substr($key, 0, 6) == 'amount'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $amount = trim((string) $val) === '' ? 0 : $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['amount'] != $amount) {
                    $faIi = array();
                    $faIi['amount'] = $amount;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }

            if (substr($key, 0, 11) == 'description'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $description = $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($invoiceItemRec['description'] != $description) {
                    $faIi = array();
                    $faIi['description'] = $description;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }

            $total_invoice_amount = 0;
            $total_invoice_amount_minus = 0;
            $sqlIi = "
            SELECT *
            FROM invoice_item
            WHERE invoice_id = {$invoice_id}
            ";
            $resultIi  = $db->sql_query($sqlIi);
            while ($rowIi = $db->sql_fetchrow($resultIi)) {
                if($rowIi['add_minus'] == 'Minus'){
                    if($rowIi['amount'] != "") {
                        $total_invoice_amount_minus += $rowIi['amount'];
                    } else if($rowIi['unit_price'] > 0 && $rowIi['qty'] > 0) {
                        $total_invoice_amount_minus += $rowIi['unit_price'] * $rowIi['qty'];
                    } else if($rowIi['unit_price'] > 0 && ($rowIi['qty'] == "" || $rowIi['qty'] == 0)) {
                        $total_invoice_amount_minus += $rowIi['unit_price'];
                    }
                } else {
                    if($rowIi['amount'] != "") {
                        $total_invoice_amount += $rowIi['amount'];
                    } else if($rowIi['unit_price'] > 0 && $rowIi['qty'] > 0) {
                        $total_invoice_amount += $rowIi['unit_price'] * $rowIi['qty'];
                    } else if($rowIi['unit_price'] > 0 && ($rowIi['qty'] == "" || $rowIi['qty'] == 0)) {
                        $total_invoice_amount += $rowIi['unit_price'];
                    }                    
                }
            }
    
            $total_cost = $total_invoice_amount - $total_invoice_amount_minus;
    
            /* Update quote status */
            $faInvoice = array();
            $faInvoice['invoice_amount'] = $total_cost;
            $fn->saveRecord($faInvoice, 'invoice', 'invoice_id', $invoice_id);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditInvoiceFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('invoice_date', 'Please select the date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSendOutstandingEmailToAdmin() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $validate = Zend_Registry::get('validate');

        $rows = '';
        $sql = "
        SELECT * FROM invoice
        WHERE (status = 'Due' OR status = 'Late')
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $total_invoice_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $invoice_date = $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY');
            $total_invoice_amount += $row['invoice_amount'];
            
            $rows .= "
            <tr>
                <td>{$row['invoice_code']}</td>
                <td>{$row['title']}</td>
                <td>{$invoice_date}</td>
                <td>{$row['invoice_amount']}</td>
            </tr>
            ";
        }

        $total_invoice_amount = number_format($total_invoice_amount, 2);
        if ($numRows > 0) {
            $message = "
            <table border='1'>
                <thead>
                    <tr>
                        <th>Invoice Code</th>
                        <th>Title</th>
                        <th>Invocie Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                    <tr>
                        <td colspan='3'><strong>Total Outstanding Amount</strong></td>
                        <td>{$total_invoice_amount}</td>
                    </tr>
                </tbody
            </table
            ";
        } else {
            $message = "Please note that there are no outstanding invoices to be paid.";
        }

        $subject = "Engg CRM - Reminder email for outstanding invoices";
        
        $fromName    = 'Admin';
        $fromEmail   = $cpCfg['cp.adminEmail'];
        $toName      = $cpCfg['cp.siteTitle'];
        //$toEmail     = $cpCfg['cp.adminEmail'];
        $toEmail     = 'arif@usoftsolutions.com';

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        return $validate->getSuccessMessageXML();
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

        $company_id = $fn->getReqParam('company_id');

        $sql = "
        SELECT c.company_name
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_po_code
              ,c.billing_address_country
        FROM company c
        WHERE c.company_id = {$company_id}
        ";
        $result = $db->sql_query($sql);
        $rowIntro = $db->sql_fetchrow($result);

        $template = 'Statement.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = $rowIntro['company_name'] . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name.'.xlsx');

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;

        $current_date = date('Y-m-d');

        $SQL = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        WHERE (i.status = 'Due' OR i.status = 'Partial Payment' OR i.status = 'Late')
              AND o.company_id = {$company_id}
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
        $blkAmtPayable  = array();
        
        $arr['parent_name']     = $rowIntro['company_name'];
        $arr['address_flat']    = $rowIntro['billing_address_flat'];
        $arr['address_street']  = $rowIntro['billing_address_street'];
        $arr['address_country'] = $rowIntro['billing_address_country'] . " - " . $rowIntro['billing_address_po_code'];

        $total_invoice_amount = 0;
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

            if ($row['gst_percentage'] > 0) {
                $total_invoice_amount = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $row['gst_percentage']);
            } else {
                $total_invoice_amount = $row['invoice_amount'];
            }

            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD-MM-YYYY');

            $amount_payable = $total_invoice_amount;

            $invoice_code = 'PT/' . substr($row['invoice_code'], 2). '/' . $row['invoice_code_user'];
            //repeating rows of product values
            $arr11 = array('invoice_code' => $invoice_code);
            $blkInvCode[] = $arr11;

            $arr2 = array('invoice_date' => $invoice_date);
            $blkInvDate[] = $arr2;

            $arr1 = array('invoice_amt' => number_format($total_invoice_amount, 2));
            $blkInvAmt[] = $arr1;

            $arr3 = array('reference_no' => $row['reference_no']);
            $blkRefNo[] = $arr3;

            $amount_payable_after_rec = $amount_payable - $receipt_amount;
            $amount_payable_after_rec_formatted = number_format($amount_payable_after_rec, 2);
            $arrAmtPayable = array('amount_payable_after_rec' => $amount_payable_after_rec_formatted);
            $blkAmtPayable[] = $arrAmtPayable;

            $total_outstanding_amount += $amount_payable_after_rec;

            $serialNo++;
        }

        $arr['total_outstanding_amount'] = number_format($total_outstanding_amount, 2);
        $arr['current_date']             = $fn->getCPDate($current_date, 'd M Y');;
        $arr['30days_due']               = number_format($this->getPastBalanceAmount($company_id, $current_date, 30), 2);
        $arr['60days_due']               = number_format($this->getPastBalanceAmount($company_id, $current_date, 60), 2);
        $arr['60moredays_due']           = number_format($this->getPastBalanceAmount($company_id, $current_date, 61), 2);

        $blkMain[] = $arr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkInvCode', $blkInvCode);
        $TBS->MergeBlock('blkInvDate', $blkInvDate);
        $TBS->MergeBlock('blkRefNo', $blkRefNo);
        $TBS->MergeBlock('blkInvAmt', $blkInvAmt);
        $TBS->MergeBlock('blkAmountReceived', $blkAmountReceived);
        $TBS->MergeBlock('blkAmtPayable', $blkAmtPayable);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getGenerateCreditNoteFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $site_id = $fn->getSessionParam('cp_site_id');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $order_id        = $fn->getReqParam('order_id');
        $date            = $fn->getPostParam('date');
        $remarks         = $fn->getPostParam('remarks');
        $apply_digital_signature = $fn->getPostParam('apply_digital_signature');
        $signature_name  = $fn->getPostParam('signature_name');
        $title_arr       = $fn->getPostParam('title', array());
        $description_arr = $fn->getPostParam('description', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $invoice_id_arr  = $fn->getReqParam('invoice_id', array());

        if (!$this->getGenerateCreditNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($amount_arr);
        if ($count > 0) {
            /* Adding zeros for invoice to make it 4 digit */
            // $length_of_inv_code = strlen($fn->getSettingsValueByKey('nextCreditNoteCode'));
            // if ($length_of_inv_code == 1) {
            //     $inv_code = '000' . $fn->getSettingsValueByKey('nextCreditNoteCode');
            // } else if ($length_of_inv_code == 2) {
            //     $inv_code = '00' . $fn->getSettingsValueByKey('nextCreditNoteCode');
            // } else if ($length_of_inv_code == 3) {
            //     $inv_code = '0' . $fn->getSettingsValueByKey('nextCreditNoteCode');
            // } else {
            //     $inv_code = $fn->getSettingsValueByKey('nextCreditNoteCode');
            // }
            $creditNoteCode = $this->getGenerateCreditNoteCode();

            /* Generation of Invoice record */
            $orderRec   = $fn->getRecordRowByID('order', 'order_id', $order_id);

            $faCn = array();
            $faCn['credit_note_code'] = $creditNoteCode;
            $faCn['date']             = $date;
            $faCn['remarks']          = $remarks;        
            $faCn['apply_digital_signature'] = $apply_digital_signature;
            $faCn['signature_name']  = $signature_name;
            $faCn['order_id']         = $order_id;
            $faCn['site_id']            = $site_id;
            $faCn['creation_date']    = date('Y-m-d H:i:s');
            $faCn['created_by']       = $fn->getSessionParam('userName');
            
            $gst_percentage = $fn->getSettingsValueByKey("cp.gstPercentage");
            if ($gst_percentage) {
                //$faCn['gst_percentage'] = $gst_percentage;
            }

            $insertCn  = $dbUtil->getInsertSQLStringFromArray($faCn, 'credit_note');
            $resultCn  = $db->sql_query($insertCn);
            $credit_note_id = $db->sql_nextid();

            // $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCreditNoteCode'";
            // $result = $db->sql_query($SQL);

            $total_amount = 0;
            for ($i= 0; $i < $count; $i++) {
                $amount         = $amount_arr[$i];
                $description    = $description_arr[$i];
                $title          = $title_arr[$i];
                $invoice_id     = $invoice_id_arr[$i];

                if ($amount) {
                    $fa = array();
                    $fa['invoice_id']       = $invoice_id;
                    $fa['credit_note_id']   = $credit_note_id;
                    $fa['amount']           = $amount;
                    $fa['item_title']       = $title;
                    $fa['description']      = $description;
                    $fa['creation_date']    = date('Y-m-d H:i:s');
                    $fa['created_by']       = $fn->getSessionParam('userName');

                    $gst_percentage = $fn->getSettingsValueByKey("cp.gstPercentage");
                    if ($gst_percentage) {
                        $fa['gst_percentage'] = $gst_percentage;
                    }

                    $insert3 = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_credit_note_history');
                    $result3 = $db->sql_query($insert3);
                }

                $total_amount += $amount;
            }

            $fa2 = array();
            $fa2['amount']      = $total_amount;
            $fa2['gst_amount']  = round(($total_amount*$gst_percentage)/100, 2);
            $whereCondition = "WHERE credit_note_id = {$credit_note_id}";
            $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa2, 'credit_note', $whereCondition);
            $db->sql_query($SQLUpdate);
        }

        return $validate->getSuccessMessageXML();
    }

    function getEditCreditNoteFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $creditNoteId = $fn->getPostParam('credit_note_id');
        $date = $fn->getPostParam('date');
        $remarks = $fn->getPostParam('remarks');
        $applyDigitalSignature = $fn->getPostParam('apply_digital_signature');
        $signatureName = $fn->getPostParam('signature_name');
        $historyIds = $fn->getPostParam('history_id', array(), false, true);
        $invoiceIds = $fn->getPostParam('invoice_id', array(), false, true);
        $titles = $fn->getPostParam('title', array(), false, true);
        $descriptions = $fn->getPostParam('description', array(), false, true);
        $amounts = $fn->getPostParam('amount', array(), false, true);
        $gstPercentage = $fn->getSettingsValueByKey('cp.gstPercentage');

        $validate->resetErrorArray();
        $validate->validateData('date', 'Please select the date');
        foreach ($amounts as $index => $amount) {
            if ((float) $amount > 0 && isset($invoiceIds[$index])) {
                $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoiceIds[$index]);
                if ((float) $amount > (float) $invoiceRec['invoice_amount']) {
                    $validate->validateData('error_box', 'Please enter the credit note amount within the invoice amount');
                }
            }
        }
        if (count($validate->errorArray) > 0) {
            return $validate->getErrorMessageXML();
        }

        $creditNoteData = array(
            'date' => $date,
            'remarks' => $remarks,
            'apply_digital_signature' => $applyDigitalSignature,
            'signature_name' => $signatureName
        );
        $creditNoteData = $fn->addModificationDetailsToFieldsArray($creditNoteData, 'credit_note');
        $updateSql = $dbUtil->getUpdateSQLStringFromArray($creditNoteData, 'credit_note', "WHERE credit_note_id = {$creditNoteId}");
        $db->sql_query($updateSql);

        $totalAmount = 0;
        foreach ($historyIds as $index => $historyId) {
            if ($historyId == '') {
                continue;
            }

            $amount = isset($amounts[$index]) ? $amounts[$index] : 0;
            $historyData = array(
                'item_title' => isset($titles[$index]) ? $titles[$index] : '',
                'description' => isset($descriptions[$index]) ? $descriptions[$index] : '',
                'amount' => $amount,
                'gst_percentage' => $gstPercentage
            );
            $historyData = $fn->addModificationDetailsToFieldsArray($historyData, 'invoice_credit_note_history');
            $historyUpdateSql = $dbUtil->getUpdateSQLStringFromArray($historyData, 'invoice_credit_note_history', "WHERE invoice_credit_note_history_id = {$historyId}");
            $db->sql_query($historyUpdateSql);
            $historyAffectedRows = $db->sql_affectedrows();

            if ($historyAffectedRows == 0 && isset($invoiceIds[$index])) {
                $historyUpdateSql = $dbUtil->getUpdateSQLStringFromArray($historyData, 'invoice_credit_note_history', "WHERE credit_note_id = {$creditNoteId} AND invoice_id = {$invoiceIds[$index]}");
                $db->sql_query($historyUpdateSql);
            }
            $totalAmount += (float) $amount;
        }

        $creditNoteData = array(
            'amount' => $totalAmount,
            'gst_percentage' => 0,
            'gst_amount' => round(($totalAmount * $gstPercentage) / 100, 2)
        );
        $updateSql = $dbUtil->getUpdateSQLStringFromArray($creditNoteData, 'credit_note', "WHERE credit_note_id = {$creditNoteId}");
        $db->sql_query($updateSql);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
   function getUpdateAddInvoiceCode()
{
$db = Zend_Registry::get('db');
$fn = Zend_Registry::get('fn');

$site_id = $fn->getSessionParam('cp_site_id');

// Main Invoice Prefix
$SQL = "
    SELECT value
    FROM setting
    WHERE key_text = 'invoiceCodePrefix'
    LIMIT 1
";
$result = $db->sql_query($SQL);
$row = $db->sql_fetchrow($result);

$invoicePrefix = !empty($row['value']) ? $row['value'] : 'INV/';

// Country Prefix
switch ($site_id) {
    case 1:
        $prefixKey = 'invoiceCodePrefixKWT';
        break;

    case 2:
        $prefixKey = 'invoiceCodePrefixKSA';
        break;

    default:
        $prefixKey = 'invoiceCodePrefixDefault';
        break;
}

// Get Country Prefix
$SQL = "
    SELECT value
    FROM setting
    WHERE key_text = '{$prefixKey}'
    LIMIT 1
";
$result = $db->sql_query($SQL);
$row = $db->sql_fetchrow($result);

$countryPrefix = !empty($row['value']) ? $row['value'] : '';

// Get Common Next Invoice Number
$SQL = "
    SELECT value
    FROM setting
    WHERE key_text = 'nextInvoiceCode'
    LIMIT 1
";
$result = $db->sql_query($SQL);
$row = $db->sql_fetchrow($result);

$nextInvoiceCode = !empty($row['value']) ? $row['value'] : 1;

// Generate Invoice Code
$invoiceCode = $invoicePrefix . $countryPrefix . $nextInvoiceCode . '-' . date('Y');

// Increment Common Counter
$SQL = "
    UPDATE setting
    SET value = value + 1
    WHERE key_text = 'nextInvoiceCode'
";
$db->sql_query($SQL);

return $invoiceCode;
}

function getGenerateCreditNoteCode()
{
    $db = Zend_Registry::get('db');
    $fn = Zend_Registry::get('fn');

    $site_id = $fn->getSessionParam('cp_site_id');

    // Main Credit Note Prefix
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = 'creditNotePrefix'
        LIMIT 1
    ";

    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $creditNotePrefix = !empty($row['value'])
        ? $row['value']
        : 'CN';

    // Country Prefix
    switch ($site_id) {

        case 1:
            $prefixKey = 'creditNotePrefixKWT';
            break;

        case 2:
            $prefixKey = 'creditNotePrefixKSA';
            break;

        default:
            $prefixKey = 'creditNotePrefixDefault';
            break;
    }

    // Get Country Prefix
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = '{$prefixKey}'
        LIMIT 1
    ";

    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $countryPrefix = !empty($row['value'])
        ? $row['value']
        : '';

    // Get Next Credit Note Number
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = 'nextCreditNoteCode'
        LIMIT 1
    ";

    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $nextCreditNoteCode = !empty($row['value'])
        ? $row['value']
        : 1;

    // Format Number
    $formattedNumber = str_pad(
        $nextCreditNoteCode,
        4,
        '0',
        STR_PAD_LEFT
    );

    // Generate Credit Note Code
    $creditNoteCode =
        $creditNotePrefix .
        $countryPrefix .
        $formattedNumber;

    // Increment Counter
    $SQL = "
        UPDATE setting
        SET value = value + 1
        WHERE key_text = 'nextCreditNoteCode'
    ";

    $db->sql_query($SQL);

    return $creditNoteCode;
}
/**
     *
     */
    function getGenerateCreditNoteFormValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $amount_arr      = $fn->getPostParam('amount', array());
        $invoice_id_arr  = $fn->getReqParam('invoice_id', array());

        $validate->resetErrorArray();

        $count = count($amount_arr);
        for ($i= 0; $i < $count; $i++) {
            $amount     = $amount_arr[$i];
            $invoice_id = $invoice_id_arr[$i];

            if ($amount > 0) {
                $invoiceRec = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);
                if ($amount > $invoiceRec['invoice_amount']) {
                    $msg = "<div>Please enter the credit note amount not greater than invoice amount</div>";
                    $validate->validateData('error_box', $msg);
                }
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
