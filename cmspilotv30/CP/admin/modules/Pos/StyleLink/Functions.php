<?
class CP_Admin_Modules_Pos_StyleLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_styleLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'valuelist'
           ,'keyField'  => 'valuelist_id'
           ,'hasFlagInList' => 0
           ,'titleField' => 'value'
        ));
    }
}
