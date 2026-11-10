<?
class CP_Admin_Modules_Project_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
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
          AND invoice_sequence < i.invoice_sequence
          AND status != LOWER('Cancelled')
        )";

        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $joinTbls .= "LEFT JOIN branch b ON(p.branch_id = b.branch_id)";
            $joinFlds .= ",b.title AS branch_name";
        }

        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
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
                    AND invoice_sequence < i.invoice_sequence
                ) AS prior_invoice_billed
                ";
            }

            $SQL = "
            {$flds}
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
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

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
            $searchVar->sortOrder = "
            CASE
            WHEN (i.status = 'Late' ) THEN 1
            WHEN (i.invoice_due_date != '' AND i.invoice_due_date IS NOT NULL AND i.invoice_due_date != '0000-00-00' ) THEN 2
            ELSE 3
            END, i.invoice_due_date
            ";
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
        $fa = $fn->addToFieldsArray($fa, 'invoice_amount_base');
        $fa = $fn->addToFieldsArray($fa, 'invoice_amount_ref');
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

        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
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
    function getExportData1($dataArray){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

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

        foreach ($dataArray as $row){
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
        ob_end_clean();

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
    function getPrintSubscriptionPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $invoice_id = $fn->getReqParam('invoice_id');

        $SQL = "
        SELECT i.*
              ,c.company_name
              ,c.company_id
              ,i.invoice_code
              ,i.invoice_date
              ,i.invoice_due_date
              ,i.invoice_terms
              ,i.invoice_amount
              ,i.invoice_amount_ref
              ,i.inv_currency
              ,p.branch_id
              ,cont.contact_id
              ,cont.address_flat
              ,cont.address_street
              ,cont.address_town
              ,cont.address_state
              ,cont.address_country
              ,cont.address_po_code
              ,cont.phone_direct
              ,ca.address_flat as company_flat
              ,ca.address_street as company_street
              ,ca.address_town as company_town
              ,ca.address_state as company_state
              ,ca.address_country as company_country
              ,ca.address_po_code as company_po_code
              ,ca.phone
              ,CONCAT_WS(' ', cont.first_name, cont.last_name ) AS contact_name
              ,cont.salutation
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id)
        LEFT JOIN (company_address ca) ON (ca.company_id = c.company_id)
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result   = $db->sql_query($SQL);
        $result2  = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        $dateMonth = $fn->getCPDate($today, 'm');
        $dateYear  = $fn->getCPDate($today, 'Y');

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
        $product_group_title = '';
        $subtotalvalue = '';

        $rowInvoice = $db->sql_fetchrow($result2);


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');

        if($rowInvoice['branch_id'] == 2){
            include_once(CP_LOCAL_PATH.'lib/headfoot1.php');
        }else{
            include_once(CP_LOCAL_PATH.'lib/headfoot.php');
        }

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Universal Software Solutions');
        $pdf->SetSubject('Print Invoice');
        $pdf->SetTitle('Print Invoice');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUOTE QUERY START

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/

        $pdf->SetFont('Courier','',10);
        $pdf->AddPage();

        $tbl2 = '<table border="0" width="100%" cellpadding="5">
                        <tr>
                            <td border="0" align="center" height="30"><font style="font-size:25px; font-weight:bold">INVOICE</font>
                                <br/>#'.$rowInvoice['invoice_code'].'
                            </td>
                        </tr>
                    </table>';
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $invoice_date     = $fn->getCPDate($rowInvoice['invoice_date'],"d-m-Y");
        $invoice_due_date = $fn->getCPDate($rowInvoice['invoice_due_date'],"d-m-Y");

        $company_flat    = '';
        $company_street  = '';
        $company_town    = '';
        $company_state   = '';
        $company_country = '';
        $company_po_code = '';
        $company_phone   = '';

        if($rowInvoice['company_flat'] != ''){
            $company_flat   = $rowInvoice['company_flat'].'<br/>';
        }

        if($rowInvoice['company_street'] != ''){
            $company_street = $rowInvoice['company_street'].'<br/>';
        }

        if($rowInvoice['company_town'] != ''){
            $company_town   = $rowInvoice['company_town'].'<br/>';
        }

        if($rowInvoice['company_state'] != ''){
            $company_state  = $rowInvoice['company_state'].'<br/>';
        }

        if($rowInvoice['company_country'] != ''){
            $company_country = $rowInvoice['company_country'];
        }

        if($rowInvoice['company_po_code'] != ''){
            $company_po_code = ' - '.$rowInvoice['company_po_code'].'<br/>';
        }

        if($rowInvoice['phone'] != ''){
            $company_phone   = $rowInvoice['phone'];
        }

        /*else{
            $company_flat    = $rowInvoice['address_flat'].'<br/>';
            $company_street  = $rowInvoice['address_street'].'<br/>';
            $company_town    = $rowInvoice['address_town'].'<br/>';
            $company_state   = $rowInvoice['address_state'].'<br/>';
            $company_country = $rowInvoice['address_country'];
            $company_po_code = '-'.$rowInvoice['address_po_code'].'<br/>';
            $company_phone   = $rowInvoice['phone_direct'];
        }*/

        $tbl1 = '<table border="0" width="100%" cellpadding="0">
                        <tr>
                            <td border="0"width="64%"align="left" style="text-decoration:underline;">Invoice TO</td>
                            <td width="22%"align="left">Invoice Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="14%"align="right">'.$invoice_date.'</td>
                        </tr>
                        <tr>
                            <td border="0" align="left" width="64%"><b>Kind Attn: '.$rowInvoice['salutation'] . '.' .$rowInvoice['contact_name'].'</b><br/>
                            '.$rowInvoice['company_name'].'<br/>
                            '.$company_flat.'
                            '.$company_street.'
                            '.$company_town.'
                            '.$company_state.'
                            '.$company_country.''.$company_po_code.'
                            '.$company_phone.'
                            </td>
                            <td width="22%"align="left">Invoice Due Date&nbsp;:</td>
                            <td width="14%"align="right">'.$invoice_due_date.'</td>
                        </tr>
                    </table>';
        $pdf->writeHTML($tbl1, true, false, false, false, '');

        $tbl3 ='<table cellpadding="2" border="0" width="100%">';

        $tbl3 = $tbl3.'
                    <thead>
                    <tr>
                        <th style="border-bottom:2px solid #1242AB;" align="center" width="70%"><b>DESCRIPTION</b></th>
                        <th style="border-bottom:2px solid #1242AB;" align="right" width="30%"><b>AMOUNT '.$rowInvoice['inv_currency'].'</b></th>
                    </tr>
                    </thead>';

        while ($row = $db->sql_fetchrow($result)) {

             $tbl3 = $tbl3.'
                    <tr style="border:1px solid #DDE4FF;" bgcolor="#DDE4FF"><br>
                        <td style= "border-right:1px white;" width="70%">'.nl2br($row['notes']).'</td>
                        <td style= "border-right:1px white;" align="right" width="30%">'.number_format($row['invoice_amount'],2).'</td>
                    </tr>
                ';

             $subtotalvalue  += $row['invoice_amount'];
        }

        $tbl3 = $tbl3.'
        <tr style="border:1px solid #DDE4FF;" bgcolor="#DDE4FF"><br>
            <td style= "border-right:1px white;" width="70%"></td>
            <td style= "border-right:1px white;" width="30%"></td>
        </tr>
        <tr>
            <td style="border-bottom:2px solid #1242AB;" width="70%"></td>
            <td style="border-bottom:2px solid #1242AB;" width="30%"></td>
        </tr>
        ';

        $tbl3 = $tbl3.'
        <tr>
            <td width="70%"></td>
            <td width="30%"></td>
        </tr>
        <tr style="border:1px solid #DDE4FF;">
            <td width="25%"></td>
            <td width="25%"></td>
            <td bgcolor="#DDE4FF" width="20%" style="border-right:5px solid white;">SUB TOTAL</td>
            <td bgcolor="#DDE4FF" width="30%" align="right">'.number_format($subtotalvalue,2).'</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td width="25%"></td>
            <td width="25%"></td>
            <td bgcolor="#DDE4FF" width="20%" style="border-right:5px solid white;">DISCOUNT</td>
            <td bgcolor="#DDE4FF" width="30%" align="right"></td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td width="25%"></td>
            <td width="25%"></td>
            <td bgcolor="#DDE4FF" width="20%" style="border-right:5px solid white;">TRANSFER CHARGES</td>
            <td bgcolor="#DDE4FF" width="30%" align="right"></td>
        </tr>
        <tr>
             <td width="25%" style="border-bottom:2px solid #1242AB;"></td>
             <td width="25%" style="border-bottom:2px solid #1242AB;"></td>
             <td width="20%" style="border-bottom:2px solid #1242AB;"></td>
             <td width="30%" style="border-bottom:2px solid #1242AB;"></td>
        </tr><br>
        ';

        $totalvalue = $subtotalvalue;

        $tbl3 = $tbl3.'
        <tr style="border:1px solid #DDE4FF;">
            <td width="25%"></td>
            <td width="25%"></td>
            <td bgcolor="#DDE4FF" style= "border-right:5px solid white; color:#1242AB;"><font style="font-size:17px; font-weight:bold;">TOTAL</font></td>
            <td bgcolor="#DDE4FF" align="right" style="border-right:5px solid white; color:#1242AB;"><font style="font-size:15px; font-weight:bold;">'.number_format(round($totalvalue), 2).'</font></td>
        </tr>
        ';

        $tbl3 = $tbl3.'</table>';

        $tblTerms = '
        <table style="font-weight:bold;" border="0" cellpadding="2" width="100%">
            <tr>
                <td>TERMS:</td>
            </tr>
            <tr>
                <td>'.nl2br($rowInvoice['invoice_terms']).'</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(10);
        $pdf->writeHTML($tblTerms, true, false, false, false, '');
        $pdf->Output('print_invoice.pdf', 'I');

    }

    /**
     *
     */
    function getPrintSubscriptionPdfOLD() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Courier','',11);

        $invoice_id = $fn->getReqParam('invoice_id');

        $SQL = "
        SELECT i.*
              ,c.company_name
              ,c.company_id
              ,i.invoice_code
              ,i.invoice_date
              ,i.invoice_due_date
              ,i.invoice_terms
              ,i.invoice_amount
              ,i.invoice_amount_ref
	          ,cont.contact_id
              ,cont.address_flat
              ,cont.address_street
              ,cont.address_town
              ,cont.address_state
              ,cont.address_country
              ,cont.phone_direct
              ,CONCAT_WS(' ', cont.first_name, cont.last_name ) AS contact_name
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        LEFT JOIN (contact cont)ON (p.contact_id = cont.contact_id)
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        $dateMonth = $fn->getCPDate($today, 'm');
        $dateYear = $fn->getCPDate($today, 'Y');
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
        $product_group_title = '';

        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',124,10,85);

                $pdf->SetFont('Courier','B',11);
                $pdf->SetXY(6,3);
                $pdf->Rect(5, 7, 118, 44);
                $pdf->Cell(28, 20, "Invoice# :", 0, 0, 'L');
                $pdf->Cell(28, 20, $row['invoice_code']);
				$pdf->Ln(7);
                $pdf->SetXY(6,6);
                $pdf->Cell(28, 25, "Company Name:", 0, 0, 'L');
                $pdf->SetX(40);
                $pdf->Cell(28, 25, $row['company_name']);
                $pdf->Ln(5);
                $pdf->SetXY(7,22);
                $pdf->drawTextBox($row['address_flat'], 120, 50, 'L', 'T', 0);
	            if($row['address_street']!= ''){
                $pdf->SetXY(7,28);
                $pdf->drawTextBox($row['address_street'], 120, 50, 'L', 'T', 0);
                $pdf->Ln(5);
				}
                $pdf->SetXY(6,22);
                $pdf->Cell(28, 28, "Chennai:", 0, 0, 'L');
                $pdf->SetX(25);
                $pdf->Cell(28, 28, $row['address_town']);
                $pdf->Ln(5);
                $pdf->SetXY(6,25);
                $pdf->Cell(28, 33, "Phone:", 0, 0, 'L');
                $pdf->SetX(25);
                $pdf->Cell(28, 33, $row['phone_direct']);
                $pdf->Ln(5);
                $pdf->SetXY(6,29);
                $pdf->Cell(28, 35, "Attn:", 0, 0, 'L');
                $pdf->SetX(25);
                $pdf->Cell(28, 35, $row['contact_name']);
                $pdf->Ln(5);


                /* List of order items header */
                $pdf->SetXY(5,70);
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,128,128);
                $pdf->Cell(27,8,"Date",1,0, 'C', 1);
                $pdf->Cell(28,8,"Your Order",1,0, 'C', 1);
                $pdf->Cell(28,8,"Our Order",1,0, 'C', 1);
                $pdf->Cell(32,8,"Sales Rep.",1,0, 'C', 1);
                $pdf->Cell(85,8,"Terms",1,0, 'C', 1);
                $pdf->Ln();

                $pdf->SetXY(5,78);
                $pdf->SetFillColor(255,255,255);
                //$pdf->SetTexColor(255,255,255);
	            $pdf->Cell(27, 12, $row['invoice_date'], 1, 0, 'L', 1);
	            $pdf->Cell(28, 12, '', 1, 0, 'L', 1);
	            $pdf->Cell(28, 12, '', 1, 0, 'L', 1);
	            $pdf->Cell(32, 12, 'Syed Magthum', 1, 0, 'L', 1);
                $pdf->drawTextBox($row['invoice_terms'], 85, 12, 'L', 1, 'T', 1);

                $pdf->SetXY(5,100);
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,128,128);
                $pdf->Cell(20,8,"Quantity",1,0, 'L', 1);
                $pdf->Cell(100,8,"Description",1,0, 'L', 1);
                $pdf->Cell(23,8,"Discount",1,0, 'L', 1);
                $pdf->Cell(25,8,"Unit Price.",1,0, 'L', 1);
                $pdf->Cell(32,8,"Total- INR",1,0, 'L', 1);
                $pdf->Ln();

                $invoice_due_date = $fn->getCPDate($row['invoice_due_date'], 'd M Y');

                $pdf->SetXY(5,108);
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(20, 12, '1.', 1, 0, 'L', 1);
                $pdf->drawTextBox('Online Trading System Subscription'. ' '. $row['invoice_amount'].' '. 'for'.' '. $invoice_due_date, 100, 12, 'L', 1, 'T', 0);
                $pdf->SetXY(125,108);
	            $pdf->Cell(23, 12, '', 1, 0, 'L', 1);
	            $pdf->Cell(25, 12, '', 1, 0, 'L', 1);
	            $pdf->Cell(32, 12, '', 1, 0, 'L', 1);

                $pdf->SetXY(5,120);
                $pdf->Cell(20,12,"",1,0, 'L', 1);
                $pdf->Cell(100,12,"",1,0, 'L', 1);
                $pdf->Cell(23,12,"",1,0, 'L', 1);
                $pdf->Cell(25,12,"",1,0, 'L', 1);
                $pdf->Cell(32,12,"",1,0, 'L', 1);

                $pdf->SetXY(5,130);
                $pdf->Cell(20,12,"",1,0, 'L', 1);
                $pdf->Cell(100,12,"",1,0, 'L', 1);
                $pdf->Cell(23,12,"",1,0, 'L', 1);
                $pdf->Cell(25,12,"",1,0, 'L', 1);
                $pdf->Cell(32,12,"",1,0, 'L', 1);

                $pdf->SetXY(148,142);
                $pdf->Cell(25,14,"Subtotal",1,0, 'L', 1);
                $pdf->Cell(32,14,$row['invoice_amount_ref'],1,0, 'L', 1);

                $pdf->SetXY(148,155);
                $pdf->Cell(25,12,"Discount",1,0, 'L', 1);
                $pdf->Cell(32,12, "",1,0, 'L', 1);

                $pdf->SetXY(148,167);
                $pdf->drawTextBox("Transfer Charges", 25, 15, 'L', 1, 'T', 1);
                $pdf->SetXY(173,167);
                $pdf->Cell(32,15, "",1,0, 'L', 1);

                $pdf->SetXY(148,180);
                $pdf->Cell(25,14,"Total",1,0, 'L', 1);
                $pdf->Cell(32,14,$row['invoice_amount_ref'],1,0, 'L', 1);

                $pdf->SetXY(5,170);
                $pdf->Cell(25,14,"Pay : A.M Syed Magthum",0, 'L', 1);
                $pdf->SetXY(5,176);
                $pdf->Cell(25,17,"Bank: ICICI",0, 'L', 1);
                $pdf->SetXY(5,180);
                $pdf->Cell(25,16,"Ac NO : 60200 131 8444",0, 'L', 1);
                $pdf->SetXY(4,183);
                $pdf->Cell(25,17,"(Two Thousand and Five Hundred Only)",0, 'L', 1);

                $pdf->SetXY(45,240);
                $pdf->Cell(25,17,"36 , Second Street, Balaji nagar, Royapettah, Chennai - 14",0, 'L', 1);
                $pdf->SetXY(45,247);
                $pdf->Cell(25,14,"Email: syed@usoftsolutions.com Website: www.usscrm.com",0, 'L', 1);



	            //$pdf->Cell(35, 8, $row['part_number'], 1, 0, 'R', 1);
	            //$pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
	            //$pdf->Cell(2, 8, $row['unit'], 1, 0, 'R', 1);
	            //$pdf->Cell(22, 8, $selling_price, 1, 0, 'R', 1);
	            //$pdf->Cell(31, 8, $tsp, 1, 0, 'R', 1);
	            $pdf->Ln();


                $pdf->SetXY(10,5);
                $pdf->SetFont('Courier','',8);
              //  $pdf->Cell(10, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(10,10);
              //  $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(10,15);
              //  $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(10, 20);
             //   $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(10,25);
             //   $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

                /* Header */
                $pdf->SetFont('Courier','BU',15);
                $pdf->SetXY(94, 48);
                $pdf->Cell(21, 20, "INVOICE", 0, 0, 'C');
                $pdf->Ln(20);

				/*$billingAddressFlat = '';
				$billingAddressStreet = '';
				$billingAddressTown = '';
				$billingAddressState = '';
				$billingAddressCountry = '';

				/*if ($row['shipping_address1'] != ''
					|| $row['shipping_address2'] != ''
					|| $row['shipping_address_city'] != ''
					|| $row['shipping_address_state'] != ''
					|| $row['shipping_address_country'] != '') {
						//Delivery Address Fields in Order
						$deliveryAddressFlat 	= $row['shipping_address1'];
						$deliveryAddressStreet 	= $row['shipping_address2'];
						$deliveryAddressTown 	= $row['shipping_address_city'];
						$deliveryAddressState 	= $row['shipping_address_state'];
						$deliveryAddressCountry = $row['shipping_address_country'];
						$deliveryCompanyName 	= $row['shipping_first_name'];
				} else {
					//Delivery Address Fields in client
					$deliveryAddressFlat 	= $row['address_flat'];
					$deliveryAddressStreet 	= $row['address_street'];
					$deliveryAddressTown 	= $row['address_town'];
					$deliveryAddressState 	= $row['address_state'];
					$deliveryAddressCountry = $row['address_country'];
					$deliveryCompanyName 	= $row['company_name'];
				}*/

                /*$pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(95,8,"BILL TO",1,0, 'L', 1);
                $pdf->Cell(95,8,"DELIVERY TO",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Courier','B',10.5);

                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(95, 8, $row['company_name'],'LR', 0, 'L', 1);
            	$pdf->Cell(95, 8, $deliveryCompanyName , 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',9);
            	$pdf->Cell(95, 5, $row['billing_address_flat'], 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, $deliveryAddressFlat, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',9);
	            $pdf->Cell(95, 5, $row['billing_address_street'], 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, $deliveryAddressStreet, 'LR', 0, 'L', 1);
                $pdf->Ln();
	        	$pdf->Cell(95, 5, $row['billing_address_town'], 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, $deliveryAddressTown, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',9);
	            $pdf->Cell(95, 5, $row['billing_address_country'] .' - '. $row['billing_address_state'], 'BLR', 0, 'L', 1);
                $pdf->SetFont('Courier','B',9);
	            $pdf->Cell(95, 5, $deliveryAddressCountry .' - '. $deliveryAddressState, 'BLR', 0, 'L', 1);
                $pdf->Ln();

                $pdf->Ln(10);
                /*
                $billingAddressFields = $row['company_name'] . $pdf->Ln(1) . $row['company_name'] ;
                $pdf->SetXY(115,95);
                $pdf->drawTextBox($billingAddressFields, 90, 55, 'L', 'T', 1);
                $pdf->drawTextBox($deliveryAddressCountry, 90, 55, 'L', 'T', 1);
                $pdf->Ln(55);
                */


				//======================================
				/*$quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);

                $date = $fn->getCPDate($row['creation_date'], 'd-m-Y');

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(95,8,"QUOTE CODE",1,0, 'L', 1);
                $pdf->Cell(95,8,"DATE",1,0, 'L', 1);
                //$pdf->Cell(38,8,"TIME",1,0, 'L', 1);
                //$pdf->Cell(38,8,"LOCATION",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(95, 8, $quoteCode, 1, 0, 'L', 1);
	            $pdf->Cell(95, 8, $date, 1, 0, 'L', 1);

                $pdf->Ln(18);
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(20,8,"ITEM NO",1,0, 'L', 1);
                $pdf->Cell(23,8,"ITEM CODE",1,0, 'L', 1);
                $pdf->Cell(130,8,"NAME OF THE ITEM",1,0, 'L', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'L', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'L', 1);
                $pdf->Ln();*/
	       }


                /* List of order items header */

            //===================================MAIN TABLE============================= //
           /* $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 8, $lineItemNumber, 1, 0, 'L', 1);
            $pdf->Cell(23, 8, $row['item_code'], 1, 0, 'L', 1);
            $pdf->Cell(130, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;
            //$product_group_title = $row['product_group_title'];

			$quote_code = $row['quote_code'];*/
        }

        /*$pdf->Ln(15);
        $pdf->SetFont('Courier','B',9);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(100,8,"GOODS RECEIVED IN GOOD ORDER & CONDITION",0,0, 'L', 1);
        $pdf->Cell(90,8,$cpCfg['cp.siteTitle'],0,0, 'R', 1);
        $pdf->Ln();

        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(80,8,"CUSTOMER AUTHORISED SIGNATURE & STAMP",0,0, 'L', 1);

        $pdf->Cell('',8,"AUTHORISED SIGNATORY & STAMP",0,0, 'R', 1);
        $pdf->Ln(15);
        $pdf->SetFont('Courier','B',11);

        /* Creation of media record of the invoice */
        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        //$pdf->Output($outputFileName , "F");
				$pdf->Output();

	}

}
