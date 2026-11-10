<?
class CPL_Admin_Widgets_Project_InvoiceSummaryChart_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
	function getWidgetDataJSON(){
        return $this->model->getWidgetDataJSON();
    }

    function getRowsHTML(){
        return $this->view->getRowsHTML();
    }
}