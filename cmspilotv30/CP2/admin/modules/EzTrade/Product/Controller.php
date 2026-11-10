<?
class CP_Admin_Modules_EzTrade_Product_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getCalculatedValuesRfq() {
        return $this->model->getCalculatedValuesPoItems();
    }

    function getCalculatedValuesQuoteItems() {
        return $this->model->getCalculatedValuesQuoteItems();
    }

    function getCalculatedValuesPoItems() {
        return $this->model->getCalculatedValuesPoItems();
    }

    function getCalculatedValuesSoItems() {
        return $this->model->getCalculatedValuesSoItems();
    }
}