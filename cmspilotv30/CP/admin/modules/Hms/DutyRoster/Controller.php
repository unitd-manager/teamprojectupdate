<?
class CP_Admin_Modules_Hms_DutyRoster_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	var $handle      = 'calendarDutyRoster';
    var $cssClass    = '';
    var $eventAction = 'index.php?module=hms_dutyRoster&_spAction=eventDetails&showHTML=0';

    // header options
    var $headerLeft       = 'prev,next today';
    var $headerCenter     = 'title';
    var $headerRight      = 'agendaDay,agendaWeek,agenda';
    var $minTime          = 8;
    var $maxTime          = 21;
    var $monthTimeFormat  = "month: 'H:mm-{H:mm}'";
    var $genTimeFormat    = "'': 'H:mm-{H:mm}'";

    function getAddDutyRosterDetails(){
        return $this->view->getAddDutyRosterDetails();
    }

    function getAddDutyRosterDetailsSubmit(){
        return $this->model->getAddDutyRosterDetailsSubmit();
    }

    function getEventDetails(){
        return $this->model->getEventDetails();
    }

    function getAddMoreWorkingTime1(){
        return $this->view->getAddMoreWorkingTime1();
    }

    function getAddMoreWorkingTime2(){
        return $this->view->getAddMoreWorkingTime2();
    }

    function getDutyRosterEdit(){
        return $this->view->getDutyRosterEdit();
    }

    function getDutyRosterEditFormSubmit(){
        return $this->model->getDutyRosterEditFormSubmit();
    }

    function getDoctorDetails(){
        return $this->view->getDoctorDetails();
    }

    function getPrintDutyRosterForm(){
        return $this->view->getPrintDutyRosterForm();
    }

    function getPrintDutyRosterFormSubmit(){
        return $this->model->getPrintDutyRosterFormSubmit();
    }

    function getPrintDutyRosterPdf(){
        return $this->view->getPrintDutyRosterPdf();
    }

}