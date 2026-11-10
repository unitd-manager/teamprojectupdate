<?
class CP_Admin_Modules_AgileIms_Company_Controller extends CP_Common_Modules_AgileIms_Company_Controller
{
    function getPrintVoucher() {
        return $this->fns->getPrintVoucher();
    }
    
    /**
     *
     */
    function getCompanyNew(){
        return $this->view->getCompanyNew();
    }

    /**
     *
     */
    function getCompanyAddSubmit(){
        return $this->model->getCompanyAddSubmit();
    }    

    /**
     *
     */
    function getCancelEnrollmentForCompany(){
        return $this->model->getCancelEnrollmentForCompany();
    }

    /**
     *
     */
    function getPrintCourseConfirmation(){
        return $this->model->getPrintCourseConfirmation();
    }

    /**
     *
     */
    function getCompanyDetailsForContactJson(){
        return $this->view->getCompanyDetailsForContactJson();
    }
}