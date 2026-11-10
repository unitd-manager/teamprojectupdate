<?
class CP_Admin_Modules_Account_CurrencyConvert_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_currencyConvert');
        $modObj['tableName'] = 'currency_convert';
        $modObj['keyField']  = 'currency_convert_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Base $ Conversion'
           ,'actBtnsList' => array('new', 'export', 'import')
           ,'hasCheckboxInList' => false
        ));
    }
}