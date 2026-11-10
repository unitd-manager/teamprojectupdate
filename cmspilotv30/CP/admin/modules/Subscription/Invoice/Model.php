<?
class CP_Admin_Modules_Subscription_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
                
        $SQL = "
        SELECT i.*
        FROM invoice i
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

        $invoice_id    = $fn->getReqParam('invoice_id');
        $record_id     = $fn->getReqParam('record_id');
        $company_id    = $fn->getReqParam('company_id');
        $status        = $fn->getReqParam('status');
        $date1 = $fn->getReqParam('date1');
        $date2 = $fn->getReqParam('date2');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$tv['record_id']}'";
        } else if ($invoice_id != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$invoice_id}'";
        } else {
    
            /*if ($status == "" && $tv['searchDone'] == 0 && $tv['record_id'] == '') {
                $status = 'Due';
            }*/
            
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
            
            //------------------------------------------------------------------------//    
            /*
            $searchVar->sortOrder = "
            CASE
            WHEN (i.status = 'Late' ) THEN 1
            WHEN (i.invoice_due_date != '' AND i.invoice_due_date IS NOT NULL AND i.invoice_due_date != '0000-00-00' ) THEN 2
            ELSE 3
            END, i.invoice_due_date
            ";
            */
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

        //-----------------------------------------------------------------------//
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
                $fa['invoice_date']     = $current_year . '-' . $month . '-' . $rowDate['value'];
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
                $modObj = getCPModuleObj('subscription_order');
                $modObj->model->getGenerateInvoiceForEntMedia($invoice_id); 
                */ 
            }
        }
        
        return $validate->getSuccessMessageXML();
    }
}
