<?
class CP_Admin_Widgets_Tradingsg_StockReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;

		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",i.site_id" ;
		}

        $SQL = "
        SELECT i.*
        	  ,p.title AS product_title
              ,p.carton_no
              ,p.item_code
              ,p.model
              {$appendSql}
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $location_id    = $fn->getReqParam('location_id');

        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "i.site_id = {$location_id}";
        }
        $searchVar->sortOrder       = "p.item_code ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_stockReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getExportToExcel12($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');


        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'contactDate'         => $phpExcel->getFldObj('Date')
             ,'company_name'        => $phpExcel->getFldObj('Client')
             ,'comments'            => $phpExcel->getFldObj('Meeting Notes')
             ,'staff_name'  		=> $phpExcel->getFldObj('Staff')
        );

        $file_name = "LeadByStaff_" . date("d-m-Y") . ".xls";

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $staff_id  = $fn->getReqParam('staff_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');

        $rows = '';
        $appendSql = '';


        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "StockReport__" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Model');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Carton Number');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Stock');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cost Price/Qty');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Cost');
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

        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $appendSql = "WHERE i.site_id = {$location_id}";
        }

        $SQL = "
        SELECT i.*
        	  ,p.title AS product_title
              ,p.carton_no
              ,p.item_code
              ,p.model
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        {$appendSql}
        ORDER BY p.item_code ASC
        ";

        $result = $db->sql_query($SQL);

        $sum_purchase_cp_per_qty = '';

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

	        $StockSql = "
	        SELECT
                (SELECT SUM(qty) FROM po_product
                WHERE product_id = {$row['product_id']}) as product_qty_purchased
                ,(SELECT SUM(fc_price*xrate) FROM po_product
                WHERE product_id = {$row['product_id']}) as purchase_cp_per_qty
                ,(SELECT SUM(oi.qty) FROM order_item oi
                LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.order_status = 'Paid'
                  AND o.record_type = 'POS'
                ) as product_qty_sold_pos
                ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.record_type != 'POS'
                  {$linkToStock}
                ) as product_qty_sold_from_quote
                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$row['product_id']}
                  AND srh.status IS NULL
                ) as sales_return_qty
                ,(SELECT po.damaged_qty FROM product po
                WHERE po.product_id = {$row['product_id']}
                ) as damaged_qty
	        ";

	        $resultStockSql = $db->sql_query($StockSql);
	        $rowStockSql    = $db->sql_fetchrow($resultStockSql);

	        $stock = $rowStockSql['product_qty_purchased'] - $rowStockSql['product_qty_sold_pos'] - $rowStockSql['product_qty_sold_from_quote'] + $rowStockSql['sales_return_qty'] - $rowStockSql['damaged_qty'];
            $sum_purchase_cp_per_qty = $stock * $rowStockSql['purchase_cp_per_qty'];
            $rowStockSql['purchase_cp_per_qty'] = number_format($rowStockSql['purchase_cp_per_qty']);

            if($sum_purchase_cp_per_qty){
                $sum_purchase_cp_per_qty = number_format($sum_purchase_cp_per_qty);
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['model']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['carton_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stock);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowStockSql['purchase_cp_per_qty']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $sum_purchase_cp_per_qty);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}