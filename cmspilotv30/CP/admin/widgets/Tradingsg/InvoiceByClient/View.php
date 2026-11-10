<?
class CP_Admin_Widgets_Tradingsg_InvoiceByClient_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
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
        <h2>Invoice by Client</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Client Name</th>
					{$siteLocation}
					<th class='txtRight'>Amount</th>
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
        $count = 1;
        $total = 0;
		$siteTitle = '' ;
		$siteLocationTotal = '' ;

        foreach($this->model->dataArray as $row){

				// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";
			}

            if ($row['company_id'] != '') {
                $company_invoice_amount = number_format($row['company_invoice_amount'], 2);
                $rows .= "
			    <tr>
			    	<td>{$count}</td>
			    	<td>{$row['company_name']}</td>
			    	{$siteTitle}
			    	<td class='txtRight'>{$company_invoice_amount}</td>
			    </tr>
			    ";
	            $total += $row['company_invoice_amount'];
			    $count++;
			}
        }

        $total = number_format($total, 2);

				// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
				$siteLocationTotal = "
			    <tr class=''>
			        <td class='lastRowBgColor' colspan='3'>Total</td>
			        <td class='txtRight lastRowBgColor'>{$total}</td>
			    </tr>
			    ";
		    } else {

		    	//***** THIS IS A DEFAULT CONDTION FOR TAKING ALL TRADING REPORTS  EXCEPT BLOSSOMS *****//
		    	$siteLocationTotal = "
		        <tr class=''>
		            <td class='lastRowBgColor'>Total</td>
            		<td colspan='6' class='lastRowBgColor txtRight'>Total ($total)</td>
		        </tr>
		        ";
		    }

        $text = "
        {$rows}
        {$siteLocationTotal}
        ";

        return $text;
    }
}