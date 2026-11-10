<?
class CP_Common_Modules_Ek_Teacher_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_teacher');
        $modules->registerModule($modObj, array(
            'tableName'   => 'staff'
           ,'keyField'    => 'staff_id'
        ));
    }
}