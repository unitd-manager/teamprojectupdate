<?
class CP_Admin_Modules_AgileIms_Teacher_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getTeacherBySubjectJSON() {
        return $this->model->getTeacherBySubjectJSON();
    }
}