<?
class CPL_Admin_Modules_Common_Dashboard_Controller extends CP_Admin_Modules_Common_Dashboard_Controller
{
    function getMarkAttendanceForm() {
        return $this->view->getMarkAttendanceForm();
    }

    /**
     *
     */
    function getMarkAttendanceFormSubmit() {
        return $this->model->getMarkAttendanceFormSubmit();
    }    
}