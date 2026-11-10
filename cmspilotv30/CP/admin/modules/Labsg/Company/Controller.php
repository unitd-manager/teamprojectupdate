<?
class CP_Admin_Modules_Labsg_Company_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getTreatment() {
        return $this->view->getTreatment();
    }

    function getTreatmentFormSubmit() {
        return $this->model->getTreatmentFormSubmit();
    }

    function getTreatmentValidate() {
        return $this->model->getTreatmentValidate();
    }

    function getEditTreatment() {
        return $this->view->getEditTreatment();
    }

    function getEditTreatmentFormSubmit() {
        return $this->model->getEditTreatmentFormSubmit();
    }

    function getDeleteTreatment() {
        return $this->model->getDeleteTreatment();
    }

    function getAddTreatment() {
        return $this->view->getAddTreatment();
    }

}