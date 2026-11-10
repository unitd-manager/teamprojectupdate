<?
class CP_Admin_Modules_Ecommerce_StockInLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_stockInLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'stock_in'
           ,'keyField'  => 'stock_in_id'
        ));
    }
}
