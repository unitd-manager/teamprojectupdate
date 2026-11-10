<?
class CP_Www_Plugins_PaymentMethods_Cash_Model extends CP_Common_Lib_PluginModelAbstract
{

    /**
     *
     */
    function getDataArray(){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        
        $c = &$this->controller;
        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $successUrl  = $cpUrl->getUrlByCatType('Order Success', $basketArray['basketSecType']);
        $cancelUrl   = $cpUrl->getUrlByCatType('Order Cancel', $basketArray['basketSecType']);
        $siteUrl     = substr($cpCfg['cp.siteUrl'], 0, strlen($cpCfg['cp.siteUrl'])-1);
        
        $arr = array();
        $arr['title']        = $ln->gd('p.paymentMethods.lbl.cash');
        $arr['siteUrl']      = $cpCfg['cp.siteUrl'];
        $arr['siteName']     = $cpCfg['cp.companyName']; 
        $arr['logoUrl']      = '';
        $arr['successUrl']   = "{$siteUrl}{$successUrl}";
        $arr['cancelUrl']    = "{$siteUrl}{$cancelUrl}";
        
        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function proceedToGateway($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        if ($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }
        $_SESSION['cpOrderId'] = $order_id;

        $basketObj = getCPModelObj('ecommerce_basket');
        $basketObj->sendOrderConfirmationEmails($order_id);
        $arr = $this->getDataArray();
        $successUrl = $arr['successUrl'];
        $cpUtil->redirect($successUrl);
    }
}