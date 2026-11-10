<?
class CP_Admin_Modules_Tradingsg_QuoteLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_quoteLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'enquiry_quote'
           ,'keyField'  => 'enquiry_quote_id'
        ));
    }
}
