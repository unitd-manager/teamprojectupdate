<?
class CP_Admin_Modules_AceIms_Subject_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSubjectByCourseJSON() {
        return $this->model->getSubjectByCourseJSON();
    }

    function getSubjectList() {
        return $this->view->getSubjectList();
    }

    function getEditSubjectList() {
        return $this->view->getEditSubjectList();
    }

    function getAddSubjectToSession() {
        return $this->model->getAddSubjectToSession();
    }

    function getAddBatchToSession() {
        return $this->model->getAddBatchToSession();
    }

    function getAddSubjectToSessionInEditEnrollment() {
        return $this->model->getAddSubjectToSessionInEditEnrollment();
    }
}