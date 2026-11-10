<?
class CP_Admin_Widgets_Tradingsg_InvoiceSummaryByProductGroup_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT inv.*
              ,i.invoice_date
              ,i.invoice_code
              ,o.cust_company_name
              ,p.part_number
              ,pg.title
        FROM invoice_item inv
        LEFT JOIN (`invoice` i) ON (i.invoice_id = inv.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        LEFT JOIN product p ON (inv.record_id = p.product_id)
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
        $searchVar->mainTableAlias = 'i';

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $product_id     = $fn->getReqParam('product_group_id');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $searchVar->sqlSearchVar[] = "i.invoice_date = '{$current_date}'" ;
        }

		if ($product_id != '') {
			$searchVar->sqlSearchVar[] = "p.product_group_id = '{$product_id}'";
		}

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'" ;
        $searchVar->sortOrder = 'i.invoice_date DESC';

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
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "InvoiceSummaryByProductGroup_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Part Number');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Group Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Customer Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Quantity');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Unit Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
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
            $orderDate = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $orderDate = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $orderDate = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $orderDate = "i.invoice_date = '{$current_date}'" ;
        }


        $productID = '' ;
		if ($product_id != '') {
			$productID = "AND p.product_group_id = '{$product_id}'";
		}

		$SQL = "
        SELECT inv.*
              ,i.invoice_date
              ,i.invoice_code
              ,o.cust_company_name
              ,p.part_number
              ,pg.title
        FROM invoice_item inv
        LEFT JOIN (`invoice` i) ON (i.invoice_id = inv.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        LEFT JOIN product p ON (inv.record_id = p.product_id)
        LEFT JOIN product_group pg ON (pg.product_group_id = p.product_group_id)
        WHERE
        {$orderDate}
        {$productID}
	 	AND i.status != 'Cancelled'
	 	ORDER BY i.invoice_date DESC
	 	";

        $result = $db->sql_query($SQL);

        $quantity_total = '';
        $price = 0;
        $amount = 0;
        $count = 0 ;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $count++;

            $totalAmount = $row['qty'] * $row['unit_price'];
            $amount += $totalAmount;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['part_number']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['cust_company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['qty']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['unit_price']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAmount);

            $quantity_total += $row['qty'];
            $price += $row['unit_price'];
        }

        $colc = 4;
        $rowc++;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Qty');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $quantity_total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $price);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}