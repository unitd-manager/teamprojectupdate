<?
class CP_Admin_Widgets_AceIms_CalendarDisplay_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $handle = 'calendar1';
    var $cssClass = '';
    var $eventAction = 'index.php?widget=aceIms_calendarDisplay&_spAction=eventDetails&showHTML=0';

    // header options
    var $headerLeft   = 'prev,next today';
    var $headerCenter = 'title';
    var $headerRight  = 'month,agendaWeek,agendaDay';
    var $minTime      = 8;
    var $monthTimeFormat  = "month: 'H:mm-{H:mm}'";
    var $genTimeFormat    = "'': 'H:mm-{H:mm}'";

    function getEventDetails() {
        return $this->model->getEventDetails();
    }

    function getBatchDetails() {
        return $this->model->getBatchDetails();
    }
}