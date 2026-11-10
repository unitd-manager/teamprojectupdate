<?
class CP_Admin_Modules_Pos_StaffLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('pos_staffLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff'
           ,'keyField'  => 'staff_id'
        ));
    }
}
