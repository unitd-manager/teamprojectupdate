<?
class CP_Admin_Themes_Angle_Controller extends CP_Admin_Lib_ThemeControllerAbstract
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


}