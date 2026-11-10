<?
class CP_Admin_Widgets_Hms_CompanyInvoiceSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $bill_type     = $fn->getReqParam('bill_type');

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.nric
              ,p.worker_id
              ,pv.check_up_date
              ,pv.patient_visit_id
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `patient_information` p ON (p.patient_information_id = o.patient_information_id)
        LEFT JOIN `patient_visit` pv ON (pv.patient_visit_id = o.patient_visit_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');


        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        $searchVar->sortOrder       = "patient_name ASC";
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
        //$dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_detailSummaryByClient');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_companyInvoiceSummary');

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

        $location_id    = $fn->getReqParam('location_id');
        $company_id     = $fn->getReqParam('company_id');

        $rows = '';


        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "CompanyInvoiceSummary__" . date("d-m-Y") . ".xls";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $company_id      = $fn->getReqParam('company_id');
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'IC / No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Emp No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Treatment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fees');
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

        $totalInvoiceAmount = 0;
        $totalBalanceAmount = 0 ;
        $paymentTotalofInvoiceAmount = 0 ;
        $totalPaidAmount = 0 ;

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.nric
              ,p.worker_id
              ,pv.check_up_date
              ,pv.patient_visit_id
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `patient_information` p ON (p.patient_information_id = o.patient_information_id)
        LEFT JOIN `patient_visit` pv ON (pv.patient_visit_id = o.patient_visit_id)
        WHERE {$startDateAppendSql}
        ORDER BY patient_name ASC
        ";

        $result1 = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result1)) {

            $SqlCondition = "WHERE o.company_id = {$company_id}";

            if($company_id != ''){
            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.related_invoice_id = inv.invoice_id
              AND rcp.receipt_status = 'Paid'
            ) AS total_amount_paid
            ,if(
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            ),
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            )
            ,''
            )as sales_return_amount
            FROM invoice inv
            LEFT JOIN `order` o ON (o.order_id = inv.order_id)
            {$SqlCondition}
            AND inv.status != 'Cancelled'
            AND o.patient_visit_id = {$row['patient_visit_id']}
            {$appendSql}
            ";


            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;
                $rowc++;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount, 2);
                $balance_amount = number_format($balance_amount, 2);
                $rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid'], 2);

                $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=hms_order&record_id={$rowInv['order_id']}&_action=edit'>";
                $invoiceCode = $rowInv['invoice_code'];

                $SQL = "
                SELECT it.*
                FROM invoice_item it
                WHERE it.invoice_id = '{$rowInv['invoice_id']}'
                  AND it.record_type = 'Treatment'
                ";
                $result = $db->sql_query($SQL);
                $treatment = '';

                while ($rowIt = $db->sql_fetchrow($result)) {
                    $treatment .= $rowIt['item_title'].', ';
                }
                $treatment = rtrim($treatment, ', ');

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($row['check_up_date'], 'd-m-Y'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['patient_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['nric']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['worker_id']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $treatment);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
            }
            }
        }

        $colc = 0;
        $rowc++;

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            if($company_id == ''){
               $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        $rowc++;
        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');


        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}