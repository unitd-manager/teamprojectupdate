<?
class CP_Admin_Widgets_EnterpriseIms_IncomeByStudent_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,c.registration_no
              ,crse.title as course_title
              ,(SELECT SUM(amount) FROM invoice_receipt_history irh
                  WHERE i.invoice_id = irh.invoice_id
                  ) as net_total
              ,(SELECT SUM(amount) FROM invoice_receipt_history irh
                  WHERE i.invoice_id = irh.invoice_id
                  AND irh.invoice_paid_status = 'Paid'
                  ) as amount_paid
              ,o.order_date
        FROM contact c
        LEFT JOIN (`order` o) ON (c.contact_id = o.contact_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        LEFT JOIN course_contact cc ON (c.contact_id = cc.contact_id)
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

        /*$searchVar->sqlSearchVar[] = "o.order_status = 'Due'";*/

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "o.order_date <= '{$end_date}'";
        }

        //$searchVar->groupBy = 'oi.record_id';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_incomeByStudent');

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC No.');
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
        
        $sqlAppend = '';
        if($start_date != '' && $end_date != ''){
            $sqlAppend = "WHERE o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) as contact_name
              ,c.id_card_no
              ,(SELECT SUM(amount) FROM invoice_receipt_history irh
                  WHERE i.invoice_id = irh.invoice_id
                  ) as net_total
              ,(SELECT SUM(amount) FROM invoice_receipt_history irh
                  WHERE i.invoice_id = irh.invoice_id
                  AND irh.invoice_paid_status = 'Paid'
                  ) as amount_paid
        FROM contact c
        LEFT JOIN (`order` o) ON (c.contact_id = o.contact_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        {$sqlAppend}
        ORDER BY contact_name 
        ";

        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';

        while ($row = $db->sql_fetchrow($result)) {
            $net_total = number_format($row['net_total'], 2);
            $amount_paid = number_format($row['amount_paid'], 2);
            
            $due = number_format($row['net_total'] - $row['amount_paid'], 2);
            
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['id_card_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_total);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $due);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_paid);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}