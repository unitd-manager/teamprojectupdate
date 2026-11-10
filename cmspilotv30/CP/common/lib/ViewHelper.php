<?
class CP_Common_Lib_ViewHelper
{

    /**
     *
     */
    function setJSSArrays($type, $name, $fileName){
        $cpCfg = Zend_Registry::get('cpCfg');

        $jsFilesArr = array();
        $jsFilesArrLast = array();

        $cssFilesArr = array();
        $cssFilesArrLast = array();

        $folder = $name;

        if ($type == 'themes' || $type == 'skins'){
            $arrName = ($fileName == 'functions.js') ? 'jsFilesArrLast' : 'cssFilesArrLast';
        } else {
            $arrName = ($fileName == 'functions.js') ? 'jsFilesArr' : 'cssFilesArr';
        }

        $arr = &$$arrName;
        $const = strtoupper($type);

        $path1 = constant("CP_{$const}_PATH_COMMON") . $folder . "/{$fileName}";
        $path2 = constant("CP_{$const}_PATH"). $folder . "/{$fileName}";
        $path3 = constant("CP_{$const}_PATH_LOCAL"). $folder . "/{$fileName}";

        $path1als = constant("CP_{$const}_PATH_COMMON_ALIAS"). $folder . "/{$fileName}";
        $path2als = constant("CP_{$const}_PATH_ALIAS"). $folder . "/{$fileName}";
        $path3als = constant("CP_{$const}_PATH_LOCAL_ALIAS"). $folder . "/{$fileName}";

        if (file_exists($path1)){
            $arr[] = $path1als;
        }

        if (file_exists($path2)){
            $arr[] = $path2als;
        }


        if (defined('CP_PATH2')){
            $path4 = constant("CP_{$const}_PATH2_COMMON") . $folder . "/{$fileName}";
            $path5 = constant("CP_{$const}_PATH2"). $folder . "/{$fileName}";

            $path4als = constant("CP_{$const}_PATH2_COMMON_ALIAS"). $folder . "/{$fileName}";
            $path5als = constant("CP_{$const}_PATH2_ALIAS"). $folder . "/{$fileName}";

            if (file_exists($path4)){
                $arr[] = $path4als;
            }

            if (file_exists($path5)){
                $arr[] = $path5als;
            }
        }

        if (file_exists($path3)){
            $arr[] = $path3als;
        }
        if ($fileName == 'functions.js'){
            CP_Common_Lib_Registry::arrayMerge('jsFilesArr', $jsFilesArr);
            CP_Common_Lib_Registry::arrayMerge('jsFilesArrLast', $jsFilesArrLast);
        } else {
            CP_Common_Lib_Registry::arrayMerge('cssFilesArr', $cssFilesArr);
            CP_Common_Lib_Registry::arrayMerge('cssFilesArrLast', $cssFilesArrLast);
        }
    }

    /**
     *
     */
    function getListViewWrapper($html){
        return $html;
    }

    /**
     *
     */
    function getDetailViewWrapper($html, $exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $modulesArr = Zend_Registry::get('modulesArr');
        $clsInst = $fn->getIssetParam($exp, 'clsInst');

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }
        $model = $clsInst->model;
        $view = $clsInst->view;
        $module = $clsInst->name;

        $row = $model->dataArray[0];
        $keyField = $modulesArr[$module]["keyField"];
        $showActBtnsBelowForm = $modulesArr[$module]["showActBtnsBelowForm"];

        $btns = '';
        if ($showActBtnsBelowForm){
		    $action = Zend_Registry::get('action');
            $actBtns = $action->getActionButtons('button');

            if ($actBtns != ''){
                $btns = "
                <div class='floatbox'>
                    <div class='type-button float_right'>
                        {$actBtns}
                    </div>
                </div>
                ";
            }
        }

        $formText = "
        <div class='yform columnar detail'>
            {$html}
            {$btns}
        </div>
        ";

        if (method_exists($view, 'getRightPanel') || CP_SCOPE == 'admin'){
            $right = method_exists($view, 'getRightPanel') ? $view->getRightPanel($row) : '';

            $text = "
            <div class='subcolumns col3Body'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$formText}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='rightPanel'>
                        {$right}
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            <div class='col3Body'>
                {$formText}
            </div>
            ";
        }

        /** the output is surrounded by form just to inherit the styles of yform - to change later **/
        $text = "
        {$text}
        <input id='record_id' type='hidden' name='{$keyField}' value='{$row[$keyField]}'>
        <input id='room_name' type='hidden' value='{$module}'>
        <input id='returnToListUrl' type='hidden' value='{$pager->getReturnToListLinkAfterDelete()}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditViewWrapper($html, $exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $modulesArr = Zend_Registry::get('modulesArr');

        $clsInst = Zend_Registry::get('currentModule');
        $model = $clsInst->model;
        $view = $clsInst->view;
        $row = $model->dataArray[0];
        $keyField = $modulesArr[$tv['module']]["keyField"];
        $showActBtnsBelowForm = $modulesArr[$tv['module']]["showActBtnsBelowForm"];

        $returnUrl = $fn->getIssetParam($exp, 'returnUrl');

        if (CP_SCOPE == 'www'){
            $formAction = '/index.php?_spAction=save';
            $returnUrl = ($returnUrl !='') ? $returnUrl : $cpUrl->getUriWithNoQstr();
        } else {
            $formAction = str_replace("_action=edit", "_spAction=save", $pager->searchQueryString);
            $returnUrl = ($returnUrl !='') ? $returnUrl : '';
        }

        $formAction .= '&showHTML=0';

        $strPos = $cpUtil->stringPosition($formAction, "module");
        if ($strPos == -1) {
            $formAction  = $formAction . "&module=" . $tv['module'];
        }

        $btns = '';
        if ($showActBtnsBelowForm){
		    $action = Zend_Registry::get('action');
            $actBtns = $action->getActionButtons('button');

            if ($actBtns != ''){
                $btns = "
                <div class='type-button float_right'>
                    {$actBtns}
                </div>
                ";
            }
        }

        $formText = "
        <form name='edit' class='yform columnar edit cpJqForm' id='frmEdit' action='{$formAction}' method='post'>
            {$html}
            {$btns}
            <input id='record_id' type='hidden' name='{$keyField}' value='{$row[$keyField]}'>
            <input type='hidden' name='apply' value='0'>
            <input type='hidden' name='returnUrl' value='{$returnUrl}'>
        </form>
        ";

        if (method_exists($view, 'getRightPanel') || CP_SCOPE == 'admin'){
            $right = method_exists($view, 'getRightPanel') ? $view->getRightPanel($row) : '';

            $text = "
            <div class='subcolumns col3Body'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$formText}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='rightPanel'>
                        {$right}
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            <div class='col3Body'>
                {$formText}
            </div>
            ";
        }

        $text = "
        {$text}
        <input id='room_name' type='hidden' value='{$tv['module']}'>
        <input id='returnToListUrl' type='hidden' value='{$pager->getReturnToListLinkAfterDelete()}'>
        ";

        return $text;
    }


    /**
     *
     */
    function getNewViewWrapper($html, $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl  = Zend_Registry::get('cpUrl');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $action = Zend_Registry::get('action');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module = $tv['module'];
        $returnUrl = $fn->getIssetParam($exp, 'returnUrl');
        $returnUrlText = ($returnUrl !='') ? "<input type='hidden' name='returnUrl' value='{$returnUrl}'>" : '';
        
        $returnUrlPfxText = '';
        if (CP_SCOPE == 'www'){
            $returnUrlPfxText = "<input type='hidden' name='returnUrlPfx' value='{$cpUrl->getUriWithNoQstr()}'>";
            $formAction = '/index.php?_spAction=add';
        } else {
            $formAction  = str_replace("_action=new", "_spAction=add", $pager->searchQueryString);
        }

        $strPos = $cpUtil->stringPosition($formAction, "module");
        if ($strPos == -1) {
            $formAction  = $formAction . "&module=" . $module;
        }
        $formAction .= '&showHTML=0';
        
        $actBtns = '';
        if ($cpCfg['cp.showActionButtonsBelowNewForm']){
		    $actions = $action->getActionButtons('button');
            $actBtns = "
            <div class='type-button float_right hid actBtnsInNewForm'>
                {$actions}
            </div>
            ";
        }

        $formText = "
        <form id='frmNew' class='yform columnar cpJqForm' action='{$formAction}' method='post'>
            {$html}
            {$actBtns}
            {$returnUrlText}
            {$returnUrlPfxText}
        </form>
        <input id='room_name' type='hidden' value='{$tv['module']}'>
        ";

        if (CP_SCOPE == 'admin'){
            $text = "
            <div class='subcolumns'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$formText}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='subcr rightPanel'>
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            {$formText}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getSearchViewWrapper($html){
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php";

        $text = "
        <div class='subcolumns'>
            <div class='c50l'>
                <div class='subcl'>
                    <form name='search' class='yform columnar' action='{$formAction}' method='get'>
                        <input name='_topRm' type='hidden' value='{$tv['topRm']}'>
                        <input name='module' type='hidden' value='{$tv['module']}'>
                        <input name='_action' type='hidden' value='list'>
                        <input name='searchDone' type='hidden' value='1'>
                        {$html}
                    </form>
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr rightPanel'>
                </div>
            </div>
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getLinkListViewWrapper($html){
        return $html;
    }

    /**
     *
     */
    function getLinkDetailViewWrapper($html){
        return $html;
    }

    /**
     *
     */
    function getLinkEditViewWrapper($html){
        return $html;
    }

    /**
     *
     */
    function getLinkNewViewWrapper($html){
        return $html;
    }

    /**
     *
     */
    function getWidgetWrapper($html, $widget, $widgetCssClass, $widgetObj){
        $cpCfg = Zend_Registry::get('cpCfg');

    	$name = 'w-' . str_replace('_', '-', $widget);

    	$appendCls = ($widgetCssClass != '') ? " {$widgetCssClass}" : '';

        /*
        $widgetUrl = '';
        foreach($widgetObj as $var => $value) {
            if(!is_object($value)){
                $widgetUrl .= "&{$var}={$value}";
            }
        }

        $widgetUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?widget={$widget}&_spAction=widget&showHTML=0{$widgetUrl}";
    	$text = "
    	<div class='{$name}{$appendCls}'>
            {$html}
            <input type='hidden' name='{$name}_url' value='{$widgetUrl}' />
    	</div>
    	";
    	*/

        $text = '';
        if ($html !=''){
            $loadingClass = $widgetObj->hideWidgetOnLoading ? 'cpwLoading' : '';
            $text = "
            <div class='wdWrapper {$loadingClass}'>
                <div class='{$name}{$appendCls}'>
                    {$html}
                </div>
            </div>
            ";
        } else if ($widgetObj->wrapWidgetForEmptyFoundset){
            $text = "
            <div class='{$name}{$appendCls}'>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getWidgetPropertiesInHiddenVariable($widget, $widgetObj){
        $cpCfg = Zend_Registry::get('cpCfg');

    	$name = 'w-' . str_replace('_', '-', $widget);

        $hiddenVars = '';
        foreach($widgetObj as $var => $value) {
            if(!is_object($value)){
                if(is_bool($value)){//to replace true / false with 1 & 0
                    $value = ($value) ? 1 : 0;
                }

                $hiddenVars .= "<input type='hidden' name='{$name}_{$var}' value='{$value}' />\n";
            }
        }

        return $hiddenVars;
    }

    /**
     *
     */
    function getPluginPropertiesInHiddenVariable($plugin, $pluginObj){
        $cpCfg = Zend_Registry::get('cpCfg');

    	$name = 'p-' . str_replace('_', '-', $plugin);

        $hiddenVars = '';
        foreach($pluginObj as $var => $value) {
            if(!is_object($value)){
                if(is_bool($value)){//to replace true / false with 1 & 0
                    $value = ($value) ? 1 : 0;
                }
                $value = urlencode($value);

                $hiddenVars .= "<input type='hidden' name='{$name}_{$var}' value='{$value}' />\n";
            }
        }

        return $hiddenVars;
    }

    /**
     *
     */
    function getPluginViewWrapper($html, $plugin, $pluginObj){
        $cpCfg = Zend_Registry::get('cpCfg');
    	$name = 'p-' . str_replace('_', '-', $plugin);

        $pluginUrl = '';
        foreach($pluginObj as $var => $value) {
            if(!is_object($value)){
                $value = urlencode($value);
                $pluginUrl .= "&{$var}={$value}";
            }
        }

        $pluginUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin={$plugin}&_spAction=view&showHTML=0{$pluginUrl}";

    	$text = "
    	<div class='{$name}'>
    		{$html}
    		<input type='hidden' name='{$name}_url' value='{$pluginUrl}' />
    	</div>
    	";
        return $text;
    }

    /**
     *
     */
    function getPageCSSClass(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $recType = $tv['currentViewRecType'];
        $recType = $cpUrl->getUrlText($recType);
        $cfgName = "cp.viewRecType.{$recType}.pageCSSClass";
        $cfgNameWithAction = "cp.viewRecType.{$recType}.{$tv['action']}.pageCSSClass";
        $cfgNameWithModule = "m." . str_replace('_', '.', $tv['module']) . ".pageCSSClass";
        $cfgNameWithModuleWithAction = "m." . str_replace('_', '.', $tv['module']) . ".{$tv['action']}.pageCSSClass";
        if ($tv['forcedPageCSSClass'] != ''){
            return $tv['forcedPageCSSClass'];
        } else if (isset($cpCfg[$cfgNameWithAction])){
            return $cpCfg[$cfgNameWithAction];
        } else if (isset($cpCfg[$cfgName])){
            return $cpCfg[$cfgName];
        } else if (isset($cpCfg[$cfgNameWithModuleWithAction])){
            return $cpCfg[$cfgNameWithModuleWithAction];
        } else if (isset($cpCfg[$cfgNameWithModule])){
            return $cpCfg[$cfgNameWithModule];
        } else {
            return $cpCfg['cp.defaultPageCssClass'];
        }
    }

    /**
     *
     */
    function getPageCSSClassTop(){
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');
        $text = 'rt-' . $cpUrl->getUrlText($tv['currentViewRecType']);

        $text .= ' ' . $tv['pageCSSClassTop'];
        
        return $text;
    }
}
