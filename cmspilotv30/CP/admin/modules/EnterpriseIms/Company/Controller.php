<?
class CP_Admin_Modules_EnterpriseIms_Company_Controller extends CP_Common_Modules_EnterpriseIms_Company_Controller
{
    function getPrintVoucher() {
        return $this->fns->getPrintVoucher();
    }
    
    /**
     */
    function getCompanyNew(){
        return $this->view->getCompanyNew();
    }

    /**
     */
    function getCompanyAddSubmit(){
        return $this->model->getCompanyAddSubmit();
    }    
}