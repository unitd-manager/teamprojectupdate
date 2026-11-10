<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaterialsUsed_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
	/**
    */
    function getProjectMaterialPortal() {
        return $this->view->getProjectMaterialPortal();
    }

	/**
    */
    function getAddMultipleMaterials(){
        return $this->view->getAddMultipleMaterials();
    }

    /**
    */
    function getAddMaterialRecord(){
        return $this->view->getAddMaterialRecord();
    }

    /**
    */
    function getAddMultipleMaterialsSubmit(){
        return $this->model->getAddMultipleMaterialsSubmit();
    }

	/**
    */
    function getCancelMaterial(){
        return $this->model->getCancelMaterial();
    }

    /**
    */
    function getCreationModificationMU() {
        return $this->model->getCreationModificationMU();
    }

    /**
    */
    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    /**
    */
    function getprintmaterialLinkForPdf(){
        return $this->view->getprintmaterialLinkForPdf();
    }

    /**
    */
    function getEditForMaterialUsed() {
        return $this->view->getEditForMaterialUsed();
    }
    
    /**
    */
    function getEditForMaterialUsedSubmit() {
        return $this->model->getEditForMaterialUsedSubmit();
    }

    /**
    */
    function getReturnMaterialUsed() {
        return $this->view->getReturnMaterialUsed();
    }
    
    /**
    */
    function getReturnMaterialUsedSubmit() {
        return $this->model->getReturnMaterialUsedSubmit();
    }

    /**
    */
    function getReturnedMaterialHistory() {
        return $this->view->getReturnedMaterialHistory();
    }

    /**
    */
    function getUpdateVirescoFactory() {
        return $this->model->getUpdateVirescoFactory();
    }
}