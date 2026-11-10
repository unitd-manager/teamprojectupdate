<?
class CP_Admin_Modules_EnggCrm_TimesheetLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_timesheetLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'timesheet'
           ,'keyField'  => 'timesheet_id'
        ));
    }
}
