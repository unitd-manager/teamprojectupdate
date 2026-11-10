<?
class CP_Admin_Modules_Edukloud_ParentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_parentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'parent'
           ,'keyField'  => 'parent_id'
        ));
    }
}
