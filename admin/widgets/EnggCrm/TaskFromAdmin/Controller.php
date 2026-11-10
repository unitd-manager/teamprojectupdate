<?
class CPL_Admin_Widgets_EnggCrm_TaskFromAdmin_Controller extends CP_Common_Lib_WidgetControllerAbstract
{

	function getRowsHTML() {
        return $this->view->getRowsHTML();
    }
    
	function getUpdateIsRead() {
        return $this->model->getUpdateIsRead();
    }

    function getUpdateEmpStatusIsRead() {
        return $this->model->getUpdateEmpStatusIsRead();
    }

    function getUpdateNotRead() {
        return $this->model->getUpdateNotRead();
    }

    function getUpdateIsRead5Days() {
        return $this->model->getUpdateIsRead5Days();
    }
    /**
     *
     */
    function getNotificationMessageCount() {
        return $this->view->getNotificationMessageCount();
    }

    function getAddNotification() {
        return $this->view->getAddNotification();
    }

    function getNotificationSubmit() {
        return $this->model->getNotificationSubmit();
    }
}