<?
class CP_Admin_Modules_Pos_ValuelistLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_valuelistLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'valuelist'
           ,'keyField'  => 'valuelist_id'
           ,'hasFlagInList' => 0
        ));
    }

}
