<?
class CP_Admin_Modules_Pos_Mwo_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_mwo');
        $modObj['tableName'] = 'mwo';
        $modObj['keyField']  = 'mwo_id';
        $modules->registerModule($modObj, array(
            'title' => 'MWO'
           ,'hasFlagInList' => 0
        ));
    }
}