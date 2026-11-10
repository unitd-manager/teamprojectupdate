<?
class CP_Admin_Modules_EnterpriseIms_BatchTransfer_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getStudentSearchResult() {
        return $this->view->getStudentSearchResult();
    }

    /**
     *
    */
    function getSelectedStudentListRow() {
        return $this->view->getSelectedStudentListRow();
    }

    /**
     *
    */
    function getRemoveTrainee() {
        return $this->view->getRemoveTrainee();
    }

    /**
     *
    */
    function getAllSelectedStudentListRow() {
        return $this->view->getAllSelectedStudentListRow();
    }
    
    /**
     *
    */
    function getBatchTransferStudentSubmit() {
        return $this->model->getBatchTransferStudentSubmit();
    }

    /**
     *
    */
    function getRemoveAllTrainee() {
        return $this->view->getRemoveAllTrainee();
    }
}