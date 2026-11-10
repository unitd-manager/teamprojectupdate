<?
class CP_Www_Plugins_PaymentMethods_Alipay_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    //==================================================================//
    function getNotify() {
        return $this->model->getNotify();
    }
    
    //==================================================================//
    function getProceedToGateway() {
        return $this->model->proceedToGateway();
    }
}