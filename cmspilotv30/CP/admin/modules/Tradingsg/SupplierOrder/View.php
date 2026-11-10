<?
class CP_Admin_Modules_Tradingsg_SupplierOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rowCounter = 0;
        $rows  = '';

        foreach ($dataArray as $row){

            $date   = $fn->getCPDate($row['date'], 'd-m-Y');
            $url ="/admin/index.php?module=tradingsg_supplierOrder&_spAction=printPurchaseOrder&supplier_id={$row['supplier_id']}&supplier_order_id={$row['supplier_order_id']}&showHTML=0";
            $print = "<a href='{$url}' target='_blank'>Print SO PDF</a>";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['so_code'])}
            {$listObj->getListDataCell($date)}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($print)}
            {$listObj->getListDataCell($row['creation_date'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('SO Code', 'so.so_code')}
        {$listObj->getListHeaderCell('Date', 'so.date')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Print SO', 'supplier_name')}
        {$listObj->getListHeaderCell('Creation Date', 'creation_date')}
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
        $fn = Zend_Registry::get('fn');

        $sqlSupplier = $fn->getDDSql('tradingsg_company', array('condn' => "category = 'Supplier'"));
        $currentDate = date('Y-m-d');

        $fieldset = "
        {$formObj->getDateRow('Date', 'date', $currentDate)}
        {$formObj->getDDRowBySQL('Supplier', 'supplier_id', $sqlSupplier)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Supplier Order Header', $fieldset)}
        ";

        return $text;

    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $expNoEdit = array('isEditable' => 0);
        $expCompany = array('sqlType' => 'OneField');
        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('SO Code', 'so_code', $row['so_code'], $expNoEdit)}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTBRow('Supplier', 'supplier_name', $row['supplier_name'], $expNoEdit)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Supplier Order Header', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){

        $text = "
        {$this->getProductPortalDisplay($row['supplier_order_id'])}
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

        $company_id = $fn->getReqParam('company_id');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";

        //$sqlCompany = $fn->getDDSql('tradingsg_company');

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     */
    function getProductPortalDisplay($supplier_order_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows    = "";
        $printPO = '';
        $soRow   = $fn->getRecordRowByID('supplier_order', 'supplier_order_id', $supplier_order_id);

        $SQL = "
        SELECT po.*
              ,q.title AS quote_title
              ,c.company_name AS supplier_name
              ,co.company_name AS client_name
              ,soh.purchase_order_id as supplier_history_purchase_order_id
        FROM purchase_order po
        LEFT JOIN company c ON c.company_id = po.company_id_supplier
        LEFT JOIN quote q ON q.quote_id = po.quote_id
        LEFT JOIN company co ON co.company_id = q.company_id
        LEFT JOIN supplier_order_history soh ON po.purchase_order_id = soh.purchase_order_id
        WHERE po.company_id_supplier = {$soRow['supplier_id']}
            AND po.status != 'closed'
            AND po.status != 'cancelled'
        ORDER BY soh.purchase_order_id DESC, po.creation_date DESC
        ";

        $result   = $db->sql_query($SQL);

        while ($rowPO = $db->sql_fetchrow($result)) {

            //$histRow = $fn->getRecordRowByID('supplier_order_history', 'purchase_order_id', $rowPO['purchase_order_id']);

            if($rowPO['supplier_history_purchase_order_id'] != '') {
                $checkbox = "<img src='/cmspilotv30/CP/common/images/icons/checkbox_checked.gif'>";
            } else {
                $checkbox = "<img src='/cmspilotv30/CP/common/images/icons/checkbox_unchecked.gif'>";
            }

            $printPO = "index.php?module=tradingsg_supplierOrder&_spAction=printPurchaseOrder&supplier_id={$soRow['supplier_id']}&supplier_order_id={$supplier_order_id}&showHTML=0";
            $urlPo = "index.php?_topRm=order&module=tradingsg_purchaseOrder&_action=edit&record_id={$rowPO['purchase_order_id']}";
            $showPoProduct = "index.php?module=tradingsg_supplierOrder&_spAction=pORelatedProducts&purchase_order_id={$rowPO['purchase_order_id']}&supplier_order_id={$supplier_order_id}&showHTML=0";

            $rows .= "
            <tr>
                <td class='txtCenter'>{$checkbox}</td>
                <td>{$fn->getCPDate($rowPO['creation_date'], 'd-m-Y')}</td>
                <td><a href='{$urlPo}'>{$rowPO['po_code']}</a></td>
                <td>{$rowPO['quote_title']}</td>
                <td>{$rowPO['client_name']}</td>
                <td>{$rowPO['status']}</td>
                <td><a href='{$showPoProduct}' id='shwoPoProduct'>VIEW</a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th></th>
            <th>Date</th>
            <th>PO Code</th>
            <th>Title</th>
            <th>Client</th>
            <th>Status</th>
            <th>View Products</th>
        </tr>
        ";

        $text = "
        <div class='float_right button mb10'>
            <a href='{$printPO}' target='_blank'>PRINT PO</a>
        </div>
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
     function getPORelatedProducts() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $supplier_order_id = $fn->getReqParam('supplier_order_id');

        $sqlPoProduct = "
        SELECT pop.*
              ,p.title AS product_title
        FROM po_product pop
        LEFT JOIN product p ON p.product_id = pop.product_id
        WHERE pop.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($sqlPoProduct);

        while ($row = $db->sql_fetchrow($result)) {
            $checkbox = '';
            $supplRec = $fn->getRecordRowByID('supplier_order', 'supplier_order_id', $row['supplier_order_id'] );

            if($row['supplier_order_id'] > 0 ) {
                $checkbox = "checked ='checked'";
            } else {
                $checkbox = '';
            }

            if($row['supplier_order_id'] == $supplier_order_id || $row['supplier_order_id'] == 0){

                $inputRow = "<input class='purchaseOrderId checkProduct' type='checkbox' {$checkbox}  name='purchaseOrderId[]'
                         value='{$row['purchase_order_id']}' product_id='{$row['product_id']}' supplier_order_id='{$supplier_order_id}'>";
            }
            else{
                $inputRow = "";
            }

            $rows .= "
            <tr>
                <td>
                    {$inputRow}
                </td>
                <td>{$row['product_title']}</td>
                <td>{$row['qty']}</td>
                <td>{$row['po_product_id']}</td>
                <td>{$supplRec['so_code']}</td>
            </tr>
            ";
        }

        $text = "
        <form id='portalForm' class='yform columnar' method='post'>
            <div class=''>
                (Note: Please check the products to print out)
            </div>

            <table class='thinlist room-order-table'>
                <thead>
                    <th></th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Related SO</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>

        </form>
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
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        //$pdf = new MYPDF();
        $pdf = new PDF_MC_Table();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',11);

        $supplier_id 	= $fn->getReqParam('supplier_id');
        $supplier_order_id 	= $fn->getReqParam('supplier_order_id');
        $delivery_terms = $fn->getReqParam('delivery_terms');
        $notes    		= $fn->getReqParam('notes');
        $soRow          = $fn->getRecordRowByID('supplier_order', 'supplier_order_id', $supplier_order_id);
        $sohRow         = $fn->getRecordRowByID('supplier_order_history', 'supplier_order_id', $supplier_order_id);

	    $SQL = "
        SELECT pop.*
              ,p.title AS product_title
              ,p.part_number
              ,p.unit
              ,p.item_code
              ,supl.p_f
              ,supl.cst
              ,supl.vat
              ,supl.company_name
			  ,supl.address_flat
			  ,supl.address_street
			  ,supl.address_town
			  ,supl.address_state
			  ,supl.address_country
              ,supl.fax
              ,supl.phone
              ,supl.add_vat
              ,supl.add_cst
              ,supl.add_pf
              ,supl.add_freight_cost
              ,pop.creation_date
              ,po.po_code
              ,po.status
              ,po.delivery_terms
              ,po.notes
              ,po.payment_terms
		      ,po.freight_cost
              ,q.quote_code
              ,q.delivery_date
              ,q.delivery_location
              ,q.company_id
              ,(SELECT SUM(poph.qty) FROM  po_product poph
		        LEFT JOIN purchase_order poo ON (poo.purchase_order_id = poph.purchase_order_id)
               WHERE poph.product_id = pop.product_id
                    AND po.status != 'closed'
                    AND po.status != 'cancelled'
                    AND poph.supplier_order_id > 0
               GROUP BY item_code) AS sum_qty
        FROM po_product pop
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN company supl ON (supl.company_id = pop.supplier_id)
        LEFT JOIN quote q ON (q.quote_id = pop.quote_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE po.company_id_supplier = {$supplier_id}
            AND po.status != 'closed'
            AND po.status != 'cancelled'
            AND pop.supplier_order_id > 0

         ";

        $result = $db->sql_query($SQL);
        $result1 = $db->sql_query($SQL);
        //print $SQL;
        $numRows  = $db->sql_numrows($result);

        if ($numRows < 1){
			$pdf->SetFont('Courier','B',11);
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please check the related product to print the Purchase Order PDF");
			$pdf->Output();
		} else {

        $today = date("d-m-Y");
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
		$totalAmount = '';
		$company_names = '';
		$delivery_terms = '';
		$delivery_term = '';
		$notes = '';
		$note  = '';
		$subTotal = '';
		$pf = '';
		$freight_cost = '';
        $add_cst= '';
        $add_vat = '';
        $cst = '';
        $vat = '';
        $po_id = '';
        $payment_terms = '';
        $payment_term = '';
        $add_pf = '';
        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        $num = '';
        $pdf->SetWidths(array(10, 65, 35, 10, 10, 30, 30));
        $pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'R', 'R'));

        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                //$pdf->Cell(50, 20, "Authorized Distributor of");
                //$pdf->SetXY(10,25);
                //$pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate = $fn->getCPDate($row['creation_date'], 'd-m-Y');
                $deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

                $pdf->SetXY(130,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(130,5);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(130,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(130,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->SetXY(130,30);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->SetXY(130,35);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 40);
                $pdf->Cell(21, 20, "PURCHASE ORDER", 0, 0, 'C');
                $pdf->Ln(25);

				$pdf->SetXY(155, 55);
                $pdf->SetFont('Courier','B',11);
				$pdf->Cell(20, 8, "DATE : {$today}", 0, 0, 'L');
				$pdf->Ln(10);

	            $pdf->SetXY(10, 55);
	            $pdf->Cell(21, 10, "SO CODE:", 0, 0, 'L');
	            $pdf->Cell(21, 10, $soRow['so_code'], 0, 0, 'L');
				$pdf->Ln(10);

                /* Company Details*/
				$billingAddressFlat     = $row['address_flat'];
				$billingAddressStreet   = $row['address_street'];
				$billingAddressTown     = $row['address_town'];
				$billingAddressState    = $row['address_state'];
				$billingAddressCountry  = $row['address_country'];

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(190,8,"PURCHASE ORDER TO",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(190, 8, $row['company_name'], 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(190, 5, $billingAddressFlat, 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(190, 5, $billingAddressStreet, 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(190, 5, $billingAddressTown, 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(190, 5, $billingAddressCountry . ' - ' . $billingAddressState, 'BLR', 0, 'L', 1);
                $pdf->Ln(10);

			    $quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);

                /* Company Details*/
                $date = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(95,8,"INVOICE TO",1,0, 'L', 1);
                $pdf->Cell(95,8,"DELIVERY TO",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(95, 8, $cpCfg['cp.companyName'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 8, $cpCfg['cp.companyName'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf1'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf1'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf2'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf2'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf3'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf3'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf4'], 'LRB', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf4'], 'LRB', 0, 'L', 1);
                $pdf->Ln(10);


                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(10,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(65,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(35,8,"PART-NUMBER",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(30,8,"PRICE",1,0, 'C', 1);
                $pdf->Cell(30,8,"TOTAL",1,0, 'C', 1);
                $pdf->Ln();

				$fa = array();
				$po_code = array();
                $poIds = array();

                if($row['add_cst'] == 1 && $row['add_vat'] == 0){
                    $add_cst = $row['cst'];
                }
                if($row['add_vat'] == 1 && $row['vat'] > 0){
                    $add_vat = $row['vat'];
                }
            }

            //===================================MAIN TABLE============================= //
		    $companyRec  	= $fn->getRecordRowByID('company', 'company_id', $row['company_id']);
			$company_name 	= $companyRec['company_name'];

			$poIdkey = array_search($row['purchase_order_id'], $poIds);
			if ($poIdkey != true) {
				$poIds[$row['purchase_order_id']] = $row['purchase_order_id'];

				if($row['delivery_terms']){
                    $delivery_terms = $row['delivery_terms'];
                    $delivery_term .= $delivery_terms . "\n"  ;
                }

				if($row['notes']){
                    $notes = $row['notes'];
                    $note .= $notes . "\n";
                }

				if($row['payment_terms']){
                    $payment_term = $row['payment_terms'];
                    $payment_terms .= $payment_term . "\n";
                }
		    }

        	$p_f = $row['p_f'];
        	$freight_cost += $row['freight_cost'];
        	$add_pf = $row['add_pf'];

			$poCode = $row['po_code'];
			list($code, $number) = explode("-", $poCode);
			$pokey = array_search($number, $po_code);
			if ($pokey != true) {
				$po_code[$number] = $number;
			    $num .= "-" . $number;
		    }

			//$company_names .= $company_name . ",";

			$key = array_search($row['item_code'], $fa);

			if ($key != true) {
				$fa[$row['item_code']] = $row['item_code'];
				//print_r($fa[$row['product_title']]);
				$total = $row['sum_qty'] * $row['price'];
				$totalDis = number_format($total,2);
				$subTotal += $total;

				$totalAmount += $total;
                //$product_title = substr($row['product_title'], 0, 15);
                $product_title = $row['product_title'];
                $price = number_format($row['price'], 2);

                $pdf->Row(array($lineItemNumber, $product_title , $row['part_number'], $row['unit'], $row['sum_qty'],$price, $totalDis));

                /*
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(10, 8, $lineItemNumber, 1, 0, 'L', 1);
                $pdf->Cell(70, 8, $product_title, 1, 0, 'L', 1);
                $pdf->Cell(35, 8, $row['part_number'], 1, 0, 'R', 1);
                $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
                $pdf->Cell(10, 8, $row['sum_qty'], 1, 0, 'R', 1);
                $pdf->Cell(25, 8, $price, 1, 0, 'R', 1);
                $pdf->Cell(30, 8, $totalDis, 1, 0, 'R', 1);
                $pdf->Ln();
                */
			}

            $count++;
            $lineItemNumber++;
            $pfPercent = $row['p_f'];

        }
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(160, 8, "SUB TOTAL", 1, 0, 'R', 1);
        $pdf->Cell(30, 8, number_format($subTotal,2), 1, 0, 'R', 1);
        $pdf->Ln();
		$totalAmountDis = number_format($totalAmount,0);

		$p_f = '';
		if($add_pf == 1){
            $p_f = $subTotal * ($pfPercent / 100);
			$pdf->Cell(160, 8, "ADD P&F: {$pfPercent}%" , 1, 0, 'R', 1);
			$pdf->Cell(30, 8, number_format($p_f, 2), 1, 0, 'R', 1);
			$pdf->Ln();
        }

		if($freight_cost > 0){
            $pdf->Cell(160, 8, "ADD FREIGHT COST: " , 1, 0, 'R', 1);
            $pdf->Cell(30, 8, number_format($freight_cost, 2), 1, 0, 'R', 1);
            $pdf->Ln();
        }
        $subTotal = $subTotal + $p_f + $freight_cost;

        if($add_cst){
            $cst = $subTotal * ($add_cst/ 100);
            $pdf->Cell(160, 8, "ADD CST: {$add_cst}%" , 1, 0, 'R', 1);
            $pdf->Cell(30, 8, number_format($cst, 2), 1, 0, 'R', 1);
            $pdf->Ln();
        }
        if($add_vat){
            $vat = $subTotal * ($add_vat/ 100);
            $pdf->Cell(160, 8, "ADD VAT: {$add_vat}%" , 1, 0, 'R', 1);
            $pdf->Cell(30, 8, number_format($vat, 2), 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $totalAmount = $subTotal + $vat + $cst;

		//$totalAmountDis = number_format($totalAmount,2);

        $pdf->SetFont('Courier','B',11);
        $pdf->Cell(160,8,"TOTAL",1,0, 'R', 1);
        $pdf->SetFont('Courier','B',11);
        $pdf->Cell(30,8, number_format($totalAmount, 2),1,0, 'R', 1);
        $pdf->Ln(10);

        /*$xaxis = $pdf->GetX();
        $yaxis = $pdf->GetY();
        $po_code = "PO{$num}";
        $pdf->SetXY(10, 55);
        $pdf->Cell(21, 10, "PO CODE:", 0, 0, 'L');
        $pdf->Cell(21, 10, $po_code, 0, 0, 'L');
        $pdf->Ln(10);
        $pdf->SetXY($xaxis, $yaxis);*/

		$pdf->Ln(5);

        //$pdf->SetXY(130,15);
        if($payment_terms){
            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(195,8, "Payment Terms :", 0, 0, 'L', 1);
            $pdf->Ln(12);
            $pdf->SetFillColor(255,255,255);
            $pdf->drawTextBox($payment_terms, 170, 32, 'L', 'T', 0);
            $pdf->Ln();
            $pdf->Ln(5);
        }

        if($delivery_term){
            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(195,8, "Delivery Terms :", 0, 0, 'L', 1);
            $pdf->Ln(12);
            $pdf->SetFillColor(255,255,255);
            $pdf->drawTextBox($delivery_term, 170, 32, 'L', 'T', 0);
            $pdf->Ln();
            $pdf->Ln(5);
        }

		//$note = substr($note, 0, -3);
        if($note){
            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(195,8, "Notes :", 0, 0, 'L', 1);
            $pdf->Ln(12);
            $pdf->SetFillColor(255,255,255);
            $pdf->drawTextBox($note, 170, 32, 'L', 'T', 0);
        }

        $pdf->Ln(5);
        /* Best Regards & Engex Power */
        $pdf->Cell(55, 5, $cpCfg['printBestRegards']);
        $pdf->SetX(10);
        $pdf->Cell(55, 16, $cpCfg['printEngexPower']);

        /* Creation of media record of the invoice */
        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        //$pdf->Output($outputFileName , "F");
		$pdf->Output();
    }
	}
}