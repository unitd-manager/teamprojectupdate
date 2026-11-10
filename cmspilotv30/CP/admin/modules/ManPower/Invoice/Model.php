<?
class CP_Admin_Modules_ManPower_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
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

        if ($cpCfg['m.manPower.invoice.hasMultiBranches'] == 1){
            $joinTbls .= "LEFT JOIN branch b ON(p.branch_id = b.branch_id)";
            $joinFlds .= ",b.title AS branch_name";
        }

        if ($cpCfg['m.manPower.invoice.hasMultipleCompanyAddress'] == 1) {
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
                ,a.agent_id
                ,CONCAT_WS(' ', a.first_name, a.last_name) AS agent_name
                ,CONCAT_WS(' ', ca.first_name, ca.last_name) AS candidate_name
	            ,gc.name AS address_country
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
            LEFT JOIN (project p)    ON (p.project_id = i.project_id)
            LEFT JOIN (`order` o)    ON (o.order_id = i.order_id)
            LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id)
            LEFT JOIN (company c)    ON (c.company_id = o.company_id)
            LEFT JOIN (project_candidate pc)    ON (pc.project_id = i.project_id)
            LEFT JOIN (candidate ca)    ON (ca.candidate_id = pc.candidate_id)
            LEFT JOIN (agent a)    ON (a.agent_id = ca.agent_id)
            LEFT JOIN (geo_country gc) ON (c.address_country_code = gc.country_code)
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
                                        c.company_name   LIKE '%{$tv['keyword']}%' OR
                                        cont.name LIKE '%{$tv['keyword']}%'
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

            if ($cpCfg['m.manPower.invoice.hasAutoAffix'] == 0){
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
        $fa = $fn->addToFieldsArray($fa, 'invoice_to');
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
    function getGenerateInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getGenerateInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $orderRowItem               = $fn->getPostParam('orderRowItem', array());
        $client_invoice             = $fn->getPostParam('client_invoice', array());
        $invoice_Title              = $fn->getPostParam('invoice_Title');
        $hourly_Rate                = $fn->getPostParam('hourly_Rate');
        $invoice_amount             = $fn->getPostParam('invoice_Amount');
        $invoice_Amount_Candidate   = $fn->getPostParam('invoice_Amount_Candidate');
        $start_date                 = $fn->getPostParam('invoice_start_date');
        $end_date                   = $fn->getPostParam('invoice_end_date');
        $invoice_terms              = $fn->getPostParam('client_invoice_terms');
        $invoice_notes              = $fn->getPostParam('client_invoice_notes');
        $hrs_Client                 = $fn->getPostParam('hrs_Client');
        $order_id                   = $fn->getReqParam('order_id');
        $hourly_Rate_client         = $fn->getReqParam('hourly_Rate_client');
        $hourly_Rate_candidate      = $fn->getReqParam('hourly_Rate_candidate');

        if (in_array('Client', $client_invoice)) {

            //To update invoice code
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

            $fa = array();
            $fa['invoice_code']             = $invoice_code;
            $fa['invoice_amount']           = $invoice_amount;
            $fa['invoice_date']             = $fn->getCurrentDate();
            $fa['invoice_terms']            = $invoice_terms;
            $fa['order_id']                 = $order_id;
            $fa['no_of_hours']              = $hrs_Client;
            $fa['status']                   = 'Due';
            $fa['staff_id']                 = $_SESSION['staff_id'];
            $fa['creation_date']            = date("Y-m-d H:i:s");
            $fa['created_by']               = $fn->getSessionParam('userName');
            $fa['invoice_type']             = 'Client';
            $fa['notes']                    = $invoice_notes;
            $fa['client_hourly_rate']       = $hourly_Rate_client;
            $fa['candidate_hourly_rate']    = $hourly_Rate_candidate;
            $fa['start_date']               = $start_date;
            $fa['end_date']                 = $end_date;

            /*$start_date_formatted = explode('-', $start_date);
            $end_date_formatted   = explode('-', $end_date);

            if (count($start_date_formatted) == 3){
                $fa['start_date'] = $start_date_formatted[2] . '-' . $start_date_formatted[0] . '-' . $start_date_formatted[1];
                $fa['end_date']   = $end_date_formatted[2] . '-' . $end_date_formatted[0] . '-' . $end_date_formatted[1];
            } else {
                $fa['start_date'] = '';
                $fa['end_date']   = '';
            }*/

            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
            $resultSQL          = $db->sql_query($insertInvoiceSQL);
            $invoice_id         = $db->sql_nextid();

            $SQLUpdate = "UPDATE `order` SET order_status = 'Due' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }


        if (in_array('Candidate', $client_invoice)) {
            $this->getGenerateInvoiceFormCandidateSubmit();
        }

        if (in_array('Referral', $client_invoice)) {
            $this->getGenerateInvoiceFormReferralSubmit();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateInvoiceFormCandidateSubmit() {

        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $orderRowItem               = $fn->getPostParam('orderRowItem', array());
        $invoice_Title              = $fn->getPostParam('invoice_Title');
        $hourly_Rate                = $fn->getPostParam('hourly_Rate');
        $invoice_amount             = $fn->getPostParam('invoice_Amount');
        $invoice_Amount_Candidate   = $fn->getPostParam('invoice_Amount_Candidate');
        $start_date                 = $fn->getPostParam('invoice_start_date');
        $end_date                   = $fn->getPostParam('invoice_end_date');
        $invoice_terms              = $fn->getPostParam('candidate_invoice_terms');
        $invoice_notes              = $fn->getPostParam('candidate_invoice_notes');
        $hrs_Client                 = $fn->getPostParam('hrs_Client');
        $fed                        = $fn->getPostParam('fed');
        $soc                        = $fn->getPostParam('soc');
        $med                        = $fn->getPostParam('med');
        $state                      = $fn->getPostParam('state');
        //$FUTA                       = $fn->getPostParam('FUTA');
        //$SUTA                       = $fn->getPostParam('SUTA');
        $deductions                 = $fn->getPostParam('deductions');
        $net                        = $fn->getPostParam('net');
        $order_id                   = $fn->getReqParam('order_id');
        $order_type                 = $fn->getReqParam('order_type');
        $hourly_Rate_client         = $fn->getReqParam('hourly_Rate_client');
        $hourly_Rate_candidate      = $fn->getReqParam('hourly_Rate_candidate');

        //To update invoice code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa1 = array();
        $fa1['invoice_code']          = $invoice_code;
        $fa1['invoice_amount']        = $invoice_Amount_Candidate;
        $fa1['invoice_date']          = $fn->getCurrentDate();
        $fa1['invoice_terms']         = $invoice_terms;
        $fa1['order_id']              = $order_id;
        $fa1['no_of_hours']           = $hrs_Client;
        $fa1['status']                = 'Due';
        $fa1['staff_id']              = $_SESSION['staff_id'];
        $fa1['creation_date']         = date("Y-m-d H:i:s");
        $fa1['created_by']            = $fn->getSessionParam('userName');
        $fa1['invoice_type']          = 'Candidate';
        $fa1['notes']                 = $invoice_notes;
        $fa1['client_hourly_rate']    = $hourly_Rate_client;
        $fa1['candidate_hourly_rate'] = $hourly_Rate_candidate;

        /*$start_date_formatted = explode('-', $start_date);
        $end_date_formatted   = explode('-', $end_date);
        if (count($start_date_formatted) == 3){
            $fa1['start_date'] = $start_date_formatted[2] . '-' . $start_date_formatted[0] . '-' . $start_date_formatted[1];
            $fa1['end_date']   = $end_date_formatted[2] . '-' . $end_date_formatted[0] . '-' . $end_date_formatted[1];
        } else {
            $fa1['start_date'] = '';
            $fa1['end_date']   = '';
        }*/

        $fa1['start_date']               = $start_date;
        $fa1['end_date']                 = $end_date;

        if ($order_type == 'Full Time'){
            $fa1['fed']          = $fed;
            $fa1['soc']          = $soc;
            $fa1['med']          = $med;
            $fa1['state']        = $state;
            //$fa1['FUTA']         = $FUTA;
            //$fa1['SUTA']         = $SUTA;
            $fa1['deductions']   = $deductions;
        }

        $insertInvoiceCandidateSQL   = $dbUtil->getInsertSQLStringFromArray($fa1, 'invoice');
        $resultSQL                   = $db->sql_query($insertInvoiceCandidateSQL);
        $invoice_id                  = $db->sql_nextid();

        $SQLUpdate = "UPDATE `order` SET order_status = 'Due' WHERE order_id = {$order_id}";
        $resultUpdate = $db->sql_query($SQLUpdate);

    }

    /**
     *
     */
    function getGenerateInvoiceFormReferralSubmit() {

        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $orderRowItem               = $fn->getPostParam('orderRowItem', array());
        $invoice_Title              = $fn->getPostParam('invoice_Title');
        $hourly_Rate                = $fn->getPostParam('hourly_Rate');
        $invoice_amount             = $fn->getPostParam('invoice_Amount');
        $invoice_Amount_Candidate   = $fn->getPostParam('invoice_Amount_Candidate');
        $start_date                 = $fn->getPostParam('invoice_start_date');
        $end_date                   = $fn->getPostParam('invoice_end_date');
        $invoice_terms              = $fn->getPostParam('referral_invoice_terms');
        $invoice_notes              = $fn->getPostParam('referral_invoice_notes');
        $hrs_Client                 = $fn->getPostParam('hrs_Client');
        $fed                        = $fn->getPostParam('fed');
        $soc                        = $fn->getPostParam('soc');
        $med                        = $fn->getPostParam('med');
        $state                      = $fn->getPostParam('state');
        $net                        = $fn->getPostParam('net');
        $commission_percentage      = $fn->getPostParam('commission_percentage_value');
        $commission_amount          = $fn->getPostParam('commission_amount');
        $order_id                   = $fn->getReqParam('order_id');
        $order_type                 = $fn->getReqParam('order_type');
        $hourly_Rate_client         = $fn->getReqParam('hourly_Rate_client');
        $hourly_Rate_candidate      = $fn->getReqParam('hourly_Rate_candidate');

        //$commission_amount = (($invoice_amount - $invoice_Amount_Candidate) * $commission_percentage/100);
        //To update invoice code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa2 = array();
        $fa2['invoice_code']          = $invoice_code;
        $fa2['invoice_amount']        = $commission_amount;
        $fa2['invoice_date']          = $fn->getCurrentDate();
        $fa2['invoice_terms']         = $invoice_terms;
        $fa2['order_id']              = $order_id;
        $fa2['no_of_hours']           = $hrs_Client;
        $fa2['status']                = 'Due';
        $fa2['staff_id']              = $_SESSION['staff_id'];
        $fa2['creation_date']         = date("Y-m-d H:i:s");
        $fa2['created_by']            = $fn->getSessionParam('userName');
        $fa2['invoice_type']          = 'Referral';
        $fa2['notes']                 = $invoice_notes;
        $fa2['client_hourly_rate']    = $hourly_Rate_client;
        $fa2['candidate_hourly_rate'] = $hourly_Rate_candidate;
        $fa2['commission_percentage'] = $commission_percentage;

        /*$start_date_formatted = explode('-', $start_date);
        $end_date_formatted   = explode('-', $end_date);
        if (count($start_date_formatted) == 3){
            $fa2['start_date'] = $start_date_formatted[2] . '-' . $start_date_formatted[0] . '-' . $start_date_formatted[1];
            $fa2['end_date']   = $end_date_formatted[2] . '-' . $end_date_formatted[0] . '-' . $end_date_formatted[1];
        } else {
            $fa2['start_date'] = '';
            $fa2['end_date']   = '';
        }*/

        $fa2['start_date']               = $start_date;
        $fa2['end_date']                 = $end_date;

        $insertInvoiceReferralSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'invoice');
        $resultSQL                = $db->sql_query($insertInvoiceReferralSQL);
        $invoice_id               = $db->sql_nextid();

        $SQLUpdate = "UPDATE `order` SET order_status = 'Due' WHERE order_id = {$order_id}";
        $resultUpdate = $db->sql_query($SQLUpdate);

    }


    /**
     *
     */
    function getGenerateEmpTaxFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $FUTA            = $fn->getPostParam('FUTA');
        $SUTA            = $fn->getPostParam('SUTA');
        $invoice_id      = $fn->getPostParam('invoice_id');
        $order_id        = $fn->getPostParam('order_id');
        $tax_no_of_hours = $fn->getPostParam('tax_no_of_hours');
        $invoice_start_date = $fn->getPostParam('invoice_start_date');
        $invoice_end_date   = $fn->getPostParam('invoice_end_date');

        //$date     = date("Y-m-d", strtotime($date));
        //$due_date = date("Y-m-d", strtotime($due_date));

        $UCS_fed     = 0;
        $UCS_Tax     = 0;
        $UCS_Cost    = 0;

        $invoiceRow = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $orderRow   = $fn->getRecordRowByID('order', 'order_id', $invoiceRow['order_id']);

        $UCS_fed  = $invoiceRow['fed'] + $invoiceRow['soc'] * 2 + $invoiceRow['med'] * 2;
        $UCS_Tax  = $UCS_fed + $invoiceRow['state'] + $FUTA + $SUTA;
        $UCS_Cost = $invoiceRow['soc'] + $invoiceRow['med'] + $FUTA + $SUTA;

        $UCS_fed  = number_format($UCS_fed,2);
        $UCS_Tax  = number_format($UCS_Tax,2);
        $UCS_Cost = number_format($UCS_Cost,2);

        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa4 = array();
        $fa4['invoice_code']      = $invoice_code;
        //$fa4['invoice_amount']  = $invoiceRow['invoice_amount'];
        $fa4['invoice_amount']    = $UCS_fed;
        $fa4['invoice_date']      = $fn->getCurrentDate();
        $fa4['order_id']          = $order_id;
        $fa4['status']            = 'Due';
        $fa4['staff_id']          = $_SESSION['staff_id'];
        $fa4['creation_date']     = date("Y-m-d H:i:s");
        $fa4['created_by']        = $fn->getSessionParam('userName');
        $fa4['invoice_type']      = 'Employer Tax';
        $fa4['start_date']        = $invoice_start_date;
        $fa4['end_date']          = $invoice_end_date;
        $fa4['FUTA']              = $FUTA;
        $fa4['SUTA']              = $SUTA;
        $fa4['fed']               = $invoiceRow['fed'];
        $fa4['soc']               = $invoiceRow['soc'];
        $fa4['med']               = $invoiceRow['med'];
        $fa4['state']             = $invoiceRow['state'];
        $fa4['ucs_fed']           = $UCS_fed;
        $fa4['ucs_tax']           = $UCS_Tax;
        $fa4['ucs_cost']          = $UCS_Cost;
        $fa4['source_invoice_id'] = $invoice_id;
        $fa4['no_of_hours']       = $tax_no_of_hours;

        $insertInvoicetaxSQL = $dbUtil->getInsertSQLStringFromArray($fa4, 'invoice');
        $resultSQL           = $db->sql_query($insertInvoicetaxSQL);
        $invoice_id          = $db->sql_nextid();

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getGenerateInvoiceFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('hrs_Client', 'Please Enter Chargeable Hours');
        $validate->validateData('client_invoice', 'Please select either Client or Candidate or Referral or Both');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $order_id   = $fn->getReqParam('order_id');
        $client_invoice = $fn->getPostParam('client_invoice', array());

        /*if (in_array('Client', $client_invoice)) {
            $sqlInvoice = "
            SELECT * FROM invoice
            WHERE ((start_date >= '{$start_date}' AND start_date <= '{$end_date}')
              OR (end_date >= '{$start_date}' AND end_date <= '{$end_date}'))
              AND order_id = {$order_id}
              AND invoice_type = 'Client'
              AND status != 'Cancelled'
            ";
            $resultInvoice = $db->sql_query($sqlInvoice);
            $numRows = $db->sql_numrows($resultInvoice);
            if($numRows > 0){
                $validate->errorArray['start_date']['name'] = 'start_date';
                $validate->errorArray['start_date']['msg']  = 'Client Invoice already created for this date range';
            }
        }

        if (in_array('Candidate', $client_invoice)) {
            $sqlInvoice1 = "
            SELECT * FROM invoice
            WHERE ((start_date >= '{$start_date}' AND start_date <= '{$end_date}')
              OR (end_date >= '{$start_date}' AND end_date <= '{$end_date}'))
              AND order_id = {$order_id}
              AND invoice_type = 'Candidate'
              AND status != 'Cancelled'
            ";
            $resultInvoice1 = $db->sql_query($sqlInvoice1);
            $numRows1 = $db->sql_numrows($resultInvoice1);
            if($numRows1 > 0){
                $validate->errorArray['start_date']['name'] = 'start_date';
                $validate->errorArray['start_date']['msg']  = 'Candidate Invoice already created for this date range';
            }
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
        $fa['invoice_to']      = 'Agent';

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
               /* $pdf->SetFont('Arial','B',10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(155,8,"Description1",1 ,0, 'L', 1);
                $pdf->Cell(30,8,"Amount (SG$)",1 ,0, 'R', 1);
                $pdf->Ln();*/

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
            $pdf->SetX(10);
            $pdf->Cell(155,10, " " . $row['item_title'] , 1);
			$amount = number_format($row['amount']);

            if ($amount == 0){
                $amount = '';
            }

            /*$pdf->Cell(30,10, " " . $amount, 1,  0, 'R');
            $total = $row['total'];
			$total = number_format($total);
            $pdf->Ln();
            $invoice_terms = $row['invoice_terms'];
            $notes = $row['notes'];
            $count++;*/

            $pdf->SetFont('Arial','',10);
			$amount = number_format($row['invoice_items_amount']);

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

        $record_id     = $fn->getReqParam('id');
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
			  ,i.invoice_to
			  ,p.project_id
			  ,p.contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
			  ,cont.position as position
			  ,cont.salutation
              ,a.agent_id
              ,a.company_address_flat
              ,a.company_address_street
              ,a.company_address_town
              ,a.company_address_state
              ,a.company_address_country
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS agent_name
			  ,c.company_name
			  ,c.phone
              ,c.address_flat    AS comp_mul_address_flat
              ,c.address_street  AS comp_mul_address_street
              ,c.address_state   AS comp_mul_address_state
              ,c.address_country_code AS comp_mul_address_country
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
        LEFT JOIN (project_candidate pc)    ON (pc.project_id = i.project_id)
        LEFT JOIN (candidate can)    ON (can.candidate_id = pc.candidate_id)
        LEFT JOIN (agent a)    ON (a.agent_id = can.agent_id)
		JOIN invoice_items it    ON (i.invoice_id = it.invoice_id)
		WHERE i.invoice_id = {$record_id}
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
                if($row['invoice_to'] == 'Agent'){
                    $name = $row['agent_name'];
                } else {
                    $name = $row['company_name'];
                }
                $date = $fn->getCPDate($row['invoice_date'], 'd m Y');
                $code = 'Invoice # : '. $row['invoice_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);
                $pdf->Image('images/logo-print.jpg',10,5,45);

	            $pdf->SetXY(135,1);
	            $pdf->SetFillColor(255,255,255);
	            $pdf->SetFont('Arial','B',10);
	            $pdf->SetX(135);
	            $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
	            $pdf->SetFont('Arial','',8);
	            $pdf->Ln(5);
	            $pdf->SetX(135);
	            $pdf->Cell(50, 20, $cpCfg['cp.companyAddress1']);
	            $pdf->Ln(5);
	            $pdf->SetX(135);
	            $pdf->Cell(50, 20, $cpCfg['cp.companyAddress2']);
	            $pdf->Ln(5);
	            $pdf->SetX(135);
	            $pdf->Cell(50, 20, $cpCfg['cp.companyAddress3']);
	            $pdf->Ln(5);
	            $pdf->SetX(135);
	            $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
	            $pdf->Ln(18);


                // Invoice Date & Code
                $pdf->SetFont('Arial','B',9);
                $pdf->SetXY(165, 35);
                $pdf->Cell(50, 20, "$code" );
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "Date :" . $date);

	            $pdf->SetFont('Arial','BU',10);
                $pdf->SetXY(105, 30);
	            $pdf->Cell(50, 20, "INVOICE" );
	            $pdf->Ln(10);

                $pdf->SetFont('Arial','',10);

                // To Address
                $pdf->SetXY(10, 40);
				$pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 45, 85, 50, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(10);
                $pdf->Cell(20, 20, "Invoice To :");
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $name);
                $pdf->Ln(5);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(15,20,"Attn : " . $row['salutation']);
                $pdf->Cell(40,20,$row['contact_name']);
                $pdf->Ln(5);

                if($row['invoice_to'] == 'Agent'){
    				if ($row['company_address_flat']){
                        $pdf->Cell(50, 20, $row['company_address_flat']);
                        $pdf->Ln(5);
                    }
                    if ($row['company_address_street']){
                        $pdf->Cell(50, 20, $row['company_address_street']);
                        $pdf->Ln(5);
                    }
                    if ($row['company_address_town']){
                        $pdf->Cell(50, 20, $row['company_address_town']);
                        $pdf->Ln(5);
                    }
                    if ($row['company_address_state']){
                        $pdf->Cell(50, 20, $row['company_address_state']);
                        $pdf->Ln(5);
                    }
                    if ($row['company_address_country']){
                        $pdf->Cell(50, 20, $row['company_address_country']);
                        $pdf->Ln(5);
                    }
                } else {
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
                }

                $pdf->Ln(30);

                // Table Heading
                $pdf->SetFont('Arial','B',10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $pdf->SetFillColor(142,182,212);
                $amountLbl = 'Amount (' . $row['inv_currency'] . ')';
                //header
                $col[] = array('text' => 'Description', 'width' => '165', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '10', 'font_style' => 'B', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
                $col[] = array('text' => $amountLbl, 'width' => '20', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '10', 'font_style' => 'B', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
                $columns[] = $col;
                $total = $row['invoice_item_sum'];
                $total = number_format($total, 2);
            }
             //Table Content
            $pdf->SetFont('Arial','',10);
			$amount = number_format($row['invoice_items_amount'], 2);

            /*if ($amount == 0){
                $amount = '';
            }*/

            $col = array();
            $col[] = array('text' => $row['invoice_items_description']. $count, 'width' => '165', 'height' => '8', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $col[] = array('text' => $amount, 'width' => '20', 'height' => '8', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $columns[] = $col;

            $invoice_terms = $row['invoice_terms'];
            $notes = $row['notes'];
            $count++;
        }

        if ($numRows == 2){
            $col = array();
            $col[] = array('text' => '', 'width' => '165', 'height' => '8', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $col[] = array('text' => '', 'width' => '20', 'height' => '8', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $columns[] = $col;
		}

        if ($numRows == 1){
            $col = array();
            $col[] = array('text' => '', 'width' => '165', 'height' => '8', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $col[] = array('text' => '', 'width' => '20', 'height' => '8', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $columns[] = $col;
            $col = array();
            $col[] = array('text' => '', 'width' => '165', 'height' => '8', 'align' => 'L', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $col[] = array('text' => '', 'width' => '20', 'height' => '8', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '9', 'font_style' => '', 'fillcolor' => '224,235,255', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
            $columns[] = $col;
		}

        $col = array();
        $col[] = array('text' => 'Total', 'width' => '165', 'height' => '5', 'align' => 'C', 'font_name' => 'Arial', 'font_size' => '10', 'font_style' => 'B', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
        $col[] = array('text' => $total, 'width' => '20', 'height' => '5', 'align' => 'R', 'font_name' => 'Arial', 'font_size' => '10', 'font_style' => 'B', 'fillcolor' => '135,206,250', 'textcolor' => '0,0,0', 'drawcolor' => '0,0,0', 'linewidth' => '0.001', 'linearea' => 'LTBR');
        $columns[] = $col;

        $pdf->WriteTable($columns);
        $printFooter = $cpCfg['printFooter'];

        //Final Values
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',9);
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
    function getManPowerInvoiceManPowerInvoiceItemSQL($id) {

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
}
