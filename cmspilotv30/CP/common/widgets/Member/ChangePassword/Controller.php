<?
class CP_Common_Widgets_Member_ChangePassword_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $formAction = '/index.php?widget=member_changePassword&_spAction=save&showHTML=0';
    var $returnUrl = '';
    var $showFormInModal = true;
    var $showSuccessMsgInDialog = true;
    var $validateCSRFToken = false;

    //========================================================//
    function getSave() {
        return $this->model->getSave();
    }
}