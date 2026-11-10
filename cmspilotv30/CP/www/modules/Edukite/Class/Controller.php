<?
class CP_Www_Modules_Edukite_Class_Controller extends CP_Common_Modules_Edukite_Class_Controller
{
    function getClassList() {
        return $this->model->getClassList();
    }

    function getStudentList() {
        return $this->view->getStudentList();
    }

    function getLeftPanel() {
        return $this->view->getLeftPanel();
    }

    function getRightPanel() {
        return $this->view->getRightPanel();
    }

    function getLinkedClassList() {
        return $this->model->getLinkedClassList();
    }

    function getLeftPanelDefaultContent() {
        return $this->view->getLeftPanelDefaultContent();
    }

    function getRightPanelDefaultContent() {
        return $this->view->getRightPanelDefaultContent();
    }

    function getLinkStudentToRightPanel() {
        return $this->view->getLinkStudentToRightPanel();
    }

    function getDeleteLinkedStudents() {
        return $this->model->getDeleteLinkedStudents();
    }

    function getLinkAllStudentToRightPanel() {
        return $this->view->getLinkAllStudentToRightPanel();
    }

    function getDeleteAllLinkedStudents() {
        return $this->model->getDeleteAllLinkedStudents();
    }

    function getStudentDisplayAfterSearch() {
        return $this->view->getStudentDisplayAfterSearch();
    }
}