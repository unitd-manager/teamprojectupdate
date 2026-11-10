<?
class CP_Www_Plugins_Member_ResetPassword_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    /**
     *
     */
    function getSubmit() {
        return $this->model->getSubmit();
    }

    function getResetPasswordForm() {
        return $this->view->getResetPasswordForm();
    }

    function getResetSubmit() {
        return $this->model->getResetSubmit();
    }

    function getSendMessageToAdmin() {
        return $this->view->getSendMessageToAdmin();
    }

    /**
     *
     */
    function getSendMessageToAdminSubmit() {
        return $this->model->getSendMessageToAdminSubmit();
    }
}