<?
class CP_Admin_Widgets_Tradingsg_ProfitByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;

		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",o.site_id" ;
		}

        $price_from_supplier = $fn->getReqParam('price_from_supplier');

        $additional_field = "";
        if ($price_from_supplier == 1) {
            $additional_field .= ",(SUM(oi.price_from_supplier*oi.qty)) AS total_cost_price_monthly";
        }
        else{
            $additional_field .= ",(SUM(oi.cost_price*oi.qty)) AS total_cost_price_monthly";
        }

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%M') AS profit_month
        ,(SUM(oi.unit_price*oi.qty)) AS total_selling_price_monthly
      	{$appendSql}
        {$additional_field}
        FROM `order_item` oi
        LEFT JOIN `order` o ON (oi.order_id = o.order_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o .site_id = {$location_id}";
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $searchVar->sqlSearchVar[] = "o.order_status != 'Cancelled'";

        if($cpCfg['cp.excludeStock'] == 1){
            $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        }

        $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        $searchVar->groupBy = "DATE_FORMAT(o.order_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {
        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_profitByMonth');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     */
    function getExportToExcelOLD(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $price_from_supplier = $fn->getReqParam('price_from_supplier');

        $additional_field = "";
        if ($price_from_supplier == 1) {
            $additional_field .= ",(SUM(p.price_from_supplier)) AS total_cost_price_monthly";
        }

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "ProfitByMonth_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $SQL = "
        SELECT DATE_FORMAT(p.creation_date, '%M') AS profit_month
              ,(SUM(p.price)) AS total_selling_price_monthly
              {$additional_field}
        FROM product p
        WHERE p.creation_date BETWEEN '{$last12Month}' AND '{$today}'
        GROUP BY DATE_FORMAT(p.creation_date, '%Y-%m')
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $additional_field_value = "";
            if ($price_from_supplier == 1) {
                $additional_field_value = $row['total_cost_price_monthly'];
            }

            $total_profit = $row['total_selling_price_monthly'] - $additional_field_value;

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['profit_month']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_profit);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "ProfitByMonth_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();
        $location_id    = $fn->getReqParam('location_id');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        if($cpCfg['cp.hasMultiUniqueSites']){
            if($location_id != ''){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
            }
         }
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $price_from_supplier = $fn->getReqParam('price_from_supplier');

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $orderDate = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";

        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND o.site_id = {$location_id}";
        }

        $siteTitle = '' ;

        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",o.site_id" ;
        }

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

		$SQL = "
  		SELECT DATE_FORMAT(o.order_date, '%M') AS profit_month
        	,(SUM(oi.unit_price*oi.qty)) AS total_selling_price_monthly
        	,(SUM(oi.price_from_supplier*oi.qty)) AS total_cost_price_monthly
           {$siteTitle}

        FROM `order_item` oi
        LEFT JOIN `order` o ON (oi.order_id = o.order_id)
        WHERE
        {$orderDate}
        {$linkToStock}
        {$appendSql}
 		GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
 		";

        $result = $db->sql_query($SQL);

        $payment_total = '';
		$total = '';
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                $siteRecSql ="
                SELECT s.title
                FROM site s
                WHERE s.site_id = {$row['site_id']}
                ";

                $resultSiteLocation = $db->sql_query($siteRecSql);
                $rowSite            = $db->sql_fetchrow($resultSiteLocation);
             }


            $additional_field = "";

	            if ($price_from_supplier == 1) {
	                $additional_field = $row['total_cost_price_monthly'];
	            }

            $total_profit = $row['total_selling_price_monthly'] - $additional_field;
            $payment_total += $total_profit;
            $total_profit = number_format($total_profit, 2);


            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['profit_month']);
            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id != ''){
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
                }
            }
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_profit);
        }

        $colc = 0;
        $rowc++;

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            if($location_id != ''){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        $rowc++;


        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($payment_total,2));

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
            if($location_id != ''){
                $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);
            }
        }else {
            $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}