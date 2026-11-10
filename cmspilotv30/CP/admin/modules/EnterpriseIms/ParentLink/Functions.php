<?
class CP_Admin_Modules_EnterpriseIms_ParentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_parentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'parent'
           ,'keyField'  => 'parent_id'
        ));
    }
}
