<?
class CP_Www_Plugins_PaymentMethods_Stripe_Model extends CP_Common_Lib_PluginModelAbstract
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
        $arr['title']        = $ln->gd('p.paymentMethods.lbl.stripe');
        $arr['siteUrl']      = $cpCfg['cp.siteUrl'];
        $arr['siteName']     = $cpCfg['cp.companyName'];
        $arr['logoUrl']      = '';
        $arr['successUrl']   = "{$siteUrl}{$successUrl}";
        $arr['cancelUrl']    = "{$siteUrl}{$cancelUrl}";
        $arr['currencyCode'] = $fn->getIssetParam($cpCfg, 'paypalCurrency', 'USD');

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function getStripeFormSubmit() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl  = Zend_Registry::get('cpUrl');

        require 'vendor/autoload.php';

        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/apikeys
        \Stripe\Stripe::setApiKey($cpCfg['cp.stripePaymentSecretKey']);

        $basketArray = $cpCfg['cp.basketArray']['ecommerce_product'];
        $successUrl  = $cpUrl->getUrlByCatType('Order Success', $basketArray['basketSecType']);
        $cancelUrl   = $cpUrl->getUrlByCatType('Order Cancel', $basketArray['basketSecType']);
        $siteUrl     = $cpCfg['cp.siteUrl'];

        if (substr($siteUrl, -1, 1) == '/'){
            $siteUrl = substr($siteUrl, 0, strlen($siteUrl)-1);
        }

        // Token is created using Stripe Checkout or Elements!
        // Get the payment token ID submitted by the form:
        $token      = $_POST['stripeToken'];
        $contact_id = $_POST['contact_id'];
        $order_id   = $_POST['order_id'];

        if ($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }
        
        $total = getCPModuleObj('ecommerce_basket')->model->getOrderTotal($order_id);

        $totalInvoiceAmount = $total;
        $totalInvoiceAmount = $totalInvoiceAmount * 100;

       $SQLContact = "
        SELECT c.*
              ,CONCAT_WS('', c.first_name, c.last_name) AS contact_name
        FROM `visitors` c
        WHERE visitors_id = {$contact_id}
        ";
        $resultContact = $db->sql_query($SQLContact);
        $rowContact    = $db->sql_fetchrow($resultContact);

         /*$customer = \Stripe\Customer::create([
            'email'       => $rowContact['email'],
            'description' => $rowContact['contact_name'],
            'source'      => $token,
        ]);*/

        $SQLOrderItem = "
        SELECT item_title
        FROM order_item
        WHERE order_id = '{$order_id}'
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $productName = "";
        while($rowOrderItem = $db->sql_fetchrow($resultOrderItem)) {
            $productName .= $rowOrderItem['item_title'].", ";
        }

        $productName = rtrim($productName, ', ');

        $charge = \Stripe\Charge::create([
          'amount'      => $totalInvoiceAmount,
          'currency'    => $fn->getIssetParam($cpCfg, 'paypalCurrency', 'INR'),
          'description' => $rowContact['contact_name']."-".$productName,
          'source'      => $token,
        ]);

        if($charge) {
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
            $fa['modification_date'] = date("Y-m-d H:i:s");

            $condn  = "WHERE order_id = {$order_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'order', $condn);
            $result = $db->sql_query($SQL);

            $basketObj = getCPModelObj('ecommerce_basket');
            $basketObj->sendOrderConfirmationEmails($order_id);
            $siteUrl   = $cpCfg['cp.siteUrl'];

            if (substr($siteUrl, -1, 1) == '/'){
                $siteUrl = substr($siteUrl, 0, strlen($siteUrl)-1);
            }

            $successUrl = "{$siteUrl}{$successUrl}?orderId={$order_id}";
            $cpUtil->redirect($successUrl);
        } else {
            header("Location:".$cancelUrl);
        }
    }
}