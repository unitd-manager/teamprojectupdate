<?
class CP_Admin_Widgets_Tradingsg_SummaryByClient_Model extends CP_Common_Lib_WidgetModelAbstract
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
            $appendSqlSite = "AND oi.site_id = o.site_id";
		}

        $SQL = "
        SELECT DISTINCT c.company_id
              ,c.company_name
              {$appendSql}
            ,(SELECT SUM(invoice_amount)
            FROM invoice i
            LEFT JOIN `order` oi ON (oi.order_id = i.order_id)
            WHERE i.status != 'Cancelled'
            AND  oi.company_id = c.company_id
            {$appendSqlSite}
            ) AS total_amount_invoiced
            ,(SELECT SUM(r.amount)
            FROM receipt r
            LEFT JOIN `order` oi ON (oi.order_id = r.order_id)
            WHERE oi.company_id = c.company_id
            AND r.receipt_status != 'Cancelled'
            {$appendSqlSite}
            ) AS total_amount_paid
            ,if(
            (SELECT SUM(srh.qty_return*srh.price) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN `order` oi ON (oi.order_id = inv.order_id)
            WHERE oi.company_id = c.company_id
              AND srh.status IS NULL
              {$appendSqlSite}
            )
            ,(SELECT SUM(srh.qty_return*srh.price) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN `order` oi ON (oi.order_id = inv.order_id)
            WHERE oi.company_id = c.company_id
              AND srh.status IS NULL
              {$appendSqlSite}
            )
            ,''
            )as sales_return_amount

        FROM company c
        LEFT JOIN `order` o ON (o.company_id = c.company_id)
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

        $location_id    = $fn->getReqParam('location_id');

        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
        }

        if($cpCfg['cp.excludeStock'] == 1){
            $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        }

        $searchVar->sqlSearchVar[] = "o.record_type = 'Quote'";
        $searchVar->sortOrder = "c.company_name ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_summaryByClient');

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

        $rows = '';


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
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Invoice Raised');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Balance To Be Paid');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
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

        $siteTitle = '' ;
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",o.site_id" ;
            $appendSqlSite = "AND oi.site_id = o.site_id";
        }

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }


        if ($location_id != '') {
            $appendSql = " AND o.site_id = {$location_id}";
        }

        $SQL = "
        SELECT DISTINCT c.company_id
              ,c.company_name
               {$siteTitle}
              ,(SELECT SUM(invoice_amount)
                FROM invoice i
                LEFT JOIN `order` oi ON (oi.order_id = i.order_id)
                WHERE i.status != 'Cancelled'
                AND  oi.company_id = c.company_id
                {$appendSqlSite}
               ) AS total_amount_invoiced
              ,(SELECT SUM(r.amount)
                FROM receipt r
                LEFT JOIN `order` oi ON (oi.order_id = r.order_id)
                WHERE oi.company_id = c.company_id
                AND r.receipt_status != 'Cancelled'
                {$appendSqlSite}
                ) AS total_amount_paid
              ,if(
                (SELECT SUM(srh.qty_return*srh.price) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN `order` oi ON (oi.order_id = inv.order_id)
                WHERE oi.company_id = c.company_id
                  AND srh.status IS NULL
                  {$appendSqlSite}
                )
                ,(SELECT SUM(srh.qty_return*srh.price) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN `order` oi ON (oi.order_id = inv.order_id)
                WHERE oi.company_id = c.company_id
                  AND srh.status IS NULL
                  {$appendSqlSite}
                )
                ,''
                )as sales_return_amount

        FROM company c
        LEFT JOIN `order` o ON (o.company_id = c.company_id)
        WHERE o.record_type = 'Quote'
        {$appendSql}
        {$linkToStock}
        ORDER BY c.company_name ASC
        ";

        $result = $db->sql_query($SQL);

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

     
            $invoice_amount = $row['total_amount_invoiced'] - $row['sales_return_amount'];
            if($row['total_amount_paid']){
                $amount_paid    = $row['total_amount_paid'] - $row['sales_return_amount'] ;
                $balance_amount = number_format($invoice_amount -$amount_paid);
                $amount_paid    = number_format($amount_paid,0);
            }
            else{
                $amount_paid    = '0';
                $balance_amount = number_format($invoice_amount);
            }

            $invoice_amount = number_format($invoice_amount,0);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount_paid);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_amount);
            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
            }   
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}