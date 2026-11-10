<?
class CP_Common_Lib_Widgets
{
    //==================================================================//
    function __construct(){
    }

    //==================================================================//
    function registerWidget($widgetObj, $overrideArr){
        foreach($overrideArr as $key => $value){
            $widgetObj[$key] = $value;
        }

        $widgetsArr[$widgetObj['name']] = $widgetObj;
        CP_Common_Lib_Registry::arrayMerge('widgetsArr', $widgetsArr);
    }

    //==================================================================//
    function setWidgetsArray(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $arr = $cpCfg['cp.availableWidgets'];

        foreach($arr as $widget){
            $modObj = getCPWidgetObj($widget);
            if (method_exists($modObj->fns, 'setWidgetArray')) {
                $modObj->fns->setWidgetArray($this);
            } else {
                exit("setWidgetArray is missing in {$widget}");
            }
        }
    }

    //==================================================================//
    function getWidgetObj($widget){
        $arr['name'] = $widget;
        return $arr;
    }
  
    //==================================================================//
}