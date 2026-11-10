<?
class CP_Admin_Modules_Pos_Uom_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_uom');
        $modObj['tableName'] = 'uom';
        $modObj['keyField']  = 'uom_id';
        $modules->registerModule($modObj, array(
            'title' => 'UOM'
           ,'actBtnsList' => array('new', 'printListScreen')
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
        ));
    }

}