<?
class CP_Admin_Modules_AgileIms_Subject_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSubjectByCourseJSON() {
        return $this->model->getSubjectByCourseJSON();
    }
}