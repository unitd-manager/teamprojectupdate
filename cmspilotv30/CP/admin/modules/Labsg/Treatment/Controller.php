<?
class CP_Admin_Modules_Labsg_Treatment_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getMedicineTemplate() {
        return $this->view->getMedicineTemplate();
    }

    function getMedicineTemplateFormSubmit() {
        return $this->model->getMedicineTemplateFormSubmit();
    }

    function getMedicineTemplateValidate() {
        return $this->model->getMedicineTemplateValidate();
    }

    function getEditMedicineTemplate() {
        return $this->view->getEditMedicineTemplate();
    }

    function getEditMedicineTemplateFormSubmit() {
        return $this->model->getEditMedicineTemplateFormSubmit();
    }

    function getDeleteMedicineTemplate() {
        return $this->model->getDeleteMedicineTemplate();
    }

}