<?
class CP_Www_Widgets_Museum_Booking_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $formAction = '/index.php?widget=museum_booking&_spAction=bookingFormSubmit&showHTML=0';
    var $returnUrl  = '';

    function getBookingForm() {
        return $this->view->getBookingForm();
    }

    function getBookingFormSubmit() {
        return $this->model->getBookingFormSubmit();
    }

    function getSpExhibitBookingForm($facility_id) {
        return $this->view->getSpExhibitBookingForm($facility_id);
    }

    function getSpExhibitBookingSubmit() {
        return $this->model->getSpExhibitBookingSubmit();
    } 

    function getVenueHireForm($facility_id) {
        return $this->view->getVenueHireForm($facility_id);
    }

    function getVenueHireSubmit() {
        return $this->model->getVenueHireSubmit();
    }    
    
    /**
     * 
     * @return json
     */
    function getBookingJSON() {
        return $this->model->getBookingJSON();
    }
}