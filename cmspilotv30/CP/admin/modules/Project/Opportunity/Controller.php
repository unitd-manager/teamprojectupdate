<?
class CP_Admin_Modules_Project_Opportunity_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getConfirmedQuoteIDJSON() {
        return $this->model->getConfirmedQuoteIDJSON();
    }

    /**
     *
     */
    function getCompanyLinked() {
        return $this->view->getCompanyLinked();
    }

	/**
     *
     */
    function getAddCompanyLinked() {
        return $this->view->getAddCompanyLinked();
    }

	/**
     *
     */
    function getAddCompanyLinkedSubmit() {
        return $this->model->getAddCompanyLinkedSubmit();
    }

    /**
     *
     */
    function getEditCompanyLinked() {
        return $this->view->getEditCompanyLinked();
    }

    /**
     *
     */
    function getEditCompanyLinkedSubmit() {
        return $this->model->getEditCompanyLinkedSubmit();
    }

    /**
     *
     */
    function getDeleteCompanyLinked() {
        return $this->model->getDeleteCompanyLinked();
    }

    /**
     *
     */
    function getContactLinked() {
        return $this->view->getContactLinked();
    }

	/**
     *
     */
    function getAddContactLinked() {
        return $this->view->getAddContactLinked();
    }

	/**
     *
     */
    function getAddContactLinkedSubmit() {
        return $this->model->getAddContactLinkedSubmit();
    }

    /**
     *
     */
    function getEditContactLinked() {
        return $this->view->getEditContactLinked();
    }

    /**
     *
     */
    function getEditContactLinkedSubmit() {
        return $this->model->getEditContactLinkedSubmit();
    }

    /**
     *
     */
    function getDeleteContactLinked() {
        return $this->model->getDeleteContactLinked();
    }

    /**
     *
     */
    function getContactByCompanyJSON() {
        return $this->model->getContactByCompanyJSON();
    }

    /**
     *
     */
    function getDeleteContactLinkedAll() {
        return $this->model->getDeleteContactLinkedAll();
    }
}