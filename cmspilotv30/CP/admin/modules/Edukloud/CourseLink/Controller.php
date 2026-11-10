<?
class CP_Admin_Modules_Edukloud_CourseLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getCourseSummary(){
        $fn = Zend_Registry::get('fn');
        $course_id = $fn->getReqParam('course_id');

        return $this->view->getCourseSummary($course_id);
    }

    /**
    */
    function getCourseCompanySummary(){
        $fn = Zend_Registry::get('fn');
        $course_id = $fn->getReqParam('course_id');

        return $this->view->getCourseCompanySummary($course_id);
    }
    
    /**
    */
    function getSubsidyData(){
        $fn = Zend_Registry::get('fn');
        $course_subsidy_history_id = $fn->getReqParam('course_subsidy_history_id');
        $course_id = $fn->getReqParam('course_id');
        $course_contact_id = $fn->getReqParam('course_contact_id');

        return $this->view->getSubsidyData($course_subsidy_history_id, $course_contact_id, $course_id);
    }
    
    /**
    */
    function getNewCoursePvtLink(){
        $fn = Zend_Registry::get('fn');
        $course_id = $fn->getReqParam('course_id');

        return $this->view->getNewCoursePvtLink($course_id);
    }
    
    /**
    */
    function getEditCoursePvtLink(){
        $fn = Zend_Registry::get('fn');
        $course_id = $fn->getReqParam('course_id');

        return $this->view->getEditCoursePvtLink($course_id);
    }
    
    /**
    */
    function getDiscountValueForPvt(){

        return $this->model->getDiscountValueForPvt();
    }
    
    /**
    */
    function getInstallmentAmountForPvt(){

        return $this->model->getInstallmentAmountForPvt();
    }
    
    /**
    */
    function getCourseValueForDropDown(){

        return $this->model->getCourseValueForDropDown();
    }
    
    /**
    */
    function getAddCoursePvtLink(){

        return $this->model->getAddCoursePvtLink();
    }
    
    /**
    */
    function getSaveCoursePvtLink(){

        return $this->model->getSaveCoursePvtLink();
    }
}