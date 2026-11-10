<?
class CP_Admin_Modules_EnterpriseIms_SubjectLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    /**
     *
     */
    function getSubjectValueForCheckBox() {
        return $this->model->getSubjectValueForCheckBox();
    }
    /**
     *
     */
    function getAddSubjectAmountToTotal() {
        return $this->model->getAddSubjectAmountToTotal();
    }
}