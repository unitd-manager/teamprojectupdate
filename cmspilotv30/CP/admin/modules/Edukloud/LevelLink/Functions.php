<?
class CP_Admin_Modules_Edukloud_LevelLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_levelLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'level'
           ,'keyField'  => 'level_id'
        ));
    }
}
