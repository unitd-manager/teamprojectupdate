<?
class CP_Admin_Modules_Project_Quote_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getNewFromTemplate() {
        return $this->view->getNewFromTemplate();
    }

    /**
     *
     */
    function getAddFromTemplate() {
        return $this->model->getAddFromTemplate();
    }

    /**
     *
     */
    function getConfirmedQuoteValue() {
        return $this->model->getConfirmedQuoteValue();
    }

    /**
     *
     */
    function getQuotesPortal($recId ='', $recType='') {
        return $this->view->getQuotesPortal($recId, $recType);
    }
}