<?
class CP_Admin_Widgets_Pms_PaymentOutstandingReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DISTINCT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,o.order_id 
              ,CONCAT_WS(' ', p.first_name, p.last_name) as parent_name
              ,p.mobile
              ,o.year_of_enrollment
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

        $site_id        = $fn->getSessionParam('cp_site_id');
        $year           = $fn->getReqParam('year');
        $payment_mode   = $fn->getReqParam('payment_mode');
        $ageing_value   = $fn->getReqParam('ageing_value');

        if ($site_id) {
            $searchVar->sqlSearchVar[] = "c.site_id = {$fn->getSessionParam('cp_site_id')}";
        }

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }

        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$payment_mode}'";
        
        if ($ageing_value != '') {
            $startDate  = date('Y-m-d',mktime (0,0,0,date("m")-$ageing_value,date("d"), date("Y"))); // 2013-10-19
            $searchVar->sqlSearchVar[] = "inv.invoice_date <= '{$startDate}'";
            $searchVar->sqlSearchVar[] = "(inv.status = 'Due' OR inv.status = 'Partial Payment')";
            
            $start_date_year = date('Y',mktime (0,0,0,date("m")-$ageing_value,date("d"), date("Y"))); // Finding year from ageing value selected
            $current_year    = date('Y');
            if ($start_date_year != $current_year) {
                $searchVar->sqlSearchVar[] = "(o.year_of_enrollment = '{$start_date_year}' OR o.year_of_enrollment = '{$current_year}' )";
            } else {
                $searchVar->sqlSearchVar[] = "o.year_of_enrollment = '{$year}'";
            }
        } else {
            $searchVar->sqlSearchVar[] = "o.year_of_enrollment = '{$year}'";
        }
        
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_paymentOutstandingReport');

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

        $file_name = "Financial-Report-of-Student_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name of Student');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Jan');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Feb');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mar');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Apr');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'May');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Jun');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Jul');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Aug');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sep');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Oct');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Nov');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dec');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Due');
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
        $payment_mode   = $fn->getReqParam('payment_mode');
        $ageing_value   = $fn->getReqParam('ageing_value');
        $site_id        = $fn->getSessionParam('cp_site_id');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }
        
        $appendSql = "";
        if ($ageing_value != '') {
            $startDate  = date('Y-m-d',mktime (0,0,0,date("m")-$ageing_value,date("d"), date("Y"))); // 2013-10-19
            $appendSql .= "AND inv.invoice_date <= '{$startDate}'";
            $appendSql .= "AND (inv.status = 'Due' OR inv.status = 'Partial Payment')";

            $start_date_year = date('Y',mktime (0,0,0,date("m")-$ageing_value,date("d"), date("Y"))); // Finding year from ageing value selected
            $current_year    = date('Y');
            if ($start_date_year != $current_year) {
                $appendSql .= "AND (o.year_of_enrollment = '{$start_date_year}' OR o.year_of_enrollment = '{$current_year}' )";
            } else {
                $appendSql .= "AND o.year_of_enrollment = '{$year}'";
            }
        } else {
            $appendSql .= "AND o.year_of_enrollment = '{$year}'";
        }

        if ($site_id) {
            $appendSql .= " AND c.site_id = {$site_id}";
        }

       	$SQL = "
     	SELECT DISTINCT c.contact_id
	          ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
	          ,o.order_id 
	          ,CONCAT_WS(' ', p.first_name, p.last_name) as parent_name
	          ,p.mobile
	          ,o.year_of_enrollment
        FROM contact c
        LEFT JOIN (invoice inv)         ON (c.contact_id  = inv.contact_id)
        LEFT JOIN (`order` o)           ON (inv.order_id  = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id  = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id  = p.parent_id)
        WHERE c.status = 'Active'
          AND p.mode_of_payment = '{$payment_mode}'
	      AND o.year_of_enrollment = '{$year}'          
          {$appendSql}
		ORDER BY parent_name ASC, c.contact_id ASC
 		";	
        $result = $db->sql_query($SQL);      
        $total_outstanding_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $balance_amount = $this->view->getOutstandingBalanceAmount($row['order_id']);
            $total_outstanding_amount += $balance_amount;

            if ($balance_amount) {
                $colc = 0;
                $rowc++;
                
                $brachRec = $fn->getRecordRowById('site', 'site_id', $site_id);
    
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $brachRec['title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_mode);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['parent_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 1, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 2, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 3, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 4, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 5, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 6, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 7, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 8, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 9, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 10, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 11, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 12, 'excel'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_amount);
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
            $balance_amount = $this->view->getOutstandingBalanceAmount($row['order_id']);
            if ($balance_amount) {
                $serial_no += 1;
                $total_outstanding_amount += $balance_amount;
            }
        }
        
        $row = array(
                     'total_students'           => $serial_no
                    ,'total_outstanding_amount' => $total_outstanding_amount
                    );

        return $row;
    }
}