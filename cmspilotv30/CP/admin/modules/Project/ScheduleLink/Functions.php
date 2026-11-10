<?
class CP_Admin_Modules_Project_ScheduleLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_scheduleLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'schedule'
           ,'keyField'  => 'schedule_id'
        ));
    }
}
