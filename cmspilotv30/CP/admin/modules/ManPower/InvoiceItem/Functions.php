<?
class CP_Admin_Modules_ManPower_InvoiceItem_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_invoiceItem');
        $modObj['tableName'] = 'invoice_items';
        $modObj['keyField']  = 'invoice_items_id';
        $cpCfg = Zend_Registry::get('cpCfg');
        $modules->registerModule($modObj, array(
            'title'         => 'Invoice Items'
        ));
    }
}
