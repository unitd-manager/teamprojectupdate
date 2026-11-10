<?
class CP_Admin_Widgets_Tradingsg_ProfitByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
    	$fn    = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $location_id    = $fn->getReqParam('location_id');
	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			if($location_id != ''){
				$siteLocation = "
				<th>Location</th>
				";
			}
		}

        $text = "
        <h2>Profit by Last 12 Months</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Month</th>
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
        $location_id    = $fn->getReqParam('location_id');
        $price_from_supplier = $fn->getReqParam('price_from_supplier');

        $rows = '';
        $class = '';
        $total = '';
		$siteTitle = '' ;
		$siteLocationTotal = '' ;

        foreach($this->model->dataArray as $row){
            $additional_field = "";
            if ($price_from_supplier == 1) {
                $additional_field = $row['total_cost_price_monthly'];
            }

            $total_profit = $row['total_selling_price_monthly'] - $additional_field;
            $total += $total_profit;
            $total_profit = number_format($total_profit, 2);

				// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);
			    if($location_id != ''){
					$siteTitle = "
					<td>{$siteRec['title']}</td>
					";
				}
			}

            $rows .= "
			<tr>
				<td>{$row['profit_month']}</td>
				{$siteTitle}
				<td  class='txtRight'>{$total_profit}</td>
			</tr>
			";
        }

        $total = number_format($total, 2);

				// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
				$siteLocationTotal = "
			    <tr class=''>
			        <td class='lastRowBgColor' colspan='1'>Total</td>
			        <td class='txtRight lastRowBgColor'>{$total}</td>
			    </tr>
			    ";

			    if($location_id != ''){
			    	$siteLocationTotal = "
				    <tr class=''>
				        <td class='lastRowBgColor' colspan='2'>Total</td>
				        <td class='txtRight lastRowBgColor'>{$total}</td>
				    </tr>
				    ";
			    }
		    } else {

		    	//***** THIS IS A DEFAULT CONDTION FOR TAKING ALL TRADING REPORTS  EXCEPT BLOSSOMS *****//
		    	$siteLocationTotal = "
		        <tr class=''>
		            <td class='lastRowBgColor'>Total</td>
		            <td class='txtRight lastRowBgColor'>{$total}</td>
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