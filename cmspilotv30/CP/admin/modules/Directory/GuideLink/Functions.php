<?
class CP_Admin_Modules_Directory_GuideLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('directory_guideLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'guide'
           ,'keyField'  => 'guide_id'
        ));
    }
}
