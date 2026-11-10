  <?
class CP_Admin_Widgets_Tradingsg_QuoteSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Quote Summary</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Date</th>
					<th>Code</th>
					<th>Amount</th>
					<th>Invoiced</th>
					<th>Paid</th>
					<th>Staff Name</th>
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

        $rows = '';

        //$today  = date('Y-m-d');

        foreach($this->model->dataArray as $row){

			//if($row['contact_date'] > $today || $row['contact_date'] == $today){
				$quote_date = $fn->getCPDate($row['quote_date'],"d-m-Y");
				$invoiceamount = number_format($row['Invoice_Amount'],2);
				$quoteAmount = number_format($row['Quote_Amount'],2);
			    $rows .= "
				<tr>
					<td>{$quote_date}</td>
					<td><a href='index.php?_topRm=order&module=tradingin_quote&record_id={$row['quote_id']}&_action=edit'><u>{$row['quote_code']}</u></a></td>
					<td class='txtRight w100'>{$quoteAmount}</td>
					<td class='txtRight w100'>{$invoiceamount}</td>
					<td class='txtRight w100'>{$row['Receipt_Amount']}</td>
					<td>{$row['Staff_Name']}</td>
				</tr>
				";
			//}

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}