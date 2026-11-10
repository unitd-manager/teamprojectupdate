<?
class CP_Admin_Widgets_Project_DetailSummaryByClient_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id = $fn->getReqParam('company_id');

        $company_name = '';
        if($company_id == ''){
            $company_name = "<th>Client Name</th>";
        }

        $company_Title = '';
        if($company_id != ''){
            $SQLCompany = "
            SELECT company_name
            FROM  company
            WHERE company_id = {$company_id}
            ";
            $resultCompany = $db->sql_query($SQLCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $company_Title = "<b>{$rowCompany['company_name']}</b>";
        
        }else{
            $company_Title = 'Client';
        }

        $text = "
        <h2>Detail Summary By {$company_Title}</h2>
				<div class = 'tableOuter scroll-pane'>
				<table class='thinlist'>
					<thead>
						<tr>
							{$company_name}
							<th>Date</th>
							<th>Invoice Code</th>
							<th>Invoice Amount</th>
							<th>Paid</th>
							<th>Amount Due</th>
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
        $db      = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $location_id     = $fn->getReqParam('location_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $company_id      = $fn->getReqParam('company_id');

        $rows = '';
		$siteTitle = '' ;
        $totalInvoiceAmount = 0;
        $totalBalanceAmount = 0;
        $totalPaidAmount = 0;

        foreach($this->model->dataArray as $row){
            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.invoice_id = inv.invoice_id
              AND rcp.receipt_status = 'Paid'
            ) AS total_amount_paid
            FROM invoice inv
            LEFT JOIN `order` o ON (o.order_id = inv.order_id)
            WHERE o.company_id = {$row['company_id']}
            AND inv.status != 'Cancelled'
            ";

            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {
        		$invoice_amount = $rowInv['invoice_amount'];
        		$balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount, 2);
                $balance_amount = number_format($balance_amount, 2);
        		$rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid'], 2);

                $invoiceCode = $rowInv['invoice_code'];
                $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=enggCrm_order&record_id={$rowInv['order_id']}&_action=edit'>";

                $company_name = '';
                if($company_id == ''){
                    $company_name = "<td>{$row['company_name']}</td>";
                }

			    $rows .= "
				<tr>
					{$company_name}
					<td>{$fn->getCPDate($rowInv['invoice_date'], 'd-m-Y')}</td>
					<td>{$todaylink}{$invoiceCode}</td>
					<td align='right'>{$invoice_amount}</td>
					<td align='right'>{$rowInv['total_amount_paid']}</td>
					<td align='right'>{$balance_amount}</td>
				</tr>
				";
            }

        }

        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);
        $totalBalanceAmount = number_format($totalBalanceAmount,2);
        $totalPaidAmount    = number_format($totalPaidAmount,2);

        $total_th = '';
        if($company_id == ''){
            $total_th = "<th colspan='2'></th>";
        }else{
            $total_th = "<th></th>";
        }

        $text = "
        {$rows}
        <tr bgcolor=\"#A9A9A9\">
            {$total_th}
            <th>TOTAL</th>
            <th class='lastRowBgColor txtRight'>{$totalInvoiceAmount}</th>
            <th class='lastRowBgColor txtRight'>{$totalPaidAmount}</th>
            <th class='lastRowBgColor txtRight'>{$totalBalanceAmount}</th>
        </tr>
        ";

        return $text;
    }

}