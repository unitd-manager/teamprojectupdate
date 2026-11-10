<?
class CP_Admin_Widgets_Hms_CompanyInvoiceSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $company_id = $fn->getReqParam('company_id');

        $text = "";
        if($company_id != ''){
            $text = "
            <h2>Company Invoice Summary</h2>
            <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>IC / No.</th>
                        <th>Emp No.</th>
                        <th>Treatment</th>
                        <th>Fees</th>
                        <th>Paid</th>
                        <th>Amount Due</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    {$this->getRowsHTML()}
                </tbody>
            </table>
            </div>
            ";
        }

        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $location_id     = $fn->getReqParam('location_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $company_id      = $fn->getReqParam('company_id');
        $site_id         = $fn->getReqParam('site_id');

        $rows = '';
        $siteTitle = '' ;
        $totalInvoiceAmount = 0;
        $totalBalanceAmount = 0;
        $totalPaidAmount = 0;

        foreach($this->model->dataArray as $row){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                if($site_id != ''){
                    $appendSql = "AND inv.site_id = {$site_id}";
                }
            }

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
                //$invoice_amount = $rowInv['invoice_amount'] - $rowInv['discount'] - $rowInv['sales_return_amount'];
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

                $rows .= "
                <tr>
                    <td>{$fn->getCPDate($row['check_up_date'], 'd-m-Y')}</td>
                    <td>{$row['patient_name']}</td>
                    <td>{$row['nric']}</td>
                    <td>{$row['worker_id']}</td>
                    <td>{$treatment}</td>
                    <td align='right'>{$invoice_amount}</td>
                    <td align='right'>{$rowInv['total_amount_paid']}</td>
                    <td align='right'>{$balance_amount}</td>
                    <td></td>
                </tr>
                ";
            }
            }
        }

        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);
        $totalBalanceAmount = number_format($totalBalanceAmount,2);
        $totalPaidAmount    = number_format($totalPaidAmount,2);


        $text = "
        {$rows}
        <tr bgcolor=\"#A9A9A9\">
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>TOTAL</th>
            <th class='lastRowBgColor txtRight'>{$totalInvoiceAmount}</th>
            <th class='lastRowBgColor txtRight'>{$totalPaidAmount}</th>
            <th class='lastRowBgColor txtRight'>{$totalBalanceAmount}</th>
            <th></th>
        </tr>
        ";

        return $text;
    }
}