<?
class CP_Admin_Modules_Edukloud_CourseSubsidyLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    /**
     *
     */
    function getSubsidyValueForDropDown() {
        return $this->model->getSubsidyValueForDropDown();
    }

    /**
     *
     */
    function getDiscountValueForDropDown() {
        return $this->model->getDiscountValueForDropDown();
    }
}
