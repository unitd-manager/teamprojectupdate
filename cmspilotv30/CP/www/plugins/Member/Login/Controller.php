<?
class CP_Www_Plugins_Member_Login_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    /**
     *
     */
    function getLoginSubmit() {
        return $this->model->getLoginSubmit();
    }

     
    /**
     *
     */
    function getLogout() {
        return $this->model->getLogout();
    }
}