<?
class CP_Admin_Widgets_Tradingsg_SalesByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
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
        <h2>Sales by Last 12 Months</h2>
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

    /**
     *
     */
    function getRowsHTMLOld() {
        $rows = '';
        foreach($this->model->dataArray as $row){
            $order_amount_monthly = number_format($row['order_amount_monthly'], 2);
            $rows .= "
			<tr>
				<td>{$row['order_month']}</td>
				<td class='txtRight'>{$order_amount_monthly}</td>
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    function getRowsHTMLArif() {
        $db = Zend_Registry::get('db');

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $text = '';
        $sqlOrder = "
        SELECT o.* FROM `order` o
        WHERE o.order_date BETWEEN '{$last12Month}' AND '{$today}'
        ";
        $resultOrder = $db->sql_query($sqlOrder);
        $prev_month_val = '';
        while ($rowOrder = $db->sql_fetchrow($resultOrder)) {
            $month_val = substr($rowOrder['order_date'], 5,2);

            $sqlOI = "
            SELECT (oi.unit_price * oi.qty) AS total_amount
            FROM order_item oi
            WHERE oi.order_id = {$rowOrder['order_id']}
            ";
            $resultOI = $db->sql_query($sqlOI);

            $total_amount = 0;
            $prev_month   = 0;

            while ($rowOI = $db->sql_fetchrow($resultOI)) {
                if ($prev_month != $month_val) {
                    $total_amount = $rowOI['total_amount'];

                    $text .= "
    		    	<tr>
    		    		<td>{$rowOrder['order_date']}</td>
    		    		<td class='txtRight'>{$rowOI['total_amount']}</td>
    		    	</tr>
                    ";

                    $prev_month = $month_val;
                } else {
                    $total_amount .= $rowOI['total_amount'];
                    $prev_month = substr($rowOrder['order_date'], 5,2);
                }
            }
            /*
            $text .= "
	    	<tr>
	    		<td>{$rowOrder['order_date']}</td>
	    		<td class='txtRight'>{$rowOI['total_amount']}</td>
	    	</tr>
            ";
            */
        }

        return $text;
    }
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $location_id    = $fn->getReqParam('location_id');

        $rows = '';
        $total = '';
		$siteTitle = '' ;
		$siteLocationTotal = '' ;

        foreach($this->model->dataArray as $row){
            $order_amount_monthly = number_format($row['order_amount_monthly'], 2);

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
				<td>{$row['order_month']}</td>
				{$siteTitle}
				<td class='txtRight'>{$order_amount_monthly}</td>
			</tr>
			";
            $total += $row['order_amount_monthly'];
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