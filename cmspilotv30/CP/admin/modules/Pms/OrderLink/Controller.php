<?
class CP_Admin_Modules_Pms_OrderLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
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
    function getSelectedTraineeResult(){
        return $this->view->getSelectedTraineeResult();
    }

    /**
    */
    function getSelectedTraineeResultRow(){
        return $this->view->getSelectedTraineeResultRow();
    }

    /**
    */
    function getRemoveTrainee(){
        return $this->model->getRemoveTrainee();
    }

    /**
    */
    function getContactDetails(){
        return $this->view->getContactDetails();
    }

    /**
    */
    function getContactEdit(){
        return $this->view->getContactEdit();
    }

    /**
    */
    function getContactSave(){
        return $this->model->getContactSave();
    }    
    
    /**
    */
    function getAddCompanyCourseLink(){
        return $this->model->getAddCompanyCourseLink();
    }    
    
    /**
    */
    function getContactNew(){
        return $this->view->getContactNew();
    }

    /**
    */
    function getContactAddSubmit(){
        return $this->model->getContactAddSubmit();
    }
       
    /**
    */
    function getBulkParentStudentEnrollment(){
        return $this->view->getBulkParentStudentEnrollment();
    }

    /**
    */ 
    function getSelectedStudentList(){
        return $this->view->getSelectedStudentList();
    }
    /**
    */
    function getSelectedStudentListRow(){
        return $this->view->getSelectedStudentListRow();
    }
    /**
    */
    function getAddBulkParentStudentSubmit(){
        return $this->model->getAddBulkParentStudentSubmit();
    }

    /**
    */
    function getSaveBulkParentStudentSubmit(){
        return $this->model->getSaveBulkParentStudentSubmit();
    }

    /**
    */
    function getCalculateStudentAge(){
        return $this->model->getCalculateStudentAge();
    }

    /**
    */
    function getMonthList(){
        return $this->view->getMonthList();
    }

    /**
    */
    function getCalculateTotalStudentsInBatch(){
        return $this->model->getCalculateTotalStudentsInBatch();
    }
}