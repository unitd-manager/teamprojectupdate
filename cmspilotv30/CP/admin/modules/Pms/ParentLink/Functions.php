<?
class CP_Admin_Modules_Pms_ParentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_parentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'parent'
           ,'keyField'  => 'parent_id'
        ));
    }
}
