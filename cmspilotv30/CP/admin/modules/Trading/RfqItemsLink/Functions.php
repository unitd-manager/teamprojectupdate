<?
class CP_Admin_Modules_Trading_RfqItemsLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_rfqItemsLink');
        $modObj['tableName'] = 'rfq_items';
        $modObj['keyField']  = 'rfq_items_id';
        $modules->registerModule($modObj, array(
             'title' => 'RFQ Items'
        ));
    }
}
