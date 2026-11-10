<?
class CP_Admin_Modules_Project_Timesheet_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getNewRecordFromTask() {
        return $this->view->getNewRecordFromTask();
    }

    /**
     *
     */
    function getAddRecordFromTask() {
        return $this->model->getAddRecordFromTask();
    }

    /**
     *
     */
    function getTimesheetSummaryByMonth() {
        return $this->model->getTimesheetSummaryByMonth();
    }
}