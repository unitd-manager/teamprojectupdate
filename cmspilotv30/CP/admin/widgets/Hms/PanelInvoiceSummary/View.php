<?
class CP_Admin_Widgets_Hms_PanelInvoiceSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $company_id = $fn->getReqParam('company_id');

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('location_id');

        $sqlCompany = "
        SELECT company_id
              ,company_name
        FROM company
        WHERE company_id = '{$company_id}'
        ";
        $resultCompany = $db->sql_query($sqlCompany);
        $rowCompany = $db->sql_fetchrow($resultCompany);
        $pdf = '';
        $exportPDFLink = '';
        if($rowCompany['company_name'] == 'MAJLIS BANDARAYA PETALING JAYA') {
            $exportToPdfMBPJLink = "index.php?_topRm=reports&module=hms_reports&_spAction=exportToPdfMBPJ&company_id={$rowCompany['company_id']}&start_date={$start_date}&end_date={$end_date}&monthVal={$monthVal}&yearVal={$yearVal}&site_id={$site_id}&showHTML=0";
            $pdf = "<a href='{$exportToPdfMBPJLink}' target='blank' class='exportPdfLink button'>
                        <u1>Export to Pdf MBPJ</u1>
                    </a>";
        }elseif($rowCompany['company_name'] == 'SYARIKAT BEKALAN AIR SELANGOR SDN BHD'){
            $exportToPdfSyabasLink = "index.php?_topRm=reports&module=hms_reports&_spAction=exportToPdfSyabas&company_id={$rowCompany['company_id']}&start_date={$start_date}&end_date={$end_date}&monthVal={$monthVal}&yearVal={$yearVal}&site_id={$site_id}&showHTML=0";
            $pdf = "<a href='{$exportToPdfSyabasLink}' target='blank' class='exportPdfLink button'>
                        <u1>Export to Pdf Syabas</u1>
                    </a>";
        }elseif($rowCompany['company_name'] == 'PM CARE SDN BHD'){
            $exportToPdfSDNLink = "index.php?_topRm=reports&module=hms_reports&_spAction=exportToPdfSDN&company_id={$rowCompany['company_id']}&start_date={$start_date}&end_date={$end_date}&monthVal={$monthVal}&yearVal={$yearVal}&site_id={$site_id}&showHTML=0";
            $pdf = "<a href='{$exportToPdfSDNLink}' target='blank' class='exportPdfLink button'>
                        <u1>Export to Pdf SDN</u1>
                    </a>";
        }

        $text = "";
        if($company_id != ''){
            $text = "
            {$pdf}
            <h2>Panel Invoice Summary</h2>
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

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $company_id      = $fn->getReqParam('company_id');
        $site_id         = $fn->getReqParam('location_id');

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

                    $SQLIt = "
                    SELECT it.*
                    FROM invoice_item it
                    WHERE it.invoice_id = '{$rowInv['invoice_id']}'
                      AND it.record_type = 'Treatment'
                    ";
                    $resultIt = $db->sql_query($SQLIt);
                    $treatment = '';

                    while ($rowIt = $db->sql_fetchrow($resultIt)) {
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