<?
class CP_Common_Lib_Errors
{
    var $errorArray = array();

    function __construct(){
        $this->setErrorArray();
    }


    function setErrorArray() {
        $arr = &$this->errorArray;
        $arr = array(
             'pluginMethodNotFound' => 'Plugin: [clsName]->[funcName] Not Found'
            ,'widgetNotFound' => 'Widget: [clsName]->getWidget Not Found'
            ,'widgetMethodNotFound' => 'Widget: [clsName]->[funcName] Not Found'
            ,'missingPluginInAvailableArr' => 'Plugin: [plugin] is missing in cp.availablePlugins'
            ,'moduleMethodNotFound' => 'Module: [clsName]->[funcName] does not exist'
            ,'spActionMethodNotFound' => 'SpAction: SpAction->[funcName] does not exist'
            ,'themeMethodNotFound' => 'Theme: [clsName]->[funcName] does not exist'
            ,'linkModuleMethodNotFound' => 'Link Module: [clsName]->[funcName] does not exist'
        );
    }

    function getError($errorInd, $exp = array()) {
        $replaceArr = isset($exp['replaceArr']) ? $exp['replaceArr'] : array();

        $error = $this->errorArray[$errorInd];

        foreach ($replaceArr as $repInd => $repVal) {
            $error = str_replace($repInd, $repVal, $error);
        }

        $error = htmlspecialchars($error);
        $error = '<h3>' . $error . '</h3>';

        return $error;
    }
}