<?
class CP_Www_Widgets_Edukite_CalendarDisplay_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle = 'calendar';
    var $cssClass = '';
    var $eventAction = '/index.php?widget=edukite_calendarDisplay&_spAction=eventDetails&showHTML=0';

    // header options
    var $headerLeft   = 'prev,next';
    var $headerCenter = 'title';
    var $headerRight  = '';
    var $minTime      = 8;
    var $monthTimeFormat  = "month: ''";
    var $genTimeFormat    = "'': ''";
    var $executeScript    = true;

    function getEventDetails() {
        return $this->model->getEventDetails();
    }

    function getNoticeDetails() {
        return $this->model->getNoticeDetails();
    }
}