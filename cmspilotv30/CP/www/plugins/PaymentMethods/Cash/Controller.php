<?
class CP_Www_Plugins_PaymentMethods_Cash_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    var $modName = 'ecommerce_product';
    
    //==================================================================//
    function getProceedToGateway() {
        return $this->model->proceedToGateway();
    }
}