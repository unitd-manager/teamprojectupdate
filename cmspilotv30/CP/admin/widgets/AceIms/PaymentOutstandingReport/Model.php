<?
class CP_Admin_Widgets_AceIms_PaymentOutstandingReport_Model extends CP_Common_Lib_WidgetModelAbstract
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
        $searchVar->mainTableAlias = 'c';

        $year           = $fn->getReqParam('year');
        $payment_mode   = $fn->getReqParam('payment_mode');
        $ageing_value   = $fn->getReqParam('ageing_value');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($year == '') {
            $year = date('Y');
        }

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
        
        //$searchVar->groupBy = 'oi.record_id';
        $searchVar->sortOrder = 'c.registration_no ASC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_paymentOutstandingReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $year       = $fn->getReqParam('year');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "IncomeByStudent_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S/No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course');
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
        
        $sqlAppend = '';
        if($start_date != '' && $end_date != ''){
            $sqlAppend .= "AND o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        }

        if ($year == '') {
            $year = date('Y');

            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
            $sqlAppend .= "AND o.order_date BETWEEN '{$startYear}' AND '{$endYear}'";
        } else if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
            $sqlAppend .= "AND o.order_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        $SQL = "
        SELECT DISTINCT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,c.id_card_no
              ,o.order_id
              ,crse.title as course_title
        FROM contact c
        LEFT JOIN (invoice inv) ON (c.contact_id = inv.contact_id)
        LEFT JOIN (`order` o) ON (inv.order_id = o.order_id)
        LEFT JOIN (course_contact cc) ON (cc.contact_id = c.contact_id)
        LEFT JOIN (course crse) ON (cc.course_id = crse.course_id)
        WHERE o.order_id != ''
        {$sqlAppend}
        ORDER BY c.first_name 
        ";

        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';

        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;
            
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['id_card_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 1));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 2));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 3));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 4));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 5));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 6));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 7));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 8));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 9));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 10));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 11));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->view->getStudentPaymentStatus($row['order_id'], $row['contact_id'], 12));
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}