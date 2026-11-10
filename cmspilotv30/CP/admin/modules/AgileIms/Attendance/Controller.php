<?
class CP_Admin_Modules_AgileIms_Attendance_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getTakeAttendance() {
        return $this->view->getTakeAttendance();
    }

    /**
     *
     */
    function getTakeAttendanceSubmit() {
        return $this->model->getTakeAttendanceSubmit();
    }

    /**
     *
     */
    function getEditAttendance() {
        return $this->view->getEditAttendance();
    }

    /**
     *
     */
    function getEditAttendanceSubmit() {
        return $this->model->getEditAttendanceSubmit();
    }

}