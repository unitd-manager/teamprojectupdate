<?
class CP_Admin_Modules_Tradingsg_PackingList_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['packing_list_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDateCell($row['packing_list_date'])}
            {$listObj->getListDataCell($row['port_of_loading'])}
            {$listObj->getListDataCell($row['port_of_discharge'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['packing_list_id'])}

            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Packing List Code', 'pl.packing_list_code')}
        {$listObj->getListHeaderCell('Title', 'pl.title')}
        {$listObj->getListHeaderCell('Date', 'pl.packing_list_date')}
        {$listObj->getListHeaderCell('Port of Loading', 'pl.port_of_loading')}
        {$listObj->getListHeaderCell('Port of Discharge', 'pl.port_of_discharge')}
        {$listObj->getListHeaderCell('Status', 'pl.status')}
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
        {$formObj->getTextBoxRow('Title', 'title')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln'); 

        $expVl              = array('sqlType' => 'OneField');
        $sqlCountry         = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlContainerSize   = $fn->getValueListSQL('containerSize');
        $sqlStatus          = $fn->getValueListSQL('packingListStatus');

        $fieldset1 = "
		{$formObj->getTBRow('Title', 'title', $row['title'])}
  		{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
		{$formObj->getDateRow('Packing Date', 'packing_list_date', $row['packing_list_date'])}
		{$formObj->getTBRow('Port Of Loading', 'port_of_loading', $row['port_of_loading'])}
		{$formObj->getTBRow('Port Of Discharge', 'port_of_discharge', $row['port_of_discharge'])}
		{$formObj->getDDRowBySQL('Country of Final Destination', 'final_destination_country',$sqlCountry, $row['final_destination_country'])}
		{$formObj->getTBRow('Exporters Reference No', 'exporters_reference_no', $row['exporters_reference_no'])}
        {$formObj->getTBRow('Buyers Order No', 'buyers_order_no', $row['buyers_order_no'])}
        {$formObj->getTARow('Terms', 'terms', $row['terms'])}
        {$formObj->getYesNoRRow('Show Bank Name in Packing List', 'show_bank_name', $row['show_bank_name'])}
		{$formObj->getTBRow('Vessel', 'vessel', $row['vessel'])}
		{$formObj->getDateRow('Departure Date', 'departure_date', $row['departure_date'])}        
        {$formObj->getDDRowBySQL('Origin Goods Country', 'origin_goods_country', $sqlCountry, $row['origin_goods_country'])}
  		{$formObj->getDDRowBySQL('Container Size', 'container_size', $sqlContainerSize, $row['container_size'], $expVl)}
		{$formObj->getTBRow('No of Cartons', 'no_of_cartons', $row['no_of_cartons'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
		{$formObj->getTBRow('Net Wt (Kg)', 'net_wt', $row['net_wt'])}
		{$formObj->getTBRow('Gross Wt (Kg)', 'gross_wt', $row['gross_wt'])}
		{$formObj->getTBRow('Cube M3', 'cube_m3', $row['cube_m3'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Packing List', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
        {$formObj->getTextBoxRow('Code', 'code', $row['code'])}
        {$formObj->getTextBoxRow('Margin', 'margin', $row['margin'])}
     *
     */
    function getQuickSearch() {

        $text = '';

        return $text;
    }
    
    /**
     *
     */
    function getRightPanel($row) {
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $urlPackingListAsPdf = "index.php?module=tradingsg_packingList&_spAction=printPackingListAsPdf&id={$row['packing_list_id']}&showHTML=0";
		
        $packingListAsPdf ="
        <div class='button mb5'>
            <a href='{$urlPackingListAsPdf}' target='blank' id='packinglistasPdf'>Packing List PDF</a>
        </div>
        ";
        $text = "
        {$packingListAsPdf}
        ";

        return $text;
    }    

    /**
     *
     */
    function getGeneratePackingListForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';
               
        $order_id = $fn->getReqParam('order_id');
        $due_date = date('Y-m-d', strtotime("+14 days"));
        
        $qty_balance = '';
        $sqlInvoiceItem = "
        SELECT ii.*
              ,SUM(ii.qty) AS total_invoice_quantity
        FROM invoice_item ii
        LEFT JOIN (invoice i) ON (ii.invoice_id = i.invoice_id)
        WHERE i.order_id = {$order_id}
        GROUP BY ii.record_id
        ";
        $resultInvoiceItem   = $db->sql_query($sqlInvoiceItem);  
        $numRowsInvoiceItem  = $db->sql_numrows($resultInvoiceItem);
        
        if ($numRowsInvoiceItem == 0) {
            return "Please create the invoice and then you can create packing list";
        }
        
        while ($rowII = $db->sql_fetchrow($resultInvoiceItem)) {
            $sqlQty = "
            SELECT SUM(plh.qty) AS qty_packed
            FROM packing_list_history plh
            JOIN packing_list pl ON (plh.packing_list_id = pl.packing_list_id)
            WHERE pl.order_id = {$order_id}
              AND plh.invoice_item_id = {$rowII['invoice_item_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);  
            $rowQty = $db->sql_fetchrow($resultQty);
            
            $qty_balance   = $rowII['total_invoice_quantity'] - $rowQty['qty_packed'];
            
            $inputRow   = '';
            $qtyRow     = '';
            $cartonRow  = '';

            if ($rowQty['qty_packed'] != $rowII['total_invoice_quantity']) {
                $pfx = $rowII['invoice_item_id'] . '_' ;
                $inputRow = "<input class='orderItemId' type='checkbox' name='invoiceItemId[]' value='{$rowII['invoice_item_id']}'>";
                $qtyRow = "<input type='text' value='{$qty_balance}' id='fld_qty' class='text w50' name='{$pfx}qty'>";
                $cartonRow = "<input type='text' value='' id='fld_no_of_carton' class='text w50' name='{$pfx}no_of_carton'>";
            }
            
            $rows .= "
            <tr>
                <td>{$inputRow}</td>
                <td>{$rowII['item_title']}</td>
                <td class=''>{$rowII['total_invoice_quantity']}</td>
                <td class=''>{$qtyRow}</td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$rowQty['qty_packed']}</td>
                <td class=''>{$cartonRow}</td>
            </tr>
            ";
        }
        
        $expVl              = array('sqlType' => 'OneField');
        $expNoEdit          = array('isEditable' => 0);
        $orderRec           = $fn->getRecordRowById('order', 'order_id', $order_id);
        $sqlCountry         = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlContainerSize   = $fn->getValueListSQL('containerSize');
        $sqlStatus          = $fn->getValueListSQL('packingListStatus');

        $formAction = "index.php?_topRm=finance&module=tradingsg_packingList&_spAction=generatePackingListFormSubmit&showHTML=0";
        
        $title = $orderRec['shipping_first_name'] . ' ' . date('d-m-Y');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            <div class=''>
                (Note: Please check the products listed to generate Packing List)
            </div>

            <div class='edit_invoices'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>

            <table class='thinlist room-order-table'>
                <thead>
                    <th class='click-all-top'>
                        <a href='#' class='check-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                        </a>
                        <a href='#' class='uncheck-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                        </a>
                    </th>
                    <th>Product Name</th>
                    <th>Qty Ordered</th>
                    <th class=''>Qty</th>
                    <th>Qty (Balance)</th>
                    <th>Qty (Sent)</th>
                    <th>No of Cartons</th>
                </thead>
                
                <tbody>
                    {$rows}
                </tbody>
            </table>

			{$formObj->getTBRow('Title', 'title', $title)}
      		{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $expVl)}
			{$formObj->getDateRow('Date', 'packing_list_date', $fn->getCurrentDate())}
            {$formObj->getTBRow('Port Of Loading', 'port_of_loading', $orderRec['port_of_loading'])}
            {$formObj->getTBRow('Port Of Discharge', 'port_of_discharge', $orderRec['port_of_discharge'])}
			{$formObj->getDDRowBySQL('Country of Final Destination', 'final_destination_country',$sqlCountry, $orderRec['final_destination_country'])}
			{$formObj->getTBRow('Exporters Reference No', 'exporters_reference_no', $orderRec['exporters_reference_no'])}
	        {$formObj->getTBRow('Buyers Order No', 'buyers_order_no', $orderRec['buyers_order_no'])}
            {$formObj->getTARow('Terms', 'terms', $orderRec['invoice_terms'])}
            {$formObj->getYesNoRRow('Show Bank Name in Packing List', 'show_bank_name', '0')}
			{$formObj->getTBRow('Vessel', 'vessel', $orderRec['vessel'])}
			{$formObj->getDateRow('Departure Date', 'departure_date', $fn->getCurrentDate())}        
	        {$formObj->getDDRowBySQL('Origin Goods Country', 'origin_goods_country', $sqlCountry, $orderRec['origin_goods_country'])}

      		{$formObj->getDDRowBySQL('Container Size', 'container_size', $sqlContainerSize, '', $expVl)}
			{$formObj->getTBRow('No of Cartons', 'no_of_cartons')}
			{$formObj->getTARow('Description', 'description')}

			{$formObj->getTBRow('Net Wt (Kg)', 'net_wt')}
			{$formObj->getTBRow('Gross Wt (Kg)', 'gross_wt')}
			{$formObj->getTBRow('Cube M3', 'cube_m3')}

            {$formObj->getYesNoRRow('Packing List in Next Page', 'packing_list_in_next_page', '0')}


            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}

            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='company_id' value='{$orderRec['company_id']}' />
            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPackingListForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';
               
        $order_id        = $fn->getReqParam('order_id');
        $packing_list_id = $fn->getReqParam('packing_list_id');

        $packingListRec     = $fn->getRecordRowById('packing_list', 'packing_list_id', $packing_list_id);
        
        $qty_balance = '';
        $sqlPackingListHist = "
        SELECT plh.*
              ,ii.item_title
        FROM packing_list_history plh
        LEFT JOIN (invoice_item ii) ON (plh.invoice_item_id = ii.invoice_item_id)
        WHERE packing_list_id = {$packing_list_id}
        ";
        $resultPackingListHist   = $db->sql_query($sqlPackingListHist);  
        while ($rowPLH = $db->sql_fetchrow($resultPackingListHist)) {
            $sqlQty = "
            SELECT SUM(plh.qty) AS qty_packed
            FROM packing_list_history plh
            JOIN packing_list pl ON (pl.packing_list_id = plh.packing_list_id)
            WHERE plh.invoice_item_id = {$rowPLH['invoice_item_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $sqlInvoiceItem = "
            SELECT ii.*
                  ,SUM(oi.qty) AS total_invoice_quantity
            FROM invoice_item ii
            LEFT JOIN (invoice i)     ON (ii.invoice_id    = i.invoice_id)
            LEFT JOIN (order_item oi) ON (ii.order_item_id = oi.order_item_id)
            WHERE i.order_id = {$order_id}
              AND ii.invoice_item_id = {$rowPLH['invoice_item_id']}
            GROUP BY ii.order_item_id
            ";
            $resultInvoiceItem = $db->sql_query($sqlInvoiceItem);
            $rowOI = $db->sql_fetchrow($resultInvoiceItem);
            
            $qty_balance  = $rowOI['total_invoice_quantity'] - $rowQty['qty_packed'];
            $qty_invoiced = $rowQty['qty_packed'] - $rowPLH['qty'];
            
            $inputRow = "<input class='packingListHistId' type='checkbox' name='packingListHistId[]' value='{$rowPLH['packing_list_history_id']}'>";
            
            $rows .= "
            <tr>
                <td>{$inputRow}</td>
                <td>{$rowPLH['item_title']}</td>
                <td class=''>{$rowOI['total_invoice_quantity']}</td>
                <td><input type='text' value='{$rowPLH['qty']}' id='fld_qty' class='text w50' name='qty[]'></td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$rowQty['qty_packed']}</td>
                <td><input type='text' value='{$rowPLH['no_of_cartons']}' id='fld_no_of_carton' class='text w50' name='no_of_carton[]'></td>
            </tr>
            ";
        }
        
        $expVl              = array('sqlType' => 'OneField');
        $expNoEdit          = array('isEditable' => 0);
        $sqlCountry         = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlContainerSize   = $fn->getValueListSQL('containerSize');
        $sqlStatus          = $fn->getValueListSQL('packingListStatus');

        $formAction = "index.php?_topRm=order&module=tradingsg_packingList&_spAction=editPackingListFormSubmit&showHTML=0";
        
        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            <div class=''>
                (Note: Please check the products listed to modify Packing List)
            </div>

            <div class='edit_invoices'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>

            <table class='thinlist room-order-table'>
                <thead>
                    <th class='click-all-top'>
                        <a href='#' class='check-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                        </a>
                        <a href='#' class='uncheck-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                        </a>
                    </th>
                    <th>Product Name</th>
                    <th>Qty Ordered</th>
                    <th class=''>Qty</th>
                    <th>Qty (Balance)</th>
                    <th>Qty (Sent)</th>
                    <th>No of Cartons</th>
                </thead>
                
                <tbody>
                    {$rows}
                </tbody>
            </table>

			{$formObj->getTBRow('Title', 'title', $packingListRec['title'])}
      		{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $packingListRec['status'], $expVl)}
			{$formObj->getDateRow('Date', 'packing_list_date', $packingListRec['packing_list_date'])}
            {$formObj->getTBRow('Port Of Loading', 'port_of_loading', $packingListRec['port_of_loading'])}
            {$formObj->getTBRow('Port Of Discharge', 'port_of_discharge', $packingListRec['port_of_discharge'])}
			{$formObj->getDDRowBySQL('Country of Final Destination', 'final_destination_country',$sqlCountry, $packingListRec['final_destination_country'])}
			{$formObj->getTBRow('Exporters Reference No', 'exporters_reference_no', $packingListRec['exporters_reference_no'])}
	        {$formObj->getTBRow('Buyers Order No', 'buyers_order_no', $packingListRec['buyers_order_no'])}
            {$formObj->getTARow('Terms', 'terms', $packingListRec['terms'])}
            {$formObj->getYesNoRRow('Show Bank Name in Packing List', 'show_bank_name', $packingListRec['show_bank_name'])}
			{$formObj->getTBRow('Vessel', 'vessel', $packingListRec['vessel'])}
			{$formObj->getDateRow('Departure Date', 'departure_date', $packingListRec['departure_date'])}        
	        {$formObj->getDDRowBySQL('Origin Goods Country', 'origin_goods_country', $sqlCountry, $packingListRec['origin_goods_country'])}

      		{$formObj->getDDRowBySQL('Container Size', 'container_size', $sqlContainerSize, $packingListRec['container_size'], $expVl)}
			{$formObj->getTBRow('No of Cartons', 'no_of_cartons', $packingListRec['no_of_cartons'])}
			{$formObj->getTARow('Description', 'description', $packingListRec['description'])}

			{$formObj->getTBRow('Net Wt (Kg)', 'net_wt', $packingListRec['net_wt'])}
			{$formObj->getTBRow('Gross Wt (Kg)', 'gross_wt', $packingListRec['gross_wt'])}
			{$formObj->getTBRow('Cube M3', 'cube_m3', $packingListRec['cube_m3'])}

            {$formObj->getYesNoRRow('Packing List in Next Page', 'packing_list_in_next_page', $packingListRec['packing_list_in_next_page'])}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}

            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
            <input type='hidden' name='packing_list_id' value='{$packing_list_id}' />
        </form>
        ";

        return $text;
    }
}