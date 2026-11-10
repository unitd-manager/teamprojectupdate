<?
class CP_Admin_Plugins_Common_Login_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    /**
     *
     */
    function getLoginForm(){
        return $this->view->getLoginForm();
    }

    /**
     *
     */
    function getLoginSubmit(){
        return $this->model->getLoginSubmit();
    }

    /**
     *
     */
    function getChooseUsergroupSubmit(){
        return $this->model->getChooseUsergroupSubmit();
    }

    /**
     *
     */
    function loginWithCookie(){
        return $this->model->loginWithCookie();
    }

    /**
     *
     */
    function getAutoLoginRandomID(){
        return $this->model->getAutoLoginRandomID();
    }

    /**
     *
     */
    function getAutoLoginUserByRandomID(){
        return $this->model->getAutoLoginUserByRandomID();
    }
    
    /**
     *
     */
    function getLogout() {
        return $this->model->getLogout();
    }

    function getPaymentReminder2(){
        return $this->view->getPaymentReminder2();
    }
}