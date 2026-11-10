<?
class CP_Common_Lib_Action
{
    //==================================================================//
    function getActionButtons($displayType = 'ul'){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $actionButArray = Zend_Registry::get('actionButArray');
        $module = $tv['module'];
        $action = $tv['action'];
        $rows   = '';
        $text = '';


        $actKey = 'actBtns'. ucfirst($tv['action']);

        if (!array_key_exists($actKey, $modulesArr[$module])) {
            return '';
        }

        $actKeyArr    = $modulesArr[$module][$actKey];
        $totalRecords = count($actKeyArr);
        $counter      = 0;

        foreach ($actKeyArr as $key => $actionName) {
            if (CP_SCOPE == 'admin' && $cpCfg['cp.hasAccessModule']){
                $modulesArrAccess = Zend_Registry::get('modulesArrAccess');

                $new = isset($modulesArrAccess[$module]['new']) ? $modulesArrAccess[$module]['new'] : 0;
                if ($actionName == 'new' && $new == 0) {
                    continue;
                }

                $publish = isset($modulesArrAccess[$module]['publish']) ? $modulesArrAccess[$module]['publish'] : 0;
                if ($actionName == 'publish' && $publish == 0) {
                    continue;
                }

                $unpublish = isset($modulesArrAccess[$module]['unpublish']) ? $modulesArrAccess[$module]['unpublish'] : 0;
                if ($actionName == 'unPublish' && $unpublish == 0) {
                    continue;
                }

                $edit = isset($modulesArrAccess[$module]['edit']) ? $modulesArrAccess[$module]['edit'] : 0;
                if ($actionName == 'edit' && $edit == 0) {
                    continue;
                }

                $delete = isset($modulesArrAccess[$module]['delete']) ? $modulesArrAccess[$module]['delete'] : 0;
                if ($actionName == 'delete' && $delete == 0) {
                    continue;
                }

                $print = isset($modulesArrAccess[$module]['print']) ? $modulesArrAccess[$module]['print'] : 0;
                if ($actionName == 'print' && $print == 0) {
                    continue;
                }

                $import = isset($modulesArrAccess[$module]['import']) ? $modulesArrAccess[$module]['import'] : 0;
                if ($actionName == 'import' && $import == 0) {
                    continue;
                }

                $export = isset($modulesArrAccess[$module]['export']) ? $modulesArrAccess[$module]['export'] : 0;
                if ($actionName == 'export' && $export == 0) {
                    continue;
                }
            }
            $counter     = $counter+1;
            $actionText  = $actionButArray[$actionName]['title'];
            $url         = $actionButArray[$actionName]['url'];
            //$displayType = $actionButArray[$actionName]['displayType'];

            /*** WARNING DO NOT REPLCE DOUBLE QUOTE TO SINGLE AS THE URL MAY CONTAIN JS AND CAUSES SOME ERRORS ***/
            if ($displayType == 'ul') {
                $rows .= "
                <li>
                    <a href=\"{$url}\" id='actBtn_{$actionName}'
                       dialogTitle='{$actionText}' class='actionButtons'>{$actionText}</a>
                </li>
                ";
            } else if ($displayType == 'button') {
                $rows .= "
                <input type='button' value='{$actionText}' id='actBtn_{$actionName}' title='{$actionText}' url=\"{$url}\" />
                ";
            }
        }

        if ($rows != ''){
            if ($displayType == 'ul'){
                $text = "<ul class='noDefault'>{$rows}</ul>";
            } else {
                $text = $rows;
            }
        }

        return $text;
    }

    //==================================================================//
    function getRoomTitleImage() {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $image = $modulesArr[$tv['module']]["searchPanel"]["image"];
        $text  = "<img src='{$image}' />";

        return $text;
    }

    //==================================================================//
    function getRoomTitleText() {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $text = $modulesArr[$tv['module']]["searchPanel"]["text"];

        return $text;
    }

    //==================================================================//
}
