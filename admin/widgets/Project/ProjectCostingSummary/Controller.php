<?
class CPL_Admin_Widgets_Project_ProjectCostingSummary_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $searcVarCondn = '';

    function getRowsHTML(){
        return $this->view->getRowsHTML();
    }

    function getCreateCostingSummary(){
        return $this->view->getCreateCostingSummary();
    }

    function getEditCostingSummary(){
        return $this->view->getEditCostingSummary();
    }

    function getAddLineItemRecord(){
        return $this->view->getAddLineItemRecord();
    }

    function getCostingSummarySubmit(){
        return $this->model->getCostingSummarySubmit();
    }

    function getEditCostingSummarySubmit(){
        return $this->model->getEditCostingSummarySubmit();
    }

    function getSearchSubCon(){
        return $this->model->getSearchSubCon();
    }

    function getAddActualCharges(){
        return $this->view->getAddActualCharges();
    }

    function getActualChargesSubmit(){
        return $this->model->getActualChargesSubmit();
    }

    function getAddNewSupplier(){
        return $this->view->getAddNewSupplier();
    }

    function getAddNewSupplierSubmit(){
        return $this->model->getAddNewSupplierSubmit();
    }

    function getAddNewProductMaster(){
        return $this->view->getAddNewProductMaster();
    }

    function getAddNewProductMasterSubmit(){
        return $this->model->getAddNewProductMasterSubmit();
    }

    function getSupplierByJSON(){
        return $this->model->getSupplierByJSON();
    }

    function getSearchProductTitle(){
        return $this->model->getSearchProductTitle();
    }
}