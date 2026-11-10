<?
class CP_Admin_Widgets_Tradingsg_SalesByYear_Model extends CP_Common_Lib_WidgetModelAbstract
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
        SELECT DATE_FORMAT(o.order_date, '%Y') AS order_year
              ,(SUM(oi.unit_price * oi.qty)) AS order_amount_yearly
              {$appendSql}
        FROM `order` o
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
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
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
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
        $searchVar->groupBy = "DATE_FORMAT(o.order_date, '%Y')";

    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_salesByYear');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcelOLD($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'order_year'            => $phpExcel->getFldObj('Year')
             ,'order_amount_yearly'   => $phpExcel->getFldObj('Amount')
        );

        $file_name = "SalesByYear_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SalesByYear_" . date("d-m-Y") . ".xls";

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

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $orderDate = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";

        $siteTitle1    = '';
        $siteTitle     = '' ;
        $payment_total = '';

        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",o.site_id" ;
        }

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }


        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND o.site_id = {$location_id}";
        }

         $SQL = "
  		 SELECT DATE_FORMAT(o.order_date, '%Y') AS order_year
               ,(SUM(oi.unit_price * oi.qty)) AS order_amount_yearly
               {$siteTitle} 
         FROM `order` o
         LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
         WHERE
         {$orderDate}
         {$appendSql}
         {$linkToStock}
 		 GROUP BY DATE_FORMAT(o.order_date, '%Y')
		 ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id!=''){
                $siteRec = $fn->getRecordRowById('site', 'site_id', $location_id);

                $siteTitle1 = $siteRec['title'];
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
                    $siteTitle1 = trim($siteTitleAll,' /');
                }
            }

            $payment_total += $row['order_amount_yearly'];

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_year']);
            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $siteTitle1);
            }   
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_amount_yearly']);
        }

        $colc = 0;
        $rowc++;

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }   

        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);
        }else {
            $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);           
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}