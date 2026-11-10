<?
class CPL_Admin_Lib_Widgets extends CP_Common_Lib_Widgets
{
    //==================================================================//
    function __construct(){
        $this->setWidgetsArray();
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $widgetNameInfo = explode('_', $widget);
        $widgetFold     = $widgetNameInfo[0];
        $widgetName     = $widgetNameInfo[1];
        
        $arr['name']  = $widget;
        $arr['title'] = ucfirst($widgetName);
        return $arr;
    }
  
    //==================================================================//
    function getValueByKey($widget, $key){
        $widgetsArr = Zend_Registry::get('widgetsArr');
        $value = '';

        if (isset($widgetsArr[$widget][$key])){
            $value = $widgetsArr[$widget][$key];
        }

        return $value;
    }

}