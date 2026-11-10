<?
class CP_Admin_Modules_EnggCrm_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
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
                                        i.invoice_code   LIKE '%{$tv['keyword']}%' OR
                                        i.invoice_amount LIKE '%{$tv['keyword']}%' OR
                                        p.title          LIKE '%{$tv['keyword']}%'OR
                                        i.project_id     LIKE '%{$tv['keyword']}%'OR
                                        c.company_name   LIKE '%{$tv['keyword']}%'
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
            $searchVar->sortOrder = "i.invoice_date DESC, i.invoice_code DESC";
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
             ,'contact_name'        => $phpExcel->getFldObj('Client Contact')
             ,'company_name'        => $phpExcel->getFldObj('Client Company')
             ,'project_title'       => $phpExcel->getFldObj('Project Name')
             ,'project_value'       => $phpExcel->getFldObj('Project Value')
             
             ,'still_to_bill'       => $phpExcel->getFldObj('Still to Bill')
             
             ,'invoice_amount'      => $phpExcel->getFldObj('Invoice Amount')
             ,'status'              => $phpExcel->getFldObj('Status')        
             ,'inv_currency'        => $phpExcel->getFldObj('Currency')
             ,'invoice_date'        => $phpExcel->getFldObj('Invoice Date')
             
             ,'invoice_sent_out'    => $phpExcel->getFldObj('Invoice Sent Out')
             
             ,'invoice_due_date'    => $phpExcel->getFldObj('Invoice Due Date')
             ,'invoice_paid_date'   => $phpExcel->getFldObj('Invoice Paid Date')
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $order_id        = $fn->getReqParam('order_id');
        $title_arr       = $fn->getPostParam('title', array());
        $description_arr = $fn->getPostParam('description', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $description_arr = $fn->getPostParam('description', array());
        $remarks_arr     = $fn->getPostParam('remarks', array());

        if (!$this->getGenerateInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $orderRec   = $fn->getRecordRowByID('order', 'order_id', $order_id);
        /* Generation of Invoice record */
        $faInv = array();
        $faInv['title']            = $orderRec['quote_title'];
        $faInv['invoice_code']     = $fn->getSettingsValueByKey('invoiceCodePrefix') . $fn->getSettingsValueByKey('nextInvoiceCode');
        $faInv['invoice_date']     = date('Y-m-d');
        $faInv['status']           = 'Due';
        $faInv['order_id']         = $order_id;
        $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+30 days"));
        $faInv['creation_date']    = date('Y-m-d H:i:s');
        $faInv['created_by']       = $fn->getSessionParam('userName');
        $faInv['invoice_terms']    = $fn->getSettingsValueByKey("invoiceTermsAndCondition");
        
        $gst_percentage = $fn->getSettingsValueByKey("cp.gstPercentage");
        if ($gst_percentage) {
            $faInv['gst_percentage'] = $gst_percentage;
        }

        $insertInv  = $dbUtil->getInsertSQLStringFromArray($faInv, 'invoice');
        $resultInv  = $db->sql_query($insertInv);
        $invoice_id = $db->sql_nextid();

        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $result = $db->sql_query($SQL);

        $count = count($description_arr);
        for ($i= 0; $i < $count; $i++) {
            $title       = $title_arr[$i];
            $description = $description_arr[$i];
            $amount      = $amount_arr[$i];
            $unit        = $unit_arr[$i];
            $quantity    = $quantity_arr[$i];
            $description = $description_arr[$i];
            $remarks     = $remarks_arr[$i];

            if ($description) {
                $faIi = array();
                $faIi['invoice_id']   = $invoice_id;
                $faIi['description']  = $description;
                $faIi['item_title']   = $title;
                $faIi['unit_price']   = $amount;
                $faIi['unit']         = $unit;
                $faIi['qty']          = $quantity;
                $faIi['description']  = $description;
                $faIi['remarks']      = $remarks;

                $insert = $dbUtil->getInsertSQLStringFromArray($faIi, 'invoice_item');
                $result = $db->sql_query($insert);
                $invoice_item_id = $db->sql_nextid();
            }
        }

        $total_invoice_amount = 0;
        $sqlIi = "
        SELECT *
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultIi  = $db->sql_query($sqlIi);
        while ($rowIi = $db->sql_fetchrow($resultIi)) {
            if ($rowIi['qty'] > 0) {
                $total_invoice_amount += $rowIi['unit_price'] * $rowIi['qty'];
            } else {
                $total_invoice_amount += $rowIi['unit_price'];
            }
        }

        /* Update quote status */
        $faInvoice = array();
        $faInvoice['invoice_amount'] = $total_invoice_amount;
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

        $order_id        = $fn->getReqParam('order_id');
        $amount_arr      = $fn->getPostParam('amount', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());

        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);

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
        $validate->resetErrorArray();

        $total_entered_amount = 0;
        $count = count($amount_arr);
        for ($i= 0; $i < $count; $i++) {
            $amount   = $amount_arr[$i];
            $quantity = $quantity_arr[$i];

            /*
            if ($i == 0 && ($amount == '' || $quantity == '')) {
                $msg = 'Please enter amount/quantity for atleast 1 item.';
                $validate->validateData('error_box', $msg);
            }
            */

            if ($i == 0 && $amount == '') {
                $msg = 'Please enter amount for atleast 1 item.';
                $validate->validateData('error_box', $msg);
            }

            if ($amount > 0 && $quantity > 0) {
                $total_entered_amount += ($amount * $quantity);
            } else if ($amount > 0 && $quantity == '') {
                $total_entered_amount += $amount;
            }
        }

        if ($total_entered_amount == $total_order_amount_due) {
            return true;
        }

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && $total_entered_amount > $total_order_amount_due) {
            //$msg = "<div class='invoiceAlert'>Please enter the total amount not greater than " . number_format($total_order_amount_due,2) . ". You have entered a total of " . number_format($total_entered_amount, 2) . "</div>";
            $msg = "<div class='invoiceAlert'>Please enter the total amount not greater than " . $total_order_amount_due . ". You have entered a total of " . $total_entered_amount . "</div>";
            $validate->validateData('error_box', $msg);
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
                $faInvoice['invoice_amount'] = $total_invoice_amount;
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoice_id    = $fn->getReqParam('invoice_id');
        $invoice_date  = $fn->getPostParam('invoice_date');
        $invoice_title = $fn->getPostParam('invoice_title');

        $faInv = array();
        $faInv['invoice_date'] = $invoice_date;
        $faInv['title']        = $invoice_title;
        if ($cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm']) {
            $faInv['invoice_terms'] = $fn->getPostParam('invoice_terms');
        }

        if ($cpCfg['m.enggCrm.invoice.showLocationInEditForm']) {
            $faInv['title'] = $fn->getPostParam('invoice_title');
        }

        $faInv = $fn->addModificationDetailsToFieldsArray($faInv, 'invoice');
        $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

        foreach($_POST AS $key => $val){
            if (substr($key, 0, 5) == 'title'){
                $invoice_item_arr = explode('_', $key);
                $invoice_item_id = $invoice_item_arr[1];
                $title = $val;

                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
                if ($title != '' && $invoiceItemRec['item_title'] != $title) {
                    $faIi = array();
                    $faIi['item_title'] = $title;
                    $faIi = $fn->addModificationDetailsToFieldsArray($faIi, 'invoice_item');
                    $fn->saveRecord($faIi, 'invoice_item', 'invoice_item_id', $invoice_item_id);
                }
            }
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

            $invoice_code = 'PT/' . substr($row['invoice_code'], 2);
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
    function getPastBalanceAmount($company_id, $end_date, $no_of_days){
        $db = Zend_Registry::get('db');
        
        if ($no_of_days == 30) {
            $start_date = date('Y-m-d', strtotime($end_date . " -{$no_of_days} days"));;
            
            $sqlAppend  = "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($no_of_days == 60) {
            $start_date = date('Y-m-d', strtotime($end_date . " -31 days"));;
            $end_date   = date('Y-m-d', strtotime($end_date . " -{$no_of_days} days"));;
            
            $sqlAppend  = "BETWEEN '{$end_date}' AND '{$start_date}'";
        } else {
            $start_date = date('Y-m-d', strtotime($end_date . " -61 days"));;
            $sqlAppend = "< '{$start_date}'";
        }
        
        $sqlInvoice = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        WHERE (i.status = 'Due' OR i.status = 'Partial Payment' OR i.status = 'Late')
              AND o.company_id = {$company_id}
          AND i.invoice_date {$sqlAppend}
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        $total_invoice_amount     = 0;
        $total_outstanding_amount = 0;
        
        while ($rowInvoice = $db->sql_fetchrow($resultInvoice)) {

            /* GST Calculation for Invoice */
            if ($rowInvoice['gst_percentage'] > 0) {
                $gst_amount = (($rowInvoice['invoice_amount'] * $rowInvoice['gst_percentage'])/100);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }

                $total_invoice_amount += $rowInvoice['invoice_amount'] + $gst_amount;
            } else {
                $total_invoice_amount += $rowInvoice['invoice_amount'];
            }

            /* Payment received for Invoice */
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

            $amount_payable_after_rec = $total_invoice_amount - $rowRec['total_invoice_amount_paid'];
            $total_outstanding_amount += $amount_payable_after_rec;
        }

        return $total_outstanding_amount;
    }

    /**
     *
     */
    function getPrintStatementOfAccount() {
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
        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        $template = 'Statement.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = $companyRec['company_id'] . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name.'.xlsx');

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;

        $current_date = date('Y-m-d');

        $SQL = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
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
        
        $blkInvAmt      = array();
        $blkRecAmt      = array();
        $blkInvCode     = array();
        $blkInvDate     = array();
        $blkAmount      = array();
        
        $arr['parent_name']     = strtoupper($companyRec['company_name']);
        $arr['address_flat']    = strtoupper($companyRec['billing_address_flat']);
        $arr['address_street']  = strtoupper($companyRec['billing_address_street']);
        $arr['address_country'] = strtoupper($companyRec['billing_address_country']) . " " . $companyRec['billing_address_po_code'];

        $total_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {

            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD.MM.YYYY');

            $invoice_amount_after_disc = $row['invoice_amount'] - $row['discount'];
            $gst_amount = 0;
            if ($row['gst_percentage']) {
                $gst_amount = round((($invoice_amount_after_disc * $row['gst_percentage']) / 100), 2);
            }
            $amount_payable = $invoice_amount_after_disc + $gst_amount;
            $total_amount += $amount_payable;

            //repeating rows of product values
            $arr1 = array('invoice_amt' => number_format($amount_payable, 2));
            $blkInvAmt[] = $arr1;

            $total_receipt_for_invoice = $this->getTotalReceiptAmountForInvoice($row['invoice_id']);
            $arr3 = array('invoice_paid_amt' => number_format($total_receipt_for_invoice, 2));
            $blkRecAmt[] = $arr3;

            $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            $invoice_code = $inv_date . substr($row['invoice_code'], 2);
            $arr11 = array('invoice_code' => $invoice_code);
            $blkInvCode[] = $arr11;

            $arr2 = array('invoice_date' => $invoice_date);
            $blkInvDate[] = $arr2;

            $balance_amount = $total_outstanding_amount + $amount_payable - $total_receipt_for_invoice;
            $arr6 = array('total_amount_payable' => number_format($balance_amount, 2));
            $blkAmount[] = $arr6;

            $total_outstanding_amount = $balance_amount;
            $serialNo++;
        }

        $arr['total_outstanding_amount'] = number_format($total_outstanding_amount, 2);
        $arr['current_date']             = $fn->getCPDate($current_date, 'd M Y');;
        $arr['30days_due']               = number_format($this->getPastBalanceAmountStatementofAccount($company_id, $current_date, 30), 2);
        $arr['60days_due']               = number_format($this->getPastBalanceAmountStatementofAccount($company_id, $current_date, 60), 2);
        $arr['60moredays_due']           = number_format($this->getPastBalanceAmountStatementofAccount($company_id, $current_date, 61), 2);

        $blkMain[] = $arr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkInvDate', $blkInvDate);
        $TBS->MergeBlock('blkInvCode', $blkInvCode);
        $TBS->MergeBlock('blkInvAmt', $blkInvAmt);
        $TBS->MergeBlock('blkRecAmt', $blkRecAmt);
        $TBS->MergeBlock('blkAmount', $blkAmount);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPastBalanceAmountStatementofAccount($company_id, $end_date, $no_of_days){
        $db = Zend_Registry::get('db');
        
        $total_invoice_amount = 0;
        
        if ($no_of_days == 30) {
            $start_date = date('Y-m-d', strtotime($end_date . " -{$no_of_days} days"));;
            
            $sqlAppend  = "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($no_of_days == 60) {
            $start_date = date('Y-m-d', strtotime($end_date . " -31 days"));;
            $end_date   = date('Y-m-d', strtotime($end_date . " -{$no_of_days} days"));;
            
            $sqlAppend  = "BETWEEN '{$end_date}' AND '{$start_date}'";
        } else {
            $start_date = date('Y-m-d', strtotime($end_date . " -61 days"));;
            $sqlAppend = "<= '{$start_date}'";
        }
        
        $sqlInvoice = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        WHERE o.company_id = {$company_id}
          AND (i.status = 'Due' OR i.status = 'Partial Payment' OR i.status = 'Late')
          AND o.company_id = {$company_id}
          AND i.invoice_date {$sqlAppend}
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        $total_inv_amt_after_discount = 0;
        $amount_paid = 0;
        $balance_amount = 0;
        while ($rowInv = $db->sql_fetchrow($resultInvoice)) {
            $invoice_amount_after_disc = $rowInv['invoice_amount'] - $rowInv['discount'];
            $gst_amount = 0;
            if ($rowInv['gst_percentage']) {
                $gst_amount = round((($invoice_amount_after_disc * $rowInv['gst_percentage']) / 100), 2);
            }
            $total_inv_amt_after_discount = $invoice_amount_after_disc + $gst_amount;

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$rowInv['invoice_id']}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);            
            $amount_paid = $rowRec['total_invoice_amount_paid'];

            $balance_amount += $total_inv_amt_after_discount - $amount_paid;
        }
        
        return $balance_amount;
    }

    /**
     *
     */
    function getTotalReceiptAmountForInvoice($invoice_id){
        $db = Zend_Registry::get('db');
        
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

        // Finding total Credit Note Amount from History table
        $sqlCn = "
        SELECT SUM(icnh.amount) AS total_credit_note_amount FROM invoice_credit_note_history icnh
        WHERE icnh.invoice_id = '{$invoice_id}'
        ";
        $resultCn  = $db->sql_query($sqlCn);
        $numRowsCn = $db->sql_numrows($resultCn);
        $rowCn = $db->sql_fetchrow($resultCn);
        $credit_note_amt = $rowCn['total_credit_note_amount'];

        // Calculating Average GST percentage for credit note
        $sqlCnGstCalc = "
        SELECT cn.gst_percentage
        FROM credit_note cn
        LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
        WHERE icnh.invoice_id  = {$invoice_id}
        ";
        $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
        $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
        $gst_amount_cn = 0;
        if ($numRowsCnGstCalc) {
            $total_gst_percentage_cn = 0;
            while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                $total_gst_percentage_cn += $rowCnGstCalc['gst_percentage'];
            }            
            $gst_percentage_cn = ($total_gst_percentage_cn/$numRowsCnGstCalc);

            $gst_amount_cn = round((($credit_note_amt * $gst_percentage_cn)/100),2);
            // Taking two decimal values for gst amount
            $fraction_length = strlen(substr(strrchr($gst_amount_cn, "."), 1)); // Checking the length of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount_cn);
                $fraction = substr($fraction, 0, 2);
                $gst_amount_cn = $integer . "." . $fraction;
            }
        }

        return $rowRec['total_invoice_amount_paid'] + $credit_note_amt + $gst_amount_cn;
    }
}
