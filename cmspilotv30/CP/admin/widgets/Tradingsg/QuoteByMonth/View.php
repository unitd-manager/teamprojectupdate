<?
class CP_Admin_Widgets_Tradingsg_QuoteByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $text = "
        <h2>Quote by Month</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Title</th>
					<th>Client</th>
					<th>Status</th>
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

	            $rows .= "
				<tr>
					<td>{$count}</td>
					<td>{$row['title']}</td>
					<td>{$row['company_name']}</td>
					<td>{$row['status']}</td>
					{$siteTitle}
					<td class='txtRight'>{$row['quote_total_amount']}</td>
				</tr>
				";
				$count++;
	            $total += $row['quote_total_amount'];
        }

        $total = number_format($total, 2);

				// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
				$siteLocationTotal = " 
			    <tr class=''>
			        <td class='lastRowBgColor' colspan='5'>Total</td>
			        <td class='txtRight lastRowBgColor'>{$total}</td>
			    </tr>
			    ";
		    } else {

		    	//***** THIS IS A DEFAULT CONDTION FOR TAKING ALL TRADING REPORTS  EXCEPT BLOSSOMS *****//
		    	$siteLocationTotal = "
		        <tr class=''>
		            <td class='lastRowBgColor' colspan='4'>Total</td>
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