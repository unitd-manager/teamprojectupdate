<?
class CP_Admin_Modules_Pos_Pos_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getDeleteOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');

        $SQL    = "DELETE FROM order_item WHERE order_item_id = {$order_item_id}";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getInsertOrderItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $sku_no = $fn->getReqParam('sku_no');
        $staff_id = $fn->getReqParam('staff_id');
        $sku_qty = $fn->getReqParam('sku_qty',1);
        $invoice_no = $fn->getReqParam('invoice_no');

        $text = '';
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : '';
        $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);

        $SQLOrderRec = "
        SELECT max(increament_no) AS increament_no
        FROM `order`
        WHERE order_code like '%{$shop['code']}%'
        ";
        $resultOrderRec = $db->sql_query($SQLOrderRec);
        $rowOrderRec = $db->sql_fetchrow($resultOrderRec);

        $invoice = $fn->getSettingsRowByKey('pfxInvoice');
        $length = $invoice['length'] - $invoice['starting_no'];

        $i = 0;
        $extraNo = '';
        while ($i < $length) {
            $extraNo .= '0';
            $i++;
        }

        if ($rowOrderRec['increament_no'] == '' || $rowOrderRec['increament_no'] == 0){
            $start_no = $invoice['starting_no'];
        } else {
            $start_no = $rowOrderRec['increament_no'] + 1;
        }

        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $invoice_no = $invoice['value'] . '-' . $shop['code'] . '-' . $extraNo . $start_no;
        } else {
            $invoice_no = $invoice['value'] . '-' . $extraNo . $start_no;
        }

        if($session_order_id == ''){
            $fa = array();
            $fa['order_status'] = 'Due';
            $fa['order_date']   = date("Y-m-d");
            $fa['staff_id']   = $staff_id;
            $fa['order_code']   = $invoice_no;
            $fa['increament_no']  = $start_no;

            $SQLOrder = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultOrder = $db->sql_query($SQLOrder);
            $order_id = $db->sql_nextid();
            $_SESSION['order_id'] = $order_id;
        }

        $SQL = "
        SELECT *
        FROM product_item
        WHERE sku_no = '{$sku_no}'
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)){

            $productRec = $fn->getRecordRowByID('product', 'product_id', $row['product_id']);
            $groupQty = $fn->getSettingsValueByKey('invGroupQtyWithSameProduct');

            $sqlOI = "
            SELECT *
            FROM order_item
            WHERE order_id = {$_SESSION['order_id']}
              AND sku_no = '{$sku_no}'
            ";
            $resultOI = $db->sql_query($sqlOI);
            $rowOI = $db->sql_fetchrow($resultOI);
            $numRows = $db->sql_numrows($resultOI);
            if ($numRows > 0 && $groupQty == 1) {
                $fa1 = array();
                $fa1['qty'] = $rowOI['qty'] + $sku_qty;

                $whereCondition = "WHERE order_item_id = '{$rowOI['order_item_id']}'";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, "order_item", $whereCondition);
                $result = $db->sql_query($SQL);
            } else {
                $fa1 = array();
                $fa1['order_id']    = $_SESSION['order_id'];
                $fa1['record_id']   = $row['product_id'];
                $fa1['list_price']  = $productRec['price'];
                $fa1['unit_price']  = $productRec['price'];
                $fa1['item_title']  = $productRec['title'];
                $fa1['sku_no']      = $sku_no;
                $fa1['qty']         = $sku_qty;
                $fa1['discount_type'] = 'HKD';

                $SQLOrderItem = $dbUtil->getInsertSQLStringFromArray($fa1, 'order_item');
                $resultOrderItem = $db->sql_query($SQLOrderItem);
            }
        }
    }

    /**
     *
     */
    function getTotalValues(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $session_order_id = $fn->getSessionParam('order_id', '', true);
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;

        $currencySign = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $currencySign = $shop['currency_sign'];
        }

        $discount_total= '';
        $priceRound = $fn->getSettingsValueByKey('invPriceRounding');

        $arr = array('subTotal' => 0, 'discTotal' => 0, 'actualTotal' => 0, 'netTotal' => 0, 'overallDiscount' => 0);

        if($session_order_id != ''){

            $order = $fn->getRecordRowByID('order', 'order_id', $session_order_id);

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
                  ,SUM(discount) AS discount_total
                  ,SUM(qty * list_price) AS actual_total
            FROM order_item
            WHERE order_id = '{$session_order_id}'
            ";
            $result = $db->sql_query($SQL);
            while ($row = $db->sql_fetchrow($result)){
                if ($priceRound == 'Round Up'){
                    $sub_total = ceil($row['sub_total']);
                    $actual_total = ceil($row['actual_total']);
                    $discount_total = ceil($discount_total);
                } else if ($priceRound == 'Round Down'){
                    $sub_total = floor($row['sub_total']);
                    $actual_total = floor($row['actual_total']);
                    $discount_total = floor($discount_total);
                } else if ($priceRound == 'Round Off'){
                    $sub_total = round($row['sub_total']);
                    $actual_total = round($row['actual_total']);
                    $discount_total = round($discount_total);
                } else {
                    $sub_total = $row['sub_total'];
                    $actual_total = $row['actual_total'];
                    $discount_total = $discount_total;
                }

                $arr['subTotal']    = number_format($sub_total, 2);
                $arr['actualTotal'] = number_format($actual_total, 2);

                $arr['subTotal']    = $currencySign . $arr['subTotal'];
                $arr['actualTotal'] = $currencySign . $arr['actualTotal'];
            }
            $arr['discTotal'] = $currencySign . $discount_total;
            $netTotal  = $sub_total - $discount_total;
            $arr['overallDiscount'] =  ($netTotal *  $order['overall_discount'])/100;
            $arr['netTotal']  = number_format($netTotal - $arr['overallDiscount'], 2);
            $arr['netTotal'] = $currencySign . $arr['netTotal'];
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getPaymentTotalValues(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $session_order_id = $fn->getSessionParam('order_id', '', true);

        $arr = array('totalAmount' => 0);

        if($session_order_id != ''){
            $SQL = "
            SELECT SUM(amount) AS total_amount
            FROM order_payment
            WHERE order_id = '{$session_order_id}'
            ";
            $result = $db->sql_query($SQL);
            while ($row = $db->sql_fetchrow($result)){
                $arr['totalAmount']    = $row['total_amount'];
            }
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getUpdateQtyOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $unit_price = $fn->getReqParam('unit_price');
        $qty = $fn->getReqParam('qty');

        $SQL    = "
        UPDATE order_item
        set qty = {$qty}
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getUpdateDiscountOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $order_item_id = $fn->getReqParam('order_item_id');
        $discount = $fn->getReqParam('discount');

        $discount_arr = explode('.', $discount);
        $discount_length = $discount_arr[0];
        $discount_decimal_length = $discount_arr[1];
        $discountLength = strlen($discount_length);
        $discount_decimal_length = strlen($discount_decimal_length);

        if ($discountLength > $cpCfg['numCostLength'] || $discount_decimal_length > $cpCfg['numDecimalLength']) {
            $minus_length = $discountLenth - $cpCfg['numCostLength'];
            $discountLength = substr($discountLength, 0, -$minus_length);
            return $discountLength;
        } else {
            $SQL    = "
            UPDATE order_item
            set discount = {$discount}
            WHERE order_item_id = {$order_item_id}
            ";
            $result = $db->sql_query($SQL);

            return 'Yes';
        }

    }

    /**
     *
     */
    function getUpdateDiscountTypeOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $discountObj = $fn->getReqParam('discountObj');

        $SQL    = "
        UPDATE order_item
        set discount_type = '{$discountObj}'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateUnitPriceOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $order_item_id = $fn->getReqParam('order_item_id');
        $unit_price = $fn->getReqParam('unit_price');

        $unit_price_arr = explode('.', $unit_price);
        $unit_price_length = $unit_price_arr[0];
        $unit_price_decimal_length = $unit_price_arr[1];
        $unitPriceLength = strlen($unit_price_length);
        $unitPriceDecimalLength = strlen($unit_price_decimal_length);

        if ($unitPriceLength > $cpCfg['numCostLength'] || $unitPriceDecimalLength > $cpCfg['costDecimalLength']) {
            $minus_length = $unitPriceLength - $cpCfg['numCostLength'];
            $unit_price = substr($unit_price, 0, -$minus_length);
            return $unit_price;
        } else {
            $SQL = "
            UPDATE order_item
            set unit_price = {$unit_price}
            WHERE order_item_id = {$order_item_id}
            ";
            $result = $db->sql_query($SQL);

            return 'Yes';
        }
    }

    /**
     *
     */
    function getUpdatePaidAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $totalAmount = isset($_SESSION['total_cash_amount']) ? $_SESSION['total_cash_amount']  : '';

        $cash = $fn->getReqParam('cash');

        if($cash != ''){
            $totalAmount = $totalAmount + $cash;
            $_SESSION['total_cash_amount'] = $totalAmount;
        }

        return $totalAmount;
    }

    /**
     *
     */
    function getPayByCashValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $paid_amount = $fn->getReqParam('paid_amount');
        $net_total = $fn->getReqParam('net_total');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('paid_amount', 'Please enter the amount to be paid');

        /*if($paid_amount > $net_total){
            $validate->errorArray['paid_amount']['name'] = "paid_amount";
            $validate->errorArray['paid_amount']['msg']  = $ln->gd("The amount entered is higher than the net total");
        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPayByCashSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $paid_amount = $fn->getReqParam('paid_amount');
        $currency = $fn->getReqParam('currency');

        if (!$this->getPayByCashValidate()){
            return $validate->getErrorMessageXML();
        }

        $from_currency = $fn->getRecordByCondition('currency', "code = '{$currency}'");
        $to_currency = $fn->getRecordByCondition('currency', "code = 'HKD'");

        $SQLExgRate = "
        SELECT *
        FROM currency_convert
        WHERE from_currency_id = {$from_currency['currency_id']}
          AND to_currency_id = {$to_currency['currency_id']}
        ";
        $resultExgRate = $db->sql_query($SQLExgRate);
        $rowExgRate = $db->sql_fetchrow($resultExgRate);

        if($currency != 'HKD'){
            $amount = $paid_amount * $rowExgRate['exch_rate'];
        } else {
            $amount = $paid_amount;
        }

        $fa = array();
        $fa['order_id']     = $_SESSION['order_id'];
        $fa['payment_type'] = 'Cash';
        $fa['amount']       = $paid_amount;
        $fa['currency']     = $currency;
        if($currency == 'HKD' || $currency == ''){
            $fa['exchange_rate'] = 1;
        } else{
            $fa['exchange_rate'] = $rowExgRate['exch_rate'];
        }

        $SQLOrderPayment = $dbUtil->getInsertSQLStringFromArray($fa, 'order_payment');
        $resultOrderPayment = $db->sql_query($SQLOrderPayment);

        $_SESSION['multi_payment'] = $_SESSION['multi_payment'] - 1;

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPayByCashSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $paid_amount = $fn->getReqParam('paid_amount');
        $order_payment_id = $fn->getReqParam('order_payment_id');
        $currency = $fn->getReqParam('currency');

        if (!$this->getPayByCashValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['order_id']    = $_SESSION['order_id'];
        $fa['payment_type']= 'Cash';
        $fa['amount']  = $paid_amount;
        $fa['currency']  = $currency;

        $whereCondition = "WHERE order_payment_id = '{$order_payment_id}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "order_payment", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPayByCreditCardValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $expiry_date = $fn->getReqParam('expiry_date');
        $currentDate  = date("Y-m-d");

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('card_amount', 'Please enter the amount to be paid');
        $validate->validateData('card_no', 'Please enter the card number');
        $validate->validateData('card_holder_name', 'Please enter the card holder name');
        $validate->validateData('expiry_date', 'Please select the expiry date');
        $validate->validateData('card_type', 'Please select the card type');

        /*if($expiry_date < $currentDate){
            $validate->errorArray['paid_amount']['name'] = "paid_amount";
            $validate->errorArray['paid_amount']['msg']  = $ln->gd("The amount entered is higher than the net total");
        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPayByCreditCardSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $card_amount = $fn->getReqParam('card_amount');
        $card_no = $fn->getReqParam('card_no');
        $card_holder_name = $fn->getReqParam('card_holder_name');
        $expiry_date = $fn->getReqParam('expiry_date');
        $card_type = $fn->getReqParam('card_type');
        $currency = $fn->getReqParam('currency');

        if (!$this->getPayByCreditCardValidate()){
            return $validate->getErrorMessageXML();
        }

        $from_currency = $fn->getRecordByCondition('currency', "code = '{$currency}'");
        $to_currency = $fn->getRecordByCondition('currency', "code = 'HKD'");

        $SQLExgRate = "
        SELECT *
        FROM currency_convert
        WHERE from_currency_id = {$from_currency['currency_id']}
          AND to_currency_id = {$to_currency['currency_id']}
        ";
        $resultExgRate = $db->sql_query($SQLExgRate);
        $rowExgRate = $db->sql_fetchrow($resultExgRate);

        $fa = array();
        $fa['order_id']    = $_SESSION['order_id'];
        $fa['payment_type']= 'Credit Card';
        $fa['amount']  = $card_amount;
        $fa['card_no']  = $card_no;
        $fa['card_holder']  = $card_holder_name;
        $fa['expiry_date']  = $expiry_date;
        $fa['card_type']   = $card_type;
        $fa['currency']     = $currency;
        if($currency == 'HKD' || $currency == ''){
            $fa['exchange_rate'] = 1;
        } else{
            $fa['exchange_rate'] = $rowExgRate['exch_rate'];
        }

        $SQLOrderPayment = $dbUtil->getInsertSQLStringFromArray($fa, 'order_payment');
        $resultOrderPayment = $db->sql_query($SQLOrderPayment);

        $_SESSION['multi_payment'] = $_SESSION['multi_payment'] - 1;

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPayByCreditCardSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $card_amount = $fn->getReqParam('card_amount');
        $card_no = $fn->getReqParam('card_no');
        $card_holder_name = $fn->getReqParam('card_holder_name');
        $expiry_date = $fn->getReqParam('expiry_date');
        $order_payment_id = $fn->getReqParam('order_payment_id');
        $card_type = $fn->getReqParam('card_type');
        $currency = $fn->getReqParam('currency');

        if (!$this->getPayByCreditCardValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['order_id']    = $_SESSION['order_id'];
        $fa['payment_type']= 'Credit Card';
        $fa['amount']  = $card_amount;
        $fa['card_no']  = $card_no;
        $fa['card_holder']  = $card_holder_name;
        $fa['expiry_date']  = $expiry_date;
        $fa['card_type']  = $card_type;
        $fa['currency']     = $currency;

        $whereCondition = "WHERE order_payment_id = '{$order_payment_id}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "order_payment", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDeleteOrderPayment() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_payment_id = $fn->getReqParam('order_payment_id');

        $SQL    = "DELETE FROM order_payment WHERE order_payment_id = {$order_payment_id}";
        $result = $db->sql_query($SQL);

        $_SESSION['multi_payment'] = $_SESSION['multi_payment'] + 1;

    }

    /**
     *
     */
    function getInvoicePaymentValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getInvoicePaymentSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');


        if (!$this->getInvoicePaymentValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateOverallDiscountOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        $overall_discount = $fn->getReqParam('overall_discount');

        $SQL    = "
        UPDATE `order`
        set overall_discount = {$overall_discount}
        WHERE order_id = {$_SESSION['order_id']}
        ";
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getClearOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $_SESSION['order_id'] = '';
        $_SESSION['gotAuthorisation'] = '';
        $_SESSION['gotAuthorisationOverall'] = '';
        $_SESSION['multi_payment'] = '';
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : '';

        $invoice = $fn->getSettingsRowByKey('pfxInvoice');

        $length = $invoice['length'] - $invoice['starting_no'];

        $i = 0;
        $extraNo = '';
        while ($i < $length) {
            $extraNo .= '0';
            $i++;
        }

        $SQLOrder = "
        SELECT max(increament_no) AS increament_no FROM `order`
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

        return $invoice_no;
    }

    /**
     *
     */
    function getClearAmount() {
        $_SESSION['total_cash_amount'] = '';
    }

    /**
     *
     */
    function getPopulateMemberCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $member_code = $fn->getReqParam('member_code');

        $SQL    = "
        SELECT CONCAT_WS(' ', first_name, last_name) AS contact_name
        FROM contact
        WHERE member_no = '{$member_code}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['contact_name'];

    }

    /**
     *
     */
    function getUnitPriceValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        //==================================================================//
        $unit_price = $fn->getReqParam('unit_price');
        $unit_price_arr = explode('.', $unit_price);
        $unit_price_length = $unit_price_arr[0];
        $unit_price_length = strlen($unit_price_length);

        $numCostLength = $fn->getSettingsValueByKey('numCostLength');

        if ($unit_price_length > $numCostLength) {
            $minus_length = $unit_price_length - $cpCfg['numCostLength'];
            $unit_price = substr($unit_price, 0, -$minus_length);
            return $unit_price;
        } else {
            return 'Yes';
        }

    }

    /**
     *
     */
    function getItemDiscountValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        //==================================================================//
        $discount = $fn->getReqParam('discount');
        $discount_arr = explode('.', $discount);
        $discount_length = $discount_arr[0];
        $discountLenth = strlen($discount_length);

        $SQL = "
        SELECT value
        FROM setting s
        WHERE key_text = 'numCostLength'
        ORDER BY setting_id
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if ($discountLenth > $cpCfg['numCostLength']) {
            $minus_length = $discountLenth - $cpCfg['numCostLength'];
            $discountLenth = substr($discountLenth, 0, -$minus_length);
            return $discountLenth;
        } else {
            return 'Yes';
        }

    }

    /**
     *
     */
    function getInvoiceDiscountValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        //==================================================================//
        $overall_item_discount = $fn->getReqParam('overall_item_discount');
        $overall_item_discountLenth = strlen($overall_item_discount);

        $SQL = "
        SELECT value
        FROM setting s
        WHERE key_text = 'numCostLength'
        ORDER BY setting_id
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if ($overall_item_discountLenth > $cpCfg['numCostLength']) {
            $minus_length = $overall_item_discountLenth - $cpCfg['numCostLength'];
            $overall_item_discountLenth = substr($overall_item_discountLenth, 0, -$minus_length);
            return $overall_item_discountLenth;
        } else {
            return 'Yes';
        }

    }

    /**
     *
     */
    function getWarningMessage() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $session_shop_id = isset($_SESSION['shopId']) ? $_SESSION['shopId']  : false;
        $sku_no = $fn->getReqParam('sku_no');

        $title = '';
        if($session_shop_id != ''){
            $shop = $fn->getRecordRowByID('shop', 'shop_id', $session_shop_id);
            $title = $shop['title'];
        }
        $product_item = $fn->getRecordByCondition('product_item', "sku_no = '{$sku_no}'");
        $productRec = $fn->getRecordRowByID('product', 'product_id', $product_item['product_id']);

        $formAction = "index.php?module=pos_pos&_spAction=warningMessageSubmit&showHTML=0";

        $msg    = "
        <form id='warningMessageForm' name='warningMessageForm' action='{$formAction}'>
            The SKU {$sku_no} does not exist in shop {$title}, and the List Price is {$productRec['price']}
            Are you sure to add it ?
        </form>
        ";
        return $msg;

    }

    /**
     *
     */
    function getWarningMessageValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getWarningMessageSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getWarningMessageValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSecondaryAuthorizationValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $session_staff_id = isset($_SESSION['staff_id']) ? $_SESSION['staff_id']  : '';
        $staff_code     = $fn->getPostParam('staff_code', '', true);
        $password     = $fn->getPostParam('password', '', true);

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('staff_code', 'Please enter the staff code');
        $validate->validateData('password', 'Please enter the password');

        $staff = $fn->getRecordRowByID('staff', 'staff_id', $session_staff_id);

        if ($staff_code != '' && $staff_code != $staff['staff_code']){
            $validate->errorArray['staff_code']['name'] = "staff_code";
            $validate->errorArray['staff_code']['msg']  = 'Invalid staff code';
        }

        if ($password != '' && $password != $staff['pass_word']){
            $validate->errorArray['password']['name'] = "password";
            $validate->errorArray['password']['msg']  = 'Invalid password';
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSecondaryAuthorizationSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $_SESSION['gotAuthorisation'] = 1;

        if (!$this->getSecondaryAuthorizationValidate()){
            return $validate->getErrorMessageXML();
        }


        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSecondaryAuthorizationOverallValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $session_staff_id = isset($_SESSION['staff_id']) ? $_SESSION['staff_id']  : '';
        $staff_code     = $fn->getPostParam('staff_code', '', true);
        $password     = $fn->getPostParam('password', '', true);

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('staff_code', 'Please enter the staff code');
        $validate->validateData('password', 'Please enter the password');

        $staff = $fn->getRecordRowByID('staff', 'staff_id', $session_staff_id);

        if ($staff_code != '' && $staff_code != $staff['staff_code']){
            $validate->errorArray['staff_code']['name'] = "staff_code";
            $validate->errorArray['staff_code']['msg']  = 'Invalid staff code';
        }

        if ($password != '' && $password != $staff['pass_word']){
            $validate->errorArray['password']['name'] = "password";
            $validate->errorArray['password']['msg']  = 'Invalid password';
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSecondaryAuthorizationOverallSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $_SESSION['gotAuthorisationOverall'] = 1;

        if (!$this->getSecondaryAuthorizationOverallValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }
}
