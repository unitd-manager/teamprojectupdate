<?
class CP_Www_Modules_EdukiteWeb_Notice_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getStudentIdForParent() {
        return $this->view->getStudentIdForParent();
    }

    function getContactSchoolContent() {
        return $this->view->getContactSchoolContent();
    }

    function getParentProfileForm() {
        return $this->view->getParentProfileForm();
    }

    function getParentProfileFormSubmit() {
        return $this->model->getParentProfileFormSubmit();
    }

    function getActivityCalendarDisplay() {
        return $this->view->getActivityCalendarDisplay();
    }

    function getDetailPopUp() {
        return $this->view->getDetailPopUp();
    }

    function getTaskDisplay() {
        return $this->view->getTaskDisplay();
    }

    function getUpdateSessionStatus() {
        return $this->model->getUpdateSessionStatus();
    }

    function getParentFeedbackForm() {
        return $this->view->getParentFeedbackForm();
    }

    function getUpdateStudentIdInComment() {
        return $this->view->getUpdateStudentIdInComment();
    }

    function getAddTeacherFeedbackSubmit() {
        return $this->model->getAddTeacherFeedbackSubmit();
    }

    function getDisplayTeacherFeedback() {
        return $this->view->getDisplayTeacherFeedback();
    }

    /**
     *
     */
    function getPrintGalleryAsPdf() {
         return $this->view->getPrintGalleryAsPdf();
    }

    function getDailyActivityForTeacherForm() {
         return $this->view->getDailyActivityForTeacherForm();
    }

    function getDailyActivityFormSubmit() {
         return $this->model->getDailyActivityFormSubmit();
    }

    function getUpdateTaskReadNoticeParent() {
         return $this->view->getUpdateTaskReadNoticeParent();
    }
}