<?
class CP_Admin_Modules_Pos_ColorLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_colorLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'valuelist'
           ,'keyField'  => 'valuelist_id'
           ,'hasFlagInList' => 0
           ,'titleField' => 'value'
        ));
    }
}
