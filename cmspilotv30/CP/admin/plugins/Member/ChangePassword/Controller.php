<?
class CP_Admin_Plugins_Member_ChangePassword_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    /**
     *
     */
    function getChangePasswordForm() {
        return $this->view->getChangePasswordForm();
    }
    /**
     *
     */
    function getChangePasswordSubmit() {
        return $this->model->getChangePasswordSubmit();
    }
}