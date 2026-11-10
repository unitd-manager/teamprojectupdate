<?
class CP_Admin_Widgets_Tradingsg_SalesByClient_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;
	
		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",o.site_id" ;
		}

        $SQL = "
        SELECT c.company_id
              ,c.company_name
              {$appendSql}
              ,(SUM(oi.unit_price*oi.qty)) AS company_order_amount
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id   = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
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

        $searchVar->groupBy = 'c.company_id';
        $searchVar->sortOrder = 'company_name';

    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_salesByClient');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SalesByClient_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
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

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');
        $count = 1;

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $orderDate = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";

        $siteTitle = '' ;       
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",o.site_id" ;
        }


        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND o.site_id = {$location_id}";
        }

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }


        $payment_total = '';

        $SQL = "
    	SELECT c.company_id
              ,c.company_name
               {$siteTitle} 
              ,(SUM(oi.unit_price*oi.qty)) AS company_order_amount
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id   = o.order_id)
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        WHERE
        {$orderDate}
        {$appendSql}
        {$linkToStock}
 		GROUP BY c.company_id
 		ORDER BY company_name
		";
		
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {

            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                $siteRecSql ="
                SELECT s.title 
                FROM site s
                WHERE s.site_id = {$row['site_id']}
                ";

                $resultSiteLocation = $db->sql_query($siteRecSql);
                $rowSite            = $db->sql_fetchrow($resultSiteLocation);
             }   

            if ($row['company_id'] != '') {
                $colc = 0;
                $rowc++;
	
	            $payment_total += $row['company_order_amount'];

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
                if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
                }   
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_order_amount']);
                $count++;
            }
        }
        $colc = 0;
        $rowc++;

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }else {          
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }   

        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);
        }else {
            $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);           
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}