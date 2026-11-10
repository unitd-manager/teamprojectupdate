<?
class CP_Admin_Widgets_EnterpriseIms_MonthlyFinancialReport_Model extends CP_Common_Lib_WidgetModelAbstract
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
              ,inv.status
              ,(inv.invoice_amount - inv.discount_amount) AS amount_payable
        FROM contact c
        LEFT JOIN (invoice inv)         ON (c.contact_id  = inv.contact_id)
        LEFT JOIN (`order` o)           ON (inv.order_id  = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $searchVar->sqlSearchVar[] = "c.site_id = {$fn->getSessionParam('cp_site_id')}";

        $year           = $fn->getReqParam('year');
        $month          = $fn->getReqParam('month');
        $payment_mode   = $fn->getReqParam('payment_mode');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }

        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$payment_mode}'";
        $searchVar->sqlSearchVar[] = "o.year_of_enrollment = '{$year}'";
        $searchVar->sqlSearchVar[] = "inv.invoice_month = '{$month}'";
        //$searchVar->sqlSearchVar[] = "inv.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "inv.status = 'Due' OR inv.status = 'Partial Payment'";
        
        $searchVar->sortOrder = 'parent_name ASC, c.contact_id ASC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_monthlyFinancialReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
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
        
        $year           = $fn->getReqParam('year');
        $month          = $fn->getReqParam('month');
        $payment_mode   = $fn->getReqParam('payment_mode');
        $site_id        = $fn->getSessionParam('cp_site_id');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }
        
       	$SQL = "
        SELECT DISTINCT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,CONCAT_WS(' ', p.first_name, p.last_name) as parent_name
              ,p.mobile
              ,inv.invoice_code
              ,inv.status
              ,(inv.invoice_amount - inv.discount_amount) AS amount_payable
        FROM contact c
        LEFT JOIN (invoice inv)         ON (c.contact_id  = inv.contact_id)
        LEFT JOIN (`order` o)           ON (inv.order_id  = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        WHERE c.status = 'Active'
          AND p.mode_of_payment = '{$payment_mode}'
	      AND o.year_of_enrollment = '{$year}'
	      AND inv.invoice_month = '{$month}'
	      AND (inv.status = 'Due' OR inv.status = 'Partial Payment')
          AND c.site_id = {$site_id}
		ORDER BY parent_name ASC, c.contact_id ASC
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

                $colc = 0;
                $rowc++;
                
                $brachRec = $fn->getRecordRowById('site', 'site_id', $site_id);
    
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $brachRec['title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_mode);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $year);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getMonthVal($month));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
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