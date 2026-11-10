<?
class CP_Admin_Modules_Core_StaffLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('core_staffLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff'
           ,'keyField'  => 'staff_id'
        ));
    }
}
