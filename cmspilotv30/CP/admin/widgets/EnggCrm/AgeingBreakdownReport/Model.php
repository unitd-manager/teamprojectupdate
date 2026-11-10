<?
class CP_Admin_Widgets_EnggCrm_AgeingBreakdownReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DISTINCT i.invoice_id
              ,c.company_name
              ,i.invoice_code
              ,i.invoice_date
              ,i.invoice_amount
              ,i.gst_percentage
              ,i.discount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (`company` c) ON (o.company_id = c.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $company_id = $fn->getReqParam('company_id');
        $search_by  = $fn->getReqParam('search_by');

        if ($search_by == "31-60 Days") {
            $thirtyOneDays = date('Y-m-d', strtotime("-31 days"));
            $sixtyDays = date('Y-m-d', strtotime("-60 days"));

            $sqlAppend  = "BETWEEN '{$sixtyDays}' AND '{$thirtyOneDays}'";
        } else if ($search_by == "61-90 Days") {
            $sixtyOneDays = date('Y-m-d', strtotime("-61 days"));
            $nintyDays = date('Y-m-d', strtotime("-90 days"));

            $sqlAppend  = "BETWEEN '{$nintyDays}' AND '{$sixtyOneDays}'";
        } else {
            $nintyOneDays = date('Y-m-d', strtotime("-91 days"));

            $sqlAppend = "<= '{$nintyOneDays}'";
        }

        $searchVar->sqlSearchVar[] = "o.company_id = {$company_id}";
        $searchVar->sqlSearchVar[] = "(i.status = 'Due' OR i.status = 'Partial Payment' OR i.status = 'Late')";
        $searchVar->sqlSearchVar[] = "i.invoice_date {$sqlAppend}";
        
        $searchVar->sortOrder = 'i.invoice_date ASC, i.invoice_code ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_ageingBreakdownReport');

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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "AgeingBreakdownReport__" . date("d-m-Y") . ".xls";
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();
        $actSheet = &$objPHPExcel->getActiveSheet();
        $headStyle = array(
            'font' => array('bold' => true)
        );
        //--------------------------------------------------//
        $colc = 0;
        $rowc = 1;
        $actSheet->mergeCells("A{$rowc}:G{$rowc}");
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Ageing Breakdown Report');

        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:g{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Number');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $company_id = $fn->getReqParam('company_id');
        $search_by  = $fn->getReqParam('search_by');

        if ($search_by == "31-60 Days") {
            $thirtyOneDays = date('Y-m-d', strtotime("-31 days"));
            $sixtyDays = date('Y-m-d', strtotime("-60 days"));

            $sqlAppend  = "BETWEEN '{$sixtyDays}' AND '{$thirtyOneDays}'";
        } else if ($search_by == "61-90 Days") {
            $sixtyOneDays = date('Y-m-d', strtotime("-61 days"));
            $nintyDays = date('Y-m-d', strtotime("-90 days"));

            $sqlAppend  = "BETWEEN '{$nintyDays}' AND '{$sixtyOneDays}'";
        } else {
            $nintyOneDays = date('Y-m-d', strtotime("-91 days"));

            $sqlAppend = "<= '{$nintyOneDays}'";
        }

        $sql = "
        SELECT DISTINCT i.invoice_id
              ,c.company_name
              ,i.invoice_code
              ,i.invoice_date
              ,i.invoice_amount
              ,i.gst_percentage
              ,i.discount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (`company` c) ON (o.company_id = c.company_id)
        WHERE o.company_id = {$company_id}
          AND (i.status = 'Due' OR i.status = 'Partial Payment' OR i.status = 'Late')
          AND i.invoice_date {$sqlAppend}
        ORDER BY i.invoice_date ASC, i.invoice_code ASC
        ";
        $result = $db->sql_query($sql);
        $count = 1;
        $balance_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $amount_payable = 0;
            $total_receipt_amount = 0;

            $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            $invoice_code = $inv_date . substr($row['invoice_code'], 2);

            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD.MM.YYYY');

            $invoice_amount_after_disc = $row['invoice_amount'] - $row['discount'];
            $gst_amount = 0;
            if ($row['gst_percentage']) {
                $gst_amount = round((($invoice_amount_after_disc * $row['gst_percentage']) / 100), 2);
            }
            $amount_payable = $invoice_amount_after_disc + $gst_amount;

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$row['invoice_id']}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $total_receipt_amount = $rowRec['total_invoice_amount_paid'];

            $balance_amount += $amount_payable - $total_receipt_amount;

            $colc = 0;
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_code);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_payable);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_receipt_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_amount);
            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}