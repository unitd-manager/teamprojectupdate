<?
class CPL_Admin_Modules_EnggCrm_Vehicle_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	 function getAddActualCharge(){
        return $this->view->getAddActualCharge();
    }

    function getActualChargeSubmit(){
        return $this->model->getActualChargeSubmit();
    }
    
    function getAddRenewalDate(){
        return $this->view->getAddRenewalDate();
    }

    function getRenewalDateSubmit(){
        return $this->model->getRenewalDateSubmit();
    }
    function getAddService(){
        return $this->view->getAddService();
    }

    function getServiceSubmit(){
        return $this->model->getServiceSubmit();
    }
}