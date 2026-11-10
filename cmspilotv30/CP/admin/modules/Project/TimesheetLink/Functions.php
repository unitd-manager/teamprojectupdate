<?
class CP_Admin_Modules_Project_TimesheetLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_timesheetLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'timesheet'
           ,'keyField'  => 'timesheet_id'
        ));
    }
}
