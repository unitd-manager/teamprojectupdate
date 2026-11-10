
<?
class CPL_Admin_Widgets_Project_TaskAllocation_Controller extends CP_Admin_Widgets_Project_TaskAllocation_Controller
{
    /**
     *
     */
    function getTasksUpdateByAllStaff() {
        return $this->view->getTasksUpdateByAllStaff();
    }    

    /**
     *
     */
    function getTasksUpdateByStaff() {
        return $this->view->getTasksUpdateByStaff();
    }    

    /**
     *
     */
    function getAddNewTask() {
        return $this->view->getAddNewTask();
    }    

    /**
     *
     */
    function getAddTaskFormSubmit() {
        return $this->model->getAddTaskFormSubmit();
    }    

    /**
     *
     */
    function getTaskMail() {
        return $this->view->getTaskMail();
    }    

    /**
     *
     */
    function getTaskMailSubmit() {
        return $this->model->getTaskMailSubmit();
    }    

    /**
     *
     */
    function getUpdateTaskHistoryStaffIdByStaff() {
        return $this->model->getUpdateTaskHistoryStaffIdByStaff();
    }    

    /**
     *
     */
    function getUpdateTaskHistoryPriorityByStaff() {
        return $this->model->getUpdateTaskHistoryPriorityByStaff();
    }    

    /**
     *
     */
    function getUpdateTaskHistoryProgressPercentByStaff() {
        return $this->model->getUpdateTaskHistoryProgressPercentByStaff();
    }    

    /**
     *
     */
    function getUpdateTaskHistoryStatusByStaff() {
        return $this->model->getUpdateTaskHistoryStatusByStaff();
    }    

    /**
     *
     */
    function getUpdateTaskHistoryEstimatedHoursByStaff() {
        return $this->model->getUpdateTaskHistoryEstimatedHoursByStaff();
    }    

    /**
     *
     */
    function getTimeSheetDetails() {
        return $this->view->getTimeSheetDetails();
    }    

    /**
     *
     */
    function getTimeSheetEdit() {
        return $this->view->getTimeSheetEdit();
    }    

    /**
     *
     */
    function getTimeSheetEditSubmit() {
        return $this->model->getTimeSheetEditSubmit();
    }    

    /**
     *
     */
    function getSendEmail() {
        return $this->view->getSendEmail();
    }    

    /**
     *
     */
    function getSendEmailSubmit() {
        return $this->model->getSendEmailSubmit();
    }    

    /**
     *
     */
    function getUpdateTaskHistoryNowByStaff() {
        return $this->model->getUpdateTaskHistoryNowByStaff();
    }    

    /**
     *
     */
    function getNewRecordFromTask() {
        $modObj = getCPModuleObj('project_timesheet');
        return $modObj->view->getNewRecordFromTask();
    }    
}