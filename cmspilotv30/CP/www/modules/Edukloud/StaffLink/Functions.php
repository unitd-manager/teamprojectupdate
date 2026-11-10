<?
class CP_Www_Modules_Edukloud_StaffLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_staffLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'staff'
           ,'keyField'      => 'staff_id'
        ));
    }
}
