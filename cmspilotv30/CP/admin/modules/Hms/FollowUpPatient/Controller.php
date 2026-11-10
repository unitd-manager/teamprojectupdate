<?
class CP_Admin_Modules_Hms_FollowUpPatient_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	var $handle      = 'calendarFollowUpPatient';
    var $cssClass    = '';
    var $eventAction = 'index.php?module=hms_followUpPatient&_spAction=eventDetails&showHTML=0';

    // header options
    var $headerLeft       = 'prev,next today';
    var $headerCenter     = 'title';
    var $headerRight      = 'agendaDay,agendaWeek,month,agenda';
    var $minTime          = 8;
    var $maxTime          = 21;
    var $monthTimeFormat  = "month: 'H:mm-{H:mm}'";
    var $genTimeFormat    = "'': 'H:mm-{H:mm}'";

    function getEventDetails() {
        return $this->model->getEventDetails();
    }

    function getFollowUpDetails() {
        return $this->model->getFollowUpDetails();
    }

    function getFollowUpNotes() {
        return $this->model->getFollowUpNotes();
    }

    function getAddFollowUpDetails(){
        return $this->view->getAddFollowUpDetails();
    }

    function getSearchPatientDetails(){
        return $this->model->getSearchPatientDetails();
    }

    function getAddFollowUpDetailsFormSubmit(){
        return $this->model->getAddFollowUpDetailsFormSubmit();
    }

    function getChangeFollowUpByDrag(){
        return $this->model->getChangeFollowUpByDrag();
    }

    function getCreateAppointmentRecord(){
        return $this->view->getCreateAppointmentRecord();
    }

    function getCreateAppointmentRecordSubmit(){
        return $this->model->getCreateAppointmentRecordSubmit();
    }

    function getDoctorDetails(){
        return $this->view->getDoctorDetails();
    }

    function getFollowUpListDetails(){
        return $this->view->getFollowUpListDetails();
    }

    function getCancelFollowUpRecord(){
        return $this->model->getCancelFollowUpRecord();    
    }

    function getUpdateFollowUpNotes(){
        return $this->model->getUpdateFollowUpNotes();    
    }
}