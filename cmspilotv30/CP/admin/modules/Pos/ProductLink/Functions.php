<?
class CP_Admin_Modules_Pos_ProductLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_productLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField'  => 'product_id'
           ,'hasFlagInList' => 0
        ));
    }

}
