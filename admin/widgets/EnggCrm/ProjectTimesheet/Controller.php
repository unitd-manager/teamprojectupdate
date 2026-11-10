<?
class CPL_Admin_Widgets_EnggCrm_ProjectTimesheet_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    /**
    */
    function getAddHoursProjectEmployee(){
        return $this->view->getAddHoursProjectEmployee();
    }

    /**
    */
    function getAddDaysRowHeadTimesheet(){
        return $this->view->getAddDaysRowHeadTimesheet();
    }

    /**
    */
    function getUpdateDetailsProjectTimeSheetDetails() {
        return $this->model->getUpdateDetailsProjectTimeSheetDetails();
    }

    /**
    */
    function getUpdateTimeSheetSignStaff() {
        return $this->model->getUpdateTimeSheetSignStaff();
    }

    function getCreateUpdateEmployeeTimesheetRecordEdit() {
        return $this->model->getCreateUpdateEmployeeTimesheetRecordEdit();
    }

    /**
    */
    function getEditHoursProjectEmployee(){
        return $this->view->getEditHoursProjectEmployee();
    }

    /**
    */
    function getEmploymentTimeSheetNewAllView(){
        return $this->view->getEmploymentTimeSheetNewAllView();
    }

    /**
    */
    function getPrintTimeSheetPdf(){
        return $this->view->getPrintTimeSheetPdf();
    }

    /**
    */
    function getPrintSummaryPdf(){
        return $this->view->getPrintSummaryPdf();
    }

    /**
    */
    function getEmployeeAddTimeHoursNewListView(){
        return $this->view->getEmployeeAddTimeHoursNewListView();
    }

    /**
    */
    function getEmployeeForMonthDetails(){
        return $this->view->getEmployeeForMonthDetails();
    }
}