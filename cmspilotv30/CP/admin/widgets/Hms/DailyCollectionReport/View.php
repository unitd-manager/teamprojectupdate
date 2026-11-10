<?
class CP_Admin_Widgets_Hms_DailyCollectionReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($tv['module'] == 'common_dashboard'){
            $heading = "Daily Collection Report last 30 days";
        }else {
            $heading = "Daily Collection Report";
        }
        $text = "
        <h2>{$heading}</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
                        <th>Patient Name</th>
                        <th>Dr In Charge</th>
                        <th>Treatment Type</th>
                        <th>Invoice Amount</th>
						<th class='txtRight'>Amount Paid</th>
                        <th>Balance</th>
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
		$siteTitle = '' ;
        $totalAmount = 0;
        $totalBalance = 0;
        $invoice_amount = 0;
        $totalInvoiceAmount = 0;
        foreach($this->model->dataArray as $row){
			$creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
			$amount = number_format(round($row['receipt_amount']), 2);
            $balance = ($row['invoice_amount'] - $row['discount']) - $row['receipt_amount'];

            $totalAmount += $row['receipt_amount'];
            $totalBalance += $balance;
            $totalInvoiceAmount += $row['invoice_amount'] - $row['discount'];

            $balance = number_format(round($balance), 2);

            $SQL = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = '{$row['invoice_id']}'
              AND it.record_type = 'Doctor/Nurse'
            ";
            $result = $db->sql_query($SQL);
            $employee_name = '';

            while ($rowIt = $db->sql_fetchrow($result)) {
                $employee_name .= $rowIt['item_title'].', ';
            }
            $employee_name = rtrim($employee_name, ', ');

            $SQL = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = '{$row['invoice_id']}'
              AND it.record_type = 'Treatment'
            ";
            $result = $db->sql_query($SQL);
            $treatment = '';

            while ($rowIt = $db->sql_fetchrow($result)) {
                $treatment .= $rowIt['item_title'].', ';
            }
            $treatment = rtrim($treatment, ', ');

            $invoice_amount = number_format($row['invoice_amount'] - $row['discount'], 2);

		    $rows .= "
			<tr>
				<td>{$creationDate}</td>
                <td>{$row['patient_name']}</td>
                <td>{$employee_name}</td>
                <td>{$treatment}</td>
                <td class='txtRight'>{$invoice_amount}</td>
				<td class='txtRight'>{$amount}</td>
                <td class='txtRight'>{$balance}</td>
			</tr>
			";
        }

        $totalAmount = number_format(round($totalAmount), 2);
        $totalBalance = number_format(round($totalBalance), 2);
        $totalInvoiceAmount = number_format(round($totalInvoiceAmount), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='4'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalInvoiceAmount}</td>
            <td class='txtRight lastRowBgColor'>{$totalAmount}</td>
            <td class='txtRight lastRowBgColor'>{$totalBalance}</td>
        </tr>
        ";

        return $text;
    }

}