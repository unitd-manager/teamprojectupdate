<?
class CP_Admin_Widgets_ManPower_TaxReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Order Id</th>
                        <th>Candidate Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Work State</th>
                        <th>Hrs</th>
                        <th>Gross</th>
                        <th>Fed</th>
                        <th>SS</th>
                        <th>Med</th>
                        <th>State</th>
                        <th>FU</th>
                        <th>SU</th>
                        <th>Net</th>
                        <th>Tot W/H</th>
                        <th>Paid</th>
                        <th>CK #</th>
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
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $db = Zend_Registry::get('db');

        $rows = '';

        $serial_no = 0;
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

        foreach($this->model->dataArray as $row){
            
            $gross_Amount = number_format($row['invoice_amount'],2);

            //$netAmount = $row['invoice_amount'] - $row['fed'] - $row['med'] - $row['state'] - $row['FUTA'] - $row['SUTA']; 
            $net_Amount1 = $row['invoice_amount'] - $row['fed'] - $row['soc'] - $row['med'] -$row['state'] - $row['deductions'];
            $total_netAmount      += $net_Amount1;
            $net_Amount1           = number_format($net_Amount1,2);
            $total_no_of_hours    += $row['no_of_hours'];
            $total_invoice_Amount += $row['invoice_amount'];
            $total_fed_Amount     += $row['fed'];
            $total_med_Amount     += $row['med'];
            $total_soc_Amount     += $row['soc'];
            $total_state_Amount   += $row['state'];

            $SQLtaxInvoice = "
            SELECT FUTA
                  ,SUTA
            FROM invoice
            WHERE invoice_type = 'Employer Tax'
            AND status != 'Cancelled'
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

            $orderLink   = "index.php?_topRm=finance&module=manPower_order&order_id={$row['order_id']}&_action=edit";
            $linkToOrder = "<a href='{$orderLink}' target='_blank'><u>{$row['order_id']}</u></a>";

            $rows .= "
            <tr>
                <td>{$linkToOrder}</td>
                <td>{$row['candidate_name']}</td>
                <td>{$start_date}</td>
                <td>{$end_date}</td>
                <td>{$row['work_state']}</td>
                <td class='txtRight'>{$row['no_of_hours']}</td>
                <td class='txtRight'>{$currency_Symbol}{$gross_Amount}</td>
                <td class='txtRight'>{$currency_Symbol}{$row['fed']}</td>
                <td class='txtRight'>{$currency_Symbol}{$row['soc']}</td>
                <td class='txtRight'>{$currency_Symbol}{$row['med']}</td>
                <td class='txtRight'>{$currency_Symbol}{$row['state']}</td>
                <td class='txtRight'>{$currency_Symbol}{$FUTA}</td>
                <td class='txtRight'>{$currency_Symbol}{$SUTA}</td>
                <td class='txtRight'>{$currency_Symbol}{$net_Amount1}</td>
                <td class='txtRight'>{$currency_Symbol}{$TotWH}</td>
                <td>{$receiptDate}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
            </tr>
            ";
        }

        $total_no_of_hours    = number_format($total_no_of_hours,2);
        $total_invoice_Amount = number_format($total_invoice_Amount,2);
        $total_fed_Amount     = number_format($total_fed_Amount,2);
        $total_med_Amount     = number_format($total_med_Amount,2);
        $total_state_Amount   = number_format($total_state_Amount,2);
        $total_FUTA_Amount    = number_format($total_FUTA_Amount,2);
        $total_SUTA_Amount    = number_format($total_SUTA_Amount,2);
        $total_netAmount      = number_format($total_netAmount,2);
        $total_TotWH          = number_format($total_TotWH,2);
        $total_soc_Amount     = number_format($total_soc_Amount,2);

        $text = "
        {$rows}
        <tr class='txtRight'>
            <th colspan = '5'>Total</th>
            <th>{$total_no_of_hours}</th>
            <th>{$currency_Symbol}{$total_invoice_Amount}</th>
            <th>{$currency_Symbol}{$total_fed_Amount}</th>
            <th>{$currency_Symbol}{$total_soc_Amount}</th>
            <th>{$currency_Symbol}{$total_med_Amount}</th>
            <th>{$currency_Symbol}{$total_state_Amount}</th>
            <th>{$currency_Symbol}{$total_FUTA_Amount}</th>
            <th>{$currency_Symbol}{$total_SUTA_Amount}</th>
            <th>{$currency_Symbol}{$total_netAmount}</th>
            <th>{$currency_Symbol}{$total_TotWH}</th>
            <th></th>
            <th></th>
        <tr>
        ";

        return $text;
    }
}