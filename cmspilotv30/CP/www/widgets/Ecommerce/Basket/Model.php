<?
class CP_Www_Widgets_Ecommerce_Basket_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $basket = $cpCfg['cp.basketArray'][$c->modName];
        $tableName  = $basket['tableName'];
        $keyField   = $basket['keyField'];
        $titleField = $basket['titleField'];

        if ($c->orderId > 0){
            $SQL = "
            SELECT b.*
                  ,(b.qty*unit_price) AS total_price
                  ,p.{$titleField} AS title
            FROM order_item b
            LEFT JOIN {$tableName} p ON (p.{$keyField} = b.record_id)
            ";
        } else {
            $SQL = "
            SELECT b.*
                  ,(b.qty*unit_price) AS total_price
                  ,p.{$titleField} AS title
            FROM basket b
            LEFT JOIN {$tableName} p ON (p.{$keyField} = b.record_id)
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchVar = $this->searchVar;
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $searchVar->mainTableAlias = 'b';

        if ($c->orderId > 0){
            $searchVar->sqlSearchVar[] = "b.order_id = {$c->orderId}";
        } else {
            $session_id = session_id();
            $searchVar->sqlSearchVar[] = "b.module = '{$c->modName}'";
            $searchVar->sqlSearchVar[] = "b.session_id = '{$session_id}'";
        }

        $searchVar->sortOrder = "title";
    }

    /**
     *
     */
    function getDataArray(){
        $modelHelper = Zend_Registry::get('modelHelper');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $basket = $cpCfg['cp.basketArray'][$c->modName];
        $c->hasShippingCharge = ($c->hasShippingCharge != '') ? $c->hasShippingCharge  : $basket['hasShippingCharge'];
        $c->hasDiscount       = ($c->hasDiscount       != '') ? $c->hasDiscount        : $basket['hasDiscount'];
        $c->hasPromoCode      = ($c->hasPromoCode      != '') ? $c->hasPromoCode       : $basket['hasPromoCode'];
        $c->currency          = ($c->currency          != '') ? $c->currency           : $basket['currency'];
        $c->currencyDisplay   = ($c->currencyDisplay   != '') ? $c->currencyDisplay    : $basket['currencyDisplay'];
        $c->decimals          = ($c->decimals          != '') ? $c->decimals           : $basket['decimals'];
        $c->showPicture       = ($c->showPicture       != '') ? $c->showPicture        : $basket['showPictureInBasket'];
        $c->showNotesPerItem  = ($c->showNotesPerItem  != '') ? $c->showNotesPerItem   : $basket['showNotesPerItem'];
        $c->hasItemsInChildTable = ($c->hasItemsInChildTable != '') ? $c->hasItemsInChildTable   : $basket['hasItemsInChildTable'];
        $c->showCtryDdInBktForShipCalc = ($c->showCtryDdInBktForShipCalc != '') ? $c->showCtryDdInBktForShipCalc : $basket['showCtryDdInBktForShipCalc'];

        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'ecommerce_basket');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getEmptyBasket($modName = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if ($modName == ''){
            $modName = $fn->getReqParam('modName', '', true);
        }

        if ($modName == ''){
            return;
        }

        $session_id = session_id();
        $SQL = "
        DELETE FROM basket
        WHERE session_id = '{$session_id}'
          AND module = '{$modName}'
        ";
        $result = $db->sql_query($SQL);

        unset($_SESSION['cpDiscount']);
        unset($_SESSION['cpPromoCode']);
        unset($_SESSION['cpDiscountType']);
        unset($_SESSION['cpShippingCharge']);
        unset($_SESSION['cpShippingCountryCode']);
    }

    /**
     *
     */
    function getBasketGrossTotal(){
        $db = Zend_Registry::get('db');
        $c = &$this->controller;

        $sid = session_id();

        if ($c->orderId > 0){
            $SQL = "
            SELECT SUM(unit_price * qty) AS basket_amount
            FROM order_item
            WHERE order_id = {$c->orderId}
            ";
        } else {
            $SQL = "
            SELECT SUM(unit_price * qty) AS basket_amount
            FROM basket
            WHERE session_id = '{$sid}' AND module = '{$c->modName}'
            ";
        }

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row['basket_amount'];
    }

    /**
     *
     */
    function getTotalQty(){
        $db = Zend_Registry::get('db');
        $c = &$this->controller;

        $sid = session_id();

        if ($c->orderId > 0){
            $SQL = "
            SELECT SUM(qty) AS total_qty
            FROM order_item
            WHERE order_id = {$c->orderId}
            ";
        } else {
            $SQL = "
            SELECT SUM(qty) AS total_qty
            FROM basket
            WHERE session_id = '{$sid}' AND module = '{$c->modName}'
            ";
        }

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $qty = ($row['total_qty'] > 0) ? $row['total_qty'] : 0;

        return $qty;
    }

    /**
     *
     */
    function getBasketNetTotal(){
        $total = $this->getBasketGrossTotal()+ $this->getShippingCharge() - $this->getDiscount();
        return $total;
    }

    /**
     *
     */
    function getShippingCharge(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $calcShipChargebyCountry = $basketArray['calcShipChargebyCountry'];
        $calcShipChargebySite = $basketArray['calcShipChargebySite'];
        $multiplyShipChrgByQty = $basketArray['multiplyShipChrgByQty'];
        $shippingChargeSess = $fn->getSessionParam('cpShippingCharge');
        $decimals = $basketArray['decimals'];

        if ($this->getTotalQty() == 0){
            return;
        }

        $shipping = '0';

        if ($c->orderId > 0){
            $rec = $fn->getRecordRowByID('order', 'order_id', $c->orderId);

            if (is_array($rec)){
                $shipping = $rec['shipping_charge'];
            }

        } else {

            if ($shippingChargeSess != ''){
                $shipping = $shippingChargeSess;
            } else if ($calcShipChargebySite){
                $rec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
                $shipping = isset($rec['shipping_charge_1']) ? $rec['shipping_charge_1'] : $rec['shipping_charge'];

            } else if (!$calcShipChargebyCountry){
                $shipping = $fn->getIssetParam($cpCfg, 'flatShippingCharge', 0);
            }

            if ($multiplyShipChrgByQty){
                $shipping = $this->getTotalQty() * $shipping;
            }
        }
        return number_format($shipping, $decimals);
    }

    /**
     *
     */
    function getChangeShippingCountry(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $country_code = $fn->getReqParam('country_code');
        $_SESSION['cpShippingCountryCode'] = $country_code;

        $arr['netTotal'] = $this->getBasketNetTotal();

        if ($country_code != ''){
            $rec = $fn->getRecordByCondition('geo_country', "country_code = '{$country_code}'");

            if (is_array($rec)){
                $_SESSION['cpShippingCharge'] = $rec['shipping_charge'];
                $arr['netTotal'] = $this->getBasketNetTotal();
            }
        } else {
            $_SESSION['cpShippingCharge'] = '';
        }

        $arr['shippingCharge'] = $this->getShippingCharge();

        return json_encode($arr);
    }

    /**
     *
     */
    function getChangeShippingCharge(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $charge = $fn->getReqParam('charge');
        $_SESSION['cpShippingCountryCode'] = $charge;

        $arr['netTotal'] = $this->getBasketNetTotal();

        if ($charge != ''){
            $_SESSION['cpShippingCharge'] = $charge;
            $arr['netTotal'] = $this->getBasketNetTotal();
        } else {
            $_SESSION['cpShippingCharge'] = '';
        }

        $arr['shippingCharge'] = $this->getShippingCharge();

        return json_encode($arr);
    }

    /**
     *
     */
    function getDiscount(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $dicountSess = $fn->getSessionParam('cpDiscount');
        $decimals = $basketArray['decimals'];

        $dicount = '0';

        if ($c->orderId > 0){
            $rec = $fn->getRecordRowByID('order', 'order_id', $c->orderId);

            if (is_array($rec) && isset($rec['discount'])){
                $dicount = $rec['discount'];
            }

        } else {

            if ($dicountSess != ''){
                $dicount = $dicountSess;
            }
        }

        return number_format($dicount, $decimals);
    }

    /**
     *
     */
    function getAddPromoCode(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddPromoCodeValidate()){
            return $validate->getErrorMessageXML();
        }

        $discount_amount = 0;
        $promo_code = $fn->getPostParam('promo_code');
        $rec = $fn->getRecordByCondition('promo_code', "code = '{$promo_code}'");
        if ($rec['discount_type'] == 'Amount'){
            $discount_amount = $rec['discount_amount'];
        }

        $_SESSION['cpDiscount'] = $discount_amount;
        $_SESSION['cpPromoCode'] = $promo_code;

        $arr['discount'] = $discount_amount;
        $arr['netTotal'] = $this->getBasketNetTotal();
        $arr['promoCode'] = $promo_code;

        return $validate->getSuccessMessageXML('', '', $arr);
    }

    /**
     *
     */
    function getAddPromoCodeValidate() {
        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $isCodeNotValid = $validate->validateData('promo_code', $ln->gd('w.ecommerce.basket.promoCode.err'));

        if (!$isCodeNotValid){
            $promo_code = $fn->getPostParam('promo_code');
            $rec = $fn->getRecordByCondition('promo_code', "code = '{$promo_code}'");
            if (!is_array($rec)){
                $validate->errorArray['promo_code']['name'] = "promo_code";
                $validate->errorArray['promo_code']['msg']  = $ln->gd('w.ecommerce.basket.promoCode.err.noRec');
            } else {
                $exp_date = $rec['expiry_date'];

                if ($exp_date != '' && $exp_date < date('Y-m-d')){
                    $validate->errorArray['promo_code']['name'] = "promo_code";
                    $validate->errorArray['promo_code']['msg']  = $ln->gd('w.ecommerce.basket.promoCode.err.expired');
                }
            }
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
    function getRemovePromoCode(){
        unset($_SESSION['cpDiscount']);
        unset($_SESSION['cpDiscountType']);
        unset($_SESSION['cpPromoCode']);

        $arr['netTotal'] = $this->getBasketNetTotal();
        $arr['discount'] = $this->getDiscount();

        return json_encode($arr);
    }

    /**
     * [isBasketEmpty description]
     * @return boolean [description]
     */
    function isBasketEmpty(){
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $sid = session_id();

        $SQL = "
        SELECT *
        FROM basket
        WHERE session_id = '{$sid}' AND module = '{$c->modName}'
        ";

        $row = $fn->getRecordBySQL($SQL);
        $is_basket_empty = count($row) > 0 ? false : true;

        return $is_basket_empty;
    }
}