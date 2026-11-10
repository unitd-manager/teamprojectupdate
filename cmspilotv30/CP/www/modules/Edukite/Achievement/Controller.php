<?
class CP_Www_Modules_Edukite_Achievement_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getEdit(){
        return $this->view->getEdit();
    }

    function getLeftPanel() {
        return $this->view->getLeftPanel();
    }

    function getRightPanel() {
        //return $this->view->getRightPanel();
    }

    function getClassList() {
        return $this->view->getClassList();
    }

    function getLinkedClassList() {
        return $this->view->getLinkedClassList();
    }

    function getLinkClassToRightPanel() {
        return $this->view->getLinkClassToRightPanel();
    }

    function getDeleteLinkedClasses() {
        return $this->model->getDeleteLinkedClasses();
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

    function getLeftPanelDefaultContent() {
        return $this->view->getLeftPanelDefaultContent();
    }

    function getRightPanelDefaultContent() {
        return $this->view->getRightPanelDefaultContent();
    }

    function getExpandClassInRightPanel() {
        return $this->view->getExpandClassInRightPanel();
    }

    function getExpandClassInLeftPanel() {
        return $this->view->getExpandClassInLeftPanel();
    }

    function getDeleteLinkedStudentsFromClass() {
        return $this->model->getDeleteLinkedStudentsFromClass();
    }

    function getLinkClassStudentToRightPanel() {
        return $this->view->getLinkClassStudentToRightPanel();
    }

    function getExpandCohortInRightPanel() {
        return $this->view->getExpandCohortInRightPanel();
    }

    function getExpandCohortInLeftPanel() {
        return $this->view->getExpandCohortInLeftPanel();
    }

    function getDeleteLinkedStudentsFromCohort() {
        return $this->model->getDeleteLinkedStudentsFromCohort();
    }

    function getLinkCohortStudentToRightPanel() {
        return $this->view->getLinkCohortStudentToRightPanel();
    }

    function getHelpContent() {
        return $this->view->getHelpContent();
    }
}