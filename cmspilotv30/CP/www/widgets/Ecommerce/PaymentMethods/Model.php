<?
class CP_Www_Widgets_Ecommerce_PaymentMethods_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getDataArray() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        return $cpCfg['cp.basketArray'][$c->modName]['paymentMethods'];
    }
}