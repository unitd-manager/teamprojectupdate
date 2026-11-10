<?
class CP_Admin_Widgets_Tradingsg_InvoiceByYear_View extends CP_Common_Lib_WidgetViewAbstract
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
            <h2>Invoice by Year</h2>
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
        $location_id    = $fn->getReqParam('location_id');

        $rows = '';
		$siteTitle = '' ;

        foreach($this->model->dataArray as $row){
            $invoice_amount_monthly = number_format($row['invoice_amount_yearly'], 2);

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
                    $siteTitle = "<td>{$siteTitle}</td>";
                }
			}

            if ($row['invoice_year']) {
                /*$rows .= "
                <tr>
                    <td>{$row['invoice_year']}</td>
					{$siteTitle}
                    <td class='txtRight'>{$invoice_amount_monthly}</td>
                </tr>
                ";    */
                $rows .= "
                <tr>
                    <td>{$row['invoice_year']}</td>
                    {$siteTitle}
                    <td class='txtRight'>{$invoice_amount_monthly}</td>
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