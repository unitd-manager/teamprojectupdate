<?
class CP_Www_Plugins_PaymentMethods_MoneyBookers_Model extends CP_Common_Lib_PluginModelAbstract
{

    /**
     *
     */
    function getDataArray($order_id = ''){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $basketArray = $cpCfg['cp.basketArray']['ecommerce_product'];
        $successUrl  = $cpUrl->getUrlByCatType('Order Success', $basketArray['basketSecType']);
        $cancelUrl   = $cpUrl->getUrlByCatType('Order Cancel', $basketArray['basketSecType']);
        $siteUrl     = $cpCfg['cp.siteUrl'];

        if (substr($siteUrl, -1, 1) == '/'){
            $siteUrl = substr($siteUrl, 0, strlen($siteUrl)-1);
        }

        $arr = array();
        $arr['title']        = $ln->gd('p.paymentMethods.lbl.paypal');
        $arr['siteUrl']      = $cpCfg['cp.siteUrl'];
        $arr['siteName']     = $cpCfg['cp.companyName'];
        $arr['logoUrl']      = '';
        $arr['successUrl']   = "{$siteUrl}{$successUrl}";
        $arr['cancelUrl']    = "{$siteUrl}{$cancelUrl}";
        $arr['postBackUrl']  = "{$siteUrl}/index.php?plugin=PaymentMethods_MoneyBookers&_spAction=postBack&showHTML=0";
        $arr['currencyCode'] = $fn->getIssetParam($cpCfg, 'paypalCurrency', 'USD');

        if($cpCfg[CP_ENV]['paymentGatewayMode'] == 'test'){
            $arr['gatewayUrl'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            $arr['accountId']  = 'backer@pilot.com.hk';
        } else {
            $paymentGatewayByCountry = $basketArray['paymentGatewayByCountry'];

            $arr['gatewayUrl'] = 'https://www.paypal.com/cgi-bin/webscr';
            $arr['accountId']  = $cpCfg['paypalAccountId'];

            if ($paymentGatewayByCountry && $order_id != ''){
                $rec = $fn->getRecordRowByID('order', 'order_id', $order_id);
                if (!is_array($rec)) {
                    exit();
                }

                $country_code = $rec['shipping_address_country_code'];

                if ($country_code != ''){
                    $countryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$country_code}'");

                    if (is_array($countryRec) && $countryRec['stockist_id'] > 0) {
                        $stockistRec = $fn->getRecordRowByID('stockist', 'stockist_id', $countryRec['stockist_id']);

                        if (is_array($stockistRec) && $stockistRec['paypal_email'] != '') {
                            $arr['accountId']  = $stockistRec['paypal_email'];
                        }
                    }
                }
            }
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function proceedToGateway($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }
        $total = getCPModuleObj('ecommerce_basket')->model->getOrderTotal($order_id);

        $_SESSION['cpOrderId'] = $order_id;

        $arr = $this->getDataArray($order_id);

        $item_name = str_replace("'", "\\'", $cpCfg['cp.companyName']);
        if ($cpCfg['p.paymentMethods.paypal.paymentItemName'] != '') {
            $item_name = str_replace("'", "\\'", $cpCfg['p.paymentMethods.paypal.paymentItemName']);
        }
        
        $text = "
        <form action='{$arr['gatewayUrl']}' method='post' name='frmPaypal' id='frmPaypal'>
           <input type='hidden' name='business'      value='{$arr['accountId']}'>
           <input type='hidden' name='amount'        value='{$total}'>
           <input type='hidden' name='invoice'       value='{$cpCfg['cp.orderInvoiceNoPrefix']}{$order_id}'>
           <input type='hidden' name='item_name'     value='{$item_name}'>
           <input type='hidden' name='cmd'           value='_xclick'>
           <input type='hidden' name='return'        value='{$arr['successUrl']}'>
           <input type='hidden' name='cancel_return' value='{$arr['cancelUrl']}'>
           <input type='hidden' name='notify_url'    value='{$arr['postBackUrl']}'>
           <input type='hidden' name='rm'            value='2'>
           <input type='hidden' name='currency_code' value='{$arr['currencyCode']}'>
           <input type='hidden' name='lc'            value='US'>
           <input type='hidden' name='bn'            value='toolkit-php'>
           <input type='hidden' name='no_shipping'   value='1'>
           <input type='hidden' name='custom'        value='{$order_id}'>
        </form>

        <div class='proceedToGateway'>
            <h2>Processing to the payment gateway.. please wait</h2>
        </div>

        <script type='text/javascript'>
           window.onload=function() {
        	  window.document.frmPaypal.submit();
           }
        </script>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            Util.showProgressInd();
        "));

        return $text;
    }

    /**
     *
     */
    function getPostBack(){
        set_time_limit(120);
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $arr = $this->getDataArray();

        $paypalResult = $cpUtil->fsockPost($arr['gatewayUrl'], $_POST);

        //$client = new Zend_Http_Client();
        //$client->setUri($paypalURL);
        //
        //foreach ($_POST as $i => $v) {
        //   $client->setParameterPost($i, $v);
        //}
        //
        //$client->setParameterPost('cmd', '_notify-validate');
        //$response = $client->request(Zend_Http_Client::POST);
        //fwrite($handle, $response->getHeadersAsString());
        //fwrite($handle, $response->getHeadersAsString());
        //
        //$postData = '';
        //foreach ($_POST as $i => $v) {
        //   $postData.= $i . '=' . urlencode($v) . '&';
        //}
        //fwrite($handle, "postData: " . $postData . "\n\n");
        //
        //fwrite($handle, print_r ($_POST, true));

        //$handle = fopen("media/temp/paypal.txt", "w");
        //fwrite($handle, $paypalResult);
        //$paypalResult = 'VERIFIED';

        if (preg_match('/VERIFIED/i', $paypalResult)) {
            $order_id = $_POST['custom'];
            $memo = $_POST['memo'];

            //$order_id = 15;
            //$memo = '';

            $rec = $fn->getRecordRowByID('order', 'order_id', $order_id);

            if (!is_array($rec)) {
                exit();
            }

            if($rec['order_status'] != 'New'){
                exit();
            }

            //-------------------------------------------------------------------//
            $fa = array();
            $fa['order_status']      = 'Paid';
            $fa['memo']              = $memo;
            $fa['modification_date'] = date("Y-m-d H:i:s");

            $condn  = "WHERE order_id = {$order_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'order', $condn);
            $result = $db->sql_query($SQL);

            $this->getUpdateStock($order_id);

            $basketObj = getCPModelObj('ecommerce_basket');

            $funcName = "getCallbackAfterPaymentGatewayPostback";
            // write a function with the above name in modules/ecommerce/basket/functions.php to perform necessary actions
            $fnMod = getCPModuleObj('ecommerce_basket')->fns;
            if (method_exists($fnMod, $funcName)) {
                $fnMod->$funcName($order_id);
            }

            $basketObj->sendOrderConfirmationEmails($order_id);
            exit();
        }
    }

    /**
     *
     */
    function getUpdateStock($order_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $arr = $this->getDataArray();

        $rec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        if (!is_array($rec)) {
            exit();
        }

        $basketArr = $cpCfg['cp.basketArray'][$rec['module']];
        $hasItemsInChildTable = $basketArr['hasItemsInChildTable'];
        $updateStockAfterPayment = $basketArr['updateStockAfterPayment'];

        if (!$updateStockAfterPayment){
            return;
        }

        if ($rec['module'] == 'ecommerce_product'){
            $itemsArr = $fn->getArrBySQL("
                SELECT *
                FROM order_item
                "
                ,array(
                     "order_id = '{$rec['order_id']}'"
                )
            );

            foreach($itemsArr AS $row){
                if ($hasItemsInChildTable){
                    $updateSQL = $fn->getSQL("
                        UPDATE product_item
                        SET stock = (stock - {$row['qty']})
                        "
                        ,array(
                             "product_item_id = '{$row['child_id']}'"
                        )
                    );
                    $result = $db->sql_query($updateSQL);
                }
            }
        }
    }
}