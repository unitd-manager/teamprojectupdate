<?
class CP_Admin_Widgets_EnterpriseIms_ReceiptSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(r.date, '%d-%m-%Y') AS receipt_date
              ,r.receipt_code
              ,r.mode_of_payment
              ,r.amount
              ,p.first_name
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id  = o.order_id)
        LEFT JOIN (parent p)  ON (o.parent_id = p.parent_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $searchVar->sqlSearchVar[] = "r.site_id = {$fn->getSessionParam('cp_site_id')}";

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $payment_mode   = $fn->getReqParam('payment_mode');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d');
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $searchVar->sqlSearchVar[] = "r.mode_of_payment != 'Giro'";
        $searchVar->sqlSearchVar[] = "r.receipt_status = 'Paid'";
        $searchVar->sqlSearchVar[] = "r.date BETWEEN '{$start_date}' AND '{$end_date}'";

        if ($payment_mode == 'All') {
        } else {
            $searchVar->sqlSearchVar[] = "r.mode_of_payment = '{$payment_mode}'";
        }

        $searchVar->sortOrder = 'r.mode_of_payment, r.receipt_code';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_receiptSummary');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    function getExportToExcel1($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'course_title'  => $phpExcel->getFldObj('Course')
             ,'total'         => $phpExcel->getFldObj('Total')
        );

        $file_name = "IncomeByCourse_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $payment_mode = $fn->getReqParam('payment_mode');

        if ($start_date == '') {
            $start_date = date('Y-m-d');
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Daily-Collection_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Receipt Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Remarks');
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

        if ($payment_mode == 'All') {
        } else {
            $sqlAppend = "AND r.mode_of_payment = '{$payment_mode}'";
        }

        $SQL = "
        SELECT DATE_FORMAT(r.date, '%d-%m-%Y') AS receipt_date
              ,r.receipt_code
              ,r.mode_of_payment
              ,r.amount
              ,r.remarks
              ,p.first_name
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id  = o.order_id)
        LEFT JOIN (parent p)  ON (o.parent_id = p.parent_id)
        WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'
          AND r.site_id = {$_SESSION['cp_site_id']}
          AND r.receipt_status = 'Paid'
          AND r.mode_of_payment != 'Giro'
        {$sqlAppend}
        ORDER BY r.mode_of_payment, r.receipt_code
        ";
        $result = $db->sql_query($SQL);
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $total_for_payment_mode = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if ($mode_of_payment == '') {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment']) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment']) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            if ($print_total == 1) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

                $colc = 0;
                $rowc++;
            } else {
                $print_total = "";
            }

            $grand_total += $row['amount'];

            $print_total;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['remarks']);
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_for_payment_mode);

        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Grand Total');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grand_total);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');

        $serial_no   = 0;
        $grand_total = 0;

        foreach($this->dataArray as $row){
            $serial_no += 1;
            $grand_total += $row['amount'];
        }

        $row = array(
                     'total_count' => $serial_no
                    ,'grand_total' => $grand_total
                    );

        return $row;
    }
}