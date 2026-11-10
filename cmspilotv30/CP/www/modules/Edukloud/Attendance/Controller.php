<?
class CP_Www_Modules_Edukloud_Attendance_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getTakeAttendance() {
        return $this->view->getTakeAttendance();
    }

    function getTakeAttendanceSubmit() {
        return $this->model->getTakeAttendanceSubmit();
    }

}
