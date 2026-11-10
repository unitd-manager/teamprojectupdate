<?
class CP_Www_Modules_Edukite_Student_Controller extends CP_Common_Modules_Edukite_Student_Controller
{
    function getClassList() {
        return $this->view->getClassList();
    }

    function getLeftPanel() {
        return $this->view->getLeftPanel();
    }

    function getRightPanel() {
        return $this->view->getRightPanel();
    }

    function getLinkedClassList() {
        return $this->view->getLinkedClassList();
    }

    function getLinkClassToRightPanel() {
        return $this->view->getLinkClassToRightPanel();
    }

    function getLinkAllClassToRightPanel() {
        return $this->view->getLinkAllClassToRightPanel();
    }

    function getParentList() {
        return $this->view->getParentList();
    }

    function getLinkedParentList() {
        return $this->view->getLinkedParentList();
    }

    function getLinkParentToRightPanel() {
        return $this->view->getLinkParentToRightPanel();
    }

    function getDeleteLinkedClasses() {
        return $this->model->getDeleteLinkedClasses();
    }

    function getDeleteLinkedParents() {
        return $this->model->getDeleteLinkedParents();
    }

    function getLeftPanelDefaultContent() {
        return $this->view->getLeftPanelDefaultContent();
    }

    function getRightPanelDefaultContent() {
        return $this->view->getRightPanelDefaultContent();
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

    function getDeleteLinkedCohort() {
        return $this->model->getDeleteLinkedCohort();
    }

    function getImportStudentImages() {
        return $this->model->getImportStudentImages();
    }

    function getAchievementOptions() {
        return $this->view->getAchievementOptions();
    }

    function getPrintAchievementAsPdf() {
        return $this->model->getPrintAchievementAsPdf();
    }

    function getPrintAchievementAsPdfForAllStudent() {
        return $this->model->getPrintAchievementAsPdfForAllStudent();
    }

    function getAchievementDisplayAfterSearch() {
        return $this->view->getAchievementDisplayAfterSearch();
    }

    function getStudentPassword() {
        return $this->model->getStudentPassword();
    }

    function getDownloadAllOutcomes() {
        return $this->model->getDownloadAllOutcomes();
    }
}