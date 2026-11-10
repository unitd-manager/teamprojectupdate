<?
class CP_Admin_Widgets_Tradingsg_SalesSummaryByProductGroup_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT oi.*
              ,o.order_date
              ,p.item_code
              ,pg.title
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id = o.order_id)
        LEFT JOIN product p ON (oi.record_id = p.product_id)
        LEFT JOIN product_group pg ON (pg.product_group_id = p.product_group_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'oi';

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $product_id     = $fn->getReqParam('product_group_id');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}' AND o.order_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else {
            $searchVar->sqlSearchVar[] = "o.order_date = '{$current_date}'" ;
        }

		if ($product_id != '') {
			$searchVar->sqlSearchVar[] = "p.product_group_id = '{$product_id}'";
		}

        $searchVar->sqlSearchVar[] = "o.order_status = 'Paid'" ;
        $searchVar->sortOrder = 'o.order_date DESC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_salesSummaryByProduct');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcelOLD($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'order_date'  => $phpExcel->getFldObj('Sales Date')
             ,'item_code'   => $phpExcel->getFldObj('Item Code')
             ,'item_title'  => $phpExcel->getFldObj('Item Name')
             ,'carton_no'   => $phpExcel->getFldObj('Carton No')
             ,'batch_no'    => $phpExcel->getFldObj('Batch No')
             ,'qty'         => $phpExcel->getFldObj('Qty')
        );

        $file_name = "SalesSummaryByProduct_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SalesSummaryByProductGroup_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sales Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Group Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Quantity');
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $product_id   	= $fn->getReqParam('product_group_id');
        $current_date 	= date('Y-m-d');

        if ($start_date != '' && $end_date == '') {
            $orderDate = "o.order_date >= '{$start_date}' AND o.order_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $orderDate = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $orderDate = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else {
            $orderDate = "o.order_date = '{$current_date}'" ;
        }


        $productID = '' ;
		if ($product_id != '') {
			$productID = "AND p.product_group_id = '{$product_id}'";
		}

		$SQL = "
        SELECT oi.*
              ,o.order_date
              ,p.item_code
              ,pg.title
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id = o.order_id)
        LEFT JOIN product p ON (oi.record_id = p.product_id)
        LEFT JOIN product_group pg ON (pg.product_group_id = p.product_group_id)
        WHERE
        {$orderDate}
        {$productID}
	 	AND o.order_status = 'Paid'
	 	ORDER BY o.order_date DESC
	 	";

        $result = $db->sql_query($SQL);

        $quantity_total = '';
        $count = 0 ;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $count++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['qty']);

            $quantity_total += $row['qty'];
        }

        $colc = 4;
        $rowc++;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Qty');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $quantity_total);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}