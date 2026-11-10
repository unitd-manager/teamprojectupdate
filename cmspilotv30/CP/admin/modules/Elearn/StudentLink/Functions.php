<?
class CP_Admin_Modules_Elearn_StudentLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('common_studentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'student'
           ,'keyField'  => 'student_id'
        ));
    }
}
