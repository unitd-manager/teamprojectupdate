<?
class CP_Admin_Modules_Pos_SeasonLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_seasonLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'valuelist'
           ,'keyField'  => 'valuelist_id'
           ,'hasFlagInList' => 0
           ,'titleField' => 'value'
        ));
    }

}
