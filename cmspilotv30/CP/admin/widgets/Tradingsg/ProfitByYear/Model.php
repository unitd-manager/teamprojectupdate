<?
class CP_Admin_Widgets_Tradingsg_ProfitByYear_Model extends CP_Common_Lib_WidgetModelAbstract
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
            $additional_field .= ",(SUM(oi.price_from_supplier * oi.qty)) AS total_cost_price_yearly";
        }
        else {
            $additional_field .= ",(SUM(oi.cost_price * oi.qty)) AS total_cost_price_yearly";
        }

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%Y') AS profit_year
        ,(SUM(oi.unit_price * oi.qty)) AS total_selling_price_yearly
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

        if($cpCfg['cp.excludeStock'] == 1){
            $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        }

        $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";

        $searchVar->groupBy = "DATE_FORMAT(o.order_date, '%Y')";

    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_profitByYear');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $price_from_supplier = $fn->getReqParam('price_from_supplier');

        $additional_field = "";
        if ($price_from_supplier == 1) {
            $additional_field .= ",(SUM(oi.price_from_supplier)) AS total_cost_price_yearly";
        }
        else {
            $additional_field .= ",(SUM(oi.cost_price)) AS total_cost_price_yearly";
        }

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "ProfitByYear_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Year');
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

        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $location_id = $fn->getReqParam('location_id');

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND o .site_id = {$location_id}";
        }

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        $orderDate = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";

        $SQL = "
		SELECT DATE_FORMAT(o.order_date, '%Y') AS profit_year
        	,(SUM(oi.unit_price * oi.qty)) AS total_selling_price_yearly
        	,(SUM(oi.price_from_supplier * oi.qty)) AS total_cost_price_yearly
        FROM `order_item` oi
        LEFT JOIN `order` o ON (oi.order_id = o.order_id)
        WHERE
        {$orderDate}
        {$linkToStock}
        {$appendSql}
 		GROUP BY DATE_FORMAT(o.order_date, '%Y')";
 		
        $result = $db->sql_query($SQL);

		$total = '';
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['profit_year'] != '') {
                $additional_field = "";
                if ($price_from_supplier == 1) {
                    $additional_field = $row['total_cost_price_yearly'];
                }
                
                $total_profit = $row['total_selling_price_yearly'] - $additional_field;
                $total_profit = number_format($total_profit, 2);


                $colc = 0;
                $rowc++;

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['profit_year']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_profit);
            }
        }


        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}