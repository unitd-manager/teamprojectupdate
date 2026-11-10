<?
class CP_Admin_Widgets_Tradingsg_SalesByYear_View extends CP_Common_Lib_WidgetViewAbstract
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
            <h2>Sales by Year</h2>
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
		$siteTitle = '' ;
		$siteLocationTotal = '' ;
		$defaultTotal = '' ;
        $location_id    = $fn->getReqParam('location_id');
        foreach($this->model->dataArray as $row){
            $order_amount_yearly = number_format($row['order_amount_yearly'], 2);

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id!=''){
                $siteRec = $fn->getRecordRowById('site', 'site_id', $location_id);

                $siteTitle = "
                    <td>{$siteRec['title']}</td>
                ";
                }
                else{
                    
                    $sqlsite="
                    SELECT site_id
                          ,title
                    FROM site
                    WHERE published = 1
                    ";
                    $resultSite = $db->sql_query($sqlsite);
                    $siteTitleAll = '';
                    while($siterow = $db->sql_fetchrow($resultSite)){
                        $siteTitleAll .= "{$siterow['title']} / ";
                    }
                    
                    $siteTitle = trim($siteTitleAll,' /');
                    $siteTitle = "<td>{$siteTitle}</td>";
                }
            }
            
            if ($row['order_year']) {
                $rows .= "
                <tr>
                    <td>{$row['order_year']}</td>
                    {$siteTitle}
                    <td class='txtRight'>{$order_amount_yearly}</td>
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