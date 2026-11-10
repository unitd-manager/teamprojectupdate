<?
class CP_Admin_Modules_Elearn_SchoolLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_schoolLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'school'
           ,'keyField'  => 'school_id'
        ));
    }
}
