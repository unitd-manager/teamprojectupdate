<?
class CP_Admin_Widgets_AgileIms_IncomeByStudent_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.contact_id
              ,c.first_name as contact_name
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
              ,o.order_id
        FROM contact c
        LEFT JOIN (`order` o) ON (c.contact_id = o.contact_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        LEFT JOIN course_contact cc ON (c.contact_id = cc.contact_id)
        LEFT JOIN course crse ON (cc.course_id = crse.course_id)
        ";

        $SQL = "
        SELECT i.*
              ,IF(o.contact_id > 0, 'Individual', 'Company') AS invoice_type
              ,o.contact_id
              ,o.company_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $invoice_status = $fn->getReqParam('invoice_status');

        if ($invoice_status != '' && $invoice_status != 'All') {
            $searchVar->sqlSearchVar[] = "i.status = '{$invoice_status}'";
        }

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = date('Y') . '-01-01';
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y') . '-01-01';
            $end_date   = date('Y-m-d');
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $searchVar->sortOrder = 'i.invoice_date ASC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'agileIms_incomeByStudent');

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

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $invoice_status = $fn->getReqParam('invoice_status');

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student/Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Status');
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
        if ($invoice_status != '' && $invoice_status != 'All') {
            $sqlAppend .= " AND i.status = '{$invoice_status}'";
        }

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
            $sqlAppend .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = date('Y') . '-01-01';
            $sqlAppend .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $sqlAppend .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y') . '-01-01';
            $end_date   = date('Y-m-d');
            $sqlAppend .= " AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $SQL = "
        SELECT i.*
              ,IF(o.contact_id > 0, 'Individual', 'Company') AS invoice_type
              ,o.contact_id
              ,o.company_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE i.invoice_id > 0
        {$sqlAppend}
        ORDER BY i.invoice_date ASC 
        ";
        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';
        $overall_net_total = 0;
        $overall_due_total = 0;
        $overall_paid_total = 0;

        while ($row = $db->sql_fetchrow($result)) {
            if ($row['contact_id']) {
                $contactRec = $fn->getRecordRowById('contact','contact_id',$row['contact_id']);
                $name = $contactRec['first_name'];
            } else {
                $companyRec = $fn->getRecordRowById('company','company_id',$row['company_id']);
                $name = $companyRec['title'];
            }

            $amount_paid = $this->getReceiptAmountForInvoice($row['invoice_id']);

            if ($amount_paid == '') {
                $amount_paid_display = 0;
            } else {
                $amount_paid_display = $this->getReceiptAmountForInvoice($row['invoice_id']);
            }

            $due = $row['invoice_amount'] - $amount_paid;

            $overall_net_total += $row['invoice_amount'];
            $overall_due_total += $due;
            $overall_paid_total += $amount_paid;
            
            $colc = 0;
            $rowc++;
        
            $invoice_date = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $name);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $due);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_paid_display);
        }

        $colc = 0;
        $rowc++;
    
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_net_total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_due_total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_paid_total);

        //$rowc++;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getReceiptAmountForInvoice($invoice_id) {
        $db = Zend_Registry::get('db');

        $sql = "
        SELECT SUM(amount) AS total_receipt_amount
        FROM invoice_receipt_history
        WHERE invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        return $row['total_receipt_amount'];
    }
}