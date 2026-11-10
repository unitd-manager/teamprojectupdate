<?
class CP_Admin_Modules_ManPower_StaffLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('manPower_staffLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff'
           ,'keyField'  => 'staff_id'
        ));
    }
}
