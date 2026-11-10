<?php
/*
 *
 */
function firephp($print, $name) {
    require_once('include/FirePHPCore/FirePHP.class.php');
    $firephp = FirePHP::getInstance(true);
    $firephp->log($print, $name);
}

//=============================================================//
function qstr($value, $replaceHTMLSpChars = true) {
    $replacedValue = $value;

    if(is_array($value)){
        return $value;
    }

    //$replacedValue = str_replace("'", "\'", $value);
    //*** if the value is a number then no need to escape it

    if (!is_numeric($value) && $value != '') {
        $db = Zend_Registry::get('db');
        $replacedValue = $db->db_connect_id->real_escape_string($value);
    }

    if ($replaceHTMLSpChars) {
        return htmlspecialchars($replacedValue);
    } else {
        return $replacedValue;
    }

}

//=============================================================//
function escapeForDb($value) {
    $replacedValue = $value;

    if (!is_numeric($value) && $value != '') {
        $db = Zend_Registry::get('db');
        $replacedValue = $db->db_connect_id->real_escape_string($value);
    }

    return $replacedValue;
}

//=============================================================//
function qstrNum($value) {
    return (int)$value;
}

//=============================================================//
function qstrRev($value) {
    return stripslashes($value);
}


//=============================================================//
if( !function_exists('json_encode') || !function_exists('json_decode')) {
    include("lib_php/JSON/JSON.php");
}

//=============================================================//
// Future-friendly json_encode
if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

/**
 * to detect mobile browsers
 * check the script at http://detectmobilebrowsers.com/
 * update this regex periodically from http://detectmobilebrowsers.com/
 * @return boolean [description]
 */
function isMobileBrowser(){
    $isMobile = false;
    $useragent=$_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
        $isMobile = true;
    }
    return $isMobile;
}


//=============================================================//
// Future-friendly json_decode
if( !function_exists('json_decode') ) {
    function json_decode($data, $bool) {
        if ($bool) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON();
        }
        return( $json->decode($data) );
    }
}


/**
 * @param <type> $name
 * @param <type> $defaultValue
 * @return <type>
 */
function getReqParam($name, $defaultValue = '', $qstr = false, $is_Array = false, $exp = array()) {
    $retVal = '';

    if($is_Array){
        $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : array();
    } else {
        $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
    }

    if ($qstr){
        $value = qstr($value);
    }

    $strip_tags = isset($exp['strip_tags']) ? $exp['strip_tags'] : '';
    if ((CP_SCOPE == 'www' && $strip_tags == '') || $strip_tags == true){
        $value = strip_tags($value);
    }

    if($value != ''){
        $retVal = $value;
    } else {
        $retVal = $defaultValue;
    }

    return $retVal;
}

function getXSSFilter($value) {
    $retVal = '';

    if($is_Array){
        $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : array();
    } else {
        $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
    }

    if ($qstr){
        $value = qstr($value);
    }

    $strip_tags = isset($exp['strip_tags']) ? $exp['strip_tags'] : '';
    if ((CP_SCOPE == 'www' && $strip_tags == '') || $strip_tags == true){
        $value = strip_tags($value);
    }

    if($value != ''){
        $retVal = $value;
    } else {
        $retVal = $defaultValue;
    }

    return $retVal;
}

/**
 *
 */
function trace() {
    $cpCfg = Zend_Registry::get('cpCfg');
    if($cpCfg[CP_ENV]['display_errors']){

        $raw = debug_backtrace();

        $output ="
        <table border=\"1\" cellpadding=\"5\">
        <thead>
            <tr>
                <th>File</th>
                <th>Function</th>
                <th>Args</th>
            </tr>
        </thead>
        ";

        foreach($raw as $entry){
            $output.="<tr>";
            $output.="<td>".$entry['file']." (Line: ".$entry['line'].")</td>";
            $output.="<td>".$entry['function']."</td>";
            $output.="</tr>";
        }

        $output .= "</table>";


        print $output;
    }
}

//=============================================================//
function includeCPClass($fileType, $fileName, $className = '', $returnInstance = true, $exp = array()) {
    $cpCfg = Zend_Registry::get('cpCfg');

    $fileName = ucfirst($fileName);

    $args = isset($exp['args']) ? $exp['args'] : '';
    $throwError = isset($exp['throwError']) ? $exp['throwError'] : true;

    $modGrpName = '';
    $modName    = '';

    $mainFolderArr = array(
         'Lib'        => 'lib'
        ,'ModGroup'   => 'modules'
        ,'Module'     => 'modules'
        ,'ModuleView' => 'modules'
        ,'ModuleModel'=> 'modules'
        ,'ModuleFns'  => 'modules'
        ,'Widget'     => 'widgets'
        ,'WidgetView' => 'widgets'
        ,'WidgetModel'=> 'widgets'
        ,'WidgetFns'  => 'widgets'
        ,'Plugin'     => 'plugins'
        ,'PluginView' => 'plugins'
        ,'PluginModel'=> 'plugins'
        ,'PluginFns'  => 'plugins'
        ,'Theme'      => 'themes'
        ,'ThemeView'  => 'themes'
        ,'ThemeModel' => 'themes'
        ,'ThemeFns'   => 'themes'
        ,'ThemeHook'  => 'themes'
    );

    $folderMain = isset($mainFolderArr[$fileType]) ? $mainFolderArr[$fileType] : 'modules';
    $folder1 = ucfirst($folderMain);
    $folder2 = '';
    $folder3 = '';
    $file    = '';

    if($fileType == 'Theme'){
        /** ex: filename = Trading, folder will be Trading/Lib/Functions.php **/
        $folder2 = $fileName;
        $fileName = 'Controller';
    } else if($fileType == 'ThemeHook'){
        $folder2 =  $cpCfg['cp.theme'];
        $folder3 = 'Hooks';
        $fileName = $fileName;
    } else if($fileType == 'ModGroup'){
        /** ex: filename = Trading, folder will be Trading/Lib/Functions.php **/
        $folder2 = $fileName;
        $folder3 = 'Lib';
        $fileName = $className;
    } else if($fileType != 'Lib'){
        /** ex: filename = Core_Staff, folder will be Core/Staff/Controller.php **/
        $arr = explode('_', $fileName);
        $folder2 = ucfirst($arr[0]);
        $folder3 = isset($arr[1]) ? ucfirst($arr[1]) : '';
        $fileName = 'Controller';
    }

    if($fileType == 'Module' || $fileType == 'ModuleLink'){
        include_once(CP_PATH . 'common/lib/ModuleControllerAbstract.php');
        include_once(CP_PATH . 'common/lib/ModuleViewAbstract.php');
        include_once(CP_PATH . 'common/lib/ModuleModelAbstract.php');
        include_once(CP_PATH . 'common/lib/ModuleFunctionsAbstract.php');

        include_once(CP_PATH . 'common/lib/ModuleLinkControllerAbstract.php');
        include_once(CP_PATH . 'common/lib/ModuleLinkViewAbstract.php');
        include_once(CP_PATH . 'common/lib/ModuleLinkModelAbstract.php');

    } else if ($fileType == 'Plugin'){
        include_once(CP_PATH . 'common/lib/PluginControllerAbstract.php');
        include_once(CP_PATH . 'common/lib/PluginViewAbstract.php');
        include_once(CP_PATH . 'common/lib/PluginModelAbstract.php');

    } else if ($fileType == 'Widget'){
        include_once(CP_PATH . 'common/lib/WidgetControllerAbstract.php');
        include_once(CP_PATH . 'common/lib/WidgetViewAbstract.php');
        include_once(CP_PATH . 'common/lib/WidgetModelAbstract.php');

    } else if($fileType == 'Theme'){
        include_once(CP_PATH . 'common/lib/ThemeControllerAbstract.php');
        include_once(CP_PATH . 'common/lib/ThemeViewAbstract.php');
        include_once(CP_PATH . 'common/lib/ThemeModelAbstract.php');

        if ($cpCfg['cp.cssFramework'] == 'bootstrap'){
            include_once(CP_MASTER_PATH . 'lib/ThemeControllerAbstractBootstrap.php');
            include_once(CP_MASTER_PATH . 'lib/ThemeViewAbstractBootstrap.php');
            include_once(CP_MASTER_PATH . 'lib/ThemeModelAbstractBootstrap.php');
        } else {
            include_once(CP_MASTER_PATH . 'lib/ThemeControllerAbstract.php');
            include_once(CP_MASTER_PATH . 'lib/ThemeViewAbstract.php');
            include_once(CP_MASTER_PATH . 'lib/ThemeModelAbstract.php');
        }
    } else if($fileType == 'ThemeHook'){
    }

    if ($fileType == 'ModuleView'
     || $fileType == 'PluginView'
     || $fileType == 'WidgetView'
     || $fileType == 'ThemeView'){
        $fileName = 'View';

    } else if ($fileType == 'ModuleModel'
            || $fileType == 'PluginModel'
            || $fileType == 'WidgetModel'
            || $fileType == 'ThemeModel'){
        $fileName = 'Model';

    } else if ($fileType == 'ModuleFns'
            || $fileType == 'PluginFns'
            || $fileType == 'WidgetFns'
            || $fileType == 'ThemeFns'){
        $fileName = 'Functions';
    }

    if($fileType == 'ModuleView'){
        include_once(CP_PATH . 'common/lib/ModuleViewAbstract.php');

    } else if($fileType == 'ModuleModel'){
        include_once(CP_PATH . 'common/lib/ModuleModelAbstract.php');

    } else if($fileType == 'ModuleFns'){
        include_once(CP_PATH . 'common/lib/ModuleFunctionsAbstract.php');
    }

    $folder2_path = ($folder2 != '') ? "{$folder2}/" : '';
    $folder3_path = ($folder3 != '') ? "{$folder3}/" : '';

    $file = ($file != '') ? "{$file}" : "{$fileName}.php";

    $pathCommon = CP_PATH . "common/{$folderMain}/{$folder2_path}{$folder3_path}{$file}";
    $pathMaster = CP_PATH . CP_SCOPE . "/{$folderMain}/{$folder2_path}{$folder3_path}{$file}";
    $pathLocal  = CP_LOCAL_PATH. "{$folderMain}/{$folder2_path}{$folder3_path}{$file}";

    $folder2_name = ($folder2 != '') ? "{$folder2}_" : '';
    $folder3_name = ($folder3 != '') ? "{$folder3}_" : '';

    $clsCommon = CP_NAMESPACE . "Common_{$folder1}_{$folder2_name}{$folder3_name}{$fileName}";
    $clsMaster = CP_NAMESPACE . ucfirst(CP_SCOPE) . "_{$folder1}_{$folder2_name}{$folder3_name}{$fileName}";
    $clsLocal  = CP_NAMESPACE_LOCAL . ucfirst(CP_SCOPE) . "_{$folder1}_{$folder2_name}{$folder3_name}{$fileName}";

    //print "<b>File Type: {$fileType}</b><br><b>File Name: {$fileName}</b><br><b>Class Name: {$className}</b><br><b>Folder1: {$folder1}</b><br><b>Folder2: {$folder2}</b><br><b>Folder3: {$folder3}</b><br>";
    //print "Common Path: {$pathCommon} <br>Master Path: {$pathMaster} <br>Local Path: {$pathLocal} <br><br>";
    //print "Common Class: {$clsCommon} <br>Master Class: {$clsMaster} <br>Local Path: {$clsLocal} <hr>";

    Zend_Registry::set('cpClsObjFound', false);

    $obj = includeCPClass2($pathCommon, $pathMaster, $pathLocal, $clsCommon,
                           $clsMaster, $clsLocal, $args, $returnInstance);
    $objFound = Zend_Registry::get('cpClsObjFound');

    if (is_object($obj) || $objFound){
        return $obj;

    } else if ($cpCfg['cp.hasAlternativeCore']){
        $pathCommon2 = CP_PATH2 . "common/{$folderMain}/{$folder2_path}{$folder3_path}{$file}";
        $pathMaster2 = CP_PATH2 . CP_SCOPE . "/{$folderMain}/{$folder2_path}{$folder3_path}{$file}";

        Zend_Registry::set('cpClsObjFound', false);
        $obj2 = includeCPClass2($pathCommon2, $pathMaster2, $pathLocal, $clsCommon,
                                $clsMaster, $clsLocal, $args, $returnInstance);
        $obj2Found = Zend_Registry::get('cpClsObjFound');

        if ($obj2Found){
            return $obj2;
        } else if ($throwError){
            throwError($pathCommon, $pathMaster, $pathLocal, $clsCommon, $clsMaster, $clsLocal);
        }

    } else if ($throwError){
        throwError($pathCommon, $pathMaster, $pathLocal, $clsCommon, $clsMaster, $clsLocal);
    }
}

//=============================================================//
function throwError($pathCommon, $pathMaster, $pathLocal, $clsCommon, $clsMaster, $clsLocal) {
    $cpCfg = Zend_Registry::get('cpCfg');

    $pathCommon = htmlspecialchars($pathCommon);
    $pathMaster = htmlspecialchars($pathMaster);
    $pathLocal = htmlspecialchars($pathLocal);

    $clsCommon = htmlspecialchars($clsCommon);
    $clsMaster = htmlspecialchars($clsMaster);
    $clsLocal = htmlspecialchars($clsLocal);

    $err = "<hr>Common Path: {$pathCommon} <br>Master Path: {$pathMaster} <br>Local Path: {$pathLocal} <br><br>";
    $err2 = "Common Class: {$clsCommon} <br>Master Class: {$clsMaster} <br>Local Path: {$clsLocal} <hr>";
    if($cpCfg[CP_ENV]['display_errors']){
        print $err;
        print $err2;
        trace();
    }
    exit();
}

//=============================================================//
function includeCPClass2($pathCommon, $pathMaster, $pathLocal, $clsCommon, $clsMaster, $clsLocal, $args, $returnInstance) {
    Zend_Registry::set('cpClsObjFound', true);

    if ($pathCommon != ''
        && file_exists($pathCommon)
        && file_exists($pathMaster)
        && file_exists($pathLocal)
    ) {
        // core, master & local files exist
        include_once($pathCommon);
        include_once($pathMaster);
        include_once($pathLocal);

        if($returnInstance) {
            return new $clsLocal($args);
        }

    } else if ($pathCommon != '' && file_exists($pathCommon) && file_exists($pathMaster)) {
        // core & master files exist
        include_once($pathCommon);
        include_once($pathMaster);

        if($returnInstance) {
            return new $clsMaster($args);
        }

    } else if ($pathCommon != ''
        && file_exists($pathCommon)
        && file_exists($pathLocal)
    ) {
        // core & local files exist
        include_once($pathCommon);
        include_once($pathLocal);

        if($returnInstance) {
            return new $clsLocal($args);
        }

    } else if ($pathCommon != '' && file_exists($pathCommon)) {
        // core file exists
        include_once($pathCommon);

        if($returnInstance) {
            return new $clsCommon($args);
        }

    } else if (file_exists($pathMaster) && file_exists($pathLocal)) {
        // master & local files exist
        include_once($pathMaster);
        include_once($pathLocal);

        if($returnInstance) {
            return new $clsLocal($args);
        }

    } else if (file_exists($pathMaster)) {
        // master file exists
        include_once($pathMaster);

        if($returnInstance) {
           return new $clsMaster($args);
        }
    }  else if (file_exists($pathLocal)) {
        // master file exists
        include_once($pathLocal);

        if($returnInstance) {  return new $clsLocal($args);
        }


    } else {
        Zend_Registry::set('cpClsObjFound', false);
    }
}


//=============================================================//
function getCPModuleObj($module, $includeJSSFiles = false) {
    $cpCfg = Zend_Registry::get('cpCfg');

    $contObj  = includeCPClass('Module', $module, '', true, array('throwError' => true));
    $modelObj = includeCPClass('ModuleModel', $module, '', true, array('throwError' => true));
    $viewObj  = includeCPClass('ModuleView', $module, '', true, array('throwError' => true));
    $fnsObj   = includeCPClass('ModuleFns', $module, '', true, array('throwError' => true));

    $contObj->model = $modelObj;
    $contObj->view = $viewObj;
    $contObj->fns = $fnsObj;
    $contObj->name = $module;

    $modelObj->controller = $contObj;
    $modelObj->view = $contObj->view;
    $modelObj->fns = $contObj->fns;

    $viewObj->controller = $contObj;
    $viewObj->model = $contObj->model;
    $viewObj->fns = $contObj->fns;

    $fnsObj->controller = $contObj;
    $fnsObj->model = $contObj->model;
    $fnsObj->view = $contObj->view;

    if ($includeJSSFiles){
        $viewObj->includeJSSFiles();
    }

    return $contObj;
}

//=============================================================//
function getCPPluginObj($plugin, $includeJSSFiles = true) {
    $cpCfg = Zend_Registry::get('cpCfg');

    //if $includeJSSFiles then we need to specify the widget name in
    //$cpCfg['cp.availableWidgets']
    if ($includeJSSFiles && !in_array($plugin, $cpCfg['cp.availablePlugins'])) {
        $error = includeCPClass('Lib', 'Errors', 'Errors');
        $exp = array(
            'replaceArr' => array(
                 'plugin' => $plugin
            )
        );
        print $error->getError('missingPluginInAvailableArr', $exp);
        exit();
    }

    $contObj  = includeCPClass('Plugin', $plugin);
    $modelObj = includeCPClass('PluginModel', $plugin);
    $viewObj  = includeCPClass('PluginView', $plugin);
    $fnsObj   = includeCPClass('PluginFns', $plugin);

    $contObj->model = $modelObj;
    $contObj->view = $viewObj;
    $contObj->fns = $fnsObj;
    $contObj->name = $plugin;

    $modelObj->controller = $contObj;
    $modelObj->view = $contObj->view;
    $modelObj->fns = $contObj->fns;

    $viewObj->controller = $contObj;
    $viewObj->model = $contObj->model;
    $viewObj->fns = $contObj->fns;

    if ($includeJSSFiles){
        $viewObj->includeJSSFiles();
    }

    return $contObj;
}

//=============================================================//
function getCPWidgetObj($widget, $includeJSSFiles = true) {
    $cpCfg = Zend_Registry::get('cpCfg');

    //if $includeJSSFiles then we need to specify the widget name in
    //$cpCfg['cp.availableWidgets']
    if ($includeJSSFiles && !in_array($widget, $cpCfg['cp.availableWidgets']) && $cpCfg[CP_ENV]['display_errors']) {
        trace();
        exit("{$widget} is missing in cp.availableWidgets");
    }

    $contObj  = includeCPClass('Widget', $widget);
    $modelObj = includeCPClass('WidgetModel', $widget);
    $viewObj  = includeCPClass('WidgetView', $widget);
    $fnsObj   = includeCPClass('WidgetFns', $widget);

    $contObj->model = $modelObj;
    $contObj->view = $viewObj;
    $contObj->fns = $fnsObj;
    $contObj->name = $widget;

    $modelObj->controller = $contObj;
    $modelObj->view = $contObj->view;
    $modelObj->fns = $contObj->fns;

    $viewObj->controller = $contObj;
    $viewObj->model = $contObj->model;
    $viewObj->fns = $contObj->fns;

    if ($includeJSSFiles && !$cpCfg['cp.doNotLoadJSSFilesForWidgets']){
        $viewObj->includeJSSFiles();
    }

    return $contObj;
}

//=============================================================//
function getCPThemeObj($theme, $includeJSSFiles = true) {
    $contObj  = includeCPClass('Theme', $theme);
    $modelObj = includeCPClass('ThemeModel', $theme);
    $viewObj  = includeCPClass('ThemeView', $theme);
    $fnsObj   = includeCPClass('ThemeFns', $theme);

    $contObj->model = $modelObj;
    $contObj->view = $viewObj;
    $contObj->fns = $fnsObj;
    $contObj->name = $theme;

    $modelObj->controller = $contObj;
    $modelObj->view = $contObj->view;
    $modelObj->fns = $contObj->fns;

    $viewObj->controller = $contObj;
    $viewObj->model = $contObj->model;
    $viewObj->fns = $contObj->fns;

    if ($includeJSSFiles){
        $viewObj->includeJSSFiles();
        $viewObj->includeJSSFiles();
    }

    return $contObj;
}


//=============================================================//
function getCPModGrpFn($modGrop) {
    $fnsObj = includeCPClass('ModGroup', $modGrop, 'Functions');
    return $fnsObj;
}

//=============================================================//
function getCPModelObj($module) {
    $modelObj = includeCPClass('ModuleModel', $module);
    return $modelObj;
}

//=============================================================//
function getCPViewObj($module) {
    $modelObj = includeCPClass('ModuleView', $module);
    return $modelObj;
}

//=============================================================//
function getCPFnObj($module) {
    $modelObj = includeCPClass('ModuleFns', $module);
    return $modelObj;
}

//=============================================================//
function isHttps() {
    $ret = false;
    $https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
    if ($https == "on") {
        $ret = true;
    }
    return $ret;
}

//=============================================================//
function getCPHook($type, $name, $fnName, $data = array(), $obj = '') {
    $cpCfg = Zend_Registry::get('cpCfg');

    $theme = getCPThemeObj($cpCfg['cp.theme'], false);

    $hookObj = $theme->fns;
    $arr = explode('_', $name);
    $hookFnName = 'get' . ucfirst($type) . ucfirst($arr[0]) . ucfirst($arr[1]) . ucfirst($fnName) . 'Hook'; //getModuleWebBasicListHook

    //** check whether any separate file for hook exists, if so search in that ***//
    $hookFileName = $type . ucfirst($arr[0]) . ucfirst($arr[1]);
    $hookFileObj = includeCPClass('ThemeHook', $hookFileName, '', true, array('throwError' => false));
    $hookFnName2 = 'get' . ucfirst($fnName);

    if (is_object($hookFileObj) && method_exists($hookFileObj, $hookFnName2)){
        $hookObj = $hookFileObj;
        $hookFnName = $hookFnName2;
    }

    $found = false;
    if (method_exists($hookObj, $hookFnName)){
        if ($type == 'Widget' || $type == 'Plugin' ){

    	    $returnVal = $hookObj->$hookFnName($obj, $data);
            //we realy wanted to have the array index 'returnVal' only
            //we have 'html' array index for the sake of backward comaptibility
            //as it has been used in many places also in the local files
            //the reason we wanted to deprecate 'html' since the hook can return
            //non html values like arrays/objects so that would be misleading
    	    return array('status' => true
                        ,'html' => $returnVal
                        ,'returnVal' => $returnVal
            );
    	} else {

    	    if (is_array($data) || $data != ''){
                $returnVal = $hookObj->$hookFnName($data, $obj);
                return array('status' => true
                            ,'html' => $returnVal
                            ,'returnVal' => $returnVal
                );
    	    } else {
                $returnVal = $hookObj->$hookFnName($obj);
                return array('status' => true
                            ,'html' => $returnVal
                            ,'returnVal' => $returnVal
                );
    	    }
    	}
    } else {
        return array('status' => false, 'html' => '');
    }

}

function getCPModuleHook($name, $fnName, $data = array(), $obj = '') {
    return getCPHook('Module', $name, $fnName, $data, $obj);
}

function getCPModuleHook2($name, $fnName, $obj = '') {
    return getCPHook('Module', $name, $fnName, '', $obj);
}

function getCPWidgetHook($name, $fnName, $obj = '', $data = array()) {
    return getCPHook('Widget', $name, $fnName, $data, $obj);
}

function getCPPluginHook($name, $fnName, $obj = '', $data = '') {
    return getCPHook('Plugin', $name, $fnName, $data, $obj);
}


// lcfirst fix for php < 5.3
if ( false === function_exists('lcfirst') ):
    function lcfirst( $str )
    { return (string)(strtolower(substr($str,0,1)).substr($str,1));}
endif;
