<?
class CP_Admin_Modules_Labsg_Appointment_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	var $handle      = 'calendarAppointment';
    var $cssClass    = '';
    var $eventAction = 'index.php?module=labsg_appointment&_spAction=eventDetails&showHTML=0';

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

    function getAppointmentDetails() {
        return $this->model->getAppointmentDetails();
    }

    function getAddAppointmentDetails(){
        return $this->view->getAddAppointmentDetails();
    }

    function getSearchPatientDetails(){
        return $this->model->getSearchPatientDetails();
    }

    function getAddAppointmentFormSubmit(){
        return $this->model->getAddAppointmentFormSubmit();
    }

    function getChangeAppointmentByDrag(){
        return $this->model->getChangeAppointmentByDrag();
    }

    function getCreateVisitRecord(){
        return $this->model->getCreateVisitRecord();
    }

    function getUpdateAppointmentDetails(){
        return $this->view->getUpdateAppointmentDetails();
    }

    function getUpdateAppointmentDetailsSubmit(){
        return $this->model->getUpdateAppointmentDetailsSubmit();
    }

    function getDoctorDetails(){
        return $this->view->getDoctorDetails();
    }

    function getAppointmentListDetails(){
        return $this->view->getAppointmentListDetails();
    }

    function getCancelVisitRecord(){
        return $this->model->getCancelVisitRecord();
    }

    function getCancelAppointmentRecord(){
        return $this->model->getCancelAppointmentRecord();
    }

    function getUpdateAppointmentEventDetails(){
        return $this->model->getUpdateAppointmentEventDetails();
    }

}