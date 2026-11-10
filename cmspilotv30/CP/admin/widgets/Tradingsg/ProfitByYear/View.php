<?
class CP_Admin_Widgets_Tradingsg_ProfitByYear_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $c = &$this->controller;
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <h2>Profit by Year</h2>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Year</th>
						{$siteLocation}
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $price_from_supplier = $fn->getReqParam('price_from_supplier');
        $location_id    = $fn->getReqParam('location_id');

        $rows = '';
		$siteTitle = '' ;

        foreach($this->model->dataArray as $row){

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id!=''){
                $siteRec = $fn->getRecordRowById('site', 'site_id', $location_id);

                $siteTitle = "
                    {$siteRec['title']}
                ";
                }
                else{

                    $sqlsite="SELECT site_id
                                ,title
                                FROM site
                    ";
                    $resultSite = $db->sql_query($sqlsite);
                    while($siterow = $db->sql_fetchrow($resultSite)){
                    $siteTitle .= "{$siterow['title']} / ";
                    }
                    $siteTitle = trim($siteTitle,' /');
                }
            }

            if ($row['profit_year'] != '') {
                $additional_field = "";
                if ($price_from_supplier == 1) {
                    $additional_field = $row['total_cost_price_yearly'];
                }

                $total_profit = $row['total_selling_price_yearly'] - $additional_field;
                $total_profit = number_format($total_profit, 2);

                $rows .= "
			    <tr>
			    	<td>{$row['profit_year']}</td>
					<td>{$siteTitle}</td>
			    	<td class='txtRight'>{$total_profit}</td>
			    </tr>
			    ";
			}
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}