<?
class CP_Admin_Widgets_Payroll_TrainingExpiry_Functions
{
    //==================================================================//
    function setWidgetArray($widgets){
        $widgetObj = $widgets->getWidgetObj('payroll_trainingExpiry');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Training Expiry in Dashboard'
        ));
    }
}
