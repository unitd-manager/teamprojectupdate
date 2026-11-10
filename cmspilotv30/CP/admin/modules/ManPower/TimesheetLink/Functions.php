<?
class CP_Admin_Modules_ManPower_TimesheetLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_timesheetLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'timesheet'
           ,'keyField'  => 'timesheet_id'
        ));
    }
}
