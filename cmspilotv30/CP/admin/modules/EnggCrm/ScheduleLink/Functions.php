<?
class CP_Admin_Modules_EnggCrm_ScheduleLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_scheduleLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'schedule'
           ,'keyField'  => 'schedule_id'
        ));
    }
}
