<?
class CP_Admin_Modules_Pos_ShopLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_shopLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'shop'
           ,'keyField'  => 'shop_id'
           ,'hasFlagInList' => 0
        ));
    }

}
