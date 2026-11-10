<?
class CP_Admin_Modules_Tuitionsg_SubjectLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
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

    /**
     *
     */
    function getCalculateTotalCheckedSubjectAmount() {
        return $this->model->getCalculateTotalCheckedSubjectAmount();
    }
    /**
     *
     */
    function getAddAllSubjectAmountToTotal() {
        return $this->model->getAddAllSubjectAmountToTotal();
    }

    /**
     *
     */
    function getSubjectsByCourseJSON() {
        return $this->model->getSubjectsByCourseJSON();
    }
}