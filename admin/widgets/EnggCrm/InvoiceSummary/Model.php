<?
class CPL_Admin_Widgets_EnggCrm_InvoiceSummary_Model extends CP_Admin_Widgets_EnggCrm_InvoiceSummary_Model
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DISTINCT i.invoice_id
              ,i.invoice_code
              ,i.invoice_code_user
              ,i.invoice_amount
              ,i.invoice_date
              ,i.status
              ,i.gst_percentage
              ,i.reference_no
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

        $company_id  = $fn->getReqParam('company_id');
        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $record_type = $fn->getReqParam('record_type');

        if ($record_type != '') {
            $searchVar->sqlSearchVar[] = "o.record_type = '{$record_type}'";
        }

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

        $searchVar->sortOrder = "i.invoice_date ASC";
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
        $record_type = $fn->getReqParam('record_type');

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
        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
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

        $sqlAppend = '';
        $invoice_total = 0;

        if ($company_id != '') {
            $sqlAppend = "AND o.company_id = {$company_id}";
        }

        if ($record_type != '') {
            $sqlAppend = "AND o.record_type = '{$record_type}'";
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
              ,i.invoice_code_user
              ,i.invoice_amount
              ,i.invoice_date
              ,i.status
              ,i.gst_percentage
              ,i.reference_no
              ,c.company_name
        FROM `invoice` i
        LEFT JOIN `order` o   ON (o.order_id   = i.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        WHERE i.status != 'Cancelled'
          AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
          {$sqlAppend}
        ORDER BY i.invoice_date ASC
        ";
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if ($row['gst_percentage']) {
                $gst_amount = (($row['invoice_amount'] * $row['gst_percentage'])/100);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);

                    /* Checking whether 3rd decimal point is more than or equal to 5
                       If Yes, add 1 to 2nd decimal point
                     */
                    $gstDecimalMore = substr($fraction, 2, 1);
                    $fraction = substr($fraction, 0, 2);
                    if ($gstDecimalMore >= 5) {
                        $fraction = $fraction + 1;
                    }

                    $gst_amount = $integer . "." . $fraction;
                }

                $total = $row['invoice_amount'] + $gst_amount;
            } else {
                $total = $row['invoice_amount'];
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($row['invoice_date'], 'd-m-Y'));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);

            $invoice_total += $total;
        }

        $colc = 0;
        $rowc++;
        $actSheet->mergeCells("A{$rowc}:C{$rowc}");
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_total);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}