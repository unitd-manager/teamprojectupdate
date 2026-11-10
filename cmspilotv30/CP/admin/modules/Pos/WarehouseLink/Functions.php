<?
class CP_Admin_Modules_Pos_WarehouseLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_warehouseLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'warehouse'
           ,'keyField'  => 'warehouse_id'
           ,'hasFlagInList' => 0
        ));
    }

}
