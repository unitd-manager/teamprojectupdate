<?
class CP_Admin_Widgets_Tradingsg_SummaryOfProductSalesPrice_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $location_id    = $fn->getReqParam('location_id');
	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			if($location_id!=''){
				$siteLocation = "
				<th>Location</th>
				";
			}
		}

        $text = "
        <h2>Summary of Product Sales with Price</h2>
				<div class = 'tableOuter scroll-pane'>
				<table class='thinlist'>
					<thead>
						<tr>
							<th>Item Code</th>
                            <th>Item Name</th>
                            <th>Model</th>
                            <th>Carton No</th>
							<th class='txtRight'>Total Qty Sold</th>
							<th class='txtRight'>Avg Unit Price</th>
                            <th class='txtRight'>Cost Price</th>
                            <th class='txtRight'>Base Price</th>
                            <th class='txtRight'>List Price</th>
							<th class='txtRight'>Total Sales Price</th>
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
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $location_id      = $fn->getReqParam('location_id');
        $start_date       = $fn->getReqParam('start_date');
        $end_date         = $fn->getReqParam('end_date');
        $month            = $fn->getReqParam('month');
        $year             = $fn->getReqParam('year');
        $company_id       = $fn->getReqParam('company_id');
        $batch_no         = $fn->getReqParam('batch_no');
        $current_date     = date('Y-m-d');
        $month            = date('m');
        $year             = date('Y');
        $rows             = '';
		$siteTitle        = '';
		$appendSqlSite    = '';
        $appendSql        = '';
        $appendSqlCompany = '';
        $appendSqlBatchno = '';

        foreach($this->model->dataArray as $row){

        		if ($location_id != '') {
            		$appendSqlSite = "AND o.site_id = {$location_id}";
        		}

                if ($start_date != '' && $end_date == '') {
                    $appendSql = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}')";
                } else if ($start_date == '' && $end_date != ''){
                    $start_date = $year . '-' . $month . '-' . '01';
                    $appendSql = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
                } else if ($start_date != '' && $end_date != '') {
                    $appendSql = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
                } else {
                    $appendSql = "AND i.invoice_date = '{$current_date}'";
                }

                if ($company_id != '') {
                    $appendSqlCompany = "AND o.cust_company_name = '{$company_id}'";
                }

                if ($batch_no != '') {
                    $appendSqlBatchno = "AND p.batch_no = '{$batch_no}'";
                }


        		$SQLinvItem = "
        		SELECT  Invt.qty
        		       ,Invt.unit_price
        		       ,Invt.discount_type
        		       ,Invt.discount_percentage
        		       ,Invt.vat
        		       ,i.order_id
                       ,o.record_type
                       ,o.site_id
        		FROM `invoice_item` Invt
        		LEFT JOIN `invoice` i ON (i.invoice_id = Invt.invoice_id)
        		LEFT JOIN `order` o ON (o.order_id = i.order_id)
                LEFT JOIN `product` p ON (p.product_id = {$row['record_id']})
        		WHERE i.status != 'Cancelled'
        		AND Invt.record_id = {$row['record_id']}
                AND o.link_stock = 1
        		{$appendSqlSite}
                {$appendSqlCompany}
                {$appendSqlBatchno}
                {$appendSql}
        		";

        		$resultinvItem   = $db->sql_query($SQLinvItem);
        		$overall_Total   = 0;
        		$overall_qty     = 0;
        		$subtotal        = 0;
        		$discount_value_for_one_qty = 0;
        		$discountValue   = 0;
        		$total_amount    = 0;
        		$vat_for_one_qty = 0;
        		$vatAmount       = 0;
        		$average_Price   = 0;
         		while ($rowinvItem = $db->sql_fetchrow($resultinvItem)) {
         			$subtotal = $rowinvItem['qty'] * $rowinvItem['unit_price'];

        			if($rowinvItem['record_type'] == 'POS'){

        				if($rowinvItem['discount_percentage'] > 0){
			                if($rowinvItem['discount_type'] == '%'){
			                    $discount_value_for_one_qty  =  $rowinvItem['unit_price'] * ($rowinvItem['discount_percentage']/100);
			                    $discountValue = $discount_value_for_one_qty * $rowinvItem['qty'];
			                }
			                else if($rowinvItem['discount_type']  == 'Value'){
			                    $discount_value_for_one_qty  =  $rowinvItem['discount_percentage'];
			                    $discountValue = $discount_value_for_one_qty * $rowinvItem['qty'];
			                }

			            }

			            if($rowinvItem['vat'] > 0){
			                $vat_for_one_qty  =  ($rowinvItem['unit_price'] - $discountValue) * $rowinvItem['vat']/100;
			                $vatAmount = $vat_for_one_qty;
			            }

			            $total_amount = ($subtotal - $discountValue) + $vatAmount;
        			}
        			else{
        				$total_amount = $subtotal;
        			}

        			$overall_qty   += $rowinvItem['qty'];
        			$overall_Total += $total_amount;
        		}

        		if($cpCfg['cp.hasMultiUniqueSites'] == 1){
		                if($location_id!=''){
					    $siteRec = $fn->getRecordRowById('site', 'site_id', $location_id);

						$siteTitle = "
						<td>{$siteRec['title']}</td>
						";
		                }
		                /*else{

		                    $sqlsite="SELECT site_id
		                                ,title
		                              FROM site
		                              WHERE site_id = {$rowinvItem['site_id']}
		                    ";
		                    $resultSite = $db->sql_query($sqlsite);
		                    $siterow = $db->sql_fetchrow($resultSite);
		                    $siteTitle .= "{$siterow['title']}";
		                }*/
					}
        		//$siteTitle     = trim($siteTitle,' /');

				if($overall_Total != 0 || $overall_qty != 0){
	        		$average_Price = $overall_Total / $overall_qty;
	        		$overall_Total = number_format($overall_Total,2);
	        		$average_Price = number_format($average_Price,2);
                    $fc_unit       = number_format($row['fc_unit'],2);
                    $base_price    = number_format($row['base_price'],2);
                    $list_price    = number_format($row['list_price'],2);

	        		$rows .= "
				    <tr>
                        <td>{$row['item_code']}</td>
                        <td>{$row['product_name']}</td>
				        <td>{$row['model']}</td>
                        <td>{$row['carton_no']}</td>
				        <td class = 'txtRight'>{$overall_qty}</td>
				        <td class = 'txtRight'>{$average_Price}</td>
                        <td class = 'txtRight'>{$fc_unit}</td>
                        <td class = 'txtRight'>{$base_price}</td>
                        <td class = 'txtRight'>{$list_price}</td>
				        <td class = 'txtRight'>{$overall_Total}</td>
				    	{$siteTitle}
				    </tr>
					";
	        	}

        		/*$siteRec = $fn->getRecordRowByID('site', 'site_id', $row['site_id']);

        		if($cpCfg['cp.hasMultiUniqueSites']){
					$siteTitle = "
					<td>{$siteRec['title']}</td>
					";
				}*/

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}