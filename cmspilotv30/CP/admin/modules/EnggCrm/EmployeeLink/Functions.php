<?
class CP_Admin_Modules_EnggCrm_EmployeeLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('enggCrm_employeeLink');
        $modObj['tableName'] = 'employee';
        $modObj['keyField']  = 'employee_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));        
    }
}
