<?
class CP_Admin_Modules_Pos_CurrencyConvert_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_currencyConvert');
        $modObj['tableName'] = 'currency_convert';
        $modObj['keyField']  = 'currency_convert_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Currency Base Conversion'
           ,'actBtnsList' => array('new', 'export', 'import')
        ));
    }
}