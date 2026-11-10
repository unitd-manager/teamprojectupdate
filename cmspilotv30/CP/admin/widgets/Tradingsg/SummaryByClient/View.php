<?
class CP_Admin_Widgets_Tradingsg_SummaryByClient_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $text = "
        <h2>Summary By Client</h2>
				<div class = 'tableOuter scroll-pane'>
				<table class='thinlist'>
					<thead>
						<tr>
							<th>Client Name</th>
							<th>Total Invoice Raised</th>
							<th>Total Amount Paid</th>
							<th>Balance To Be Paid</th>
							{$siteLocation}
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

        $rows = '';
		$siteTitle = '' ;

        foreach($this->model->dataArray as $row){
        		$invoice_amount = $row['total_amount_invoiced'] - $row['sales_return_amount'];
				if($row['total_amount_paid']){
					$amount_paid    = $row['total_amount_paid']  ;
					$balance_amount = number_format($invoice_amount -$amount_paid);
					$amount_paid    = number_format($amount_paid,0);
				}
				else{
					$amount_paid    = '0';
					$balance_amount = number_format($invoice_amount);
				}

				$invoice_amount = number_format($invoice_amount,0);

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

				if($cpCfg['cp.hasMultiUniqueSites'] == 1){
				    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

					$siteTitle = "
					<td>{$siteRec['title']}</td>
					";
				}
					//<td>{$row['company_name']}</td>

			    $rows .= "
				<tr>
					<td>{$row['company_name']}</td>
					<td align='right'>{$invoice_amount}</td>
					<td align='right'>{$amount_paid}</td>
					<td align='right'>{$balance_amount}</td>
					{$siteTitle}
				</tr>
				";

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}