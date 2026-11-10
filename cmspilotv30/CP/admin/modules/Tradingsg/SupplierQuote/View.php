<?
class CP_Admin_Modules_Tradingsg_SupplierQuote_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');

        $rows  = "";
        $rowCounter = 0;


        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

	        $SQLTotal = "
				SELECT SUM(round(
				(sqhi.qty * sqhi.price),2)) AS total_cost
				FROM supplier_quote_history sqhi WHERE sqhi.supplier_quote_id = {$row['supplier_quote_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $total_cost = number_format($rowTotal['total_cost'] + $row['freight_cost']);
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['date'])}
            {$listObj->getListDataCell($total_cost)}
            {$listObj->getListDataCell($row['supplier_quote_id'], 'center')}
            {$listObj->getListRowEnd($row['supplier_quote_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Date', 'date')}
        {$listObj->getListHeaderCell('Total Cost', 'total_cost')}
        {$listObj->getListHeaderCell('Supplier Quote ID', 'supplier_quote_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn      = Zend_Registry::get('fn');
        
        $sqlSupplierQuoteStatus = $fn->getValuelistSql('supplierQuoteStatus');
        $expVl      = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
		{$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTBRow('Freight Cost', 'freight_cost', $row['freight_cost'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Supplier Quote Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $sort_order 	= $fn->getReqParam('sort_order');
        $printText ="";

        $urlSupplierQuote = "index.php?module=tradingsg_supplierQuote&_spAction=printExcelSupplierQuote&id={$row['supplier_quote_id']}&showHTML=0";

        //$formActionGroup = "index.php?_topRm=order&module=tradingsg_quote&_spAction=updateMarkupByGroupForm&quote_id={$row['quote_id']}&showHTML=0";

        $formActionGroup = "index.php?_topRm=inventory&module=tradingsg_supplierQuote&_spAction=addProductForm&supplier_quote_id={$row['supplier_quote_id']}&showHTML=0";
            //<a href='{$formActionGroup}' id='supplier_quote_id'>Add</a>
           // <a class='addProductForm' href='#'>Add</a>

            //$formActionGroup = "index.php?_topRm={$tv['topRm']}&module=tradingsg_supplierQuote&_spAction=addProductForm&supplier_quote_id={$row['supplier_quote_id']}&showHTML=0";

        $printText .= "
        <div class='button mb5'>
            <a href='#' id='raisePo' supplier_quote_id='{$row['supplier_quote_id']}'>Raise PO</a>
        </div> 
        <div class='button mb5'>
            <a href='{$urlSupplierQuote}' id='print'>Export to Excel</a>
        </div>
        <div class='button mb5'>
            <a href='{$formActionGroup}' id='addProductForm'>Add Product</a>
        </div> 
        ";

        $sortArray = array(
            "Product"
           ,"Supplier"
        );

        $text ="
        {$printText}
        <select name='sort_order'>
            <option value=''>Sort By</option>
            {$cpUtil->getDropDown1($sortArray, $sort_order)}
        </select>
        {$displayLinkData->getLinkPortalMain('tradingsg_supplierQuote', 'tradingsg_supplierQuoteHistoryLink', 'Product Linked', $row)}
        {$this->getPOPortalDisplay($row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_supplierQuote', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
        $supplier_id = $fn->getReqParam('supplier_id');

        $sqlProduct  = $fn->getDDSql('tradingsg_product');
        $sqlSupplier = "
	        SELECT c.company_id
	        	,company_name
	        FROM company c
	        WHERE category = 'Supplier'
        ";

        $text = "
        ";        

			/*<td>
            <select name='product_id'>
                <option value=''>Product Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlProduct, $product_id)}
            </select>
        </td>
        <td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>*/
        
        return $text;
    }

    /**
     */
    function getPOPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $formAction = '';        
                
        $text = "
        <tr class=''>
        <td>
            <div class='header'>Purchase Orders Linked</div>            
            <div id='' class='poDisplay'>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getPOPortalDisplayDetail($row)}
                    </div>
                </form>                 
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     */
    function getPOPortalDisplayDetail($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        
        $status = $fn->getReqParam('status');
        
        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }
        
        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);
        
        $SQL = "
        SELECT po.*
              ,com.company_name AS supplier_name
        FROM purchase_order po
        LEFT JOIN company com ON po.company_id_supplier = com.company_id
        WHERE po.supplier_quote_id = {$row['supplier_quote_id']}
        ORDER BY com.company_name
        ";

        $result   = $db->sql_query($SQL);  
        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $checkBoxStatus = '';
        $count = 1;
        $invoice_code = ''; 
        $add_registration_fee = '';
        $invoice_hist_amount  = '';
        
        while ($rowPo = $db->sql_fetchrow($result)) {
            $detail = "<a href='index.php?module=tradingsg_supplierQuote&_spAction=purchaseOrderViewDetail&id={$rowPo['purchase_order_id']}&showHTML=0' class='purchaseOrderDetail'>View Detail</a>";
            
            $printPo = "<a href='index.php?module=tradingsg_supplierQuote&_spAction=printPurchaseOrder&id={$rowPo['purchase_order_id']}' target='_blank'>PDF to Supplier</a>";
            
            $printPoWithPrice = "<a href='index.php?module=tradingsg_supplierQuote&_spAction=printPurchaseOrderWithPrice&id={$rowPo['purchase_order_id']}' target='_blank'>PDF with price</a>";
            
            if($rowPo['notes'] != ''){
                $addRemarks = "<a href='index.php?module=tradingsg_quote&_spAction=addNotePo&showHTML=0&id={$rowPo['purchase_order_id']}' class='addNotePo'>View Remarks</a>";
            } else {
                $addRemarks = "<a href='index.php?module=tradingsg_quote&_spAction=addNotePo&showHTML=0&id={$rowPo['purchase_order_id']}' class='addNotePo'>Add Remarks</a>";
            }
            
            $rows .= "
            <tr>
                <td>{$rowPo['po_code']}</td>
                <td>{$rowPo['supplier_name']}</td>
                <td>{$detail}</td>
                <td>{$printPo}</td>
                <td>{$printPoWithPrice}</td>
                <td>{$addRemarks}</td>
                <td>{$rowPo['created_by']} {$rowPo['creation_date']}</td>
                <td>{$rowPo['modified_by']} {$rowPo['modification_date']}</td>
            </tr>
            ";
        }
                                
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>PO Code</th>
        <th>Supplier Name</th>
        <th>View Detail</th>
        <th>Print PDF</th>
        <th>Print PDF/Price</th>
        <th>Add Remarks</th>
        <th>Created By</th>
        <th>Modified By</th>
        </tr>
        ";
        
        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintPurchaseOrder() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',11);

        $purchase_order_id = $fn->getReqParam('id');

        $SQL = "
        SELECT pop.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,c.company_name
              ,c.fax
              ,c.phone
              ,pop.creation_date
              ,po.po_code
              ,q.quote_code
              ,q.delivery_date
              ,q.delivery_location
        FROM po_product pop
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN company c ON (c.company_id = pop.supplier_id)
        LEFT JOIN quote q ON (q.quote_id = pop.quote_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE pop.purchase_order_id = {$purchase_order_id}
        ORDER BY pg.sort_order ASC, p.title
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
				if ($numRows == 0){
		            $pdf->SetXY(30,30);
		            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
					$pdf->Output();
					return;
				}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $creationDate = $fn->getCPDate($row['creation_date'], 'd-m-Y');
                $deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

                /* Company address */
                //Address to be got from settings
                /*
                $pdf->Cell(50, 20, '25 LORONG 39 GEYLANG SINGAPORE 387875');
                $pdf->Ln(5);
                $pdf->SetXY(140,5);

                $pdf->Cell(50, 20, 'TEL: +65 674 74 126 FAX: +65 674 84 322');
                $pdf->Ln(5);
                $pdf->SetXY(140,10);

                $pdf->Cell(50, 20, 'EMAIL: enquiry@novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140, 15);

                $pdf->Cell(50, 20, 'WEBSITE: www.novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140,20);
                $pdf->Cell( 50, 20, 'GST REG. NO: 201203469M');
                */
                $pdf->SetXY(140,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(140,5);
                $pdf->SetFont('Courier','',8);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(140,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(140,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(140, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(140,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "PURCHASE ORDER", 0, 0, 'C');                
                $pdf->Ln(20);


                /* Company Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(100,8,"VENDOR",1,0, 'L', 1);
                $pdf->Cell(45,8,"TEL",1,0, 'L', 1);
                $pdf->Cell(45,8,"FAX",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(100, 8, $row['company_name'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['phone'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['fax'], 1, 0, 'L', 1);
                $pdf->Ln(20);

			    $quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);
								
                /* Purchase Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(70,8,"QUOTE CODE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO DATE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO CODE",1,0, 'L', 1);
                $pdf->Cell(60,8,"DELIVERY DATE",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            //$pdf->Cell(70, 8, $formatedQC[3], 1, 0, 'L', 1);
	            $pdf->Cell(70, 8, $quoteCode, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $creationDate, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $row['po_code'], 1, 0, 'L', 1);
	            $pdf->Cell(60, 8, $deliveryDate, 1, 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(190,8,"LOCATION: {$row['delivery_location']}",1,0, 'L', 1);
                //$pdf->Cell(30,8,"TIME: AM(TBC)",1,0, 'L', 1);
                //$pdf->Cell(33,8,"DELIVERY DATE : ",'TBL',0, 'L', 1);
				//$pdf->Cell(27,8, $deliveryDate, 'TBR', 0, 'L', 1);
                $pdf->Ln(10);

				$pdf->Cell(30,15,"(Note : Please mention the exact Item Code for all the products.)",0,0, 'L', 1);
                $pdf->Ln(10);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25,8,"ITEM CODE",1,0, 'C', 1);
                $pdf->Cell(145,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Ln();
            }
            
            //===================================MAIN TABLE============================= //
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(25, 8, $row['item_code'], 1, 0, 'L', 1);
            //$pdf->Cell(145, 8, substr($row['product_title'], 0, 61), 1, 0, 'L', 1);
            $pdf->Cell(145, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Ln();
            
            $count++;
            $lineItemNumber++;
        } 
        
	        /* Creation of media record of the invoice */
	        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
	        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
	
	        $outputFileName = $outputPath . '/' . $file_name;
	        //$pdf->Output($outputFileName , "F");
			$pdf->Output();

    }

    /**
     *
     */
    function getPrintPurchaseOrderWithPrice() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

				$pdf->AddPage();
				$pdf->SetFont('Arial','',11);

        $purchase_order_id = $fn->getReqParam('id');

        $SQL = "
        SELECT pop.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,c.company_name
              ,c.fax
              ,c.phone
              ,pop.creation_date
              ,q.delivery_date
              ,q.delivery_location
              ,pop.price
              ,po.po_code
              ,q.quote_code
              ,q.currency
              ,pop.qty * pop.price AS amount
              ,(SELECT SUM(popp.qty * popp.price) FROM po_product popp
               WHERE popp.purchase_order_id = pop.purchase_order_id) AS sub_total
        FROM po_product pop
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN company c ON (c.company_id = pop.supplier_id)
        LEFT JOIN quote q ON (q.quote_id = pop.quote_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE pop.purchase_order_id = {$purchase_order_id}
        ORDER BY pg.sort_order ASC, p.title
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
				if ($numRows == 0){
		            $pdf->SetXY(30,30);
		            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
					$pdf->Output();
					return;
				}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
		
		
        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $creationDate = $fn->getCPDate($row['creation_date'], 'd-m-Y');
                $deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

				$printTaxName = $cpCfg['printTaxName'] ;
				$gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
				$gstvalue = $row['sub_total'] * $gsttaxvalue / 100;
				$totalvalue = $gstvalue + $row['sub_total'];
				
                /* Company address */
                //Address to be got from settings
                /*
                $pdf->Cell(50, 20, '25 LORONG 39 GEYLANG SINGAPORE 387875');
                $pdf->Ln(5);
                $pdf->SetXY(140,5);
                $pdf->Cell(50, 20, 'TEL: +65 674 74 126 FAX: +65 674 84 322');
                $pdf->Ln(5);
                $pdf->SetXY(140,10);
                $pdf->Cell(50, 20, 'EMAIL: enquiry@novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140, 15);
                $pdf->Cell(50, 20, 'WEBSITE: www.novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140,20);
                $pdf->Cell( 50, 20, 'GST REG. NO: 201203469M');
                */
                $pdf->SetXY(140,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(140,5);
                $pdf->SetFont('Courier','',8);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(140,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(140,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(140, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(140,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "PURCHASE ORDER WITH PRICE", 0, 0, 'C');                
                $pdf->Ln(20);


                /* Company Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(100,8,"VENDOR",1,0, 'L', 1);
                $pdf->Cell(45,8,"TEL",1,0, 'L', 1);
                $pdf->Cell(45,8,"FAX",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(100, 8, $row['company_name'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['phone'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['fax'], 1, 0, 'L', 1);
                $pdf->Ln(20);

				$currency = $row['currency'];
				$quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);

                /* Purchase Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(70,8,"QUOTE CODE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO DATE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO CODE",1,0, 'L', 1);
                $pdf->Cell(60,8,"DELIVERY DATE",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            //$pdf->Cell(70, 8, $formatedQC[3], 1, 0, 'L', 1);
	            $pdf->Cell(70, 8, $quoteCode, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $creationDate, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $row['po_code'], 1, 0, 'L', 1);
	            $pdf->Cell(60, 8, $deliveryDate, 1, 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(190,8,"LOCATION: {$row['delivery_location']}",1,0, 'L', 1);
                //$pdf->Cell(30,8,"TIME: AM(TBC)",1,0, 'L', 1);
                //$pdf->Cell(33,8,"DELIVERY DATE : ",'TBL',0, 'L', 1);
				//$pdf->Cell(27,8, $deliveryDate, 'TBR', 0, 'L', 1);
                $pdf->Ln(10);


				$pdf->Cell(30,15,"(Note : Please mention the exact Item Code for all the products.)",0,0, 'L', 1);
                $pdf->Ln(10);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(17,8,"ITEM NO",1,0, 'C', 1);
                $pdf->Cell(24,8,"ITEM CODE",1,0, 'C', 1);
                $pdf->Cell(86,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(19,8,"UP",1,0, 'C', 1);
                $pdf->Cell(25,8,"AMOUNT(" . $row['currency'] . ")",1,0, 'C', 1);
                $pdf->Ln();
            }
            
            //===================================MAIN TABLE============================= //
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(17, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(24, 8, $row['item_code'], 1, 0, 'L', 1);
            //$pdf->Cell(91, 8, substr($row['product_title'], 0, 61), 1, 0, 'L', 1);
            $pdf->Cell(86, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(19, 8, $row['price'], 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $row['amount'], 1, 0, 'R', 1);
            $pdf->Ln();
            
            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
        } 
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, "SUB TOTAL {$currency}", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $sub_total, 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, "ADD: {$printTaxName} {$gsttaxvalue}%", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($gstvalue, 2), 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($totalvalue, 2), 1, 0, 'R', 1);
			$pdf->Ln(20);
        
	        /* Creation of media record of the invoice */
	        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
	        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
	
	        $outputFileName = $outputPath . '/' . $file_name;
	        //$pdf->Output($outputFileName , "F");
			$pdf->Output();

    }

    /**
     *
     */
    function getPurchaseOrderViewDetail() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_id = $fn->getReqParam('id');
        $rows = '';

        $SQL = "
        SELECT p.product_id                
              ,p.title AS product_name
              ,po.po_product_id
              ,po.price
              ,po.qty
              ,po.qty_delivered
              ,po.qty_due
              ,po.qty_cancelled
              ,com.company_name AS supplier_name
        FROM po_product po
        LEFT JOIN product p ON (p.product_id = po.product_id)
        LEFT JOIN purchase_order purOrd ON (po.purchase_order_id = purOrd.purchase_order_id)
        LEFT JOIN company com ON (purOrd.company_id_supplier = com.company_id)
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result   = $db->sql_query($SQL);  

        while ($row = $db->sql_fetchrow($result)) {
            $supplier_name = $row['supplier_name'];
            $po_product_id = $row['po_product_id'];
            $qty_delivered = "<input type='text' value='{$row['qty_delivered']}' id='qty_delivered' class='text txtRight w50' name='qty_delivered' po_product_id='{$po_product_id}'>";
            $qty_cancelled = "<input type='text' value='{$row['qty_cancelled']}' id='qty_cancelled' class='text txtRight w50' name='qty_cancelled' po_product_id='{$po_product_id}'>";
            $qtyBalance = $row['qty'] - $row['qty_delivered'];
            $qtyBalance = $qtyBalance - $row['qty_cancelled'];
            $rows .= "
            <tr>
                <td>{$row['product_name']}</td>
                <td class='txtRight'>{$row['price']}</td>
                <td class='qtyOrdered txtRight'>{$row['qty']}</td>
                <td class=''>{$qty_delivered}</td>
                <td class='txtRight'>{$qtyBalance}</td>
                <td class='qtyCancelled'>{$qty_cancelled}</td>
            </tr>
            ";
       }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Product</th>
        <th>Cost Price</th>
        <th>Qty Ordered</th>
        <th>Qty Delivered</th>
        <th>Qty Due</th>
        <th>Qty Cancelled</th>
        </tr>
        ";
        
        $text = "
        <h1>Supplier Name: $supplier_name</h1>
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";
        return $text;
    }
    /**
     *
     */
    function getProductViewHistory() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db'); 
        $dateUtil = Zend_Registry::get('dateUtil');

        $supplier_quote_history_id = $fn->getReqParam('supplier_quote_history_id');

        $supplierHistRec           = $fn->getRecordRowByID('supplier_quote_history', 'supplier_quote_history_id', $supplier_quote_history_id);

        $rows = '';

        $SQL = "
        SELECT p.product_id                
              ,p.title AS product_name
              ,poProd.po_product_id
              ,poProd.price
              ,poProd.qty
              ,c.company_name as supplier_name
              ,po.purchase_order_date
        FROM po_product poProd
        JOIN product p ON (p.product_id = poProd.product_id)
        JOIN company c ON (c.company_id = poProd.supplier_id)
        JOIN purchase_order po ON (po.purchase_order_id = poProd.purchase_order_id)
        WHERE poProd.product_id = {$supplierHistRec['product_id']}
        ORDER BY purchase_order_date DESC, poProd.price
        ";
        $result   = $db->sql_query($SQL);  

        while ($row = $db->sql_fetchrow($result)) {
            $purchase_order_date = $dateUtil->formatDate($row['purchase_order_date'], 'DD-MM-YYYY');
            $rows .= "
            <tr> 
                <td>{$purchase_order_date}</td>
                <td class=''>{$row['supplier_name']}</td>
                <td class=''>{$row['price']}</td>
                <td class=''>{$row['qty']}</td>
            </tr>
            ";
       }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Date</th>
        <th>Supplier Name</th>
        <th>Price</th>
        <th>Qty</th>
        </tr>
        ";
        
        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";
        return $text;
    }

    /**
     *
     */
    function getPrintExcelSupplierQuote() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
       
        $supplier_quote_id  = $fn->getReqParam('id');
        $template = 'Quote-General-Trading.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $file_name = 'Quote-Product_' . $supplier_quote_id;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $today =  date('d/m/Y');
		$gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
               
        $SQL = "
        SELECT sqh.*
              ,p.title AS product_title
              ,p.unit
              ,c.company_name
              ,(SELECT SUM(sqh.qty * sqh.price) FROM  supplier_quote_history sqh
               WHERE sq.supplier_quote_id = sqh.supplier_quote_id) AS total
        FROM supplier_quote_history sqh
        LEFT JOIN product p ON (p.product_id = sqh.product_id)
        LEFT JOIN supplier_quote sq ON (sq.supplier_quote_id = sqh.supplier_quote_id)
        LEFT JOIN company c ON (c.company_id = sqh.supplier_id)
        WHERE sqh.supplier_quote_id = {$supplier_quote_id}
        ";
        
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();       
        $blkProduct     = array();
        $blkQty         = array();
        $blkUnit         = array();
        $blkPrice       = array();
        $blkSerialNo    = array();
        $blkAmount    = array();
        
        while ($row = $db->sql_fetchrow($result)) {
            //repeating rows of product values
            $arr1 = array('product_title' => $row['product_title']);
            $blkProduct[] = $arr1;

            $arr2 = array('qty' => $row['qty']);
            $blkQty[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr4 = array('price' => $row['price']);
            $blkPrice[] = $arr4;

            $arr6 = array('amount' => number_format($row['price']* $row['qty'], 2));
            $blkAmount[] = $arr6;

            $arr7 = array('company_name' => $row['company_name']);
            $blkCompanyName[] = $arr7;

            $arr8 = array('status' => $row['status']);
            $blkStatus[] = $arr8;

            $arr['total'] =  $row['total'];
            $blkMain[] = $arr;
            
            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);         
        $TBS->MergeBlock('blkProduct', $blkProduct);         
        $TBS->MergeBlock('blkQty', $blkQty);         
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);         
        $TBS->MergeBlock('blkPrice', $blkPrice);        
        $TBS->MergeBlock('blkStatus', $blkStatus);        
        $TBS->MergeBlock('blkCompanyName', $blkCompanyName);        
        $TBS->MergeBlock('blkAmount', $blkAmount);        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
        
    }

    /**
     *
     */
     function getAddProductForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        
        $supplier_quote_id  = $fn->getReqParam('supplier_quote_id');

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');
    
        $formAction = "index.php?_topRm=inventory&module=tradingsg_supplierQuote&_spAction=addProductFormSubmit&showHTML=0";

		$sqlProductGroup = "
		SELECT product_group_id
			  ,title
		FROM product_group
		";

        $result = $db->sql_query($sqlProductGroup);
        $row = $db->sql_fetchrow($result); 

        $text = "
        <form id='portalForm' class='yform columnar productForm' method='post' action='{$formAction}'>
			{$formObj->getTBRow('Product Name', 'title')}
            {$formObj->getDDRowBySQL('Product Group', 'product_group_id', $sqlProductGroup)}
	        {$formObj->getDDRowBySQL('Unit', 'unit', $sqlUnit, '', $expVl)}
			{$formObj->getTARow('Description', 'description', '')}
        </form>
        ";
        return $text;

    }

}