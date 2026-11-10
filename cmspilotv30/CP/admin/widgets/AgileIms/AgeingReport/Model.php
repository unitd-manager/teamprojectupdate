<?
class CP_Admin_Widgets_AgileIms_AgeingReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT i.invoice_id
        FROM invoice i
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        //$searchVar->sqlSearchVar[] = "c.status = 'Active'";
        //$searchVar->sqlSearchVar[] = "p.parent_id = 1";
        
        $searchVar->sortOrder = 'i.invoice_id DESC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'agileIms_ageingReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getPreviousOutstandingBalanceAmount($parent_id, $start_date){
        $db = Zend_Registry::get('db');
        
        $total_invoice_amount = 0;
        
        $sqlInvoice = "
        SELECT invoice_id, invoice_amount, discount_amount
        FROM invoice i
        LEFT JOIN (contact c)         ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)          ON (pc.parent_id = p.parent_id)
        WHERE p.parent_id = {$parent_id}
          AND i.invoice_date < '{$start_date}'
          AND i.status != 'Cancelled'
          AND i.status != 'Paid'
          AND c.status = 'Active'
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);

        while ($rowInvoice = $db->sql_fetchrow($resultInvoice)) {
            $balance_amount = $rowInvoice['invoice_amount'] - $rowInvoice['discount_amount'];
            $total_invoice_amount += $balance_amount;
            
            $sqlPayment = "
            SELECT SUM(r.amount) AS receipt_amount
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            WHERE r.receipt_status = 'Paid'
              AND irh.invoice_id = {$rowInvoice['invoice_id']}
              AND r.date < '{$start_date}'
            ";
            $resultPayment = $db->sql_query($sqlPayment);
            $rowPayment = $db->sql_fetchrow($resultPayment);
            
            $total_invoice_amount -= $rowPayment['receipt_amount'];
        }
        
        return $total_invoice_amount;
    }
   
    /**
     *
     */
    function getTotalOutstandingAmountSummary($parent_id, $start_date, $end_date){
        $db = Zend_Registry::get('db');
        
        $total_invoice_amount = 0;
        
        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
            $invoice_date_condition = "'{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = date('Y') . '-01-01';
            $invoice_date_condition = "'{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $invoice_date_condition = "'{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y') . '-01-01';
            $end_date   = date('Y-m-d');
            $invoice_date_condition = "'{$start_date}' AND '{$end_date}'";
        }

        $sqlInvoice = "
        SELECT SUM(invoice_amount) AS total_invoice_amount
              ,SUM(discount_amount) AS total_discount_amount
        FROM invoice i
        LEFT JOIN (contact c)         ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)          ON (pc.parent_id = p.parent_id)
        LEFT JOIN (`order` o)         ON (i.order_id   = o.order_id)
        WHERE p.parent_id = {$parent_id}
          AND i.invoice_date BETWEEN {$invoice_date_condition}
          AND i.status = 'Due'
          AND c.status = 'Active'
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        $rowInvoice = $db->sql_fetchrow($resultInvoice);
        
        $total_invoice_amount = $rowInvoice['total_invoice_amount'] - $rowInvoice['total_discount_amount'];
        
        return $total_invoice_amount;
    }
   
    /**
     *
     */
    function getPastBalanceAmount($parent_id, $end_date, $no_of_days){
        $db = Zend_Registry::get('db');
        
        $total_invoice_amount = 0;
        
        /*
        if ($no_of_days == 30 || $no_of_days == 60) {
            $sqlAppend = "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $sqlAppend = "< '{$start_date}'";
        }
        */
        
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
        SELECT SUM(invoice_amount) AS total_invoice_amount
              ,SUM(discount_amount) AS total_discount_amount
        FROM invoice i
        LEFT JOIN (contact c)         ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)          ON (pc.parent_id = p.parent_id)
        LEFT JOIN (`order` o)         ON (i.order_id   = o.order_id)
        WHERE p.parent_id = {$parent_id}
          AND i.status = 'Due'
          AND c.status = 'Active'
          AND i.invoice_date {$sqlAppend}
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        $rowInvoice = $db->sql_fetchrow($resultInvoice);
        
        $total_invoice_amount = $rowInvoice['total_invoice_amount'] - $rowInvoice['total_discount_amount'];
        
        return $total_invoice_amount;
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $parent_id  = $fn->getReqParam('parent_id');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "StatementOfAccount__" . date("d-m-Y") . ".xls";
        
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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'INVOICE #');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DATE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'BRANCH');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STUDENT NAME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MONTH');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'YEAR');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'INVOICE AMOUNT (SGD)');
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

        $current_date = date('Y-m-d');

        $sql = "
        SELECT i.*
              ,c.first_name AS student_name
              ,o.year_of_enrollment
              ,s.title AS branch_name
        FROM invoice i
        LEFT JOIN (contact c)         ON (i.contact_id = c.contact_id)
        LEFT JOIN (site s)            ON (i.site_id    = s.site_id)
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)          ON (pc.parent_id = p.parent_id)
        LEFT JOIN (`order` o)         ON (i.order_id   = o.order_id)
        WHERE (i.status = 'Due' OR i.status = 'Partial Payment')
          AND c.status = 'Active'
          AND p.parent_id = {$parent_id}
          AND i.invoice_date < '{$current_date}'
        ORDER BY i.invoice_date ASC
        ";
        $result = $db->sql_query($sql);

        $total_outstanding_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
           
            switch ($row['invoice_month']) {
                case 1: $prefix_month = 'Jan';
                break;
                case 2: $prefix_month = 'Feb';
                break;
                case 3: $prefix_month = 'Mar';
                break;
                case 4: $prefix_month = 'Apr';
                break;
                case 5: $prefix_month = 'May';
                break;
                case 6: $prefix_month = 'Jun';
                break;
                case 7: $prefix_month = 'Jul';
                break;
                case 8: $prefix_month = 'Aug';
                break;
                case 9: $prefix_month = 'Sep';
                break;
                case 10: $prefix_month = 'Oct';
                break;
                case 11: $prefix_month = 'Nov';
                break;
                case 12: $prefix_month = 'Dec';
                break;
            }

            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD/MM/YYYY');

            $amount_payable = $row['invoice_amount'] - $row['discount_amount'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $prefix_month);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['year_of_enrollment']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_payable);

            $total_outstanding_amount += $amount_payable;
        }

        $outstanding_amount = $total_outstanding_amount;

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL AMOUNT PAYABLE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $outstanding_amount);

        $colc = 0;
        $rowc++;

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STATEMENT SUMMARY');

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Past Due 1-30');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Past Due 31-60');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Past Due >60');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->getPastBalanceAmount($parent_id, $current_date, 30));
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->getPastBalanceAmount($parent_id, $current_date, 60));
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->getPastBalanceAmount($parent_id, $current_date, 61));
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $outstanding_amount);

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}