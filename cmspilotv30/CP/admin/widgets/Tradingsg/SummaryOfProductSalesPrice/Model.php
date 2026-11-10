<?
class CP_Admin_Widgets_Tradingsg_SummaryOfProductSalesPrice_Model extends CP_Common_Lib_WidgetModelAbstract
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
        SELECT  p.title AS product_name
               ,p.item_code
               ,p.carton_no
               ,p.model
               ,p.product_id
               ,inv.cost_price
               ,po.base_price
               ,po.price AS list_price
               ,(po.xrate * po.fc_price) AS fc_unit
               ,SUM(inv.qty) AS QTY
               ,SUM(inv.unit_price) AS UNIT_PRICE
               ,SUM(inv.vat) AS VAT
               ,inv.record_id
        FROM `invoice_item` inv
        LEFT JOIN `invoice` i ON (i.invoice_id = inv.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `product` p ON (p.product_id = inv.record_id)
        LEFT JOIN `po_product` po ON (po.product_id = p.product_id)
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
        $searchVar->mainTableAlias = 'i';

        $location_id    = $fn->getReqParam('location_id');
        $product_id     = $fn->getReqParam('product_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $company_id     = $fn->getReqParam('company_id');
        $batch_no       = $fn->getReqParam('batch_no');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        /*if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
        }*/

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $searchVar->sqlSearchVar[] = "i.invoice_date = '{$current_date}'";
        }

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = '{$product_id}'";
        }

        if ($company_id != '') {
            $searchVar->sqlSearchVar[] = "o.cust_company_name = '{$company_id}'";
        }

        if ($batch_no != '') {
            $searchVar->sqlSearchVar[] = "p.batch_no = '{$batch_no}'";
        }

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        $searchVar->groupBy   = "inv.record_id";
        $searchVar->sortOrder = "p.item_code ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_summaryOfProductSalesPrice');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $location_id    = $fn->getReqParam('location_id');
        $product_id     = $fn->getReqParam('product_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $company_id     = $fn->getReqParam('company_id');
        $batch_no       = $fn->getReqParam('batch_no');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        $rows = '';
        $appendSqlProduct  = '';
        $appendSqlSite     = '';
        $appendSqlLocation = '';
        $appendSqlBatchno  = '';
        $appendSqlDate     = '';
        $appendSqlCustomer = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Summary_of_Product_Sales_with_Price__" . date("d-m-Y") . ".xls";

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
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Model');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Carton No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Qty Sold');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Avg Unit Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cost Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Base Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'List Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Sales Price');
        if($cpCfg['cp.hasMultiUniqueSites']){
            if($location_id!=''){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
            }
         }   

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

        if ($start_date != '' && $end_date == '') {
            $appendSqlSite = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}')";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $appendSqlSite = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
        } else if ($start_date != '' && $end_date != '') {
            $appendSqlSite = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
        } else {
            $appendSqlSite = "AND i.invoice_date = '{$current_date}'";
        }

        if ($product_id != '') {
            $appendSqlProduct  = "AND p.product_id = '{$product_id}'";
        }

        if ($company_id != '') {
            $appendSqlCustomer = "AND o.cust_company_name = '{$company_id}'";
        }

        if ($batch_no != '') {
            $appendSqlBatchno  = "AND p.batch_no = '{$batch_no}'";
        }

        /*$linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }*/

        $SQL = "
        SELECT  p.title AS product_name
               ,p.item_code
               ,p.carton_no
               ,p.model
               ,inv.cost_price
               ,po.base_price
               ,po.price AS list_price
               ,(po.xrate * po.fc_price) AS fc_unit
               ,SUM(inv.qty) AS QTY
               ,SUM(inv.unit_price) AS UNIT_PRICE
               ,SUM(inv.vat) AS VAT
               ,inv.record_id
        FROM `invoice_item` inv
        LEFT JOIN `invoice` i ON (i.invoice_id = inv.invoice_id)
        LEFT JOIN `product` p ON (p.product_id = inv.record_id)
        LEFT JOIN `po_product` po ON (po.product_id = p.product_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        WHERE i.status != 'Cancelled'
        AND o.link_stock = 1
        {$appendSqlSite}
        {$appendSqlProduct}
        {$appendSqlCustomer}
        {$appendSqlBatchno}
        GROUP BY inv.record_id
        ORDER BY p.item_code
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            if ($location_id != '') {
                $appendSqlLocation = "AND o.site_id = {$location_id}";
            }

            if ($start_date != '' && $end_date == '') {
                $appendSqlDate = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}')";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $appendSqlDate = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
            } else if ($start_date != '' && $end_date != '') {
                $appendSqlDate = "AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
            } else {
                $appendSqlDate = "AND i.invoice_date = '{$current_date}'";
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
            AND o.link_stock = 1
            AND Invt.record_id = {$row['record_id']}
            {$appendSqlLocation}
            {$appendSqlCustomer}
            {$appendSqlBatchno}
            {$appendSqlDate}
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

                if($overall_Total != 0 || $overall_qty != 0){
                    $average_Price = $overall_Total / $overall_qty;
                    $overall_Total = number_format($overall_Total,2);
                    $average_Price = number_format($average_Price,2);
                    $fc_unit       = number_format($row['fc_unit'],2);
                    $base_price    = number_format($row['base_price'],2);
                    $list_price    = number_format($row['list_price'],2);

                    $colc = 0;
                    $rowc++;

                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_name']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['model']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['carton_no']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_qty);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $average_Price);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fc_unit);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $base_price);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $list_price);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_Total);

                    if($cpCfg['cp.hasMultiUniqueSites'] == 1){

                        if($location_id!=''){
                            $siteRec = $fn->getRecordRowById('site', 'site_id', $location_id);
                            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $siteRec['title']);
                        }
                    }
                }   
        }

        $rowc++;

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){

                if($location_id!=''){
                    $actSheet->getStyle("A{$rowc}:K{$rowc}")->applyFromArray($headStyle);
                }else{
                    $actSheet->getStyle("A{$rowc}:J{$rowc}")->applyFromArray($headStyle);
                }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}