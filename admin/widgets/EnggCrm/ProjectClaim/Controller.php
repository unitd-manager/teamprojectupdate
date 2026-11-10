<?
class CPL_Admin_Widgets_EnggCrm_ProjectClaim_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    function getAddClaimFormSubmit() {
        return $this->model->getAddClaimFormSubmit();
    }

    function getAddClaimPortalListView() {
        return $this->view->getAddClaimPortalListView();
    }

    function getEditForClaim() {
        return $this->view->getEditForClaim();
    }

    function getEditForClaimSubmit() {
        return $this->model->getEditForClaimSubmit();
    }

    function getAddMultipleClaimItem() {
        return $this->view->getAddMultipleClaimItem();
    }

    function getAddSingleClaimRow() {
        return $this->view->getAddSingleClaimRow();
    }

    function getAddMultipleClaimItemFormSubmit() {
        return $this->model->getAddMultipleClaimItemFormSubmit();
    }

    function getEditClaimLineItem() {
        return $this->view->getEditClaimLineItem();
    }

    function getEditMultipleClaimItemFormSubmit() {
        return $this->model->getEditMultipleClaimItemFormSubmit();
    }

    function getAddSingleClaimEditRow() {
        return $this->view->getAddSingleClaimEditRow();
    }

    function getAddClaimPaymentLineItem() {
        return $this->view->getAddClaimPaymentLineItem();
    }

    function getAddMultipleClaimPaymentItemFormSubmit() {
        return $this->model->getAddMultipleClaimPaymentItemFormSubmit();
    }

    function getEditClaimPaymentLineItem() {
        return $this->view->getEditClaimPaymentLineItem();
    }

    function getEditMultipleClaimPaymentItemFormSubmit() {
        return $this->model->getEditMultipleClaimPaymentItemFormSubmit();
    }

    function getPrintClaimPdf() {
        return $this->view->getPrintClaimPdf();
    }

    function getPrintClaimSummaryPdf() {
        return $this->view->getPrintClaimSummaryPdf();
    }
}