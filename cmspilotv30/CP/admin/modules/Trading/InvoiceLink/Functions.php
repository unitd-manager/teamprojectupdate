<?
class CP_Admin_Modules_Trading_InvoiceLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_invoiceLink');
        $modules->registerModule($modObj);
    }


}