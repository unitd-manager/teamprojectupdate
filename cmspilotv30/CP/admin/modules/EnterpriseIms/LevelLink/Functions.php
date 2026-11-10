<?
class CP_Admin_Modules_EnterpriseIms_LevelLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_levelLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'level'
           ,'keyField'  => 'level_id'
        ));
    }
}
