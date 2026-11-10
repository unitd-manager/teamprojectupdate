<?
class CP_Admin_Modules_Project_FollowUp_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	var $handle      = 'calendarOpportunity';
    var $cssClass    = '';
    var $eventAction = 'index.php?module=project_followUp&_spAction=eventDetails&showHTML=0';

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

    function getAddFollowup(){
        return $this->model->getAddFollowup();    
    }

    function getAddFollowupDetail(){
        return $this->model->getAddFollowupDetail();    
    }

        function getFollowUpNotes(){
        return $this->model->getFollowUpNotes();    
    }

    function getFollowUpNotesSubmit() {
        return $this->model->getFollowUpNotesSubmit();
    }

    function getFollowupValidate() {
        return $this->model->getFollowupValidate();
    }

    function getDeletefollowup() {
        return $this->model->getDeletefollowup();
    }

    function getEditFollowup() {
        return $this->view->getEditFollowup();
    }

    function getEditFollowupFormSubmit() {
        return $this->model->getEditFollowupFormSubmit();
    }

    function getUpdateFollowUpStatus() {
        return $this->model->getUpdateFollowUpStatus();
    }    

    function getOpportunityDetails(){
        return $this->view->getOpportunityDetails();
    }

}