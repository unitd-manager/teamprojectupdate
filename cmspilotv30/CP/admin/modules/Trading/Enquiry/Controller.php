<?
class CP_Admin_Modules_Trading_Enquiry_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function getChooseRFQForLine() {
        return $this->model->getChooseRFQForLine();
    }

    function getRaiseRfqList() {
        return $this->view->getRaiseRfqList();
    }

    function getRaiseRfqListValidation() {
        return $this->model->getRaiseRfqListValidation();
    }

    function getRaiseRfqValidation() {
        return $this->model->getRaiseRfqListValidation();
    }

    function getRaiseRfq() {
        return $this->model->getRaiseRfq();
    }

    function getChooseRFQFormForLine() {
        return $this->model->getChooseRFQFormForLine();
    }

    function getDuplicateLine() {
        return $this->model->getDuplicateLine();
    }

    function getChooseConfirmedRFQForLine() {
        return $this->model->getChooseConfirmedRFQForLine();
    }

    function getRaiseQuoteList() {
        return $this->view->getRaiseQuoteList();
    }

    function getRaiseQuoteListValidation() {
        return $this->model->getRaiseQuoteListValidation();
    }

    function getRaiseQuoteValidation() {
        return $this->model->getRaiseQuoteListValidation();
    }

    function getRaiseQuote() {
        return $this->model->getRaiseQuote();
    }

}