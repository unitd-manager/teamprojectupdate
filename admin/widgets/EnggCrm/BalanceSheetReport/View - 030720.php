<?
class CPL_Admin_Widgets_EnggCrm_BalanceSheetReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $start_date      = $fn->getReqParam('start_date');
        $end_date        = $fn->getReqParam('end_date');
        $monthVal        = $fn->getReqParam('month');
        $yearVal         = $fn->getReqParam('year');

        $month           = date('m');
        $year            = date('Y');
        $current_date    = date('Y-m-d');

        if ($start_date != '' && $end_date == '') {
            $end_date = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
        } else if ($start_date != '' && $end_date != '') {
        } else if ($start_date == '' && $end_date == ''){
            $month = $monthVal;
            $year = $yearVal;

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
        }
        
        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD MMM YYYY');
        $end_date_formatted = $dateUtil->formatDate($end_date, 'DD MMM YYYY');

        $text = "
        <h2 class='txtCenter reportTitle'><strong>Balance Sheet Report between " . $start_date_formatted . " AND " . $end_date_formatted ."</strong></h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
                        <th width=50%>Expense</th>
						<th width=50%>Income</th>
					</tr>
				</thead>
				<tbody>
					{$this->getRowsHTML()}
				</tbody>
			</table>
		</div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $total_amount_visit = 0;
        $totaltestamount = 0;
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $startDateAppendSql = '';

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
        } else if ($start_date == '' && $end_date == ''){
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
              ,o.record_type
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE i.status != 'Cancelled'
          {$startDateAppendSql}
          {$monthValAppendSql}
          {$yearValAppendSql}
          {$appendSql}
        ORDER BY i.invoice_date ASC
        ";
        $resultSub = $db->sql_query($SQLSub);
        $count_invoice = 1;
        $invoiceRow = '';
        while ($rowSub = $db->sql_fetchrow($resultSub)) {
            $gst_amount = 0;
            $total_invoice_amount = 0;
            if ($rowSub['gst_percentage'] > 0) {
                $gst_amount = round((($rowSub['invoice_amount'] * $rowSub['gst_percentage']) / 100), 2);
            }
            $total_invoice_amount = $rowSub['invoice_amount'] + $gst_amount;
            $total_invoice_amount_formatted = number_format($total_invoice_amount, 2);
            $total_amount_visit += $total_invoice_amount;

            if ($count_invoice == 1) {
                $invoiceRow .= "
                <tr>
                    <td width='65%'><strong>Invoice Code</strong></td>
                    <td width='35%' align='right'><strong>Amount</strong></td>
                </tr>";
            }

            $inv_date = $fn->getCPDate($rowSub['invoice_date'], 'yy');
            $invoice_code = $rowSub['invoice_code'] . '/' . $inv_date;
            $invoiceRow .= "
            <tr>
                <td>{$invoice_code}</td>
                <td align='right'>{$total_invoice_amount_formatted}</td>
            </tr>";
            $count_invoice++;
        }
        /* Invoice display STOP */

        /* Expense display START */
        $sqlgroup = "
        SELECT expense_group_id 
              ,title
        FROM expense_group
        ";
        $resultgroup = $db->sql_query($sqlgroup);
        $numRows     = $db->sql_numrows($resultgroup);
        $expense_group = '';
        $amount = 0;
        $expense_amount ='';
        $overAllExpense = 0;
        $rows = '';
        $count = 1;
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
            $amount = 0;
            while ($rowexp = $db->sql_fetchrow($resultexp)) {
                $amount += $rowexp['amount'] + $rowexp['gst_amount'] + $rowexp['service_charge_amount'];
            }
            $amountFormat = number_format($amount, 2);

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
            $count_expense = 1;
            while ($rowexp1 = $db->sql_fetchrow($resultexp1)) {
                $expense_date = $dateUtil->formatDate($rowexp1['date'], 'DD-MM-YYYY');
                $total_row_amount = number_format($rowexp1['amount'] + $rowexp1['gst_amount'] + $rowexp1['service_charge'], 2);

                if ($count_expense == 1) {
                $subtitle .= "
                <tr>
                    <td><strong>Company Name / Description / Date</strong></td>
                    <td align='right'><strong>Amount before GST</strong></td>
                    <td align='right'><strong>Service Charge</strong></td>
                    <td align='right'><strong>GST Amount</strong></td>
                    <td align='right'><strong>Total Amount</strong></td>
                </tr>";
                }
                $subtitle .= "
                <tr>
                    <td>{$rowexp1['company_name']} => {$rowexp1['description']} => [{$expense_date}]</td>
                    <td align='right'>{$rowexp1['amount']}</td>
                    <td align='right'>{$rowexp1['service_charge']}</td>
                    <td align='right'>{$rowexp1['gst_amount']}</td>
                    <td align='right'>{$total_row_amount}</td>
                </tr>";
                $count_expense++;
            }
            $expense_grp_title = $rowgroup['title'];
            /* Expense display STOP */

            /* Payslip & CPF Display START */
            $payslipDateAppendSql = '';
            if ($start_date != '' && $end_date == '') {
                $payslipDateAppendSql = "AND (pm.payslip_start_date BETWEEN '{$start_date}' AND '{$current_date}')
                                         AND (pm.payslip_end_date BETWEEN '{$start_date}' AND '{$current_date}')";
            } else if ($start_date == '' && $end_date != ''){
                $payslipDateAppendSql = "AND (pm.payslip_start_date BETWEEN '{$start_date}' AND '{$end_date}')
                                         AND (pm.payslip_end_date BETWEEN '{$start_date}' AND '{$end_date}')";
            } else if ($start_date != '' && $end_date != '') {
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
            }

            $sqlexp2 = "
            SELECT pm.*
                  ,e.first_name
            FROM payroll_management pm
            LEFT JOIN employee e ON (pm.employee_id = e.employee_id)
            WHERE pm.status != 'Cancelled'
            {$appendSqlSite}
            {$payslipDateAppendSql}
            ORDER BY pm.payroll_year ASC, pm.payroll_month ASC, e.first_name ASC
            ";
            $resultexp2 = $db->sql_query($sqlexp2);
            $payslipRow = '';
            $cpfRow = '';
            $total_payslip_amount = 0;
            $total_cpf_contribution = 0;
            $countCpf = 1;
            while ($rowexp2 = $db->sql_fetchrow($resultexp2)) {
                $cpf_contribution = 0;
                /* Total Pay claculation */
                $OT  = $rowexp2['ot_hours'] * $rowexp2['overtime_pay_rate'];
                $gross_pay = $rowexp2['basic_pay'] + $rowexp2['ot_amount'] + $rowexp2['commission'] + $rowexp2['allowance1'] + $rowexp2['allowance2'] + $rowexp2['allowance3'] + $rowexp2['allowance4'] + $rowexp2['allowance5'];
                $total_allowance = $rowexp2['allowance1'] + $rowexp2['allowance2'] + $rowexp2['allowance3'] + $rowexp2['allowance4'] + $rowexp2['allowance5'];
                $total_deduction = $rowexp2['cpf_employee'] + $rowexp2['sdl'] + $rowexp2['loan_amount'] + $rowexp2['income_tax_amount'] + $rowexp2['pay_cdac'] + $rowexp2['pay_sinda'] + $rowexp2['pay_mbmf'] + $rowexp2['pay_eucf'] + $rowexp2['deduction1'] + $rowexp2['deduction2'] + $rowexp2['deduction3'] + $rowexp2['loan_deduction'];
                $net_total = $gross_pay - $total_deduction + $rowexp2['reimbursement'];
                $net_total_formatted = number_format($net_total, 2);

                $cpf_contribution = $rowexp2['cpf_employee'] + $rowexp2['cpf_employer'];
                $cpf_contribution_formatted = number_format($cpf_contribution, 2);

                $payslipRow .= "
                <tr>
                    <td>{$rowexp2['first_name']} => [{$rowexp2['payroll_month']}/{$rowexp2['payroll_year']}]</td>
                    <td align='right'>{$net_total_formatted}</td>
                </tr>";

                if ($cpf_contribution > 0) {
                    $cpfRow .= "
                    <tr>
                        <td>{$countCpf} {$rowexp2['first_name']} => [{$rowexp2['payroll_month']}/{$rowexp2['payroll_year']}]</td>
                        <td align='right'>{$cpf_contribution_formatted}</td>
                    </tr>";
                    $countCpf++;
                }

                $total_payslip_amount += $net_total;
                $total_cpf_contribution += $cpf_contribution;
            }
            $total_payslip_amount_format = number_format($total_payslip_amount, 2);
            $total_cpf_contribution_format = number_format($total_cpf_contribution, 2);
            /* Payslip & CPF display STOP */

            /* Purchase Order display START */
            if ($start_date != '' && $end_date == '') {
                $startDateAppendSql = "AND po.po_date >= '{$start_date}' AND po.po_date <= '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $startDateAppendSql = "AND po.po_date >= '{$start_date}' AND po.po_date <= '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
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
                    $sub_total_formatted = number_format($subtotal_amount, 2);

                    $po_date = $dateUtil->formatDate($rowPo['po_date'], 'DD-MM-YYYY');

                    $poRow .= "
                    <tr>
                        <td>{$po_date}</td>
                        <td align='right'>{$sub_total_formatted}</td>
                    </tr>";

                }
            }
            $total_po_amount_formatted = number_format($total_po_amount, 2);
            /* Purchase Order display STOP */

            /* Credit Note display START */
            if ($start_date != '' && $end_date == '') {
                $startDateAppendSql = "AND cn.date >= '{$start_date}' AND cn.date <= '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $startDateAppendSql = "AND cn.date >= '{$start_date}' AND cn.date <= '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND cn.date >= '{$start_date}' AND cn.date <= '{$end_date}'";
            } else {
                if($monthVal != ''){
                    $month = $monthVal;
                }

                if($yearVal != ''){
                    $year = $yearVal;
                }

                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $payslipDateAppendSql = "AND (cn.date BETWEEN '{$start_date}' AND '{$end_date}')";
            }

            $sqlCn = "
            SELECT DISTINCT cn.credit_note_id, cn.date, cn.amount, cn.gst_amount FROM credit_note cn
            WHERE cn.credit_note_id != ''
              {$startDateAppendSql}
            ";
            $resultCn = $db->sql_query($sqlCn);
            $cnRow = '';
            $total_cn_amount = 0;
            while ($rowCn = $db->sql_fetchrow($resultCn)) {
                $cn_date = $dateUtil->formatDate($rowCn['date'], 'DD-MM-YYYY');
                $subtotal_amount_cn = $rowCn['amount'] + $rowCn['gst_amount'];
                $sub_total_formatted = number_format($subtotal_amount_cn, 2);

                $cnRow .= "
                <tr>
                    <td>{$cn_date}</td>
                    <td align='right'>{$sub_total_formatted}</td>
                </tr>";
                $total_cn_amount += $subtotal_amount_cn;
            }
            $total_cn_amount_formatted = number_format($total_cn_amount, 2);
            /* Credit Note display STOP */

            $rows .= "
            <tr>
                <td width = 85% class='expenseDetailsHead'>
                <div class='expenseDetails'><strong>{$expense_grp_title}</strong></div>
                <div class='subTitles'><table width=100%>{$subtitle}</table></div>
                </td>
                <td width = 15% align='right'>{$amountFormat}</td>
            </tr>
            ";
            
            if ($count == $numRows) {
                $rows .= "
                <!-- PAYROLL DATA -->
                <tr>
                    <td width = 80% class='expenseDetailsHead'>
                    <div class='expenseDetails'><strong>+ PAYSLIPS</strong></div>
                    <div class='subTitles'><table width=100%>{$payslipRow}</table></div>
                    </td>
                    <td width = 20% align='right'>
                    {$total_payslip_amount_format}
                    </td>
                </tr>

                <!-- CPF DATA -->
                <tr>
                    <td width = 80% class='expenseDetailsHead'>
                    <div class='expenseDetails'><strong>+ CPF CONTRIBUTION</strong></div>
                    <div class='subTitles'><table width=100%>{$cpfRow}</table></div>
                    </td>
                    <td width = 20% align='right'>
                    {$total_cpf_contribution_format}
                    </td>
                </tr>

                <!-- PURCHASE ORDER DATA -->
                <tr>
                    <td width = 80% class='expenseDetailsHead'>
                    <div class='expenseDetails'><strong>+ TOTAL PURCHASE ORDER</strong></div>
                    <div class='subTitles'><table width=100%>{$poRow}</table></div>
                    </td>
                    <td width = 20% align='right'>
                    {$total_po_amount_formatted}
                    </td>
                </tr>

                <!-- CREDIT NOTE DATA -->
                <tr>
                    <td width = 80% class='expenseDetailsHead'>
                    <div class='expenseDetails'><strong>+ TOTAL CREDIT NOTE</strong></div>
                    <div class='subTitles'><table width=100%>{$cnRow}</table></div>
                    </td>
                    <td width = 20% align='right'>
                    {$total_cn_amount_formatted}
                    </td>
                </tr>
                ";
                $overAllExpense += $total_payslip_amount;
                $overAllExpense += $total_cpf_contribution;
                $overAllExpense += $total_po_amount;
                $overAllExpense += $total_cn_amount;
            }

            $overAllExpense += $amount;
            $count++;
        }

        $overAllIncome = $total_amount_visit;
        $overAllProfit = $overAllIncome - $overAllExpense;
        $overAllIncome = number_format($overAllIncome, 2);
        $overAllExpense = number_format($overAllExpense, 2);
        $overAllProfit = number_format($overAllProfit, 2);
        $total_amount_visit = number_format($total_amount_visit, 2);
        
        $text = "
        <tr>

            <td class='incomeReport'>
                <table width=100%>
                    {$rows}
                </table>
            </td>
            <td class='incomeReport'>
                <table width=100%>
                    <!-- INVOICE DATA -->
                    <tr>
                        <td width = 75% class='invoiceDetailsHead'>
                        <div class='invoiceDetails'><strong>Sales Accounts</strong></div>
                        <!--<div class='invoiceTitles'><table width=100%>{$invoiceRow}</table></div>-->
                        </td>
                        <td width = 25% align='right'>{$total_amount_visit}</td>
                    </tr>
                </table>
            </td>        </tr>
        <tr>
            <td class='totalValue'>
                <div class='float_left'>Total</div> <div class='float_right'>{$overAllIncome}</div>
            </td>
            <td class='totalValue' align='right'>
                <div class='float_left'>Total</div> <div class='float_right'>{$overAllExpense}</div>
            </td>
        </tr>
        <tr><td colspan='2'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='2' class='totalValue lastRowBgColor'>
                <div class='float_left'><strong>BALANCE</strong></div>
                <div class='float_right'><strong>{$overAllProfit}</strong></div>
            </td>
        </tr>
        ";

        return $text;
    }
}