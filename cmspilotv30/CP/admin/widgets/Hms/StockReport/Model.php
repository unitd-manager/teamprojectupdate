<?
class CP_Admin_Widgets_Hms_StockReport_Model extends CP_Common_Lib_WidgetModelAbstract
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
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.product_code AS item_code
              ,p.unit
              ,p.mol_type AS molType
              ,p.pack_type
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

       /* $location_id    = $fn->getReqParam('location_id');

        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "i.site_id = {$location_id}";
        }*/

        //$searchVar->sqlSearchVar[] = "i.actual_stock > 0";
        $searchVar->sortOrder = "p.title ASC";
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

        $location_id = $fn->getReqParam('location_id');

        $rows = '';
        $appendSql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');


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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Purchased / Stock Transfer Qty');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sold Qty');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Available Stock');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
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

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($location_id != '') {
            $cpSiteIdSession = $location_id;
        }

        $SQL = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.product_code AS item_code
              ,p.unit
              ,p.mol_type AS molType
              ,p.pack_type
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        ORDER BY p.title ASC
        ";

        $result = $db->sql_query($SQL);

        $appendSql = '';
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $appendSql = "WHERE site_id = {$location_id}";
        }
            
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

	        $stock = 0;
            $purchased_qty = 0;
            $sold_qty = 0;
            
            $SQLSite = "
            SELECT site_id, title
            FROM site
            {$appendSql}
            ";
            $resultSite     = $db->sql_query($SQLSite);
            $siteTitle = '';
            while ($rowSite = $db->sql_fetchrow($resultSite)) {
                $appendSqlStockTransfer ='';
                $appendSqlExpense ='';
                $appendSqlInvoice ='';
                $appendSqlPoOrder ='';
                $appendSqlStockTransferCurrent ='';
                $appendSqlExpenseCurrent ='';
                $appendSqlInvoiceCurrent ='';
                $appendSqlPoOrderCurrent ='';

                if ($start_date != '' && $end_date == '') {
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$current_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$current_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$current_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";
                    
                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$current_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$current_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$current_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";
                } else if ($start_date == '' && $end_date != ''){
                    $month = date('m', strtotime(date('Y-m')." -3 month"));
                    $start_date = $year . '-' . $month . '-' . '01';
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";


                } else if ($start_date != '' && $end_date != '') {
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
                
                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                } else if ($monthVal == '' && $yearVal == ''){
                    $start_date = $year . '-' . $month . '-' . '01';
                    $end_date   = $year . '-' . $month . '-' . '31';
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
                }

                if ($monthVal != '') {
                    $appendSqlStockTransfer .= "AND DATE_FORMAT(st.date, '%m') = '{$monthVal}'" ;
                    $appendSqlExpense .= "AND DATE_FORMAT(e.creation_date, '%m') = '{$monthVal}'";
                    $appendSqlInvoice .= "AND DATE_FORMAT(inv.invoice_date, '%m') = '{$monthVal}'";
                    $appendSqlPoOrder .= "AND DATE_FORMAT(po.purchase_order_date, '%m') = '{$monthVal}'";
                    
                    $appendSqlStockTransferCurrent .= "AND DATE_FORMAT(st.date, '%m') <= '{$monthVal}'";
                    $appendSqlExpenseCurrent .= "AND DATE_FORMAT(e.creation_date, '%m') <= '{$monthVal}'";
                    $appendSqlInvoiceCurrent .= "AND DATE_FORMAT(inv.invoice_date, '%m') <= '{$monthVal}'";
                    $appendSqlPoOrderCurrent .= "AND DATE_FORMAT(po.purchase_order_date, '%m') <= '{$monthVal}'";
                }

                if ($yearVal != '') {
                    $appendSqlStockTransfer .= "AND DATE_FORMAT(st.date, '%Y') = '{$yearVal}'" ;
                    $appendSqlExpense .= "AND DATE_FORMAT(e.creation_date, '%Y') = '{$yearVal}'";
                    $appendSqlInvoice .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
                    $appendSqlPoOrder .= "AND DATE_FORMAT(po.purchase_order_date, '%Y') = '{$yearVal}'";

                    $appendSqlStockTransferCurrent .= "AND DATE_FORMAT(st.date, '%Y') = '{$yearVal}'" ;
                    $appendSqlExpenseCurrent .= "AND DATE_FORMAT(e.creation_date, '%Y') = '{$yearVal}'";
                    $appendSqlInvoiceCurrent .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
                    $appendSqlPoOrderCurrent .= "AND DATE_FORMAT(po.purchase_order_date, '%Y') = '{$yearVal}'";
                }

                $SQLStockTransfer = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowSite['site_id']}
                {$appendSqlStockTransfer}
                ";

                $resultStockTransfer = $db->sql_query($SQLStockTransfer);
                $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


                $SQLStockTransferto = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty_to
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowSite['site_id']}
                {$appendSqlStockTransfer}
                ";

                $resultStockTransferto = $db->sql_query($SQLStockTransferto);
                $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

                $SQLOthersite = "
                SELECT
                    (SELECT SUM(qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                     {$appendSqlPoOrder}
                     ) as product_qty_purchased

                   ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE record_id = {$row['product_id']}
                      AND o.site_id = {$rowSite['site_id']}
                      {$appendSqlInvoice}
                    ) as product_qty_sold_from_quote

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = {$row['product_id']}
                      AND inv.site_id = {$rowSite['site_id']}
                    {$appendSqlInvoice}
                    ) as sales_return_qty

                    ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                      LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                      WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                     ) as damaged_qty
                ";
                $resultothersite = $db->sql_query($SQLOthersite);
                $rowothersite = $db->sql_fetchrow($resultothersite);

                $SqlExpenseProduct = "
                SELECT SUM(ep.qty) AS qty
                FROM expense_product ep
                LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                WHERE ep.product_id = {$row['product_id']}
                AND ep.status = 'Added'
                AND e.site_id = {$rowSite['site_id']}
                AND ep.stock_deducted = 1
                {$appendSqlExpense}
                ";
                $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
                $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

                /*** The following SQL for Current Stock ***/
                    $SQLStockTransferCurrent = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowSite['site_id']}
                    {$appendSqlStockTransferCurrent}
                    ";

                    $resultStockTransferCurrent = $db->sql_query($SQLStockTransferCurrent);
                    $rowStockTransferCurrent = $db->sql_fetchrow($resultStockTransferCurrent);


                    $SQLStockTransfertoCurrent = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty_to
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowSite['site_id']}
                    {$appendSqlStockTransferCurrent}
                    ";

                    $resultStockTransfertoCurrent = $db->sql_query($SQLStockTransfertoCurrent);
                    $rowStockTransfertoCurrent = $db->sql_fetchrow($resultStockTransfertoCurrent);

                    $SQLOthersiteCurrent = "
                    SELECT
                        (SELECT SUM(qty) FROM po_product pp
                         LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                         WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         {$appendSqlPoOrderCurrent}
                         ) as product_qty_purchased

                       ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                        LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                        WHERE record_id = {$row['product_id']}
                          AND o.site_id = {$rowSite['site_id']}
                          {$appendSqlInvoiceCurrent}
                        ) as product_qty_sold_from_quote

                        ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                        WHERE ini.record_id = {$row['product_id']}
                          AND inv.site_id = {$rowSite['site_id']}
                        {$appendSqlInvoiceCurrent}
                        ) as sales_return_qty

                        ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                          LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                          WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         ) as damaged_qty
                    ";
                    $resultothersiteCurrent = $db->sql_query($SQLOthersiteCurrent);
                    $rowothersiteCurrent = $db->sql_fetchrow($resultothersiteCurrent);

                    $SqlExpenseProductCurrent = "
                    SELECT SUM(ep.qty) AS qty
                    FROM expense_product ep
                    LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                    WHERE ep.product_id = {$row['product_id']}
                    AND ep.status = 'Added'
                    AND e.site_id = {$rowSite['site_id']}
                    AND ep.stock_deducted = 1
                    {$appendSqlExpenseCurrent}
                    ";
                    $resultExpenseProductCurrent = $db->sql_query($SqlExpenseProductCurrent);
                    $rowExpenseProductCurrent    = $db->sql_fetchrow($resultExpenseProductCurrent);
                /*** Ends Here ***/

                $stock += $rowothersiteCurrent['product_qty_purchased'] - $rowothersiteCurrent['product_qty_sold_from_quote'] + $rowothersiteCurrent['sales_return_qty'] - $rowothersiteCurrent['damaged_qty'] - $rowStockTransferCurrent['Transfer_qty'] + $rowStockTransfertoCurrent['Transfer_qty_to'] - $rowExpenseProductCurrent['qty'];
                $purchased_qty += $rowothersite['product_qty_purchased'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                $sold_qty += $rowothersite['product_qty_sold_from_quote'];

                $siteTitle = $rowSite['title'];
            }

            $item_code = '';
            if($row['item_code'] != ''){
                $item_code ='PROD - '.$row['item_code'];
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $item_code);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $purchased_qty);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $sold_qty);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stock);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $siteTitle);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportStocksToPdf(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        //$pdf->SetMargins(0, 20, 0, true);
        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot1.php)*/
        $pdf->AddPage();

        $appendSql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        $SQLSiteName ="
        SELECT site_id
               ,title
        FROM site
        ORDER BY site_id ASC
        ";
        $resultSiteName = $db->sql_query($SQLSiteName);
        $siteNameRow = "";
        while ($rowSiteName = $db->sql_fetchrow($resultSiteName)) {
            $siteNameRow .= '<th width="4.5%" align="center"><b>'. $rowSiteName['title'] .'</b></th>';
        }


        $tbl1 = '
        <table border="1" cellpadding="3" width="100%">
            <thead>
                <tr style="font-weight:bold;">
                    <th rowspan="2" width="5%">Code</th>
                    <th rowspan="2" width="19%">Product Name</th>
                    <th width="27%" align="center">Purchase / Stock Transfer</th>
                    <th width="27%" align="center">Sold / Expense</th>
                    <th width="27%" align="center">Stock Available</th>
                </tr>
                <tr>
                    '.$siteNameRow.'
                    '.$siteNameRow.'
                    '.$siteNameRow.'
                </tr>
            </thead>
            <tbody>
        ';
        
        $SQLProduct= "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.product_code AS item_code
              ,p.unit
              ,p.mol_type AS molType
              ,p.pack_type
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        ORDER BY p.title ASC
        ";
        $resultProduct = $db->sql_query($SQLProduct);

        while ($row = $db->sql_fetchrow($resultProduct)) {
                $stock = 0;
                $purchased_qty = 0;
                $sold_qty = 0;
                $purchasedQtyRow = '';
                $soldQtyRow = '';
                $stockQtyRow = '';
               
                $SQLSite ="
                SELECT site_id
                       ,title
                FROM site
                ORDER BY site_id ASC
                ";
                $resultSite = $db->sql_query($SQLSite);

                $tbl1 = $tbl1.'<tr>
                                    <td width="5%">'.$row['item_code'].'</td>
                                    <td width="19%">'.$row['product_name'].'</td>
                        ';

                $appendSqlStockTransfer = '';
                $appendSqlExpense = '';
                $appendSqlInvoice = '';
                $appendSqlPoOrder = '';

                while ($rowSite = $db->sql_fetchrow($resultSite)) {

                    if ($start_date != '' && $end_date == '') {
                        $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$current_date}'";
                        $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$current_date}'";
                        $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$current_date}'";
                        $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";

                        $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$current_date}'";
                        $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$current_date}'";
                        $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$current_date}'";
                        $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";
                    } else if ($start_date == '' && $end_date != ''){
                        $month = date('m', strtotime(date('Y-m')." -3 month"));
                        $start_date = $year . '-' . $month . '-' . '01';
                        $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                        $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                        $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                        $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                        $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                        $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                        $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                        $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";


                    } else if ($start_date != '' && $end_date != '') {
                        $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                        $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                        $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                        $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                        $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                        $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                        $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                        $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                    } else if ($monthVal == '' && $yearVal == ''){
                        $start_date = $year . '-' . $month . '-' . '01';
                        $end_date   = $year . '-' . $month . '-' . '31';
                        $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                        $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                        $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                        $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                        $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                        $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                        $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                        $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
                    }

                    if ($monthVal != '') {
                        $appendSqlStockTransfer .= "AND DATE_FORMAT(st.date, '%m') = '{$monthVal}'" ;
                        $appendSqlExpense .= "AND DATE_FORMAT(e.creation_date, '%m') = '{$monthVal}'";
                        $appendSqlInvoice .= "AND DATE_FORMAT(inv.invoice_date, '%m') = '{$monthVal}'";
                        $appendSqlPoOrder .= "AND DATE_FORMAT(po.purchase_order_date, '%m') = '{$monthVal}'";

                        $appendSqlStockTransferCurrent .= "AND DATE_FORMAT(st.date, '%m') <= '{$monthVal}'";
                        $appendSqlExpenseCurrent .= "AND DATE_FORMAT(e.creation_date, '%m') <= '{$monthVal}'";
                        $appendSqlInvoiceCurrent .= "AND DATE_FORMAT(inv.invoice_date, '%m') <= '{$monthVal}'";
                        $appendSqlPoOrderCurrent .= "AND DATE_FORMAT(po.purchase_order_date, '%m') <= '{$monthVal}'";
                    }

                    if ($yearVal != '') {
                        $appendSqlStockTransfer .= "AND DATE_FORMAT(st.date, '%Y') = '{$yearVal}'" ;
                        $appendSqlExpense .= "AND DATE_FORMAT(e.creation_date, '%Y') = '{$yearVal}'";
                        $appendSqlInvoice .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
                        $appendSqlPoOrder .= "AND DATE_FORMAT(po.purchase_order_date, '%Y') = '{$yearVal}'";

                        $appendSqlStockTransferCurrent .= "AND DATE_FORMAT(st.date, '%Y') = '{$yearVal}'" ;
                        $appendSqlExpenseCurrent .= "AND DATE_FORMAT(e.creation_date, '%Y') = '{$yearVal}'";
                        $appendSqlInvoiceCurrent .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
                        $appendSqlPoOrderCurrent .= "AND DATE_FORMAT(po.purchase_order_date, '%Y') = '{$yearVal}'";
                    }

                    $SQLStockTransfer = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowSite['site_id']}
                    {$appendSqlStockTransfer}
                    ";

                    $resultStockTransfer = $db->sql_query($SQLStockTransfer);
                    $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


                    $SQLStockTransferto = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty_to
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowSite['site_id']}
                    {$appendSqlStockTransfer}
                    ";

                    $resultStockTransferto = $db->sql_query($SQLStockTransferto);
                    $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

                    $SQLOthersite = "
                    SELECT
                        (SELECT SUM(qty) FROM po_product pp
                         LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                         WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         {$appendSqlPoOrder}
                         ) as product_qty_purchased

                       ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                        LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                        WHERE record_id = {$row['product_id']}
                          AND o.site_id = {$rowSite['site_id']}
                          {$appendSqlInvoice}
                        ) as product_qty_sold_from_quote

                        ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                        WHERE ini.record_id = {$row['product_id']}
                          AND inv.site_id = {$rowSite['site_id']}
                        {$appendSqlInvoice}
                        ) as sales_return_qty

                        ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                          LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                          WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         ) as damaged_qty
                    ";
                    $resultothersite = $db->sql_query($SQLOthersite);
                    $rowothersite = $db->sql_fetchrow($resultothersite);

                    $SqlExpenseProduct = "
                    SELECT SUM(ep.qty) AS qty
                    FROM expense_product ep
                    LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                    WHERE ep.product_id = {$row['product_id']}
                    AND ep.status = 'Added'
                    AND e.site_id = {$rowSite['site_id']}
                    AND ep.stock_deducted = 1
                    {$appendSqlExpense}
                    ";
                    $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
                    $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

                    /*** The following SQL for Current Stock ***/
                    $SQLStockTransferCurrent = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowSite['site_id']}
                    {$appendSqlStockTransferCurrent}
                    ";

                    $resultStockTransferCurrent = $db->sql_query($SQLStockTransferCurrent);
                    $rowStockTransferCurrent = $db->sql_fetchrow($resultStockTransferCurrent);


                    $SQLStockTransfertoCurrent = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty_to
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowSite['site_id']}
                    {$appendSqlStockTransferCurrent}
                    ";

                    $resultStockTransfertoCurrent = $db->sql_query($SQLStockTransfertoCurrent);
                    $rowStockTransfertoCurrent = $db->sql_fetchrow($resultStockTransfertoCurrent);

                    $SQLOthersiteCurrent = "
                    SELECT
                        (SELECT SUM(qty) FROM po_product pp
                         LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                         WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         {$appendSqlPoOrderCurrent}
                         ) as product_qty_purchased

                       ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                        LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                        WHERE record_id = {$row['product_id']}
                          AND o.site_id = {$rowSite['site_id']}
                          {$appendSqlInvoiceCurrent}
                        ) as product_qty_sold_from_quote

                        ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                        WHERE ini.record_id = {$row['product_id']}
                          AND inv.site_id = {$rowSite['site_id']}
                        {$appendSqlInvoiceCurrent}
                        ) as sales_return_qty

                        ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                          LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                          WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         ) as damaged_qty
                    ";
                    $resultothersiteCurrent = $db->sql_query($SQLOthersiteCurrent);
                    $rowothersiteCurrent = $db->sql_fetchrow($resultothersiteCurrent);

                    $SqlExpenseProductCurrent = "
                    SELECT SUM(ep.qty) AS qty
                    FROM expense_product ep
                    LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                    WHERE ep.product_id = {$row['product_id']}
                    AND ep.status = 'Added'
                    AND e.site_id = {$rowSite['site_id']}
                    AND ep.stock_deducted = 1
                    {$appendSqlExpenseCurrent}
                    ";
                    $resultExpenseProductCurrent = $db->sql_query($SqlExpenseProductCurrent);
                    $rowExpenseProductCurrent    = $db->sql_fetchrow($resultExpenseProductCurrent);
                    /*** Ends Here ***/

                    $stock = $rowothersiteCurrent['product_qty_purchased'] - $rowothersiteCurrent['product_qty_sold_from_quote'] + $rowothersiteCurrent['sales_return_qty'] - $rowothersiteCurrent['damaged_qty'] - $rowStockTransferCurrent['Transfer_qty'] + $rowStockTransfertoCurrent['Transfer_qty_to'] - $rowExpenseProductCurrent['qty'];
                    $purchased_qty = $rowothersite['product_qty_purchased'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    $sold_qty = $rowothersite['product_qty_sold_from_quote'] + $rowExpenseProduct['qty'];

                    $purchasedQtyRow .= '<td width="4.5%" align="center">'. $purchased_qty .'</td>';
                    $soldQtyRow .= '<td width="4.5%" align="center">'. $sold_qty .'</td>';
                    $stockQtyRow .= '<td width="4.5%" align="center">'. $stock .'</td>';

                }

                $tbl1 = $tbl1.'
                                    '.$purchasedQtyRow.'
                                    '.$soldQtyRow .'
                                    '.$stockQtyRow.'
                            </tr>
                ';
        
        }

        $tbl1 = $tbl1.'</tbody>
                    </table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->Output('Stock-Report.pdf', 'I');

    }

}