<?
class CP_Admin_Widgets_Pms_ReceiptSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(r.date, '%d-%m-%Y') AS receipt_date
              ,r.receipt_code
              ,s.title AS site_name
              ,r.mode_of_payment
              ,r.amount
              ,r.site_id
              ,r.payment_site_id
        FROM receipt r
        LEFT JOIN (site s) ON (r.site_id = s.site_id)
        ";

        /*
        $SQL = "
        SELECT DATE_FORMAT(r.date, '%d-%m-%Y') AS receipt_date
              ,r.receipt_code
              ,s.title AS site_name
              ,r.mode_of_payment
              ,r.amount
              ,p.first_name
              ,r.site_id
              ,r.payment_site_id
              ,i.invoice_date
              ,i.invoice_amount
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id  = i.invoice_id)
        LEFT JOIN (`order` o) ON (r.order_id  = o.order_id)
        LEFT JOIN (parent p)  ON (o.parent_id = p.parent_id)
        LEFT JOIN (site s)    ON (r.site_id   = s.site_id)
        ";
        */

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        //$searchVar->sqlSearchVar[] = "r.site_id = {$fn->getSessionParam('cp_site_id')}";

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $payment_mode   = $fn->getReqParam('payment_mode');
        $site_id        = $fn->getReqParam('site_id');
        
        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d');
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "(r.site_id = '{$site_id}' OR r.payment_site_id = '{$site_id}')";
            }
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
    
    /**
     *
     */
    function getExportToExcelOld(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $payment_mode = $fn->getReqParam('payment_mode');
        $site_id      = $fn->getReqParam('site_id');

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month/ Year for payment');
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

        if (is_numeric($site_id)) {
            $sqlAppend .= "AND (r.site_id = {$site_id} OR r.payment_site_id = '{$site_id}')";
        }

        $SQL = "
        SELECT DATE_FORMAT(r.date, '%d-%m-%Y') AS receipt_date
              ,r.receipt_code
              ,s.title AS site_name
              ,r.mode_of_payment
              ,r.amount
              ,r.remarks
              ,p.first_name
              ,r.site_id
              ,r.payment_site_id
              ,i.invoice_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id  = i.invoice_id)
        LEFT JOIN (`order` o) ON (r.order_id  = o.order_id)
        LEFT JOIN (parent p)  ON (o.parent_id = p.parent_id)
        LEFT JOIN (site s)    ON (r.site_id   = s.site_id)
        WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'
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
        
            if ($mode_of_payment == '' && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
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
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

                $colc = 0;
                $rowc++;
            } else {
                $print_total = "";
            }
            
            if (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '') {
                $grand_total += $row['amount'];
            }
            
            $payment_branch = '';
            if ($row['payment_site_id'] != '') {
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $row['payment_site_id']);
                $payment_branch = " - Payment done at " . $siteRec['title'];
            }

            $payment_year = substr($row['invoice_date'],0 ,4);
            $payment_month = substr($row['invoice_date'],5 ,2);
            switch ($payment_month) {
                case 1: $prefix_month = 'Jan';
                break;
                case 2: $prefix_month = 'Feb';
                break;
                case 3: $prefix_month = 'Mar';
                break;
                case 4: $prefix_month = 'Apr';
                break;
                case 5: $prefix_month = 'May';
                break;
                case 6: $prefix_month = 'Jun';
                break;
                case 7: $prefix_month = 'Jul';
                break;
                case 8: $prefix_month = 'Aug';
                break;
                case 9: $prefix_month = 'Sep';
                break;
                case 10: $prefix_month = 'Oct';
                break;
                case 11: $prefix_month = 'Nov';
                break;
                case 12: $prefix_month = 'Dec';
                break;
            }

            $print_total;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['site_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment'] . $payment_branch);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $prefix_month . '/' . $payment_year);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['remarks']);
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
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
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $payment_mode = $fn->getReqParam('payment_mode');
        $site_id      = $fn->getReqParam('site_id');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d');
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Parent');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month/ Year for payment');
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

        if (is_numeric($site_id)) {
            $sqlAppend .= "AND (r.site_id = {$site_id} OR r.payment_site_id = '{$site_id}')";
        }

        $SQL = "
        SELECT DATE_FORMAT(r.date, '%d-%m-%Y') AS receipt_date
              ,r.receipt_code
              ,s.title AS site_name
              ,r.mode_of_payment
              ,r.amount
              ,r.site_id
              ,r.payment_site_id
              ,r.remarks
        FROM receipt r
        LEFT JOIN (site s) ON (r.site_id = s.site_id)
        WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'
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
        
            // Printing total amount for each mode of payment. Eg: Cash, Nets, Giro etc
            if ($mode_of_payment == '' && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            // If Mode of payment changes, printing the total amount for the specific payment mode.
            // Eg: If earlier payment mode was Cash, and for next receipt it is Giro, then printing the total.
            if ($print_total == 1) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

                $colc = 0;
                $rowc++;
            } else {
                $print_total = "";
            }
            
            if (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '') {
                $grand_total += $row['amount'];
            }
            
            // Payment is done in other branch for the student studying in the chosen branch
            // Eg: Student studying in HQ but payment done in Jurong. This code will highlight the payment
            $payment_branch = '';
            if ($row['payment_site_id'] != '') {
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $row['payment_site_id']);
                $payment_branch = " - Payment done at " . $siteRec['title'];
            }

            // Parent name to be displayed
            $sqlParent = "
            SELECT p.first_name FROM parent p
            LEFT JOIN (`order` o) ON (p.parent_id = o.parent_id)
            LEFT JOIN (receipt r) ON (o.order_id = r.order_id)
            WHERE r.receipt_code = '{$row['receipt_code']}'
              AND r.site_id = '{$row['site_id']}'
            ";
            $resultParent = $db->sql_query($sqlParent);
            $rowParent = $db->sql_fetchrow($resultParent);

            // Finding invoice month and year for the paid receipt
            $sqlInv = "
            SELECT i.invoice_date, i.add_registration_fee, i.invoice_month FROM invoice i
            LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE r.receipt_code = '{$row['receipt_code']}'
            ";
            $resultInv  = $db->sql_query($sqlInv);
            $numRowsInv = $db->sql_numrows($resultInv);
            
            $countInv   = 1;
            $month_year = '';
            while ($rowInv = $db->sql_fetchrow($resultInv)) {
                $payment_year = substr($rowInv['invoice_date'],0 ,4);
                $payment_month = substr($rowInv['invoice_date'],5 ,2);

                if ($numRowsInv == $countInv) {
                    if ($rowInv['add_registration_fee'] == 1) {
                        $month_year .= $payment_month . '/' . $payment_year .' - (Reg fee)';
                    } else {
                        $month_year .= $payment_month . '/' . $payment_year;
                    }
                } else {
                    if ($rowInv['add_registration_fee'] == 1) {
                        $month_year .= $payment_month . '/' . $payment_year . ' - (Reg fee), ';
                    } else {
                        $month_year .= $payment_month . '/' . $payment_year . ', ';
                    }
                }
                $countInv++;
            }

            /*
            $payment_year = substr($row['invoice_date'],0 ,4);
            $payment_month = substr($row['invoice_date'],5 ,2);
            switch ($payment_month) {
                case 1: $prefix_month = 'Jan';
                break;
                case 2: $prefix_month = 'Feb';
                break;
                case 3: $prefix_month = 'Mar';
                break;
                case 4: $prefix_month = 'Apr';
                break;
                case 5: $prefix_month = 'May';
                break;
                case 6: $prefix_month = 'Jun';
                break;
                case 7: $prefix_month = 'Jul';
                break;
                case 8: $prefix_month = 'Aug';
                break;
                case 9: $prefix_month = 'Sep';
                break;
                case 10: $prefix_month = 'Oct';
                break;
                case 11: $prefix_month = 'Nov';
                break;
                case 12: $prefix_month = 'Dec';
                break;
            }
            */

            $print_total;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['site_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowParent['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment'] . $payment_branch);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $month_year);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['remarks']);
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
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
    function getSqlForCount($site_id) {
        $db = Zend_Registry::get('db');
        
        $serial_no   = 0;
        $grand_total = 0;

        foreach ($this->dataArray as $row) {
            $serial_no += 1;
            if (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '') {
                $grand_total += $row['amount'];
            }
        }

        $row = array(
                     'total_count' => $serial_no
                    ,'grand_total' => $grand_total
                    );

        return $row;
    }
}