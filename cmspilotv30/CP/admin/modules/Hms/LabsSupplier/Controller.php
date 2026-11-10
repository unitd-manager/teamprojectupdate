<?
class CP_Admin_Modules_Hms_LabsSupplier_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	function getAddCategoryLinkPortal() {
        return $this->view->getAddCategoryLinkPortal();
    }

    function getAddCategory() {
        return $this->view->getAddCategory();
    }

    function getAddRemoveCategoryAll() {
        return $this->model->getAddRemoveCategoryAll();
    }

    function getAddRemoveSupplierCategoryLink() {
        return $this->model->getAddRemoveSupplierCategoryLink();
    }

}