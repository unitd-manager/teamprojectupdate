<?
class CP_Admin_Widgets_Labsg_MasterFinanceSummaryReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');
        
        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='2'>Summary</th>
                </thead>
                <tr>
                    <td>Start Date : {$start_date_formatted}</td>
                    <td>End Date : {$end_date_formatted}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
                        <th>Mode of Payment</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn         = Zend_Registry::get('fn');
        $db         = Zend_Registry::get('db');
        $cpCfg      = Zend_Registry::get('cpCfg');
        $dateUtil   = Zend_Registry::get('dateUtil');

        $site_id      = $fn->getSessionParam('cp_site_id');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }
        
        $rows = '';
        $grand_total = 0;
        $total_payment_mode = 0;
        $total_for_payment_mode = 0;
        $amount = 0;
        foreach($this->model->dataArray as $row){
            $amount = number_format($row['receipt_amount'], 2);
            $rows .= "
            <tr>
                <td>{$row['mode_of_payment']}</td>
                <td class='txtRight'>{$amount}</td>
            </tr>
            ";

            $total_payment_mode += $row['receipt_amount'];
        }

        $total_for_payment_mode = number_format($total_payment_mode, 2);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND inv.site_id = {$site_id}";
        }

        $SQLInv = "
        SELECT inv.*
        ,(SELECT SUM(invh.amount)
        FROM invoice_receipt_history invh
        LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
        WHERE invh.invoice_id = inv.invoice_id
          AND rcp.receipt_status = 'Paid'
        ) AS total_amount_paid
        FROM invoice inv
        WHERE inv.status != 'Cancelled'
        AND inv.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
        {$appendSql}
        ";
        $resultInv = $db->sql_query($SQLInv);
        $totalBalanceAmount  = 0;
        $balance_amount = 0;
        $discount_amount = 0;
        while ($rowInv = $db->sql_fetchrow($resultInv)) {
            $invoice_amount = $rowInv['invoice_amount'];
            $balance_amount += $invoice_amount - $rowInv['total_amount_paid'] - $rowInv['discount'];
            $discount_amount += $rowInv['discount'];
        }
        
        $totalBalanceAmount  = number_format($balance_amount,2);
        $totalDiscountAmount = number_format($discount_amount,2);

        $grand_total = $balance_amount + $total_payment_mode;
        $grand_total_formatted = number_format($grand_total, 2);
        
        $grand_total_text = "
        <tr bgcolor=\"#B6E5F9\">
            <td class='highlight'><strong>Total of Payment & Outstanding</strong></td>
            <td class='txtRight highlight'><strong>{$grand_total_formatted}</strong></td>
        </tr>
        ";

        $totalUnbilledVisitForCompany = $this->model->getTotalUnbilledVisitForCompany($start_date, $end_date); 
        $totalUnbilledVisitForIndividual = $this->model->getTotalUnbilledVisitForIndividual($start_date, $end_date); 
        $formattedTotalUnbilledVisitForCompany    = number_format($totalUnbilledVisitForCompany, 2);
        $formattedTotalUnbilledVisitForIndividual = number_format($totalUnbilledVisitForIndividual, 2);
        $total_sales = $grand_total - $discount_amount + $totalUnbilledVisitForCompany + $totalUnbilledVisitForIndividual;
        //$formattedTotalPatientVisit               = number_format($this->model->getTotalPatientVisit($start_date, $end_date), 2);
        $formattedTotalPatientVisit = number_format($total_sales, 2);

        $text = "
        {$rows}
        <tr bgcolor=\"#B6E5F9\">
            <td class='highlight'><strong>Total</strong></td>
            <td class='txtRight highlight'><strong>{$total_for_payment_mode}</strong></td>
        </tr>
        <tr>
            <td class='highlight'>Billing Outstanding</td>
            <td class='txtRight highlight'><strong>{$totalBalanceAmount}</strong></td>
        </tr>
        {$grand_total_text}
        <tr>
            <td colspan='2'></td>
        </tr>
        <tr bgcolor=\"#B6E5F9\">
            <td class='highlight'><strong>Total Discount</strong></td>
            <td class='txtRight highlight'><strong>{$totalDiscountAmount}</strong></td>
        </tr>
        <tr>
            <td colspan='2'></td>
        </tr>
        <tr bgcolor=\"#B6E5F9\">
            <td class='highlight'><strong>Total Unbilled Revenue</strong></td>
            <td class='highlight'></td>            
        </tr>
        <tr>
            <td class='highlight'>Company</td>
            <td class='txtRight highlight'><strong>{$formattedTotalUnbilledVisitForCompany}</strong></td>
        </tr>
        <tr>
            <td class='highlight'>Individual</td>
            <td class='txtRight highlight'><strong>{$formattedTotalUnbilledVisitForIndividual}</strong></td>
        </tr>
        <tr>
            <td colspan='2'></td>
        </tr>
        <tr bgcolor=\"#B6E5F9\">
            <td class='highlight'><strong>Total Sales</strong></td>
            <td class='txtRight highlight'><strong>{$formattedTotalPatientVisit}</strong></td>            
        </tr>
        ";

        return $text;
    }
}