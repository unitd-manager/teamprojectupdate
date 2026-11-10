<?
class CP_Admin_Widgets_Tradingsg_DetailInvoiceByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
            SELECT invoice_date
                   ,invoice_code
                   ,invoice_amount
                   ,order_id
            FROM `invoice`
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
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {

            if($month != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
            }else{
                $start_date = $year . '-' . '01' . '-' . '01';
                $end_date   = $year . '-' . '12' . '-' . '31';
            }
            $searchVar->sqlSearchVar[] = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $searchVar->sqlSearchVar[] = "status != 'Cancelled'";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_detailInvoiceByMonth');

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

        $file_name = "DetailInvoiceByMonth" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');

        $appendSql = '';

        if ($start_date != '' && $end_date == '') {
            $appendSql = "invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendSql = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
             if($month != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
            }else{
                $start_date = $year . '-' . '01' . '-' . '01';
                $end_date   = $year . '-' . '12' . '-' . '31';
            }
            $appendSql = "invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }


        $SQL = "
            SELECT invoice_date
                   ,invoice_code
                   ,invoice_amount
                   ,order_id
            FROM `invoice`
            WHERE {$appendSql}
            AND status != 'Cancelled'
        ";
        $result = $db->sql_query($SQL);
        $payment_total = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $payment_total += $row['invoice_amount'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($row['invoice_amount'],2));
            $count++;
        }

        $payment_total = number_format($payment_total,2);

        $colc = 2;
        $rowc++;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}