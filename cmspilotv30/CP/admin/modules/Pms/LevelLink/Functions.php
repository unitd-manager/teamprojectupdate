<?
class CP_Admin_Modules_Pms_LevelLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_levelLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'level'
           ,'keyField'  => 'level_id'
        ));
    }
}
