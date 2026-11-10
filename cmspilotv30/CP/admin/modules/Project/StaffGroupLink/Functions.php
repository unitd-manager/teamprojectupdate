<?
class CP_Admin_Modules_Project_StaffGroupLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_staffGroupLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff_group'
           ,'keyField'  => 'staff_group_id'
        ));
    }
}
