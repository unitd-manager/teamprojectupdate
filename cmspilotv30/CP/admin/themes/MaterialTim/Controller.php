<?
class CP_Admin_Themes_MaterialTim_Controller extends CP_Admin_Lib_ThemeControllerAbstract
{


	/**
     *
     */
	function getPatientQueueNo() {
        return $this->view->getPatientQueueNo();
    }


	/**
     *
     */
	function getUpdateQueueNoNext() {
        return $this->view->getUpdateQueueNoNext();
    }

    /**
     *
     */
    function getCheckSessionStatus() {
        return $this->view->getCheckSessionStatus();
    }
}