<?
class CP_Admin_Widgets_AceIms_IncomeByStudent_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        $appendSql = "";
        
        if ($start_date != '') {
            $appendSql .= " AND o.order_date >= '{$start_date}'";
        }

        if ($end_date != '') {
            $appendSql .= " AND o.order_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT o.order_id
              ,o.order_date
              ,o.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,c.registration_no
              ,crse.title as course_title
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                LEFT JOIN (`order` od) ON (i.order_id = od.order_id)
                WHERE od.order_id = o.order_id
                    AND i.status != 'Cancelled' 
                    {$appendSql}
               ) as net_total
              ,(SELECT count(i.invoice_amount) FROM invoice i
                LEFT JOIN (`order` od) ON (i.order_id = od.order_id)
                WHERE od.order_id = o.order_id
                    AND i.add_registration_fee = 1
                    AND i.status != 'Cancelled'
                    {$appendSql}
               ) as count_of_registration_fee
              ,(SELECT SUM(amount) FROM invoice_receipt_history irh
                LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
                LEFT JOIN (`order` od) ON (i.order_id = od.order_id)
                WHERE od.order_id = o.order_id
                  AND irh.invoice_paid_status = 'Paid'
                  {$appendSql}
               ) as amount_paid
        FROM `order` o
        LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        LEFT JOIN course_contact cc ON (o.order_id = cc.order_id)
        LEFT JOIN course crse ON (cc.course_id = crse.course_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date <= '{$end_date}'";
        }

        $searchVar->sortOrder = 'c.registration_no';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_incomeByStudent');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reg No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Income (net total)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Outstanding Fees / Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
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
        
        $appendSql = '';
        if ($start_date != '') {
            $appendSql .= " AND o.order_date >= '{$start_date}'";
        }

        if ($end_date != '') {
            $appendSql .= " AND o.order_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT o.order_id
              ,o.order_date
              ,o.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,c.registration_no
              ,crse.title as course_title
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                LEFT JOIN (`order` od) ON (i.order_id = od.order_id)
                WHERE od.order_id = o.order_id
                    AND i.status != 'Cancelled' 
                    {$appendSql}
               ) as net_total
              ,(SELECT count(i.invoice_amount) FROM invoice i
                LEFT JOIN (`order` od) ON (i.order_id = od.order_id)
                WHERE od.order_id = o.order_id
                    AND i.add_registration_fee = 1
                    AND i.status != 'Cancelled'
                    {$appendSql}
               ) as count_of_registration_fee
              ,(SELECT SUM(amount) FROM invoice_receipt_history irh
                LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
                LEFT JOIN (`order` od) ON (i.order_id = od.order_id)
                WHERE od.order_id = o.order_id
                  AND irh.invoice_paid_status = 'Paid'
                  {$appendSql}
               ) as amount_paid
        FROM `order` o
        LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        LEFT JOIN course_contact cc ON (o.order_id = cc.order_id)
        LEFT JOIN course crse ON (cc.course_id = crse.course_id)
        WHERE o.contact_id != ''
        {$appendSql}
        ORDER BY c.registration_no
        ";

        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';

        while ($row = $db->sql_fetchrow($result)) {
            if ($row['net_total'] > 0) {

                $total_reg_fee = $row['count_of_registration_fee'] * $fn->getSettingsValueByKey("registrationFee");
                
                $net_total = $total_reg_fee + $row['net_total'];
                $amount_paid = $row['amount_paid'];
                $due = $net_total - $amount_paid;
    
                $colc = 0;
                $rowc++;
            
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_total);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $due);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_paid);
            }
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}