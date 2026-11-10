<?
class CP_Admin_Widgets_Hms_InvoiceSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $bill_type     = $fn->getReqParam('bill_type');

        /*$SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        ";*/

        if($bill_type == 'Company'){
            $SQL = "
            SELECT DISTINCT o.company_id
                  ,o.company_name AS patient_name
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            ";
        } else {
            $SQL = "
            SELECT DISTINCT o.patient_information_id
                  ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            ";
        }
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
        /*$patient_information_id    	= $fn->getReqParam('patient_information_id');

		if ($patient_information_id != '') {
            $searchVar->sqlSearchVar[] = "o.patient_information_id = {$patient_information_id}";
		}

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "i.site_id = {$site_id}" ;
            }
        }

        $searchVar->sqlSearchVar[]  = "i.status != 'Cancelled'";
        $searchVar->sortOrder 		= "patient_name ASC";*/

        $company_patient_id     = $fn->getReqParam('company_patient_id');
        $bill_type     = $fn->getReqParam('bill_type');

        if ($company_patient_id != '') {
            if($bill_type == 'Company' || $bill_type == 'Panel'){
                $searchVar->sqlSearchVar[] = "o.company_id = {$company_patient_id}";
            } else {
                $searchVar->sqlSearchVar[] = "o.patient_information_id = {$company_patient_id}";
            }
        }
        $searchVar->sqlSearchVar[] = "o.bill_type = '{$bill_type}'";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_invoiceSummary');

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

        $file_name = "Detail_Summary_by_Client__" . date("d-m-Y") . ".xls";

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

        if($company_id == ''){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Due');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
         }
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
            $companyId = "AND c.company_id = {$company_id}";
        }

        $totalBalanceAmount = 0 ;
        $paymentTotalofInvoiceAmount = 0 ;
        $totalPaidAmount = 0 ;

        $SQL = "
        SELECT DISTINCT c.company_id
              ,c.company_name
               {$siteTitle}
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `company` c ON (c.company_id = o.company_id)
        WHERE o.record_type = 'Quote'
        AND c.company_id !=''
        {$appendSql}
        {$companyId}
        {$linkToStock}
        ORDER BY c.company_name ASC
        ";

        $result1 = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result1)) {

            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.invoice_id = inv.invoice_id
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
            WHERE inv.status != 'Cancelled'
            AND o.company_id = {$row['company_id']}
            AND o.site_id = {$row['site_id']}
            ";

            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

                if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                    $siteRecSql ="
                    SELECT s.title
                    FROM site s
                    WHERE s.site_id = {$row['site_id']}
                    ";

                    $resultSiteLocation = $db->sql_query($siteRecSql);
                    $rowSite            = $db->sql_fetchrow($resultSiteLocation);
                 }

            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;
                $rowc++;

                if($rowInv['invoice_code_vat_quote'] != ''){
                    if($rowInv['selling_company'] == 'V-United Exports'){
                        $invoiceCode = 'INVX -' . $rowInv['invoice_code_vat_quote'];
                    }else{
                        $invoiceCode = 'INVQ -' . $rowInv['invoice_code_vat_quote'];
                    }
                }
                else {
                    $invoiceCode = $rowInv['invoice_code'];
                }

                $invoice_amount = $rowInv['invoice_amount'] - $rowInv['sales_return_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $paymentTotalofInvoiceAmount += $invoice_amount;
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount);
                $balance_amount = number_format($balance_amount);
                $rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid']);

                if($company_id == ''){
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
                }
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($rowInv['invoice_date'], 'd-m-Y'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoiceCode);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowInv['total_amount_paid']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_amount);
                if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($paymentTotalofInvoiceAmount,2));
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($totalPaidAmount,2));
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($totalBalanceAmount,2));


        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }




}