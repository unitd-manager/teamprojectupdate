<?
class CP_Admin_Modules_Tradingsg_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $currentDate  = date("Y-m-d");

        $count   = 0;
        $rows    = '';
        $rowCounter = 0;
        $hightlightDueTasks='';

        foreach ($dataArray as $row){

            if ($row['status'] =='Due' ||  $row['status'] == 'Partial Payment'){
                if ($row['invoice_due_date'] < $currentDate){

                     $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter, 'projectList2');
                }
                else{
                    $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter);
                }
            }
            else {
                    $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter);
            }

        $rows .="
		    {$hightlightDueTasks}
		    {$listObj->getGoToDetailText($count, $row['invoice_code'])}
		    {$listObj->getListDataCell($row['company_name'])}
		    {$listObj->getListDateCell($row['invoice_date'])}
		    {$listObj->getListDataCell($row['status'])}
		    {$listObj->getListDataCell($row['invoice_amount'] ,'right')}
		    {$listObj->getListDataCell($row['order_id'], 'center')}
		    {$listObj->getListRowEnd($row['invoice_id'])}
			";

        	$rowCounter++;;
		}

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice Code', 'invoice_code')}
        {$listObj->getListHeaderCell('Company Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Invoice Date', 'i.invoice_date')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'headerRight')}
        {$listObj->getListHeaderCell('Order Id', 'i.order_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
       	";

        return $text;
    }

    /**
     *
     */
     function getGenerateInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';

        $order_id = $fn->getReqParam('order_id');
        $date     = $fn->getCurrentDate();
        $due_date = date('Y-m-d', strtotime("+14 days"));

        $sqlOrderItem = "
        SELECT * FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOrderItem = $db->sql_query($sqlOrderItem);
        while ($rowOI = $db->sql_fetchrow($resultOrderItem)) {
            $sqlQty = "
            SELECT SUM(it.qty) AS qty_invoiced
            FROM invoice_item it
            JOIN invoice i ON (i.invoice_id = it.invoice_id)
            WHERE i.order_id = {$order_id}
             AND it.record_id = {$rowOI['record_id']}
             AND i.status != 'Cancelled'
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $selling_price = $rowOI['unit_price'] * $rowOI['qty'];

            $qty_balance = $rowOI['qty'] - $rowQty['qty_invoiced'];

            $inputRow = '';
            $qtyRow = '';

            if ($rowQty['qty_invoiced'] != $rowOI['qty']) {
                $pfx = $rowOI['order_item_id'] . '_' ;
                $inputRow = "<input class='orderItemId' type='checkbox' name='orderItemId[]' value='{$rowOI['order_item_id']}'>";
                $qtyRow = "<input type='text' value='{$qty_balance}' id='fld_qty' class='text w50' name='{$pfx}qty'>";
            }

            $rows .= "
            <tr>
                <td>
                    {$inputRow}
                </td>
                <td>{$rowOI['item_title']}</td>
                <td class='sellingPrice'>{$rowOI['unit_price']}</td>
                <td class=''>{$rowOI['qty']}</td>
                <td class=''>{$qtyRow}</td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$rowQty['qty_invoiced']}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=tradingsg_order&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            <div class=''>
                (Note: Please check the products listed below to create invoice)
            </div>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'invoice_date', $date)}
            {$formObj->getDateRow('Due Date', 'invoice_due_date', $due_date)}
            {$formObj->getTARow('Terms', 'invoice_terms', $orderRec['invoice_terms'])}
            {$formObj->getTARow('Notes', 'notes', $orderRec['notes'])}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>

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

            <input type='hidden' name='order_id' value='{$order_id}' />
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

        $order_id = $fn->getReqParam('order_id');
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
            SELECT SUM(it.qty) AS qty_invoiced
            FROM invoice_item it
            JOIN invoice i ON (i.invoice_id = it.invoice_id)
            WHERE (i.order_id = {$order_id} AND i.status != 'Cancelled')
              AND it.record_id = {$rowII['record_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $sqlOrderItem = "
            SELECT * FROM order_item
            WHERE order_id = {$order_id}
              AND record_id = {$rowII['record_id']}
            ";
            $resultOrdertem = $db->sql_query($sqlOrderItem);
            $rowOI = $db->sql_fetchrow($resultOrdertem);

            $selling_price = $rowII['unit_price'] * $rowII['qty'];

            $qty_balance = $rowOI['qty'] - $rowQty['qty_invoiced'];
            $qty_invoiced = $rowQty['qty_invoiced'] - $rowII['qty'];

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
                <td class='sellingPrice'>{$rowII['unit_price']}</td>
                <td class=''>{$rowOI['qty']}</td>
                <td class=''><input type='text' value='{$rowII['qty']}' id='fld_qty' class='text w50' name='qty[]'></td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$qty_invoiced}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=tradingsg_order&_spAction=editInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'invoice_date', $invoiceRec['invoice_date'])}
            {$formObj->getDateRow('Due Date', 'invoice_due_date', $invoiceRec['invoice_due_date'])}
            {$formObj->getTARow('Terms', 'invoice_terms', $invoiceRec['invoice_terms'])}
            {$formObj->getTARow('Notes', 'notes', $invoiceRec['notes'])}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>

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

            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];
        $stillToBill   = '';
        $base_value = '';
        $ref_value  = '';

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];
        $vlUrl    = "index.php?module=core_valuelist&_spAction=showValuesInModal&showHTML=0&key_text=";

        $expNotes = array();

        $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD MMM YYYY');

        $fieldset1 = "
        {$formObj->getTBRow('Client Company', 'company_id', $row['company_name'])}
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'])}
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $invoice_date)}
        {$formObj->getTBRow('Invoice Amount', 'invoice_amount', $row['invoice_amount'])}
        {$formObj->getDDRowBySQL('Status', 'status', '', $row['status'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Details', $fieldset1)}
        ";

        return $text;

    }

}