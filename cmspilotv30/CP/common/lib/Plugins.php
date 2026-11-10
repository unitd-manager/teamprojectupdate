<?
class CP_Common_Lib_Plugins
{
    //==================================================================//
    function __construct(){
    }

    //==================================================================//
    function registerPlugin($pluginObj, $overrideArr){
        foreach($overrideArr as $key => $value){
            $pluginObj[$key] = $value;
        }

        $pluginArr[$pluginObj['name']] = $pluginObj;
        CP_Common_Lib_Registry::arrayMerge('pluginsArr', $pluginsArr);
    }

    //==================================================================//
    function setPluginsArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.availablePlugin'];

        foreach($arr as $plugin){
            $pluginObj = getCPPluginObj($plugin);

            if (method_exists($pluginObj->fns, 'setPluginArray')) {
                $pluginObj->fns->setPluginArray($this);
            } else {
                exit("setPluginArray is missing in {$plugin}");
            }
        }
    }

    //==================================================================//
    function getPluginObj($plugin){
        $arr['name'] = $plugin;
        return $arr;
    }
  
    //==================================================================//
}