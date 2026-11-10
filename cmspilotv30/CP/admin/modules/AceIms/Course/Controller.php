<?
class CP_Admin_Modules_AceIms_Course_Controller extends CP_Common_Modules_AceIms_Course_Controller
{
    /**
     *
     */
    function getCourseValueForDropDown() {
        return $this->model->getCourseValueForDropDown();
    }

    function getAddSubsidyDiscountPortal(){
        return $this->model->getAddSubsidyDiscountPortal();
    }
}