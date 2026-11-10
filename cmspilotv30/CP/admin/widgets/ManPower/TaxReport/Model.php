<?
class CP_Admin_Widgets_ManPower_TaxReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){
        $SQL = "
        SELECT i.invoice_amount
              ,i.no_of_hours
              ,i.fed
              ,i.soc
              ,i.med
              ,i.state
              ,i.FUTA
              ,i.SUTA
              ,i.deductions
              ,i.invoice_type
              ,i.invoice_id
              ,i.start_date
              ,i.end_date
              ,o.order_id
              ,o.candidate_id
              ,o.work_state
              ,o.position_type
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS candidate_name
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `candidate` c ON (c.candidate_id = o.candidate_id)
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';
        
        $month        = $fn->getReqParam('month');
        $year         = $fn->getReqParam('year');
        $work_state   = $fn->getReqParam('work_state');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $month        = $fn->getReqParam('month');
        $candidate_id = $fn->getReqParam('candidate_name');

        if($work_state != ''){
            $searchVar->sqlSearchVar[] = "o.work_state = '{$work_state}'";
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.start_date >= '{$start_date}' AND i.end_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "i.end_date >= '{$end_date}' AND i.end_date <= '{$current_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "i.start_date >= '{$start_date}' AND i.end_date <= '{$end_date}'";
        } 

        if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
        
            $searchVar->sqlSearchVar[] = "i.start_date BETWEEN '{$startYear}' AND '{$endYear}'";
            $searchVar->sqlSearchVar[] = "i.end_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }

            $searchVar->sqlSearchVar[] = "i.start_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            $searchVar->sqlSearchVar[] = "i.end_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if($candidate_id != ''){
            $searchVar->sqlSearchVar[] = "candidate_name = '{$candidate_id}'";   
        }

        $searchVar->sqlSearchVar[] = "o.position_type = 'Full Time'";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "i.invoice_type IN('Candidate')";
                
        //$searchVar->sortOrder = 'sa.staff_attendance_id DESC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_taxReport');

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

        $file_name = "Tax_Report_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Id');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Candidate Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'End Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Work State');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Hrs');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gross');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fed');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'SS');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Med');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'FU');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'SU');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Net');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Tot W/H');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CK #');
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
        $month        = $fn->getReqParam('month');
        $year         = $fn->getReqParam('year');
        $work_state   = $fn->getReqParam('work_state');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $month        = $fn->getReqParam('month');
        $candidate_id = $fn->getReqParam('candidate_name');

        if($work_state != ''){
            $sqlAppend .= "AND o.work_state = '{$work_state}'";
        }

        if ($start_date != '' && $end_date == '') {
            $sqlAppend .= "AND i.start_date >= '{$start_date}' AND i.end_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $sqlAppend .= "AND i.end_date >= '{$end_date}' AND i.end_date <= '{$current_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $sqlAppend .= "AND i.start_date >= '{$start_date}' AND i.end_date <= '{$end_date}'";
        } 

        if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
        
            $sqlAppend .= "AND i.start_date BETWEEN '{$startYear}' AND '{$endYear}'";
            $sqlAppend .= "AND i.end_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }

            $sqlAppend .= "AND i.start_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            $sqlAppend .= "AND i.end_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if($candidate_id != ''){
            $sqlAppend .= "AND candidate_name = '{$candidate_id}'";   
        }

        $SQL = "
        SELECT i.invoice_amount
              ,i.no_of_hours
              ,i.fed
              ,i.soc
              ,i.med
              ,i.state
              ,i.FUTA
              ,i.SUTA
              ,i.deductions
              ,i.invoice_type
              ,i.invoice_id
              ,i.start_date
              ,i.end_date
              ,o.order_id
              ,o.candidate_id
              ,o.work_state
              ,o.position_type
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS candidate_name
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `candidate` c ON (c.candidate_id = o.candidate_id)
        WHERE i.status != 'Cancelled'
        AND i.invoice_type IN('Candidate')
        {$sqlAppend}
        ";

        $result = $db->sql_query($SQL);

        $currency_Symbol      = '$';
        $total_netAmount      = 0;
        $total_no_of_hours    = 0;
        $total_invoice_Amount = 0;
        $total_fed_Amount     = 0;
        $total_med_Amount     = 0;
        $total_state_Amount   = 0;
        $total_FUTA_Amount    = 0;
        $total_SUTA_Amount    = 0;
        $total_TotWH          = 0;
        $total_soc_Amount     = 0;

        while ($row = $db->sql_fetchrow($result)) {

            $gross_Amount = number_format($row['invoice_amount'],2);

            //$netAmount = $row['invoice_amount'] - $row['fed'] - $row['med'] - $row['state'] - $row['FUTA'] - $row['SUTA']; 
            $netAmount = $row['invoice_amount'] - $row['fed'] - $row['soc'] - $row['med'] -$row['state'] - $row['deductions'];
            $total_netAmount      += $netAmount;
            $netAmount             = number_format($netAmount,2);
            $total_no_of_hours    += $row['no_of_hours'];
            $total_invoice_Amount += $row['invoice_amount'];
            $total_fed_Amount     += $row['fed'];
            $total_soc_Amount     += $row['soc'];
            $total_med_Amount     += $row['med'];
            $total_state_Amount   += $row['state'];

            $SQLtaxInvoice = "
            SELECT FUTA
                  ,SUTA
            FROM invoice
            WHERE invoice_type = 'Employer Tax'
            AND source_invoice_id = {$row['invoice_id']}
            ";
            $resulttaxInvoice = $db->sql_query($SQLtaxInvoice);
            $rowtaxInvoice    = $db->sql_fetchrow($resulttaxInvoice);

            $FUTA  = $rowtaxInvoice['FUTA'];
            if($FUTA == ''){
                $FUTA = 0;
            }

            $FUTA = number_format($FUTA,2);

            $SUTA  = $rowtaxInvoice['SUTA'];
            if($SUTA == ''){
                $SUTA = 0;
            }

            $SUTA = number_format($SUTA,2);

            $total_FUTA_Amount    += $rowtaxInvoice['FUTA'];
            $total_SUTA_Amount    += $rowtaxInvoice['SUTA'];

            $TotWH        = $row['fed'] + $row['soc'] + $row['med'] + $row['state'];
            $total_TotWH += $TotWH;

            $sqlReceipt = "
            SELECT r.date
                  ,r.mode_of_payment
            FROM `invoice_receipt_history` ir
            LEFT JOIN `receipt` r ON (ir.receipt_id = r.receipt_id) 
            WHERE ir.invoice_id = {$row['invoice_id']}
            AND r.receipt_status != 'Cancelled'
            ";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $rowReceipt    = $db->sql_fetchrow($resultReceipt);

            $receiptDate =  $fn->getCPDate($rowReceipt['date'],'m/d/Y');
            $start_date  =  $fn->getCPDate($row['start_date'],'m/d/Y');
            $end_date    =  $fn->getCPDate($row['end_date'],'m/d/Y');

            $colc = 0;
            $rowc++;
            
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['candidate_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['start_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['end_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['work_state']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_hours']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$gross_Amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$row['fed']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$row['soc']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$row['med']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$row['state']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$FUTA);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$SUTA);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$netAmount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$TotWH);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $receiptDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowReceipt['mode_of_payment']);
        }

        $total_no_of_hours    = number_format($total_no_of_hours,2);
        $total_invoice_Amount = number_format($total_invoice_Amount,2);
        $total_fed_Amount     = number_format($total_fed_Amount,2);
        $total_med_Amount     = number_format($total_med_Amount,2);
        $total_state_Amount   = number_format($total_state_Amount,2);
        $total_FUTA_Amount    = number_format($total_FUTA_Amount,2);
        $total_SUTA_Amount    = number_format($total_SUTA_Amount,2);
        $total_netAmount      = number_format($total_netAmount,2);
        $total_soc_Amount     = number_format($total_soc_Amount,2);
        $total_TotWH          = number_format($total_TotWH,2);

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_no_of_hours);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_invoice_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_fed_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_soc_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_med_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_state_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_FUTA_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_SUTA_Amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_netAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '$'.$total_TotWH);

        $actSheet->getStyle("A{$rowc}:Q{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}