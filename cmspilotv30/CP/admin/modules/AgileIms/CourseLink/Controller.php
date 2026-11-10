<?
class CP_Admin_Modules_AgileIms_CourseLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getCourseSummary(){
        $fn = Zend_Registry::get('fn');
        $course_id = $fn->getReqParam('course_id');

        return $this->view->getCourseSummary($course_id);
    }
    
    /**
    */
    function getSubsidyData(){
        $fn = Zend_Registry::get('fn');
        $subsidy_discount_id = $fn->getReqParam('subsidy_discount_id');
        $course_id = $fn->getReqParam('course_id');
        $course_contact_id = $fn->getReqParam('course_contact_id');

        return $this->view->getSubsidyData($subsidy_discount_id, $course_contact_id, $course_id);
    }
    
    /**
    */
    function getEditStudentEnrollment(){
        $fn = Zend_Registry::get('fn');
        $course_id = $fn->getReqParam('course_id');

        return $this->view->getEditStudentEnrollment($course_id);
    }
    
    /**
    */
    function getDiscountValueForPvt(){
        return $this->model->getDiscountValueForPvt();
    }
    
    /**
    */
    function getCourseValueForDropDown(){
        return $this->model->getCourseValueForDropDown();
    }
}