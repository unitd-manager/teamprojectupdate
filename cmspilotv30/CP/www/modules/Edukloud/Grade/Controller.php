<?
class CP_Www_Modules_Edukloud_Grade_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getNewGrade() {
        return $this->view->getNewGrade();
    }

    function getNewGradeSubmit() {
        return $this->model->getNewGradeSubmit();
    }

    function getEditGrade() {
        return $this->view->getEditGrade();
    }

    function getEditGradeSubmit() {
        return $this->model->getEditGradeSubmit();
    }

}
