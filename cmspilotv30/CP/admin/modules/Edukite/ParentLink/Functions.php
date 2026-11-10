<?
class CP_Admin_Modules_Edukite_ParentLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('edukite_parentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'parent'
           ,'keyField'  => 'parent_id'
        ));
    }
}
