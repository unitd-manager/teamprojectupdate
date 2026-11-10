<?
class CP_Www_Modules_Ecommerce_Basket_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $newUserCreated = false;
    //==================================================================//
    function getConfirmOrder() {
        $hook = getCPModuleHook2('ecommerce_basket', 'confirmOrder', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = $this->getFields();
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
        $result = $db->sql_query($SQL);
        $order_id = $db->sql_nextid();

        $session_id = session_id();

        $SQL = "
        SELECT b.*
              ,p.title AS title
        FROM basket b
        LEFT JOIN product p ON (p.product_id = b.record_id)
        WHERE b.module = '{$fa['module']}'
          AND b.session_id = '{$session_id}'
        ";
        $result = $db->sql_query($SQL);

        $basketArr = $cpCfg['cp.basketArray'][$fa['module']];
        $hasItemsInChildTable = $basketArr['hasItemsInChildTable'];

        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $fa = array();
            $fa['order_id']   = $order_id;
            $fa['module']     = $row['module'];
            $fa['record_id']  = $row['record_id'];
            $fa['qty']        = $row['qty'];
            $fa['item_title'] = $row['title'];
            $fa['unit_price'] = $row['unit_price'];

            if (isset($row['notes'])){
                $fa['notes'] = $row['notes'];
            }

            if ($hasItemsInChildTable){
                $fa['child_id'] = $row['child_id'];
                $childRec = $fn->getRecordRowByID('product_item', 'product_item_id', $row['child_id']);
                $fa['sku_no'] = $childRec['sku_no'];
            }

            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order_item');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order_item');
            $db->sql_query($SQL);
        }

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        /********************************************/
        $updateSQL = "
        UPDATE `basket`
        SET order_id= {$order_id}
        WHERE module = '{$fa['module']}'
          AND session_id = '{$session_id}'
        ";
        $db->sql_query($updateSQL);

        /********************************************/
        $plObj = getCPPluginObj('paymentMethods_' . $orderRec['payment_method']);
        return $plObj->model->proceedToGateway($order_id);
    }

    /**
     *
     */
    function sendOrderConfirmationEmails($order_id) {
        $hook = getCPModuleHook('ecommerce_basket', 'sendOrderConfirmationEmails', $order_id, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $this->sendOrderConfirmationEmailToAdmin($order_id);
        $this->sendOrderConfirmationEmailToUser($order_id);
    }

    /**
     *
     */
    function sendOrderConfirmationEmailToAdmin($order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $row = $fn->getRecordRowByID('order', 'order_id', $order_id);

        if(!is_array($row)){
            return;
        }

        $basketArr = $cpCfg['cp.basketArray'][$row['module']];
        $message = $ln->gd($basketArr['emailToAdminBody']);

        $message = str_replace("[[first_name]]"     , $row["shipping_first_name"]          , $message );
        $message = str_replace("[[last_name]]"      , $row["shipping_last_name"]           , $message );
        $message = str_replace("[[email]]"          , $row["shipping_email"]               , $message );
        $message = str_replace("[[phone]]"          , $row["shipping_phone"]               , $message );
        $message = str_replace("[[address1]]"       , $row["shipping_address1"]            , $message );
        $message = str_replace("[[address2]]"       , $row["shipping_address2"]            , $message );
        $message = str_replace("[[address_area]]"   , $row["shipping_address_area"]        , $message );
        $message = str_replace("[[address_city]]"   , $row["shipping_address_city"]        , $message );
        $message = str_replace("[[address_state]]"  , $row["shipping_address_state"]       , $message );
        $message = str_replace("[[address_country]]", $row["shipping_address_country_code"], $message );
        $message = str_replace("[[currentDate]]"    , $currentDate                         , $message );

        $wBasket = getCPWidgetObj('ecommerce_basket');
        $basket  = $wBasket->getWidget(array(
             'modName' => $row['module']
            ,'mode'    => 'detail'
            ,'orderId' => $order_id
        ));

        $message  .= $basket;
        $subject   = $ln->gd($basketArr['emailToAdminSubject']);
        $fromName  = $row["shipping_first_name"] . " " . $row["shipping_last_name"];
        $fromEmail = $row["shipping_email"];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];
        $ccEmail   = '';

        $paymentGatewayByCountry = $basketArr['paymentGatewayByCountry'];
        if ($paymentGatewayByCountry){
            $country_code = $row['shipping_address_country_code'];

            if ($country_code != ''){
                $countryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$country_code}'");

                if (is_array($countryRec) && $countryRec['stockist_id'] > 0) {
                    $stockistRec = $fn->getRecordRowByID('stockist', 'stockist_id', $countryRec['stockist_id']);
                    if (is_array($stockistRec) && $stockistRec['email'] != '') {
                        $toName  = $stockistRec['company_name'];
                        $toEmail = $stockistRec['email'];
                        $ccEmail = $cpCfg['cp.adminEmail'];
                    }
                }
            }
        }

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'ccEmail'   => $ccEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

    }

    /**
     *
     */
    function sendOrderConfirmationEmailToUser($order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $row = $fn->getRecordRowByID('order', 'order_id', $order_id);

        if(!is_array($row)){
            return;
        }

        $basketArr = $cpCfg['cp.basketArray'][$row['module']];
        $message = $ln->gd2($basketArr['emailToUserBody']);

        if (!$message){
            return;
        }

        $shippingAddress  = $row['shipping_address1']             != '' ? $row['shipping_address1'] . '<br>'      : '';
        $shippingAddress .= $row['shipping_address2']             != '' ? $row['shipping_address2'] . '<br>'      : '';
        $shippingAddress .= $row['shipping_address_area']         != '' ? $row['shipping_address_area'] . '<br>'  : '';
        $shippingAddress .= $row['shipping_address_city']         != '' ? $row['shipping_address_city'] . '<br>'  : '';
        $shippingAddress .= $row['shipping_address_state']        != '' ? $row['shipping_address_state'] . '<br>' : '';
        $shippingAddress .= $row['shipping_address_country_code'] != '' ? $row['shipping_address_country_code']   : '';

        $wBasket = getCPWidgetObj('ecommerce_basket');
        $basket  = $wBasket->getWidget(array(
             'modName' => $row['module']
            ,'mode'    => 'detail'
            ,'orderId' => $order_id
        ));

        $netTotal = $wBasket->view->getPriceDisplay($wBasket->model->getBasketNetTotal());

        $message = str_replace("[[first_name]]"     , $row["shipping_first_name"], $message );
        $message = str_replace("[[last_name]]"      , $row["shipping_last_name"] , $message );
        $message = str_replace("[[email]]"          , $row["shipping_email"]     , $message );
        $message = str_replace("[[grandTotal]]"     , $netTotal                  , $message );
        $message = str_replace("[[shippingAddress]]", $shippingAddress           , $message );
        $message = str_replace("[[orderSummary]]"   , $basket                    , $message );
        $message = str_replace("[[currentDate]]"    , $currentDate               , $message );
        $message = str_replace("[[siteUrl]]"        , $cpCfg['cp.siteUrl']       , $message );

        $subject   = $ln->gd($basketArr['emailToUserSubject']);
        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $row["shipping_first_name"] . " " . $row["shipping_last_name"];
        $toEmail   = $row["shipping_email"];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
    }


    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();
        foreach ($_SESSION['shippingDetails'] as $fldName => $fldValue) {
            $fld = "shipping_{$fldName}";
            if($dbUtil->getColumnExists('order', $fld)){
                $fa[$fld] = $fldValue;
            }
        }

        $fa['contact_id']      = $this->addSaveContact();
        $fa['payment_method']  = $_SESSION['shippingDetails']['payment_method'];
        $fa['module']          = $_SESSION['shippingDetails']['modName'];
        $fa['order_status']    = 'New';
        $fa['shipping_charge'] = getCPWidgetObj('ecommerce_basket')->model->getShippingCharge();

        if($dbUtil->getColumnExists('order', 'contact_module')){
            $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'common_contact');
            $fa['contact_module'] = $_SESSION['shippingDetails']['contactModName'];
        }

        if (isset($_SESSION['shippingDetails']['organization_id'])){
            $fa['organization_id'] = $_SESSION['shippingDetails']['organization_id'];
        }

        $basketArr = $cpCfg['cp.basketArray'][$fa['module']];
        $stampOrderWithStockist = $basketArr['stampOrderWithStockist'];
        $hasDiscount = $basketArr['hasDiscount'];
        
        if ($hasDiscount){
            $fa['discount'] = getCPWidgetObj('ecommerce_basket')->model->getDiscount();
        }

        if (isset($fa['shipping_address_country_code']) && $stampOrderWithStockist){
            $country_code = $fa['shipping_address_country_code'];

            if ($country_code != ''){
                $countryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$country_code}'");

                if (is_array($countryRec) && $countryRec['stockist_id'] > 0) {
                    $fa['stockist_id'] = $countryRec['stockist_id'];
                }
            }
        }

        $hook = getCPModuleHook('ecommerce_basket', 'fields', $fa, $this);
        if($hook['status']){
            return $hook['html'];
        }

        return $fa;
    }

    /**
     *
     */
    function getOrderTotal($order_id){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT SUM(oi.unit_price * oi.qty) AS total
              ,o.*
        FROM order_item oi
        JOIN `order` o ON (o.order_id = oi.order_id)
        WHERE oi.order_id = {$order_id}
        ";

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        $module = $row['module'];
        $total  = $row['total'] + $row['shipping_charge'];

        $basketArr = $cpCfg['cp.basketArray'][$module];

        if($basketArr['hasDiscount']){
            $total  = $total - $row['discount'];
        }

        return $total;
    }

    //==================================================================//
    function getBasketObj($param = array()) {
        $fn = Zend_Registry::get('fn');
        $arr = array();

        /************** Key Details *************/
        $arr['sectionType']              = $fn->getIssetParam($param, 'sectionType', 'Product');
        $arr['tableName']                = $fn->getIssetParam($param, 'tableName', 'product');
        $arr['keyField']                 = $fn->getIssetParam($param, 'keyField', 'product_id');
        $arr['titleField']               = $fn->getIssetParam($param, 'titleField', 'title');
        $arr['basketSecType']            = $fn->getIssetParam($param, 'basketSecType', 'Basket');
        $arr['contactModName']           = $fn->getIssetParam($param, 'contactModName', 'membership_contact');

        /************** Cart Table *************/
        $arr['currency']                   = $fn->getIssetParam($param, 'currency', 'USD');
        $arr['currencyDisplay']            = $fn->getIssetParam($param, 'currencyDisplay', 'US$');
        $arr['decimals']                   = $fn->getIssetParam($param, 'decimals', 2);
        $arr['hasShippingCharge']          = $fn->getIssetParam($param, 'hasShippingCharge', true);
        $arr['hasDiscount']                = $fn->getIssetParam($param, 'hasDiscount', false);
        $arr['hasPromoCode']               = $fn->getIssetParam($param, 'hasPromoCode', false);
        $arr['showPictureInBasket']        = $fn->getIssetParam($param, 'showPictureInBasket', true);
        $arr['showCurrencyOnAllValues']    = $fn->getIssetParam($param, 'showCurrencyOnAllValues', false);

        $arr['priceFld']                   = $fn->getIssetParam($param, 'priceFld', 'price');
        $arr['stockFld']                   = $fn->getIssetParam($param, 'stockFld', 'qty_in_stock');

        // if true this a dropdown will be shown near the shipping charges
        $arr['showCtryDdInBktForShipCalc'] = $fn->getIssetParam($param, 'showCtryDdInBktForShipCalc', false);
        $arr['calcShipChargebyCountry']    = $fn->getIssetParam($param, 'calcShipChargebyCountry', false);
        $arr['calcShipChargebySite']       = $fn->getIssetParam($param, 'calcShipChargebySite', false);
        $arr['multiplyShipChrgByQty']      = $fn->getIssetParam($param, 'multiplyShipChrgByQty', false);

        // whether the products are linked to the product items table - used for storing sku no, child id etc.. in basket and order
        $arr['hasItemsInChildTable']     = $fn->getIssetParam($param, 'hasItemsInChildTable', false);

        /************** Payment Methods *************/
        $arr['paymentMethods']           = $fn->getIssetParam($param, 'paymentMethods', array('paypal'));
        $arr['defaultPaymentMethod']     = $fn->getIssetParam($param, 'defaultPaymentMethod', 'paypal');
        $arr['showPaymentMethods']       = $fn->getIssetParam($param, 'showPaymentMethods', true);
        $arr['paymentGatewayByCountry']  = $fn->getIssetParam($param, 'paymentGatewayByCountry', false);

        $arr['stampOrderWithStockist']   = $fn->getIssetParam($param, 'stampOrderWithStockist', false);
        $arr['updateStockAfterPayment']  = $fn->getIssetParam($param, 'updateStockAfterPayment', true);

        /************** Shipping Details *************/
        $arr['shipHeading']              = $fn->getIssetParam($param, 'shipHeading', 'w.ecommerce.shippingDetails.form.heading');
        $arr['shipDefaultCountry']       = $fn->getIssetParam($param, 'shipDefaultCountry');
        $arr['shipShowOrgId']            = $fn->getIssetParam($param, 'shipShowOrgId', false);
        $arr['shipShowCaptcha']          = $fn->getIssetParam($param, 'shipShowCaptcha', false);
        $arr['shipShowConfirmEmail']     = $fn->getIssetParam($param, 'shipShowConfirmEmail', false);
        $arr['shipShowAcceptTermsCbox']  = $fn->getIssetParam($param, 'shipShowAcceptTermsCbox', false);

        // if this is true, then a complete list of products with options to check & enter the qty for quick buy //
        $arr['shipShowItemsList']        = $fn->getIssetParam($param, 'shipShowItemsList', false);
        $arr['showNotesPerItem']         = $fn->getIssetParam($param, 'showNotesPerItem', false);

        /************** Confirm Order *************/
        $arr['successMsg']               = $fn->getIssetParam($param, 'successMsg', 'm.ecommerce.basket.order.message.success');
        $arr['failMsg']                  = $fn->getIssetParam($param, 'failMsg', 'm.ecommerce.basket.order.message.fail');
        $arr['emailToAdminSubject']      = $fn->getIssetParam($param, 'emailToAdminSubject', 'm.ecommerce.basket.form.new.email.notifySubject');
        $arr['emailToAdminBody']         = $fn->getIssetParam($param, 'emailToAdminBody', 'm.ecommerce.basket.form.new.email.notifyBody');
        $arr['emailToUserSubject']       = $fn->getIssetParam($param, 'emailToUserSubject', 'm.ecommerce.basket.form.new.email.notifyUserSubject');
        $arr['emailToUserBody']          = $fn->getIssetParam($param, 'emailToUserBody', 'm.ecommerce.basket.form.new.email.notifyUserBody');
        $arr['showBasketInConfirmOrder'] = $fn->getIssetParam($param, 'showBasketInConfirmOrder', true);

        /************** Contact / Guest *************/
        $arr['loginRequired']            = $fn->getIssetParam($param, 'loginRequired', true);
        $arr['createContactRecForGuest'] = $fn->getIssetParam($param, 'createContactRecForGuest', true);
        $arr['generatePasswordForGuest'] = $fn->getIssetParam($param, 'generatePasswordForGuest', false);

        /************** Emails *************/
        $arr['showSKUNoInEmail'] = $fn->getIssetParam($param, 'showSKUNoInEmail', false);


        return $arr;
    }

    /**
     *
     */
    function addSaveContact(){
        $modulesArr = Zend_Registry::get('modulesArr');

        $hook = getCPModuleHook2('ecommerce_basket', 'addSaveContact', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modules = Zend_Registry::get('modules');

        $fa = $this->getFieldsContact();

        $modName = $_SESSION['shippingDetails']['modName'];
        $basketArr = $cpCfg['cp.basketArray'][$modName];
        $contactModName = $basketArr['contactModName'];

        $table = $modules->getValueByKey($contactModName, 'tableName');
        $keyFld = $modules->getValueByKey($contactModName, 'keyField');

        $rec = $fn->getRecordByCondition($table, "email = '{$fa['email']}'");

        $id = '';

        if(isLoggedInWWW() && $fa['email'] == $_SESSION['cpEmail']){
            $id = $fn->saveRecord($fa, $table, $keyFld, $_SESSION['cpContactId']);
        } else if(is_array($rec)){
            $id = $rec[$keyFld];

        } else {
            if ($basketArr['generatePasswordForGuest']){
                $fa['pass_word'] = $cpUtil->getRandomPasswordCS1(8);
                $fa['published'] = 1;
            }

            if ($basketArr['createContactRecForGuest']){
                $id = $fn->addRecord($fa, $table);
                $this->newUserCreated = true;
            }

            if ($this->newUserCreated && $basketArr['generatePasswordForGuest']){
                // send email to user with the password: ref - widget/lawNews/eventRegister //
            }
        }

        return $id;
    }

    /**
     *
     */
    function getFieldsContact(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $s = $_SESSION['shippingDetails'];

        if (isset($s['first_name'])){
            $fa['first_name'] = $s['first_name'];
        }

        if (isset($s['last_name'])){
            $fa['last_name'] = $s['last_name'];
        }

        if (isset($s['address_area'])){
            $fa['address_area'] = $s['address_area'];
        }

        if (isset($s['subscribe'])){
            $fa['subscribe'] = $s['subscribe'];
        }

        $fa['email']                = isset($s['email'])               ?  $s['email']                  : '';
        $fa['address1']             = isset($s['address1'])            ?  $s['address1']               : '';
        $fa['address2']             = isset($s['address2'])            ?  $s['address2']               : '';
        $fa['address_city']         = isset($s['address_city'])        ?  $s['address_city']           : '';
        $fa['address_state']        = isset($s['address_state'])       ?  $s['address_state']          : '';
        $fa['address_country_code'] = isset($s['address_country_code'])?  $s['address_country_code']   : '';
        $fa['address_po_code']      = isset($s['address_po_code'])     ?  $s['address_po_code']        : '';
        $fa['phone']                = isset($s['phone'])               ?  $s['phone']                  : '';

        $hook = getCPModuleHook('ecommerce_basket', 'fieldsContact', $fa, $this);
        if($hook['status']){
            return $hook['html'];
        }

        return $fa;
    }
}