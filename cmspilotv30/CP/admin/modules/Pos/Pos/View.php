<?
class CP_Admin_Modules_Pos_Pos_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jPlayer-2.2.0');
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $expNoEdit = array('isEditable' => 0);

        $text = '';
        $rows = '';
        $rowCounter = 0;
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : '';
        //$_SESSION['order_id'] = '';
        $multiPayment = $fn->getSettingsValueByKey('invMultiPayments');
        $_SESSION['multi_payment'] = $multiPayment;
        $displayMessage = $fn->getSettingsValueByKey('invDisplayMessageBoxAfterInvoiced');

        $expSku = array('notesRight' => "
        <a class='editLinkSingle' w='900' href=''
            link='{$fn->getOpenLinkUrl('pos_pos', 'pos_productLink', 'fld_sku')}'>
            Choose
        </a>
        ");

        $expContact = array('notesRight' => "
        <a class='editLinkSingle' href=''
            link='{$fn->getOpenLinkUrl('pos_pos', 'pos_contactLink', 'fld_member_id')}'>Choose
        </a>
        ");

        $sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        WHERE s.published = 1
        AND CONCAT_WS(' ', s.first_name, s.last_name) != ''
        ORDER BY staff_name
        ";

        $deposit = '';
        if($cpCfg['invEnableDeposit'] == 1) {
            $deposit = "<li><a>Deposit(F6)</a></li>";
        }

        $member = '';
        $newMember = '';
        $memberCode = '';
        if($cpCfg['invEnableMember'] == 1) {
            $member = "<li><a>Member(F7)</a></li>";
            $newMember = "<li><a>New Member</a></li>";

            $memberCode = "
            {$formObj->getTBRow('Member Code', 'member_id', '', $expContact)}
            {$formObj->getTBRow('Member Name', 'member_name', '', $expNoEdit)}
            ";
        }

        $holdInvoice = '';
        if($cpCfg['invEnableTempInvoice'] == 1) {
            $holdInvoice = "<li><a>Hold Invoice(F5)</a></li>";
        }

        $invoice = $fn->getSettingsRowByKey('pfxInvoice');

        $length = $invoice['length'] - $invoice['starting_no'];

        $i = 0;
        $extraNo = '';
        while ($i < $length) {
            $extraNo .= '0';
            $i++;
        }

        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
        }
        $SQLOrder = "
        SELECT max(increament_no) AS increament_no
        FROM `order`
        WHERE order_code like '%{$shop['code']}%'
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        if ($rowOrder['increament_no'] == '' || $rowOrder['increament_no'] == 0){
            $start_no = $invoice['starting_no'];
        } else {
            $start_no = $rowOrder['increament_no'] + 1;
        }

        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $invoice_no = $invoice['value'] . '-' . $shop['code'] . '-' . $extraNo . $start_no;
        } else {
            $invoice_no = $invoice['value'] . '-' . $extraNo . $start_no;
        }

        if ($invoice['auto_generate_no'] == 1) {
            $invoiceNoField = $formObj->getTBRow('Invoice No.', 'invoice_no', $invoice_no, $expNoEdit);
        } else {
            $invoiceNoField = $formObj->getTBRow('Invoice No.', 'invoice_no');
        }

        if ($displayMessage == 1) {
            $id = 'clearInvoice';
        } else {
            $id = 'newInvoice';
        }

        $text = "
        <div id='pos' class='subcolumns'>
            <div class='c40l'>
                <div class='subcl'>
                    <form class='yform columnar' id='posSearch'>
                        {$formObj->getFieldSetWrapped('Search & Defaults', "
                            {$invoiceNoField}
                            {$formObj->getTBRow('SKU', 'sku', '', $expSku)}
                            {$formObj->getTBRow('Qty', 'qty')}
                            {$memberCode}
                            {$formObj->getDDRowBySQL('Salesman', 'staff_id', $sqlStaff)}
                            {$formObj->getTBRow('Remark', 'remark')}
                            "
                        )}
                        <input type='hidden' name='invoice_no' value='{$invoice_no}' />
                        <input type='submit' name='x_submit' class='submithidden' />
                    </form>
                    {$this->getPaymentMethods()}
                </div>
            </div>
            <div class='c60r'>
                <div class='subcr'>
                    <div id='orderItems'>
                        {$this->getOrderItems()}
                    </div>
                </div>
            </div>
        </div>

        <div class='actionsWrapper'>
            <ul class='noDefault floatbox'>
                <li><a>Check Stock(F3)</a></li>
                {$holdInvoice}
                {$member}
                <li>
                    <a id='invoicePayment' class='' dialogtitle='Invoice Payment' href='index.php?module=pos_pos&_spAction=invoicePaymentDetails&showHTML=0'>
                        Payment(F9)
                    </a>
                </li>
                {$newMember}
                <li><a>View Promotion</a></li>
                <li><a>Print</a></li>
            </ul>
            <ul class='noDefault floatbox'>
                <li><a>Invoice(F4)</a></li>
                {$deposit}
                <li><a>Open Drawer(F8)</a></li>
                <li><a>Installment(F10)</a></li>
                <li><a>New Product</a></li>
                <li>
                    <a id='{$id}' href='#'>
                        New Invoice
                    </a>
                </li>
            </ul>
        </div>
        <div id='jquery_jplayer'>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPaymentMethods(){
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $expNoEdit = array('isEditable' => 0);
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';

        $SQL = "
        SELECT (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
        (SELECT SUM(amount * exchange_rate)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'];

        $SQLCash = "
        SELECT SUM(amount) AS cash_amount
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
          AND payment_type = 'Cash'
        ";
        $resultCash = $db->sql_query($SQLCash);
        $rowCash = $db->sql_fetchrow($resultCash);

        $SQLVisa = "
        SELECT SUM(amount) AS visa_amount
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
          AND payment_type = 'Credit Card'
          AND card_type = 'VISA'
        ";
        $resultVisa = $db->sql_query($SQLVisa);
        $rowVisa = $db->sql_fetchrow($resultVisa);

        $SQLMaster = "
        SELECT SUM(amount) AS master_amount
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
          AND payment_type = 'Credit Card'
          AND card_type = 'MASTER'
        ";
        $resultMaster = $db->sql_query($SQLMaster);
        $rowMaster = $db->sql_fetchrow($resultMaster);

        $SQLUnion = "
        SELECT SUM(amount) AS union_amount
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
          AND payment_type = 'Credit Card'
          AND card_type = 'UNIONPAY'
        ";
        $resultUnion = $db->sql_query($SQLUnion);
        $rowUnion = $db->sql_fetchrow($resultUnion);

        $SQLOctopus = "
        SELECT SUM(amount) AS octopus_amount
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
          AND payment_type = 'Credit Card'
          AND card_type = 'OCTOPUS'
        ";
        $resultOctopus = $db->sql_query($SQLOctopus);
        $rowOctopus = $db->sql_fetchrow($resultOctopus);

        $SQLCheque = "
        SELECT SUM(amount) AS cheque_amount
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
          AND payment_type = 'Credit Card'
          AND card_type = 'CHEQUE'
        ";
        $resultCheque = $db->sql_query($SQLCheque);
        $rowCheque = $db->sql_fetchrow($resultCheque);

        $paymentMethods = '';

        if ($rowCash['cash_amount'] != ''){
            $paymentMethods .= $formObj->getTBRow('Cash', 'cash', '$'.$rowCash['cash_amount'], $expNoEdit);
        }
        if ($rowVisa['visa_amount'] != ''){
            $paymentMethods .= $formObj->getTBRow('VISA', 'visa', '$'.$rowVisa['visa_amount'], $expNoEdit);
        }
        if ($rowMaster['master_amount'] != ''){
            $paymentMethods .= $formObj->getTBRow('MASTER', 'master', '$'.$rowMaster['master_amount'], $expNoEdit);
        }
        if ($rowUnion['union_amount'] != ''){
            $paymentMethods .= $formObj->getTBRow('UNIONPAY', 'unionpay', '$'.$rowUnion['union_amount'], $expNoEdit);
        }
        if ($rowOctopus['octopus_amount'] != ''){
            $paymentMethods .= $formObj->getTBRow('OCTOPUS', 'octopus', '$'.$rowOctopus['octopus_amount'], $expNoEdit);
        }
        if ($rowCheque['cheque_amount'] != ''){
            $paymentMethods .= $formObj->getTBRow('CHEQUE', 'cheque', '$'.$rowCheque['cheque_amount'], $expNoEdit);
        }

        $text="
        <form class='yform columnar mt5' id='paymentMethods'>
            {$formObj->getFieldSetWrapped('Payment Methods', "
                {$paymentMethods}
                "
            )}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderItems(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sku_no = $fn->getReqParam('sku_no');

        $rows = '';
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : false;
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;
        $session_user_group_id = isset($_SESSION['userGroupID']) ? $_SESSION['userGroupID']  : false;
        $gotAuthorisation = isset($_SESSION['gotAuthorisation']) ? $_SESSION['gotAuthorisation']  : false;
        $gotAuthorisationOverall = isset($_SESSION['gotAuthorisationOverall']) ? $_SESSION['gotAuthorisationOverall']  : false;

        $currency = '';
        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currency = $shop['currency'];
            $currencySign = $shop['currency_sign'];
        }

        $sqlDiscountOption  = $fn->getValueListSQL('discountOption');
        $expVl = array('sqlType' => 'OneField');

        $priceRound = $fn->getSettingsValueByKey('invPriceRounding');

        $SQL = "
        SELECT *
        FROM order_item
        WHERE order_id = '{$session_order_id}'
        ";
        $result = $db->sql_query($SQL);

        $SQLUserGroup = "
        SELECT *
        FROM user_group
        WHERE user_group_id = '{$session_user_group_id}'
        ";
        $resultUserGroup = $db->sql_query($SQLUserGroup);
        $rowUserGroup = $db->sql_fetchrow($resultUserGroup);

        while ($row = $db->sql_fetchrow($result)){
            $productRec = $fn->getRecordRowByID('product', 'product_id', $row['record_id']);
            if($session_shop_id != ''){
                $productShop = $fn->getRecordByCondition('product_shop', "shop_id = {$session_shop_id} AND product_id = {$row['record_id']}");

                if ($productShop['list_price'] != '' || $productShop['list_price'] != 0) {
                    $list_price = $productShop['list_price'];
                } else {
                    if ($productRec['currency'] == $shop['currency']){
                        $list_price = $row['list_price'];
                    } else {
                        $from_currency = $fn->getRecordByCondition('currency', "code = '{$productRec['currency']}'");
                        $to_currency = $fn->getRecordByCondition('currency', "code = '{$shop['currency']}'");

                        $SQLExgRate = "
                        SELECT *
                        FROM currency_convert
                        WHERE from_currency_id = {$from_currency['currency_id']}
                          AND to_currency_id = {$to_currency['currency_id']}
                        ";
                        $resultExgRate = $db->sql_query($SQLExgRate);
                        $rowExgRate = $db->sql_fetchrow($resultExgRate);
                        if ($rowExgRate['exch_rate'] == '' || $rowExgRate['exch_rate'] == 0){
                            $rowExgRate['exch_rate'] = 1;
                        }
                        $list_price = $row['list_price'] * $rowExgRate['exch_rate'];
                    }
                }
            } else {
                if ($productRec['currency'] == $currency){
                    $list_price = $row['list_price'];
                } else {
                    $from_currency = $fn->getRecordByCondition('currency', "code = '{$productRec['currency']}'");
                    $to_currency = $fn->getRecordByCondition('currency', "code = '{$currency}'");

                    $SQLExgRate = "
                    SELECT *
                    FROM currency_convert
                    WHERE from_currency_id = {$from_currency['currency_id']}
                      AND to_currency_id = {$to_currency['currency_id']}
                    ";
                    $resultExgRate = $db->sql_query($SQLExgRate);
                    $rowExgRate = $db->sql_fetchrow($resultExgRate);
                    if ($rowExgRate['exch_rate'] == '' || $rowExgRate['exch_rate'] == 0){
                        $rowExgRate['exch_rate'] = 1;
                    }

                    $list_price = $row['list_price'] * $rowExgRate['exch_rate'];
                }
            }

            $itemDiscValue = '';
            //if item discount in usergroup is given yes then item discount will be displayed in global setting
            //$cpCfg['invEnableItemDiscount'] is global setting value for item_discount
            if($cpCfg['invEnableItemDiscount'] == 1 ) {
                if($rowUserGroup['item_discount'] == 1 && $rowUserGroup['item_discount_secondary_authorization'] != 1){
                    $itemDiscValue = "
                    <td>
                        <input type='text' class='discountOrderItem w50' id='discount' name='discount' value='{$row['discount']}' order_item_id='{$row['order_item_id']}'>
                    </td>
                    ";
                } else if($rowUserGroup['item_discount'] == 1 && $rowUserGroup['item_discount_secondary_authorization'] == 1){
                    if ($gotAuthorisation == 0){
                        $itemDiscValue = "
                        <td>
                            <a class='secondaryAuthorization' href='index.php?module=pos_pos&_spAction=secondaryAuthorization&showHTML=0'>click</a>
                        </td>
                        ";
                    } else {
                        $itemDiscValue = "
                        <td>
                            <input type='text' class='discountOrderItem w50' id='discount' name='discount' value='{$row['discount']}' order_item_id='{$row['order_item_id']}'>
                        </td>
                        ";
                    }
                }
            }

            if($row['discount_type'] == ''){
                $discount_type = 'HKD';
            } else {
                $discount_type = $row['discount_type'];
            }

            $total = $row['qty'] * $row['unit_price'];

            if ($priceRound == 'Round Up'){
                $total = ceil($total);
            } else if ($priceRound == 'Round Down'){
                $total = floor($total);
            } else if ($priceRound == 'Round Off'){
                $total = round($total);
            } else {
                $total = $total;
            }

            $total = number_format($total, 2);
            $rows .= "
            <tr id='orderItemRow' order_item_id='{$row['order_item_id']}'>
                <td>
                    <a class='deleteOrderItem' href='#'
                        order_item_id='{$row['order_item_id']}'><span>delete</span>
                    </a>
                </td>
                <td></td>
                <td>{$row['sku_no']}</td>
                <td>{$row['item_title']}</td>
                <td class=''>
                    <input type='text' name='qty' class='qtyOrderItem w20' value='{$row['qty']}' order_item_id='{$row['order_item_id']}'>
                </td>
                <td class='txtRight'>{$list_price}</td>
                <td class='unitPrice txtRight'>
                    <input type='text' name='unit_price' id='unit_price' class='unitPriceOrderItem' value='{$list_price}' order_item_id='{$row['order_item_id']}'>
                </td>
                {$itemDiscValue}
                <td class='discount'>
                    <div class='type-select row_discount_type'>
                        <select name='discount_type' id='fld_discount_type'>
                            <option value=''>Please Select</option>
                            <option value='%'>%</option>
                            <option value='{$currency}'>{$currency}</option>
                        </select>
                    </div>
                </td>
                <td class='orderItemtotal txtRight'>{$currencySign}{$total}</td>
            </tr>
            ";
        }

        if($cpCfg['invEnableItemDiscount'] == 1 ) {
            $colspan = 9;
        } else {
            $colspan = 8;
        }

        $overallDiscount = '';
        if($cpCfg['invoiceDiscount'] == 1 ) {
            if($rowUserGroup['overall_item_discount'] == 1 && $rowUserGroup['overall_item_discount_secondary_authorization'] != 1){
                $overallDiscount = "
                <tr>
                    <th colspan={$colspan} class='txtRight'>Invoice Disc. (%)</th>
                    <th class='txtRight' id='orderInvoiceDisc'>
                        <input type='text' id='overallDiscount' class='w40' id='overall_item_discount' name='overall_discount' value='' order_id='{$session_order_id}'>
                    </th>
                </tr>
                ";
            } else if($rowUserGroup['overall_item_discount'] == 1 && $rowUserGroup['overall_item_discount_secondary_authorization'] == 1){
                if ($gotAuthorisationOverall == 0){
                    $overallDiscount = "
                    <tr>
                        <th colspan={$colspan} class='txtRight'>Invoice Disc. (%)</th>
                        <th class='txtRight' id='orderInvoiceDisc'>
                            <a class='secondaryAuthorizationOverall' href='index.php?module=pos_pos&_spAction=secondaryAuthorizationOverall&showHTML=0'>click</a>
                        </th>
                    </tr>
                    ";
                } else {
                    $overallDiscount = "
                    <tr>
                        <th colspan={$colspan} class='txtRight'>Invoice Disc. (%)</th>
                        <th class='txtRight' id='orderInvoiceDisc'>
                            <input type='text' id='overallDiscount' class='w40' id='overall_item_discount' name='overall_discount' value='' order_id='{$session_order_id}'>
                        </th>
                    </tr>
                    ";
                }
            }
        }

        $itemDiscLabel = '';
        if($cpCfg['invEnableItemDiscount'] == 1 ) {
            $itemDiscLabel = "<th class='left'>Item Disc.</th>";
        }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th colspan=10>Ordered Items</th>
                </tr>
            </thead>
            <tbody>
                <tr class='portal-row2'>
                    <th class='left'>Action</th>
                    <th class='left'>Image</th>
                    <th class='left'>SKU</th>
                    <th class='left'>Name</th>
                    <th class='left'>Qty</th>
                    <th class='txtRight'>List Price</th>
                    <th class='txtRight'>Unit Price</th>
                    {$itemDiscLabel}
                    <th class='left'>Disc. option</th>
                    <th class='txtRight'>Total ({$currency})</th>
                </tr>
                <div class='orderItemContainer'>
                    {$rows}
                </div>
            </tbody>
            <tfoot id='orderBalancePayments'>
                {$this->getOrderPayments()}
            </tfoot>
            <tfoot>
                <tr>
                    <th colspan={$colspan} class='txtRight'>Original Total Amount</th>
                    <th class='txtRight' id='orderSubTotal'></th>
                </tr>
                <tr>
                    <th colspan={$colspan} class='txtRight'>Total Disc. Amount</th>
                    <th class='txtRight' id='orderDiscAmount'></th>
                </tr>
                {$overallDiscount}
                <tr id='invoiceOverallDiscount'>
                    <th colspan={$colspan} class='txtRight'>Invoice Disc. Amount</th>
                    <th class='txtRight' id='overallDiscountAmount'></th>
                </tr>
                <tr>
                    <th colspan={$colspan} class='txtRight'>Actual Amount</th>
                    <th class='txtRight' id='orderActualAmount'></th>
                </tr>
                <tr>
                    <th colspan={$colspan} class='txtRight'>Total Balance</th>
                    <th class='txtRight' id='orderNetTotal'></th>
                </tr>
            </tfoot>
        </table>
        ";
        return $text;
    }

    /**
     *
     */
    function getOrderPayments(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $discount_total = '';
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : false;
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;
        $session_user_group_id = isset($_SESSION['userGroupID']) ? $_SESSION['userGroupID']  : false;

        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currencySign = $shop['currency_sign'];
        }

        $SQL = "
        SELECT discount
              ,discount_type
              ,(qty * unit_price) AS sub_total
        FROM order_item
        WHERE order_id = '{$session_order_id}'
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)){
            if( $row['discount_type'] == '%'){
                $discount_total +=  ($row['sub_total'] *  $row['discount'])/100;
            }
            else{
                $discount_total +=  $row['discount'];
            }
        }

        $SQL = "
        SELECT SUM(qty * unit_price) AS sub_total
              ,SUM(qty) AS total_qty
        FROM order_item
        WHERE order_id = '{$session_order_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $netTotal  = $row['sub_total'] - $discount_total;
        $totalQty = $row['total_qty'];

        $totalAmount ='';
        if($session_order_id != ''){
            $SQL = "
            SELECT (SELECT SUM(amount)
            FROM order_payment
            WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
            (SELECT SUM(amount * exchange_rate)
            FROM order_payment
            WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency,
            (SELECT SUM(amount)
            FROM order_payment
            WHERE order_id = '{$session_order_id}' and payment_type = 'Credit Card') AS total_amount_card
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'] + $row['total_amount_card'];
        }

        $exchangeAmount =0;
        if($totalAmount > $netTotal){
            $exchangeAmount = $totalAmount - $netTotal;
        }

        $dueAmount =0;
        if($totalAmount < $netTotal){
            $dueAmount = $netTotal - $totalAmount;
        }

        $SQLUserGroup = "
        SELECT *
        FROM user_group
        WHERE user_group_id = '{$session_user_group_id}'
        ";
        $resultUserGroup = $db->sql_query($SQLUserGroup);
        $rowUserGroup = $db->sql_fetchrow($resultUserGroup);

        if($cpCfg['invEnableItemDiscount'] == 1 ) {
            $colspan = 9;
        } else {
            $colspan = 8;
        }

        $priceRound = $fn->getSettingsValueByKey('invPriceRounding');

        if ($priceRound == 'Round Up'){
            $dueAmount = ceil($dueAmount);
        } else if ($priceRound == 'Round Down'){
            $dueAmount = floor($dueAmount);
        } else if ($priceRound == 'Round Off'){
            $dueAmount = round($dueAmount);
        } else {
            $dueAmount = $dueAmount;
        }

        $text ="
        <tr>
            <th colspan={$colspan} class='txtRight'>Balance Due</th>
            <th class='txtRight' id='orderBalance'>{$currencySign}{$dueAmount}</th>
        </tr>
        <tr>
            <th colspan={$colspan} class='txtRight'>Total Qty Purchased</th>
            <th class='txtRight' id='totalQtyPurchased'>{$totalQty}</th>
        </tr>
        <tr>
            <th colspan={$colspan} class='txtRight'>Deposit</th>
            <th class='txtRight' id='orderDeposit'></th>
        </tr>
        <tr>
            <th colspan={$colspan} class='txtRight'>Total Amount Paid</th>
            <th class='txtRight' id='totalAmountPaid'>{$currencySign}{$totalAmount}</th>
        </tr>
        <tr>
            <th colspan={$colspan} class='txtRight'>Exchange</th>
            <th class='txtRight' id='orderBalance'>{$currencySign}{$exchangeAmount}</th>
        </tr>
        ";
        return $text;
    }

    /**
     *
     */
    function getInvoicePaymentDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $discount_total = '';
        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $order = $fn->getRecordRowByID('order', 'order_id', $session_order_id);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $currency = '';
        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currency = $shop['currency'];
            $currencySign = $shop['currency_sign'];
        }

        $SQL = "
        SELECT discount
              ,discount_type
              ,(qty * unit_price) AS sub_total
        FROM order_item
        WHERE order_id = '{$session_order_id}'
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)){
            if( $row['discount_type'] == '%'){
                $discount_total +=  ($row['sub_total'] *  $row['discount'])/100;
            }
            else{
                $discount_total +=  $row['discount'];
            }
        }
        $SQL = "
        SELECT SUM(qty * unit_price) AS sub_total
        FROM order_item
        WHERE order_id = '{$session_order_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $netTotal  = $row['sub_total'] - $discount_total;
        $overallDiscount =  ($netTotal *  $order['overall_discount'])/100;
        $netTotal  = $netTotal - $overallDiscount;

        $rows = '';
        $serial_no = 0;

        $SQL = "
        SELECT *
        FROM order_payment
        WHERE order_id = '{$session_order_id}'
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)){
            $serial_no += 1;
            $name = '';
            if($row['payment_type'] == 'Cash'){
                $name = 'Pay by Cash';
                $edit = "
                <a class='cash_editOrderPayment' href='index.php?module=pos_pos&_spAction=editPayByCash&order_payment_id={$row['order_payment_id']}&nettotal={$netTotal}&showHTML=0'>
                    <span> edit</span>
                </a>
                ";
            } else if($row['payment_type'] == 'Credit Card'){
                $name = 'Pay by Credit Card';
                $edit = "
                <a class='creditCard_editOrderPayment' href='index.php?module=pos_pos&_spAction=editPayByCreditCard&order_payment_id={$row['order_payment_id']}&nettotal={$netTotal}&showHTML=0'>
                    <span> edit</span>
                </a>
                ";
            }

            if($row['payment_type'] == 'Cash'){
                $from_currency = $fn->getRecordByCondition('currency', "code = '{$row['currency']}'");
                $to_currency = $fn->getRecordByCondition('currency', "code = 'HKD'");

                $SQLExgRate = "
                SELECT *
                FROM currency_convert
                WHERE from_currency_id = {$from_currency['currency_id']}
                  AND to_currency_id = {$to_currency['currency_id']}
                ";
                $resultExgRate = $db->sql_query($SQLExgRate);
                $rowExgRate = $db->sql_fetchrow($resultExgRate);

                if($rowExgRate['exch_rate'] == 1) {
                    $rowExgRate['exch_rate'] = '';
                }

                if($row['currency'] != 'HKD'){
                    $amount = number_format($row['amount'] * $rowExgRate['exch_rate'], 2);
                } else {
                    $amount = $row['amount'];
                }
            } else {
                $rowExgRate['exch_rate'] = '';
                $amount = $row['amount'];
            }

            $rows .= "
            <tr class='' id='invoicePaymentRow'>
                <td class='txtCenter'>{$serial_no}</td>
                <td>
                    <a class='deleteOrderPayment' href='#'
                        order_payment_id='{$row['order_payment_id']}'><span>delete </span>
                    </a> |
                    {$edit}
                </td>
                <td>{$row['payment_type']}</td>
                <td>{$name}</td>
                <td>{$row['currency']}</td>
                <td class='txtRight'>{$rowExgRate['exch_rate']}</td>
                <td class='txtRight'>{$row['amount']}</td>
                <td class='txtRight' order_payment_id='{$row['order_payment_id']}'>
                    {$currencySign}{$amount}
                </td>
            </tr>
            ";
        }
//                <td class='txtRight'>
//                    <input type='text' name='amount' class='orderPaymentAmount' value='{$row['amount']}' order_payment_id='{$row['order_payment_id']}'>
//                </td>

        $totalAmount ='';
        if($session_order_id != ''){
            $SQL = "
            SELECT (SELECT SUM(amount)
            FROM order_payment
            WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
            (SELECT SUM(amount * exchange_rate)
            FROM order_payment
            WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency,
            (SELECT SUM(amount)
            FROM order_payment
            WHERE order_id = '{$session_order_id}' and payment_type = 'Credit Card') AS total_amount_card
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'] + $row['total_amount_card'];
        }

        $exchange =0;
        if($totalAmount > $netTotal){
            $exchange = $totalAmount - $netTotal;
        }

        $due =0;
        if($totalAmount < $netTotal){
            $due = $netTotal - $totalAmount;
        }

        $formAction = "index.php?module=pos_pos&_spAction=invoicePaymentSubmit&showHTML=0";

        $multi_payment = isset($_SESSION['multi_payment']) ? $_SESSION['multi_payment']  : '';

        if ($multi_payment != '' || $multi_payment != 0) {
            $activeState = "
            <li>
                <a id='invoicePaymentCash' class='' dialogtitle='Pay by Cash' href='index.php?module=pos_pos&_spAction=payByCash&nettotal={$netTotal}&showHTML=0'>
                    Cash
                </a>
            </li>
            <li>
                <a id='invoicePaymentCreditCard' class='' dialogtitle='Pay by Credit Card' href='index.php?module=pos_pos&_spAction=payByCreditCard&nettotal={$netTotal}&showHTML=0'>
                    Credit Card
                </a>
            </li>
            <li><a class=''>Coupon</a></li>
            <li><a class=''>Gift Card</a></li>
            ";
        } else {
            $activeState = "
            <li>
                <a class='disableState'>
                    Cash
                </a>
            </li>
            <li>
                <a class='disableState'>
                    Credit Card
                </a>
            </li>
            <li><a class='disableState'>Coupon</a></li>
            <li><a class='disableState'>Gift Card</a></li>
            ";
        }

        $text ="
        <div id='invoicePaymentDetails'>
            <div class='actionsWrapper'>
                <ul class='noDefault floatbox'>
                    {$activeState}
                </ul>
            </div>
            <div class='linkPortalWrapper' id='paymentDetails'>
                <form id='invoicePaymentForm' name='invoicePaymentForm' class='columnar' method='post' action='{$formAction}'>
                <table class='list'>
                    <thead>
                        <tr>
                            <th colspan=9>Payment Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class='portal-row2'>
                            <th class='left'>Seq No.</th>
                            <th class='left'>Action</th>
                            <th class='left'>Payment Type</th>
                            <th class='left'>Name</th>
                            <th class='left'>Currency</th>
                            <th class='txtRight'>Currency Exchange Rate</th>
                            <th class='txtRight'>Currency Converted Amount</th>
                            <th class='txtRight'>Paid Amount({$currency})</th>
                        </tr>
                        {$rows}
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan=7 class='txtRight'>Total Balance</th>
                            <th class='txtRight invoicePaymentTotal' id=''>{$currencySign}{$netTotal}</th>
                        </tr>
                        <tr>
                            <th colspan=7 class='txtRight'>Balance Due</th>
                            <th class='txtRight' id=''>{$currencySign}{$due}</th>
                        </tr>
                        <tr>
                            <th colspan=7 class='txtRight'>Total Amount Paid</th>
                            <th class='txtRight' id='paymentTotalAmount'>{$currencySign}{$totalAmount}</th>
                        </tr>
                        <tr>
                            <th colspan=7 class='txtRight'>Exchange</th>
                            <th class='txtRight' id=''>{$currencySign}{$exchange}</th>
                        </tr>
                    </tfoot>
                </table>
                </form>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPayByCash() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $net_total = $fn->getReqParam('nettotal');
        $_SESSION['total_cash_amount'] = '';
        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currencySign = $shop['currency_sign'];
        }

        $sqlCash = "
        SELECT valuelist_id
              ,value
        FROM valuelist
        WHERE key_text='cash'
        ORDER BY sort_order
        ";
        $result = $db->sql_query($sqlCash);

        $rows='';
        while($row = $db->sql_fetchrow($result)) {
            $rows .="
            <li>
                <a id='{$row['valuelist_id']}_cashValue' cvalue='{$row['value']}'>+{$row['value']}</a>
            </li>
            ";
        }

        $SQL = "
        SELECT (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
        (SELECT SUM(amount * exchange_rate)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency,
        (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and payment_type = 'Credit Card') AS total_amount_card
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'] + $row['total_amount_card'];

        $total_balance_due = $net_total - $totalAmount;

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $formAction = "index.php?module=pos_pos&_spAction=payByCashSubmit&showHTML=0";

        $text ="
        <div class='actionsWrapper'>
            <ul class='noDefault floatbox' id='cashValue'>
                {$rows}
            </ul>
        </div>
        <div class='linkPortalWrapper payByCash'>
            <form id='payByCashForm' class='columnar' method='post' action='{$formAction}'>
                <table class='list'>
                    <tbody>
                        <tr>
                            <th class='txtRight'>Total Balance Due</th>
                            <th class='txtRight' id=''>{$currencySign}{$total_balance_due}</th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Amount Paid</th>
                            <th class='txtRight' id='cashPaid'>
                                <div class='floatbox'>
                                    <div class='type-text float_right'>
                                        {$currencySign} <input type='text' class='text' name='paid_amount' id='fld_paid_amount' value=''>
                                        <input type='hidden' name='net_total' value='{$total_balance_due}' />
                                    </div>
                                    <div class='float_right'>
              		                    {$formObj->getDDRowBySQL('', 'currency', $sqlCurrency, 'HKD', array('sqlType' => 'OneField'))}
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditPayByCash() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $net_total = $fn->getReqParam('nettotal');
        $order_payment_id = $fn->getReqParam('order_payment_id');
        $_SESSION['total_cash_amount'] = '';
        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currencySign = $shop['currency_sign'];
        }

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $sqlCash = "
        SELECT valuelist_id
              ,value
        FROM valuelist
        WHERE key_text='cash'
        ORDER BY value
        ";
        $resultCash = $db->sql_query($sqlCash);

        $rows='';
        while($row = $db->sql_fetchrow($resultCash)) {
            $rows .="
            <li>
                <a id='{$row['valuelist_id']}_cashValue' cvalue='{$row['value']}'>+{$row['value']}</a>
            </li>
            ";
        }

        $SQL = "
        SELECT (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
        (SELECT SUM(amount * exchange_rate)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency,
        (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and payment_type = 'Credit Card') AS total_amount_card
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'] + $row['total_amount_card'];
        $total_balance_due = $net_total - $totalAmount;

        $SQL = "
        SELECT amount
              ,currency
        FROM order_payment
        WHERE order_payment_id = '{$order_payment_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $formAction = "index.php?module=pos_pos&_spAction=editPayByCashSubmit&showHTML=0";

        $text ="
        <div class='actionsWrapper'>
            <ul class='noDefault floatbox' id='cashValue'>
                {$rows}
            </ul>
        </div>
        <div class='linkPortalWrapper payByCash'>
            <form id='editPayByCashForm' class='columnar' method='post' action='{$formAction}'>
                <table class='list'>
                    <tbody>
                        <tr>
                            <th class='txtRight'>Total Balance Due</th>
                            <th class='txtRight' id=''>{$currencySign}{$total_balance_due}</th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Amount Paid</th>
                            <th class='txtRight' id='cashPaid'>
          		                {$formObj->getDDRowBySQL('', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
                                <div class='type-text'>
                                    {$currencySign} <input type='text' class='text' name='paid_amount' id='fld_paid_amount' value='{$row['amount']}'>
                                    <input type='hidden' name='order_payment_id' value='{$order_payment_id}' />
                                    <input type='hidden' name='net_total' value='{$total_balance_due}' />
                                </div>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getPayByCreditCard() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $net_total = $fn->getReqParam('nettotal');
        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currencySign = $shop['currency_sign'];
        }

        $sqlCash = "
        SELECT code
        FROM payment
        WHERE payment_type='Invoice'
           OR payment_type='Purchase Order and Invoice'
        ";
        $result = $db->sql_query($sqlCash);
        $expVl = array('sqlType' => 'OneField');

        $SQL = "
        SELECT (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
        (SELECT SUM(amount * exchange_rate)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency,
        (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and payment_type = 'Credit Card') AS total_amount_card
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'] + $row['total_amount_card'];

        $total_balance_due = $net_total - $totalAmount;

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $formAction = "index.php?module=pos_pos&_spAction=payByCreditCardSubmit&showHTML=0";

        $text ="
        <div class='linkPortalWrapper payByCash'>
            <form id='payByCreditCardForm' class='columnar' method='post' action='{$formAction}'>
                <table class='list'>
                    <tbody>
  		                {$formObj->getDDRowBySQL('', 'card_type', $sqlCash, '', $expVl)}
                        <tr>
                            <th class='txtRight'>Total Balance Due</th>
                            <th class='txtRight' id=''>{$currencySign}{$total_balance_due}</th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Amount Paid</th>
                            <th class='txtRight'>
                                <div class='floatbox'>
                                    <div class='type-text float_right'>
                                        {$currencySign} <input type='text' class='text' name='card_amount' id='fld_card_amount' value=''>
                                    </div>
                                    <div class='float_right'>
              		                    {$formObj->getDDRowBySQL('', 'currency', $sqlCurrency, 'HKD', array('sqlType' => 'OneField'))}
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Card No.</th>
                            <th class='txtRight'>
                                <div class='type-text'>
                                    <input type='text' class='text' name='card_no' id='fld_card_no' value=''>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Card Holder Name</th>
                            <th class='txtRight'>
                                <div class='type-text'>
                                    <input type='text' class='text' name='card_holder_name' id='fld_card_holder_name' value=''>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Expiry Date</th>
                            <td class='txtRight'>{$formObj->getDateRow('', 'expiry_date')}</td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditPayByCreditCard() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $net_total = $fn->getReqParam('nettotal');
        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currencySign = $shop['currency_sign'];
        }

        $order_payment_id = $fn->getReqParam('order_payment_id');

        $sqlCash = "
        SELECT code
        FROM payment
        WHERE payment_type='Invoice'
           OR payment_type='Purchase Order and Invoice'
        ";
        $expVl = array('sqlType' => 'OneField');

        $SQL = "
        SELECT (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency = 'HKD') AS total_amount,
        (SELECT SUM(amount * exchange_rate)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and currency != 'HKD') AS total_amount_other_currency,
        (SELECT SUM(amount)
        FROM order_payment
        WHERE order_id = '{$session_order_id}' and payment_type = 'Credit Card') AS total_amount_card
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $totalAmount = $row['total_amount'] + $row['total_amount_other_currency'] + $row['total_amount_card'];

        $total_balance_due = $net_total - $totalAmount;

        $SQL = "
        SELECT *
        FROM order_payment
        WHERE order_payment_id = '{$order_payment_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $formAction = "index.php?module=pos_pos&_spAction=editPayByCreditCardSubmit&showHTML=0";

        $text ="
        <div class='linkPortalWrapper payByCash'>
            <form id='editPayByCreditCardForm' class='columnar' method='post' action='{$formAction}'>
                <table class='list'>
                    <tbody>
  		                {$formObj->getDDRowBySQL('', 'card_type', $sqlCash, $row['card_type'], $expVl)}
                        <tr>
                            <th class='txtRight'>Total Balance Due</th>
                            <th class='txtRight' id=''>{$currencySign}{$total_balance_due}</th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Amount Paid</th>
                            <th class='txtRight'>
                                <div class='floatbox'>
                                    <div class='type-text float_right'>
                                        {$currencySign} <input type='text' class='text' name='card_amount' id='fld_card_amount' value='{$row['amount']}'>
                                        <input type='hidden' name='order_payment_id' value='{$order_payment_id}' />
                                    </div>
                                    <div class='float_right'>
              		                    {$formObj->getDDRowBySQL('', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Card No.</th>
                            <th class='txtRight'>
                                <div class='type-text'>
                                    <input type='text' class='text' name='card_no' id='fld_card_no' value='{$row['card_no']}'>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Card Holder Name</th>
                            <th class='txtRight'>
                                <div class='type-text'>
                                    <input type='text' class='text' name='card_holder_name' id='fld_card_holder_name' value='{$row['card_holder']}'>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight'>Expiry Date</th>
                            <td class='txtRight'>{$formObj->getDateRow('', 'expiry_date', $row['expiry_date'])}</td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getSecondaryAuthorization() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?module=pos_pos&_spAction=secondaryAuthorizationSubmit&showHTML=0";

        $text ="
        <div class='linkPortalWrapper'>
            <form id='secondaryAuthorizationForm' class='yform columnar' method='post' action='{$formAction}'>
                {$formObj->getFieldSetWrapped('', "
                    {$formObj->getTBRow('Staff Code', 'staff_code', '')}
                    {$formObj->getTBRow('Password', 'password', '')}
                     "
                )}
           </form>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getSecondaryAuthorizationOverall() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $net_total = $fn->getReqParam('nettotal');

        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $formAction = "index.php?module=pos_pos&_spAction=secondaryAuthorizationOverallSubmit&showHTML=0";

        $text ="
        <div class='linkPortalWrapper'>
            <form id='secondaryAuthorizationOverallForm' class='yform columnar' method='post' action='{$formAction}'>
                {$formObj->getFieldSetWrapped('', "
                    {$formObj->getTBRow('Staff Code', 'staff_code', '')}
                    {$formObj->getTBRow('Password', 'password', '')}
                     "
                )}
           </form>
        </div>
        ";
        return $text;
    }
}