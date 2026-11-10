<?
class CPL_Admin_Widgets_EnggCrm_TenderSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $SQL = "
        SELECT o.*
              ,c.company_name
              ,co.first_name
        FROM opportunity o
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        LEFT JOIN (contact co) ON (co.company_id = c.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';

        $expiry_date = date('Y-m-d', strtotime("+3 days"));

        $current_date = date('Y-m-d');

        //$searchVar->sqlSearchVar[] = "c.gst_applied = 1";
        //$searchVar->sqlSearchVar[] = "q.status != 'Cancelled'";
        //$searchVar->sqlSearchVar[]  = "o.actual_closing <= '{$expiry_date}'";

        //$searchVar->groupBy   = 'pop.supplier_id';
        $searchVar->sortOrder = "o.opportunity_id DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_expenseSummaryReport');

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

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $status       = $fn->getReqParam('status');
        $current_date   = date('Y-m-d');
        $company_id = $fn->getReqParam('company_id');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        if($start_date != '' && $end_date != ''){
            $file_name = "Overall_GST_Summary_" .$start_date. "_and_" .$end_date. ".xls";
        }else{
            $file_name = "Overall_GST_Summary_" . date("d-m-Y") . ".xls";
        }

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No GST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Before GST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount with GST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Received');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Balance');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GIRO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cheque');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cheque No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cash');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cheque Issued Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Unpaid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payment Cleared Date');
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

        $companyId = '';
        if ($company_id != '') {
            $companyId = " AND e.company_id = {$company_id}";
        }

        $statusCon = '';
        if ($status != '') {
            $statusCon = " AND e.payment_status = '{$status}'";
        }

        $dateCondition = '';
        if ($start_date != '' && $end_date == '') {
            $dateCondition = " AND ((e.date BETWEEN '{$start_date}' AND '{$current_date}') OR (e.date BETWEEN '{$start_date}' AND '{$current_date}'))";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = date('Y') . '-' . date('m') . '-01';
            
            $dateCondition = " AND ((e.date BETWEEN '{$start_date}' AND '{$end_date}') OR (e.date BETWEEN '{$start_date}' AND '{$end_date}'))";
        } else if ($start_date != '' && $end_date != '') {
            $dateCondition = " AND ((e.date BETWEEN '{$start_date}' AND '{$end_date}') OR (e.date BETWEEN '{$start_date}' AND '{$end_date}'))";
        } else {
            $start_date = date('Y') . '-' . date('m') . '-01';

            $dateCondition = " AND ((e.date BETWEEN '{$start_date}' AND '{$current_date}') OR (e.date BETWEEN '{$start_date}' AND '{$current_date}'))";
        }


        $SQL = "
        SELECT e.*
        ,s.company_name
        FROM expense e
        LEFT JOIN (supplier s) ON (s.supplier_id = e.company_id)
        WHERE e.type = 'Expense'
              {$companyId}
              {$dateCondition}
              {$statusCon}
        ORDER BY e.date ASC
        ";
        $result = $db->sql_query($SQL);

        $overall_purchase   = 0;
        $overall_before_gst = 0;
        $overall_after_gst  = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $date = $fn->getCPDate($row['date'], 'd m Y');
            $totalAmt = $row['service_charge'] + $row['amount'] + $row['gst_amount'];

            $sqlReceipt = "SELECT DISTINCT payment_id, date, mode_of_payment, bank_name, cheque_no, cheque_date FROM payment WHERE record_id = '{$row['expense_id']}'";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $numRowsReceipt = $db->sql_numrows($resultReceipt);
            $count = 1;
            $bank = '';
            $cheque_no = '';
            $cheque_date = '';
            $receipt_date = '';
            $mode_of_payment = '';
            $giro = '';
            $cash = '';
            while ($rowReceipt = $db->sql_fetchrow($resultReceipt)) {
                $receipt_date .= $fn->getCPDate($rowReceipt['date'], 'd-m-Y') . ', ';
                $mode_of_payment .= $rowReceipt['mode_of_payment'] . ', ';
                $bank .= $rowReceipt['bank_name'] . ', ';
                $cheque_no .= $rowReceipt['cheque_no'] . ', ';
                $cheque_date .= $fn->getCPDate($rowReceipt['cheque_date'], 'd-m-Y') . ', ';

                if($rowReceipt['mode_of_payment'] == 'GIRO'){
                    $giro .= 'Yes, ';
                }
                if($rowReceipt['mode_of_payment'] == 'Cash'){
                    $cash .= 'Yes, ';
                }
                $count++;
            }

            $bank = rtrim($bank, ', ');
            $cheque_no = rtrim($cheque_no, ', ');
            $cheque_date = rtrim($cheque_date, ', ');
            $giro = rtrim($giro, ', ');
            $cash = rtrim($cash, ', ');

            $payment_status = '';
            if($row['payment_status'] == 'Paid'){
                $payment_status = 'Yes';
            }

            $payment_status_unpaid = '';
            if($row['payment_status'] != 'Paid'){
                $payment_status_unpaid = 'Yes';
            }

            $sqlRec = "
            SELECT SUM(p.amount) AS total_invoice_amount_paid
            FROM payment p
            WHERE p.record_id = {$row['expense_id']}
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $receipt_amount = $rowRec['total_invoice_amount_paid'];
            if ($rowRec['total_invoice_amount_paid'] == '') {
                $receipt_amount = 0;
            }

            //$receipt_amount = round($receipt_amount);
            $balance = $totalAmt - $receipt_amount;
            //$balance = round($balance);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['service_charge']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gst_amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAmt);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $receipt_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $giro);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $bank);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cheque_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cash);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cheque_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_status);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_status_unpaid);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }
        
        $colc = 0;
        $rowc++;

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}