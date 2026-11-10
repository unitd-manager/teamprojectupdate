<?
class CP_Www_Plugins_PaymentMethods_MoneyBookers_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    //==================================================================//
    function getPostBack() {
        return $this->model->getPostBack();
    }
    
    //==================================================================//
    function getProceedToGateway() {
        return $this->model->proceedToGateway();
    }
}