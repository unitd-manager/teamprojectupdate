<?
class CP_Www_Modules_Edukite_Notice_Controller extends CP_Common_Modules_Edukite_Notice_Controller
{
    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['siteType'] == 'kite' && $tv['action'] == 'detail') {
            $text = $this->getDetail('kiteNoticeDetail');
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    }

    function getClassList() {
        return $this->view->getClassList();
    }

    function getLeftPanel() {
        return $this->view->getLeftPanel();
    }

    function getRightPanel() {
        //return $this->view->getRightPanel();
    }

    function getLinkedClassList() {
        return $this->view->getLinkedClassList();
    }

    function getLinkClassToRightPanel() {
        return $this->view->getLinkClassToRightPanel();
    }

    function getLinkClassStudentToRightPanel() {
        return $this->view->getLinkClassStudentToRightPanel();
    }

    function getDeleteLinkedClasses() {
        return $this->model->getDeleteLinkedClasses();
    }

    function getDeleteLinkedStudentsFromClass() {
        return $this->model->getDeleteLinkedStudentsFromClass();
    }

    function getExpandClassInRightPanel() {
        return $this->view->getExpandClassInRightPanel();
    }

    function getExpandClassInLeftPanel() {
        return $this->view->getExpandClassInLeftPanel();
    }

    function getStudentList() {
        return $this->view->getStudentList();
    }

    function getLinkedStudentList() {
        return $this->view->getLinkedStudentList();
    }

    function getLinkStudentToRightPanel() {
        return $this->view->getLinkStudentToRightPanel();
    }

    function getDeleteLinkedStudents() {
        return $this->model->getDeleteLinkedStudents();
    }

    function getLinkAllClassToRightPanel() {
        return $this->view->getLinkAllClassToRightPanel();
    }

    function getDeleteAllLinkedClasses() {
        return $this->model->getDeleteAllLinkedClasses();
    }

    function getLinkAllStudentToRightPanel() {
        return $this->view->getLinkAllStudentToRightPanel();
    }

    function getDeleteAllLinkedStudents() {
        return $this->model->getDeleteAllLinkedStudents();
    }

    function getNoticeOptions() {
        return $this->view->getNoticeOptions();
    }

    function getEmailPublishNoticeRecordByID() {
        return $this->view->getEmailPublishNoticeRecordByID();
    }

    function getChatPublishNoticeRecordByID() {
        return $this->view->getChatPublishNoticeRecordByID();
    }

    function getTeacherChatPublishNoticeRecordByID() {
        return $this->view->getTeacherChatPublishNoticeRecordByID();
    }

    function getGlobalKitePublishNoticeRecordByID() {
        return $this->view->getGlobalKitePublishNoticeRecordByID();
    }

    function getLaunchNowToKites() {
        return $this->view->getLaunchNowToKites();
    }

    function getStaffList() {
        return $this->view->getStaffList();
    }

    function getLinkedStaffList() {
        return $this->view->getLinkedStaffList();
    }

    function getLinkStaffToRightPanel() {
        return $this->view->getLinkStaffToRightPanel();
    }

    function getDeleteLinkedStaff() {
        return $this->model->getDeleteLinkedStaff();
    }

    function getLinkAllStaffToRightPanel() {
        return $this->view->getLinkAllStaffToRightPanel();
    }

    function getDeleteAllLinkedStaff() {
        return $this->model->getDeleteAllLinkedStaff();
    }

    function getCohortList() {
        return $this->view->getCohortList();
    }

    function getLinkedCohortList() {
        return $this->view->getLinkedCohortList();
    }

    function getLinkCohortToRightPanel() {
        return $this->view->getLinkCohortToRightPanel();
    }

    function getLinkCohortStudentToRightPanel() {
        return $this->view->getLinkCohortStudentToRightPanel();
    }

    function getDeleteLinkedCohort() {
        return $this->model->getDeleteLinkedCohort();
    }

    function getDeleteLinkedStudentsFromCohort() {
        return $this->model->getDeleteLinkedStudentsFromCohort();
    }

    function getExpandCohortInRightPanel() {
        return $this->view->getExpandCohortInRightPanel();
    }

    function getExpandCohortInLeftPanel() {
        return $this->view->getExpandCohortInLeftPanel();
    }

    function getLinkAllCohortToRightPanel() {
        return $this->view->getLinkAllCohortToRightPanel();
    }

    function getDeleteAllLinkedCohort() {
        return $this->model->getDeleteAllLinkedCohort();
    }

    function getLaunchNowImageIcon() {
        return $this->view->getLaunchNowImageIcon();
    }

    function getLeftPanelDefaultContent() {
        return $this->view->getLeftPanelDefaultContent();
    }

    function getRightPanelDefaultContent() {
        return $this->view->getRightPanelDefaultContent();
    }

    function getSortMediaRecord() {
        return $this->view->getSortMediaRecord();
    }

    function getRotateMediaRecord() {
        return $this->model->getRotateMediaRecord();
    }

    function getUpdateCaptionInMedia() {
        return $this->model->getUpdateCaptionInMedia();
    }

    function getStudentDisplayAfterSearch() {
        return $this->view->getStudentDisplayAfterSearch();
    }

    function getStaffDisplayAfterSearch() {
        return $this->view->getStaffDisplayAfterSearch();
    }

    function getAutoUpdateFields() {
        return $this->model->getAutoUpdateFields();
    }

    function getAchievementPanel() {
        return $this->view->getAchievementPanel();
    }

    function getCreateAchievementHistoryRecord() {
        return $this->model->getCreateAchievementHistoryRecord();
    }

    function getDeleteAchievementHistoryRecord() {
        return $this->model->getDeleteAchievementHistoryRecord();
    }

    function getAchievementDisplayAfterSearch() {
        return $this->view->getAchievementDisplayAfterSearch();
    }

    function getAchievementSubCategoryDisplay() {
        return $this->view->getAchievementSubCategoryDisplay();
    }

    function getHomeWorkChatNoticeRecordByID(){
        return $this->view->getHomeWorkChatNoticeRecordByID();
    }

    function getNoticeReadSummary(){
        return $this->view->getNoticeReadSummary();
    }

    function getHomeworkSummary(){
        return $this->view->getHomeworkSummary();
    }

    function getViewCommentHistory(){
        return $this->view->getViewCommentHistory();
    }

    function getSendDraftEmailAlert() {
        return $this->view->getSendDraftEmailAlert();
    }

    function getCreateGalleryRecordForStudent() {
        return $this->view->getCreateGalleryRecordForStudent();
    }

}