<?
class CP_Admin_Widgets_EnggCrm_InvoiceSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DISTINCT i.invoice_id
              ,i.invoice_code
              ,i.invoice_amount
              ,i.invoice_date
              ,i.status
              ,i.gst_percentage
              ,i.discount
              ,c.company_name
        FROM `invoice` i
        LEFT JOIN `order` o   ON (o.order_id   = i.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $company_id = $fn->getReqParam('company_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-1, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";

		if ($company_id != '') {
            $searchVar->sqlSearchVar[] = "o.company_id = {$company_id}";
		}

        $searchVar->sortOrder = "i.invoice_date ASC, i.invoice_code ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_invoiceSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
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

        $company_id = $fn->getReqParam('company_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Invoice_Summary__" . date("d-m-Y") . ".xls";

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
        $actSheet->mergeCells("A{$rowc}:H{$rowc}");
        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Summary Report');

        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GST Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $sqlAppend = '';
        $invoice_total = 0;

        if ($company_id != '') {
            $sqlAppend = "AND o.company_id = {$company_id}";
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-1, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $sql = "
        SELECT DISTINCT i.invoice_id
              ,i.invoice_code
              ,i.invoice_amount
              ,i.invoice_date
              ,i.status
              ,i.gst_percentage
              ,i.discount
              ,c.company_name
        FROM `invoice` i
        LEFT JOIN `order` o   ON (o.order_id   = i.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        WHERE i.status != 'Cancelled'
          AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
          {$sqlAppend}
        ORDER BY i.invoice_date ASC, i.invoice_code ASC
        ";
        $result = $db->sql_query($sql);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $amount_payable = $row['invoice_amount'] - $row['discount'];
            if ($row['gst_percentage']) {
                $gst_amount = (($amount_payable * $row['gst_percentage'])/100);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }

                $total = $amount_payable + $gst_amount;
            } else {
                $total = $amount_payable;
            }

            $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            $invoice_code = $inv_date . substr($row['invoice_code'], 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($row['invoice_date'], 'd-m-Y'));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_code);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_payable);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gst_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);

            $invoice_total += $total;
            $count++;
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        $rowc++;
        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}