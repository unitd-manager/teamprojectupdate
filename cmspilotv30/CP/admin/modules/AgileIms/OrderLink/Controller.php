<?
class CP_Admin_Modules_AgileIms_OrderLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getCourseRow(){
        $fn = Zend_Registry::get('fn');
        $company_id = $fn->getReqParam('company_id');

        return $this->view->getCourseRow($company_id);
    }

    /**
    */
    function getCourseTraineeSearch(){
        return $this->view->getCourseTraineeSearch();
    }

    /**
    */
    function getTraineeSearchResult(){
        return $this->view->getTraineeSearchResult();
    }

    /**
    */
    function getSelectedStudentListRow(){
        return $this->view->getSelectedStudentListRow();
    }

    /**
    */
    function getRemoveTrainee(){
        return $this->model->getRemoveTrainee();
    }

    /**
    */
    function getSelectedTraineeResultRow(){
        return $this->view->getSelectedTraineeResultRow();
    }

    /**
    */
    function getCheckInvoiceForContactInCompanyEditEnrollment(){
        return $this->model->getCheckInvoiceForContactInCompanyEditEnrollment();
    }
}