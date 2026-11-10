<?
class CP_Admin_Modules_Tradingsg_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $count = 0;
        $rows  = '';

        foreach ($dataArray as $row){

	        $SQLTotal = "
				SELECT SUM(round(
				(pop.qty * pop.price),2)) AS total_cost
				FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['po_code'])}
            {$listObj->getListDataCell($row['quote_title'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($rowTotal['total_cost'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";

            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('PO Code', 'po.po_code')}
        {$listObj->getListHeaderCell('Title', 'q.quote_title')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Client', 'company_name')}
        {$listObj->getListHeaderCell('PO Value', 'amount')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('PO Date', 'purchase_order_date')}
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

        $sqlSupplier = $fn->getDDSql('trading_company', array('condn' => "category = 'Supplier'"));
        $expSupplier = array('hideFirstOption' => 1);

        $fieldset = "
        {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, '', $expSupplier)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset)}
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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');

        $expCompany = array('sqlType' => 'OneField');
        $expDeliveryTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_supplier'],
                                                        'fld_delivery_terms'
                                                        );

        $expPaymentTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_supplier'],
                                                        'fld_payment_terms'
                                                        );

        $expVl = array('sqlType' => 'OneField');
        $sqlPriority = $fn->getValuelistSql('quotePriority');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $statusArr = $cpCfg['m.trading.purchaseOrder.statusArr'];
        if($row['status'] == 'confirmed'){ //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $expStaff   = array('detailValue' => $row['staff_name']);

        $quote = "<a href='index.php?_topRm={$tv['topRm']}&module=tradingsg_quote&record_id={$row['quote_id']}&_action=edit'>{$row['quote_code']}</a>";

        $fieldset1 = "
        {$formObj->getTBRow('PO Code', 'po_code', $row['po_code'], $expNoEdit)}
        {$formObj->getTBRow('Title', 'quote_id', $row['quote_title'], $expNoEdit)}
        {$formObj->getTBRow('Quote Code', 'quote_id', $quote, $expNoEdit)}
        {$formObj->getTBRow('Supplier', 'supplier_name', $row['supplier_name'], $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'company_name', $row['company_name'], $expNoEdit)}
        {$formObj->getDDRowByArr('Status', 'status', $statusArr, $row['status'])}
        {$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager,
                                 $row['staff_id'], $expStaff)}
        {$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getDDRowBySQL('Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}
        {$formObj->getTARow('Notes to Supplier', 'notes', $row['notes'])}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'], $expDeliveryTerms)}
        {$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'], $expPaymentTerms)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $actionButtons = '';
        $links = '';
        $formActionInvoice = "index.php?module=tradingsg_purchaseOrder&_spAction=raiseInvoiceForm&purchase_order_id={$row['purchase_order_id']}&showHTML=0";

        if ($cpCfg['m.tradingsg.purchaseOrder.showInvoiceButton']){
            $actionButtons .="
            <div class='button mb5'>
                <a href='{$formActionInvoice}' id='raiseInvoice'>RAISE INVOICE</a>
            </div>
            ";
            $links .= $this->getInvoicePortalDisplay($row);
        }

        $text = "
        {$actionButtons}
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_purchaseOrder', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('tradingsg_purchaseOrder', 'tradingsg_productLink', 'Product Linked', $row)}
        {$links}
        ";

        return $text;
    }

    /**
     */
    function getInvoicePortalDisplay($row){
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
        $formAction = '';


        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        {$leftJoin}
        WHERE i.purchase_order_id = {$row['purchase_order_id']}
          AND i.invoice_type = 'Supplier'
        ORDER BY i.invoice_id
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
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';

            $urlPrint = "index.php?_topRm=finance&module=tradingsg_order&_spAction=printInvoiceRecordForPurchaseOrder&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingsg_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if($rowInvoice['status'] == 'Paid'){
                $selectedValuePaid =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Due'){
                $selectedValueDue =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Cancelled'){
                $selectedValueCancel =  "selected='selected'";
            }

            if($rowInvoice['status'] != 'Cancelled' && $invoice_code != $rowInvoice['invoice_code']){
                $total += $rowInvoice['invoice_amount'];
            }
            if($invoice_code == '' || $invoice_code != $rowInvoice['invoice_code']){

                /* Half way done. Need to do submit functioanlity. Move $editRow = ''; from below to this comment line */
                $editRow = '<td></td>';
                if ($rowInvoice['status'] == 'Due'
                 || $rowInvoice['status'] == ''
                 || $rowInvoice['status'] == 'Partial Payment'
                ) {
                    $editURL = "index.php?_topRm=order&module=tradingsg_purchaseOrder&_spAction=editInvoiceForm&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&purchase_order_id={$row['purchase_order_id']}";
                    $editRow = "<td><a href='{$editURL}' id='editInvoice'>Edit</a></td>";
                }

                $cancelInvoiceLink = '';
                if ($rowInvoice['status'] != 'Cancelled') {
                    $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code={$rowInvoice['invoice_code']}>Cancel Invoice</a>";
                }

                $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');
                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']}</td>
                    <td>{$rowInvoice['status']}</td>
                    <td>{$invoice_date}</td>
                    <td align='right'>{$rowInvoice['invoice_amount']}</td>
                    <td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>
                    <!--<td><a href='{$mediaLink}'>Print Invoice</a></td>-->
                    <td>{$cancelInvoiceLink}</td>
                    {$editRow}
                </tr>
                ";
            }

            $invoice_code = $rowInvoice['invoice_code'];
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Status</th>
        <th>Invoice Date</th>
        <th>Amount</th>
        <th>Print</th>
        <th>Cancel</th>
        <th>Edit</th>
        </tr>
        ";

        $text = "
        <tr class=''>
        <td>
            <div id='' class='invoiceDisplay'>
                <h2>Invoice(s)</h2>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        <table class='thinlist'>
                            {$header}
                            {$rows}
                            {$rowsPvt}
                        </table>
                    </div>
                </form>
            </div>
        </td>
        </tr>
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

        $status = $fn->getReqParam('status');
        $status = $fn->getReqParam('status');
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
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.purchaseOrder.statusArr'], $status)}
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
     *
     */
    function getEditInventoryForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');

        $SQL = "
        SELECT DISTINCT
               i.*
              ,p.product_code
              ,p.title product_name
              ,p.collection_name
              ,e.enquiry_code
              ,so.so_code
              ,po.po_code
              ,poi.buy_unit_price
              ,soi.sell_unit_price
        FROM inventory i
        LEFT JOIN product p ON p.product_id = i.product_id
        LEFT JOIN enquiry e ON e.enquiry_id = i.enquiry_id
        LEFT JOIN sales_order so ON so.sales_order_id = i.sales_order_id
        LEFT JOIN sales_order_items soi ON soi.sales_order_items_id = i.sales_order_items_id
        JOIN purchase_order po ON po.purchase_order_id = i.purchase_order_id
        JOIN purchase_order_items poi ON poi.purchase_order_items_id = i.purchase_order_items_id
        WHERE i.purchase_order_items_id = {$purchase_order_items_id}
        ORDER BY i.product_id
                ,i.serial_no
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $statusText = "
            <select name='inv_status[{$row['inventory_id']}]' class='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'], $row['status'])}
            </select>
            ";
            $locationText = "
            <select name='inv_location[{$row['inventory_id']}]' class='location'>
                <option value=''>Location</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'], $row['location'])}
            </select>
            ";

            $exp = array('hasFlagInList' => false
                        ,'keyFieldValue' => $row['inventory_id']
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
            );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getListDataCell($row['serial_no'])}
            {$listObj->getListDataCell($row['collection_name'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['so_code'])}
            {$listObj->getListDataCell($row['po_code'])}
            {$listObj->getListDataCell($statusText)}
            {$listObj->getListDataCell($locationText)}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnUpdateInventoryCancel' />
            <input type='button' value='Update' id='btnUpdateInventory' />
            </div>
        </form>
        ";

        $fnMod = getCPModelObj('trading_company');
        $sqlSupplier = $fnMod->getSupplierSQL();

        $exp = array('hasEditInList' => false
                    ,'hasRowNumber' => false
                    ,'hasFlagInList' => false
               );

        $rowSummary = "
        <tr class='even'>
        <td colspan='6'></td>

        <td>
        <select id='status_common'>
            <option value=''>Update Status</option>
            {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'])}
        </select>
        </td>
        <td>
        <select id='location_common'>
            <option value=''>Update Location</option>
            {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'])}
        </select>
        </td>

        <td colspan='2'></td>
        </tr>
        ";
        $text = "
        <div id='updateInventory'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Serial')}
            {$listObj->getListHeaderCell('Collection')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('Sales Order #')}
            {$listObj->getListHeaderCell('Purchase Order #')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Location')}
            {$listObj->getListHeaderCell('Creation Date')}
            {$listObj->getListHeaderEnd()}
            {$rowSummary}
            {$rows}
            {$listObj->getListFooter()}
            {$formObj->getHiddenFldObj('purchase_order_items_id', $purchase_order_items_id)}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
     function getRaiseInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['selectedPoProductIds']);

        $rows = '';
        $qty_balance = '';
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $date     = $fn->getCurrentDate();

        $sqlPurchaseOrderItem = "
        SELECT pp.*
              ,p.title
        FROM po_product pp
        LEFT JOIN (product p) ON (p.product_id = pp.product_id)
        WHERE pp.purchase_order_id = {$purchase_order_id}
        ";
        $resultPurchaseOrderItem = $db->sql_query($sqlPurchaseOrderItem);
        while ($rowPI = $db->sql_fetchrow($resultPurchaseOrderItem)) {
            $sqlQty = "
            SELECT SUM(pop.qty_delivered) AS qty_delivered
            FROM po_product pop
            WHERE pop.po_product_id = {$rowPI['po_product_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $qty_balance = $rowPI['qty'] - $rowQty['qty_delivered'];

            $inputRow = '';
            if ($rowQty['qty_delivered'] != $rowPI['qty']) {
                $inputRow = "<input class='poProductId' type='checkbox' name='poProductId[]' value='{$rowPI['po_product_id']}'>";
            }

            $rows .= "
            <tr>
                <td>
                    {$inputRow}
                </td>
                <td>{$rowPI['title']}</td>
                <td class='sellingPrice'>{$rowPI['price']}</td>
                <td class=''>{$rowPI['qty']}</td>
                <td class=''><input type='text' value='{$qty_balance}' id='fld_qty' class='text w50' name='qty[]'></td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$rowQty['qty_delivered']}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=tradingsg_purchaseOrder&_spAction=raiseInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'invoice_date', $date)}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>

            <table class='thinlist room-po-table'>
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
                    <th>Price</th>
                    <th>Qty</th>
                    <th class=''>Qty (Current Invoice)</th>
                    <th>Qty (Balance)</th>
                    <th>Qty (Delivered)</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>

            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $invoice_id = $fn->getReqParam('invoice_id');
        $date     = $fn->getCurrentDate();

        $invoiceRec = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);

        $sqlInvoiceItem = "
        SELECT * FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvoiceItem = $db->sql_query($sqlInvoiceItem);

        while ($rowII = $db->sql_fetchrow($resultInvoiceItem)) {
            $sqlQty = "
            SELECT SUM(pop.qty_delivered) AS qty_delivered
                  ,pop.qty AS total_qty
            FROM po_product pop
            WHERE pop.po_product_id = {$rowII['po_product_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $qty_balance = $rowQty['total_qty'] - $rowQty['qty_delivered'];

            $selling_price = $rowII['unit_price'] * $rowII['qty'];

            //$qty_balance = $rowOI['qty'] - $rowQty['qty_invoiced'];
            $qty_invoiced = $rowQty['qty_delivered'] - $rowII['qty'];

            $inputRow = '';
            //if ($rowQty['qty_invoiced'] != $rowII['qty']) {
                $inputRow = "<input class='invoiceItemId' type='checkbox' name='invoiceItemId[]' value='{$rowII['invoice_item_id']}'>";
            //}

            $rows .= "
            <tr>
                <td>
                    {$inputRow}
                </td>
                <td>{$rowII['item_title']}</td>
                <td class='sellingPrice'>{$selling_price}</td>
                <td class=''>{$rowII['qty']}</td>
                <td class=''><input type='text' value='{$rowII['qty']}' id='fld_qty' class='text w50' name='qty[]'></td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$qty_invoiced}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=tradingsg_purchaseOrder&_spAction=editInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $statusArr = array('Due', 'Paid');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'invoice_date', $invoiceRec['invoice_date'])}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            {$formObj->getDDRowByArr('Status', 'status', $statusArr, $invoiceRec['status'])}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>

            <table class='thinlist room-po-table'>
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
                    <th>Price</th>
                    <th>Qty</th>
                    <th class=''>Qty (Current Invoice)</th>
                    <th>Qty (Balance)</th>
                    <th>Qty (Invoiced)</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>

            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
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

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',11);

        $company_id 	   = $fn->getReqParam('company_id');

        if ($company_id == ''){
			$pdf->SetFont('Courier','B',11);
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please select the company and print the Purchase Order PDF");
			$pdf->Output();
		}else {

		    $SQL = "
	        SELECT pop.*
	              ,p.title AS product_title
	              ,p.part_number
	              ,p.unit
	              ,p.item_code
	              ,c.company_name
				  ,c.address_flat
				  ,c.address_street
				  ,c.address_town
				  ,c.address_state
				  ,c.address_country
	              ,c.fax
	              ,c.phone
	              ,pop.creation_date
	              ,po.po_code
	              ,po.status
	              ,q.quote_code
	              ,q.delivery_date
	              ,q.delivery_location
	              ,(SELECT SUM(poph.qty) FROM  po_product poph
			        LEFT JOIN purchase_order poo ON (poo.purchase_order_id = poph.purchase_order_id)
	               WHERE poph.product_id = pop.product_id
	               AND poo.status = 'confirmed'
	               GROUP BY item_code) AS sum_qty
	        FROM po_product pop

	        LEFT JOIN product p ON (p.product_id = pop.product_id)
	        LEFT JOIN company c ON (c.company_id = pop.supplier_id)
	        LEFT JOIN quote q ON (q.quote_id = pop.quote_id)
	        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
	        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
	        WHERE po.company_id_supplier = {$company_id}
	        AND po.status = 'confirmed'
	        ORDER BY pg.sort_order ASC, p.title
	         ";


	        $result = $db->sql_query($SQL);

	        $numRows  = $db->sql_numrows($result);

	        $today = date("Y-m-d");
					if ($numRows == 0){
			            $pdf->SetXY(30,30);
			            $pdf->Cell(50, 20, "Select the Supplier from the dropdown in the left");
						$pdf->Output();
						return;
					}

	        $count = 0;
	        $total = 0;
	        $discount_price = 0;
	        $rows = "";
	        $lineItemNumber = 1;  // To increment the line item in receipt
			$totalAmount = '';

	        //============================================================================= //
	        $pdf->SetFont('Arial','',11);
	        while ($row = $db->sql_fetchrow($result)) {
	            if ($count == 0){
	                /* Logo of the institution */
	                $pdf->Image('images/logo-print.gif',10,5,45);
	                /* Company address */
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
	                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

	                /* Header */
	                $pdf->SetFont('Courier','BU',11);
	                $pdf->SetXY(100, 35);
	                $pdf->Cell(21, 20, "PURCHASE ORDER", 0, 0, 'C');
	                $pdf->Ln(20);


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
	                //$pdf->MultiCell(190,5,$row['company_name'] ."\n". $row['address_flat'] ."\n". $row['address_street'] ."\n". $row['address_town'] ."\n". $row['address_country']  ." - ". $row['address_state'],1,'L');
	                $pdf->Ln(10);

				    $quoteCode = $row['quote_code'];
					$formatedQC = explode("-", $quoteCode);


					$pdf->Cell(30,15,"(Note : Please mention the exact Item Code for all the products.)",0,0, 'L', 1);
	                $pdf->Ln(10);

	                /* List of order items header */
	                $pdf->SetFont('Courier','B',11);
	                $pdf->SetFillColor(254,203,156);
	                $pdf->Cell(10,8,"S.NO",1,0, 'C', 1);
	                $pdf->Cell(105,8,"NAME OF THE ITEM",1,0, 'C', 1);
	                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
	                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
	                $pdf->Cell(25,8,"PRICE",1,0, 'C', 1);
	                $pdf->Cell(30,8,"TOTAL",1,0, 'C', 1);
	                $pdf->Ln();

					$fa = array();
	            }

	            //===================================MAIN TABLE============================= //
				$key = array_search($row['item_code'], $fa);

				if ($key != true) {
					$fa[$row['item_code']] = $row['item_code'];
					//print_r($fa[$row['product_title']]);
					$total = $row['sum_qty'] * $row['price'];
					$totalDis = number_format($total,2);
					$totalAmount += $total;

		            $pdf->SetFillColor(255,255,255);
		            $pdf->Cell(10, 8, $lineItemNumber, 1, 0, 'L', 1);
		            $pdf->Cell(105, 8, $row['product_title'], 1, 0, 'L', 1);
		            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
		            $pdf->Cell(10, 8, $row['sum_qty'], 1, 0, 'R', 1);
		            $pdf->Cell(25, 8, $row['price'], 1, 0, 'R', 1);
		            $pdf->Cell(30, 8, $totalDis, 1, 0, 'R', 1);
		            $pdf->Ln();
				}
	            $count++;
	            $lineItemNumber++;
	        }
			$totalAmountDis = number_format($totalAmount,2);

            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(160,8,"TOTAL",1,0, 'R', 1);
            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(30,8,$totalAmountDis,1,0, 'R', 1);
			$pdf->Ln(20);

		        /* Creation of media record of the invoice */
		        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
		        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

		        $outputFileName = $outputPath . '/' . $file_name;
		        //$pdf->Output($outputFileName , "F");
				$pdf->Output();

    	}
	}

}