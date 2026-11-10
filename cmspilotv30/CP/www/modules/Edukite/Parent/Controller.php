<?
class CP_Www_Modules_Edukite_Parent_Controller extends CP_Common_Modules_Edukite_Parent_Controller
{
    function getLeftPanel() {
        return $this->view->getLeftPanel();
    }

    function getRightPanel() {
        return $this->view->getRightPanel();
    }

    function getStudentList() {
        return $this->view->getStudentList();
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

    function getSendParentEmailAlert() {
        return $this->model->getSendParentEmailAlert();
    }

    function getSendUsernameToParent() {
        return $this->model->getSendUsernameToParent();
    }

    function getParentPassword() {
        return $this->model->getParentPassword();
    }

    function getStudentDisplayAfterSearch() {
        return $this->view->getStudentDisplayAfterSearch();
    }
    
    function getFindParentsForStudent() {
        return $this->model->getFindParentsForStudent();
    }
    
    function getFindSiblings() {
        return $this->model->getFindSiblings();
    }
    
    function getAddStudentCode() {
        return $this->model->getAddStudentCode();
    }

    function getAddFamilyCodes() {
        return $this->model->getAddFamilyCodes();
    }

    function getSendUsernameToParentWithStudentDetails() {
        return $this->model->getSendUsernameToParentWithStudentDetails();
    }
}