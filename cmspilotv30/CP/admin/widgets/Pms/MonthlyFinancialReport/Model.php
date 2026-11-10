<?
class CP_Admin_Widgets_Pms_MonthlyFinancialReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DISTINCT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,CONCAT_WS(' ', p.first_name, p.last_name) as parent_name
              ,p.mobile
              ,inv.invoice_code
              ,c.status AS contact_status
              ,inv.status
              ,inv.invoice_code
              ,inv.invoice_month
              ,inv.invoice_date
              ,(inv.invoice_amount - inv.discount_amount) AS amount_payable
              ,s.title AS site_name
        FROM contact c
        LEFT JOIN (invoice inv)         ON (c.contact_id  = inv.contact_id)
        LEFT JOIN (`order` o)           ON (inv.order_id  = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id     = s.site_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        //$searchVar->sqlSearchVar[] = "c.site_id = {$fn->getSessionParam('cp_site_id')}";

        $year           = $fn->getReqParam('year');
        $from_month     = $fn->getReqParam('from_month');
        $to_month       = $fn->getReqParam('to_month');
        $payment_mode   = $fn->getReqParam('payment_mode');
        $site_id        = $fn->getReqParam('site_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $status         = $fn->getReqParam('status');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }

        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "(c.site_id = '{$site_id}')";
            }
        }

        if ($status != '' && $status == 'All') {
            $searchVar->sqlSearchVar[] = "(c.status = 'Active' OR c.status = 'Applied for Withdrawal')";
        } else if ($status != '') {
            $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
        }
        
        $searchVar->sqlSearchVar[] = "(c.status != 'Graduated' OR c.status = 'Withdraw')";

        if ($payment_mode == 'All') {
        } else {
            $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$payment_mode}'";
        }

        $searchVar->sqlSearchVar[] = "p.parent_id != ''";

        //$searchVar->sqlSearchVar[] = "o.year_of_enrollment = '{$year}'";
        //$searchVar->sqlSearchVar[] = "inv.invoice_month BETWEEN '{$from_month}' AND '{$to_month}'";
        $searchVar->sqlSearchVar[] = "inv.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        $searchVar->sqlSearchVar[] = "(inv.status = 'Due' OR inv.status = 'Partial Payment')";
        
        $searchVar->sortOrder = 'c.site_id ASC, parent_name ASC, c.contact_id ASC, inv.invoice_month ASC';
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_monthlyFinancialReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $year           = $fn->getReqParam('year');
        $from_month     = $fn->getReqParam('from_month');
        $to_month       = $fn->getReqParam('to_month');
        $payment_mode   = $fn->getReqParam('payment_mode');
        $site_id        = $fn->getReqParam('site_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $status         = $fn->getReqParam('status');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Monthly-Financial-Report_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Year');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name of Student');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
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
        
        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }
        
        $appendWhere = "";
        if ($site_id) {
            if(is_numeric($site_id)) {
                $appendWhere .= " AND c.site_id = '{$site_id}'";
            }
        }

        if ($status != '' && $status == 'All') {
            $appendWhere .= " AND (c.status = 'Active' OR c.status = 'Applied for Withdrawal')";
        } else if ($status != '') {
            $appendWhere .= " AND c.status = '{$status}'";
        }

        if ($payment_mode == 'All') {
        } else {
            $appendWhere .= " AND p.mode_of_payment = '{$payment_mode}'";
        }

       	$SQL = "
        SELECT DISTINCT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,CONCAT_WS(' ', p.first_name, p.last_name) as parent_name
              ,p.mobile
              ,inv.invoice_code
              ,c.status AS contact_status
              ,inv.status
              ,inv.invoice_code
              ,inv.invoice_month
              ,inv.invoice_date
              ,(inv.invoice_amount - inv.discount_amount) AS amount_payable
              ,s.title AS site_name
              ,p.mode_of_payment
        FROM contact c
        LEFT JOIN (invoice inv)         ON (c.contact_id  = inv.contact_id)
        LEFT JOIN (`order` o)           ON (inv.order_id  = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id     = s.site_id)
        WHERE (c.status = 'Active' OR c.status = 'Applied for Withdrawal')
          AND p.parent_id != ''
	      AND inv.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
	      AND (inv.status = 'Due' OR inv.status = 'Partial Payment')
          AND (c.status != 'Graduated' OR c.status = 'Withdraw')
          {$appendWhere}
		ORDER BY c.site_id ASC, parent_name ASC, c.contact_id ASC, inv.invoice_month ASC
 		";	
        $result = $db->sql_query($SQL);      
        $total_outstanding_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            if ($row['amount_payable'] > 0) {

                if ($row['status'] == 'Due' || $row['status'] == 'Partial Payment') {
                    $amount = $row['amount_payable'];
                    $total_outstanding_amount += $row['amount_payable'];
                } else {
                    $amount = $row['status'];
                }

                $invoice_year = substr($row['invoice_date'], 0, 4);

                $colc = 0;
                $rowc++;
                
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['site_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_year);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getMonthVal($row['invoice_month']));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_status']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);
            }
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Outstanding Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_outstanding_amount);

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getSqlForCount() {
        $serial_no = 0;
        $total_outstanding_amount = 0;

        foreach($this->dataArray as $row){
            if ($row['amount_payable'] > 0) {
                $serial_no += 1;
                if ($row['status'] == 'Due' || $row['status'] == 'Partial Payment') {
                    $amount = $row['amount_payable'];
                    $total_outstanding_amount += $row['amount_payable'];
                }
            }
        }
        
        $row = array(
                     'total_students'           => $serial_no
                    ,'total_outstanding_amount' => $total_outstanding_amount
                    );

        return $row;
    }
}