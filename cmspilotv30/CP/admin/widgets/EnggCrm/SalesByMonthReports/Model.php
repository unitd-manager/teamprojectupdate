<?
class CP_Admin_Widgets_EnggCrm_SalesByMonthReports_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%b %Y') AS order_month
              ,(SUM(oi.unit_price*oi.qty)) AS order_amount_monthly
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id   = o.order_id)
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

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        $searchVar->groupBy = "DATE_FORMAT(o.order_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_salesByMonthReports');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcelOld(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SalesByMonth_" . date("d-m-Y") . ".xls";

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

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');

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


        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%b %Y') AS order_month
              ,(SUM(oi.unit_price*oi.qty)) AS order_amount_monthly
               {$siteTitle} 
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id   = o.order_id)
		WHERE
        {$orderDate}
        {$appendSql}
        {$linkToStock}
		GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
         ";

        $result = $db->sql_query($SQL);

        $payment_total = '';

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

            $colc = 0;
            $rowc++;

            $payment_total += $row['order_amount_monthly'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_month']);
            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id != ''){
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
                }
            }   
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_amount_monthly']);
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

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

    /**
     *
     */
    function getExportToExcel($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');         
        $fa = array(
              'order_month'          => $phpExcel->getFldObj('Month')
             ,'order_amount_monthly' => $phpExcel->getFldObj('Amount')
        );

        $file_name = "SalesByMonth_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}