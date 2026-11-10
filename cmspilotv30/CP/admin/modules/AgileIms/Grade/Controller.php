<?
class CP_Admin_Modules_AgileIms_Grade_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getStudentGrade() {
        return $this->view->getStudentGrade();
    }

    /**
     *
     */
    function getStudentGradeSubmit() {
        return $this->model->getStudentGradeSubmit();
    }

    /**
     *
     */
    function getEditStudentGrade() {
        return $this->view->getEditStudentGrade();
    }

    /**
     *
     */
    function getEditStudentGradeSubmit() {
        return $this->model->getEditStudentGradeSubmit();
    }
}