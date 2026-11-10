<?
class CPL_Admin_Widgets_EnggCrm_ProfitLossReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT i.invoice_date
              ,DATE_FORMAT(i.invoice_date, '%W') AS day
              ,i.invoice_id
        FROM invoice i
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
        $employee_id    = $fn->getReqParam('employee_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');

        if($tv['module'] == 'common_dashboard'){
            /*$start_date = date('Y-m-d', mktime (0,0,0,date("m"), date("d"), date("Y")));
            $end_date = $current_date;*/
            //$start_date = $year . '-' . $month . '-' . '01';
            //$end_date = $year . '-' . $month . '-' . '31';
            $last7days  = date('Y-m-d', strtotime('today - 7 days'));
            $start_date = $last7days;
            $end_date   = $current_date;
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date == '' && $end_date == ''){
            /*
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
            */
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        /*
        if ($monthVal != '') {
        }
        if ($yearVal != '') {
        }
        */

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "i.site_id = {$site_id}" ;
            }
        }

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy        = "i.invoice_date";
        $searchVar->sortOrder      = "i.invoice_date desc";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_balanceSheetReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "BalanceSheet__" . date("d-m-Y") . ".xls";

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
        $startDateAppendSql = '';
        $employeeIdAppendSql = '';
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $employee_id    = $fn->getReqParam('employee_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $totalOverAll = 0;
        $totalOverAllCase =0;
        $totalOverAllConsult = 0;
        $totalOverAllCaseConsult =0;

        $headStyle = array(
            'font' => array('bold' => true)
        );

        $styleHeader = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array('bold' => true)
                );

        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->mergeCells("A1:G1");
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($styleHeader);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'BALANCE SHEET REPORT');

        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Income');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Expense');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');

        /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $total_amount_visit = 0;
        $totaltestamount = 0;
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $startDateAppendSql = '';
        $overAllExpense = 0;

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $start_date      = $fn->getReqParam('start_date');
        $end_date        = $fn->getReqParam('end_date');
        $monthVal        = $fn->getReqParam('month');
        $yearVal         = $fn->getReqParam('year');
        
        $month           = date('m');
        $year            = date('Y');
        $current_date    = date('Y-m-d');

        /* Invoice display START */
        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND i.site_id = {$cpSiteIdSession}";
        }

        $SQLSub = "
        SELECT i.*
        FROM invoice i
        WHERE i.status != 'Cancelled'
          {$startDateAppendSql}
          {$monthValAppendSql}
          {$yearValAppendSql}
          {$appendSql}
        ";
        $resultSub = $db->sql_query($SQLSub);
        while ($rowSub = $db->sql_fetchrow($resultSub)) {
            $gst_amount = 0;
            $total_invoice_amount = 0;
            if ($rowSub['gst_percentage'] > 0) {
                $gst_amount = round((($rowSub['invoice_amount'] * $rowSub['gst_percentage']) / 100), 2);
            }
            $total_invoice_amount = $rowSub['invoice_amount'] + $gst_amount;
            $total_amount_visit += $total_invoice_amount;
        }
        /* Invoice display STOP */

        /* Expense display START */
        $sqlgroup = "
        SELECT expense_group_id 
              ,title
        FROM expense_group
        ";
        $resultgroup = $db->sql_query($sqlgroup);
        $amount = 0;
        while ($rowgroup    = $db->sql_fetchrow($resultgroup)) {
            $appendSqlSite = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
            }

            $startDateAppendSql = '';
            if ($start_date != '' && $end_date == '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else {
                if($monthVal != ''){
                    $month = $monthVal;
                }

                if($yearVal != ''){
                    $year = $yearVal;
                }

                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            }

            $sqlexp = "
            SELECT SUM(e.amount) AS amount
                  ,SUM(e.gst_amount) AS gst_amount
                  ,SUM(e.service_charge) AS service_charge_amount
                  ,e.group
                  ,es.title AS sub_title
            FROM expense e
            LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
            LEFT JOIN supplier s ON (e.company_id = s.supplier_id)
            WHERE e.group = {$rowgroup['expense_group_id']}
              AND s.supplier_type = 'Supplier Accounts'
            {$appendSqlSite}
            {$startDateAppendSql}
            GROUP BY e.group
            ";
            $resultexp = $db->sql_query($sqlexp);
            while ($rowexp = $db->sql_fetchrow($resultexp)) {
                $amount += $rowexp['amount'] + $rowexp['gst_amount'] + $rowexp['service_charge_amount'];
            }
        }

        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'INVOICE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_amount_visit);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'EXPENSE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);

        // Summary display of Indirect Expense
        $sqlgroup = "
        SELECT expense_group_id 
              ,title
        FROM expense_group
        ";
        $resultgroup = $db->sql_query($sqlgroup);
        while ($rowgroup    = $db->sql_fetchrow($resultgroup)) {
            $appendSqlSite = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
            }

            $startDateAppendSql = '';
            if ($start_date != '' && $end_date == '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else {
                if($monthVal != ''){
                    $month = $monthVal;
                }

                if($yearVal != ''){
                    $year = $yearVal;
                }

                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            }

            $sqlexp1 = "
            SELECT e.amount
                  ,e.gst_amount
                  ,e.service_charge
                  ,e.group
                  ,es.title AS sub_title
                  ,e.date
                  ,e.description
                  ,s.company_name
            FROM expense e
            LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
            LEFT JOIN supplier s ON (e.company_id = s.supplier_id)
            WHERE e.group = {$rowgroup['expense_group_id']}
              AND s.supplier_type = 'Supplier Accounts'
            {$appendSqlSite}
            {$startDateAppendSql}
            ";
            $resultexp1 = $db->sql_query($sqlexp1);
            $subtitle = '';
            $total_row_amount = 0;
            while ($rowexp1 = $db->sql_fetchrow($resultexp1)) {
                $expense_date = $dateUtil->formatDate($rowexp1['date'], 'DD-MM-YYYY');
                $total_row_amount = $rowexp1['amount'] + $rowexp1['gst_amount'] + $rowexp1['service_charge'];

                $rowc++;
                $colc = 0;
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowexp1['company_name'] .' => '. $rowexp1['description'] .' => '. $expense_date);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowexp1['amount']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowexp1['service_charge']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowexp1['gst_amount']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_row_amount);
            }
        }

        /* Payslip & CPF Display START */
        $payslipDateAppendSql = '';
        if ($start_date != '' && $end_date == '') {
            $payslipDateAppendSql = "AND (pm.payslip_start_date BETWEEN '{$start_date}' AND '{$current_date}')
                                     AND (pm.payslip_end_date BETWEEN '{$start_date}' AND '{$current_date}')";
            //$payslipDateAppendSql = "AND (pm.generated_date BETWEEN '{$start_date}' AND '{$current_date}')";
        } else if ($start_date == '' && $end_date != ''){
            $payslipDateAppendSql = "AND (pm.payslip_start_date BETWEEN '{$start_date}' AND '{$end_date}')
                                     AND (pm.payslip_end_date BETWEEN '{$start_date}' AND '{$end_date}')";
            /*
            $start_date = $year . '-' . $month . '-' . '01';
            $payslipDateAppendSql = "AND (pm.generated_date BETWEEN '{$start_date}' AND '{$end_date}')";
            */
        } else if ($start_date != '' && $end_date != '') {
            //$payslipDateAppendSql = "AND (pm.generated_date BETWEEN '{$start_date}' AND '{$end_date}')";
            $payslipDateAppendSql = "AND (pm.payslip_start_date BETWEEN '{$start_date}' AND '{$end_date}')
                                     AND (pm.payslip_end_date BETWEEN '{$start_date}' AND '{$end_date}')";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $payslipDateAppendSql = "AND (pm.payslip_start_date BETWEEN '{$start_date}' AND '{$end_date}')
                                     AND (pm.payslip_end_date BETWEEN '{$start_date}' AND '{$end_date}')";
            //$payslipDateAppendSql = "AND (pm.generated_date BETWEEN '{$start_date}' AND '{$end_date}')";
        }

        $sqlexp2 = "
        SELECT pm.*
              ,e.first_name
        FROM payroll_management pm
        LEFT JOIN employee e ON (pm.employee_id = e.employee_id)
        WHERE pm.status != 'Cancelled'
        {$appendSqlSite}
        {$payslipDateAppendSql}
        ORDER BY pm.payroll_year ASC, pm.payroll_month ASC
        ";
        $resultexp2 = $db->sql_query($sqlexp2);
        $total_payslip_amount = 0;
        $total_cpf_contribution = 0;
        while ($rowexp2 = $db->sql_fetchrow($resultexp2)) {

            /* Total Pay calculation */
            $OT  = $rowexp2['ot_hours'] * $rowexp2['overtime_pay_rate'];
            $gross_pay = $rowexp2['basic_pay'] + $rowexp2['ot_amount'] + $rowexp2['commission'] + $rowexp2['allowance1'] + $rowexp2['allowance2'] + $rowexp2['allowance3'] + $rowexp2['allowance4'] + $rowexp2['allowance5'];
            $total_allowance = $rowexp2['allowance1'] + $rowexp2['allowance2'] + $rowexp2['allowance3'] + $rowexp2['allowance4'] + $rowexp2['allowance5'];
            $total_deduction = $rowexp2['cpf_employee'] + $rowexp2['sdl'] + $rowexp2['loan_amount'] + $rowexp2['income_tax_amount'] + $rowexp2['pay_cdac'] + $rowexp2['pay_sinda'] + $rowexp2['pay_mbmf'] + $rowexp2['pay_eucf'] + $rowexp2['deduction1'] + $rowexp2['deduction2'] + $rowexp2['deduction3'] + $rowexp2['loan_deduction'];
            $net_total = $gross_pay - $total_deduction + $rowexp2['reimbursement'];

            $cpf_contribution = $rowexp2['cpf_employee'] + $rowexp2['cpf_employer'];

            $total_payslip_amount += $net_total;
            $total_cpf_contribution += $cpf_contribution;
        }
        /* Payslip & CPF display STOP */

        // Summary display of Payslip
        $rowc++;
        $colc = 0;
        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PAYSLIPS');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_payslip_amount);

        /* SQL Condition from above summary */
        $sqlexp2 = "
        SELECT pm.*
              ,e.first_name
        FROM payroll_management pm
        LEFT JOIN employee e ON (pm.employee_id = e.employee_id)
        WHERE pm.status != 'Cancelled'
        {$appendSqlSite}
        {$payslipDateAppendSql}
        ORDER BY pm.payroll_year ASC, pm.payroll_month ASC
        ";
        $resultexp2 = $db->sql_query($sqlexp2);
        while ($rowexp2 = $db->sql_fetchrow($resultexp2)) {
            /* Total Pay calculation */
            $OT  = $rowexp2['ot_hours'] * $rowexp2['overtime_pay_rate'];
            $gross_pay = $rowexp2['basic_pay'] + $rowexp2['ot_amount'] + $rowexp2['commission'] + $rowexp2['allowance1'] + $rowexp2['allowance2'] + $rowexp2['allowance3'] + $rowexp2['allowance4'] + $rowexp2['allowance5'];
            $total_allowance = $rowexp2['allowance1'] + $rowexp2['allowance2'] + $rowexp2['allowance3'] + $rowexp2['allowance4'] + $rowexp2['allowance5'];
            $total_deduction = $rowexp2['cpf_employee'] + $rowexp2['sdl'] + $rowexp2['loan_amount'] + $rowexp2['income_tax_amount'] + $rowexp2['pay_cdac'] + $rowexp2['pay_sinda'] + $rowexp2['pay_mbmf'] + $rowexp2['pay_eucf'] + $rowexp2['deduction1'] + $rowexp2['deduction2'] + $rowexp2['deduction3'] + $rowexp2['loan_deduction'];
            $net_total = $gross_pay - $total_deduction + $rowexp2['reimbursement'];

            // Detail display of Payslip
            $rowc++;
            $colc = 0;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowexp2['first_name'] . ' => ['.$rowexp2['payroll_month'].'/'.$rowexp2['payroll_year']. ']');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_total);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }

        // Summary display of CPF
        $rowc++;
        $colc = 0;
        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CPF CONTRIBUTION');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_cpf_contribution);

        /* SQL Condition from above summary */
        $sqlexp2 = "
        SELECT pm.*
              ,e.first_name
        FROM payroll_management pm
        LEFT JOIN employee e ON (pm.employee_id = e.employee_id)
        WHERE pm.status != 'Cancelled'
        {$appendSqlSite}
        {$payslipDateAppendSql}
        ORDER BY pm.payroll_year ASC, pm.payroll_month ASC
        ";
        $resultexp2 = $db->sql_query($sqlexp2);
        while ($rowexp2 = $db->sql_fetchrow($resultexp2)) {

            $cpf_contribution = $rowexp2['cpf_employee'] + $rowexp2['cpf_employer'];
            if ($cpf_contribution > 0) {
                // Detail display of Payslip
                $rowc++;
                $colc = 0;
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowexp2['first_name'] . ' => ['.$rowexp2['payroll_month'].'/'.$rowexp2['payroll_year']. ']');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpf_contribution);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        /* Purchase Order display START */
        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND po.po_date >= '{$start_date}' AND po.po_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND po.po_date >= '{$start_date}' AND po.po_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND po.po_date >= '{$start_date}' AND po.po_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND po.po_date >= '{$start_date}' AND po.po_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $payslipDateAppendSql = "AND (po.po_date BETWEEN '{$start_date}' AND '{$end_date}')";
        }

        $sqlPo = "
        SELECT po.purchase_order_id, po.po_date FROM purchase_order po
        WHERE po.purchase_order_id != 'Cancelled'
          {$startDateAppendSql}
        ";
        $resultPo = $db->sql_query($sqlPo);
        $poRow = '';
        $total_po_amount = 0;
        while ($rowPo = $db->sql_fetchrow($resultPo)) {
            $sqlPop = "
            SELECT * FROM po_product
            WHERE purchase_order_id = {$rowPo['purchase_order_id']}
              AND status != 'Cancelled'
            ";
            $resultPop = $db->sql_query($sqlPop);
            $subtotal_amount = 0;
            while ($rowPop = $db->sql_fetchrow($resultPop)) {
                $subtotal_amount = $rowPop['quantity'] * $rowPop['amount'];
                $total_po_amount += $subtotal_amount;
            }
        }
        /* Purchase Order display STOP */

        // Purchase Order display
        $rowc++;
        $colc = 0;
        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL PURCHASE ORDER');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_po_amount);

        // Detail display of Purchase Order
        $sqlPo = "
        SELECT po.purchase_order_id, po.po_date FROM purchase_order po
        WHERE po.purchase_order_id != 'Cancelled'
          {$startDateAppendSql}
        ";
        $resultPo = $db->sql_query($sqlPo);
        $poRow = '';
        while ($rowPo = $db->sql_fetchrow($resultPo)) {
            $sqlPop = "
            SELECT * FROM po_product
            WHERE purchase_order_id = {$rowPo['purchase_order_id']}
              AND status != 'Cancelled'
            ";
            $resultPop = $db->sql_query($sqlPop);
            $subtotal_amount = 0;
            while ($rowPop = $db->sql_fetchrow($resultPop)) {
                $subtotal_amount = $rowPop['quantity'] * $rowPop['amount'];
                $po_date = $dateUtil->formatDate($rowPo['po_date'], 'DD-MM-YYYY');

                $rowc++;
                $colc = 0;
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $po_date);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $subtotal_amount);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        $total_expense = $amount + $total_payslip_amount + $total_cpf_contribution + $total_po_amount;

        $rowc++;
        $colc = 0;
        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL INCOME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_amount_visit);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL EXPENSE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_expense);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}