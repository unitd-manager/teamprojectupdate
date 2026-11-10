<?
/**
 *
 */
class CP_Common_Lib_Cfg
{

    /**
     *
     */
    function __construct(){

        $cpCfg = Zend_Registry::get('cpCfg');

        $version = phpversion();

        if($version >= 5.3) {
            error_reporting(E_ALL & ~E_DEPRECATED);
        } else {
            error_reporting(E_ALL);
        }

        if (isset($cpCfg[CP_ENV]['error_reporting'])) {
            error_reporting($cpCfg[CP_ENV]['error_reporting']);
        }
        ini_set('display_errors', $cpCfg[CP_ENV]['display_errors']);
    }

    /**
     *
     */
    function setConfigFiles($type, $name){
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($type == 'modGrp'){
            $path1 = CP_MODULES_PATH_COMMON . "{$name}/Lib/config.php";
            $path2 = CP_MODULES_PATH . "{$name}/Lib/config.php";
            $path3 = CP_MODULES_PATH_LOCAL . "{$name}/Lib/config.php";

        } else if ($type == 'theme'){
            $path1 = CP_THEMES_PATH_COMMON . "{$name}/config.php";
            $path2 = CP_THEMES_PATH . "{$name}/config.php";
            $path3 = CP_THEMES_PATH_LOCAL . "{$name}/config.php";

        } else if ($type == 'plugin'){
            $path1 = CP_PLUGINS_PATH_COMMON . "{$name}/config.php";
            $path2 = CP_PLUGINS_PATH . "{$name}/config.php";
            $path3 = CP_PLUGINS_PATH_LOCAL . "{$name}/config.php";

        } else if ($type == 'widget'){
            $path1 = CP_WIDGETS_PATH_COMMON . "{$name}/config.php";
            $path2 = CP_WIDGETS_PATH . "{$name}/config.php";
            $path3 = CP_WIDGETS_PATH_LOCAL . "{$name}/config.php";
        }

        $path4 = '';
        $path5 = '';

        if (defined('CP_PATH2')){
            if ($type == 'modGrp'){
                $path4 = CP_MODULES_PATH2_COMMON . "{$name}/Lib/config.php";
                $path5 = CP_MODULES_PATH2 . "{$name}/Lib/config.php";

            } else if ($type == 'theme'){
                $path4 = CP_THEMES_PATH2_COMMON . "{$name}/config.php";
                $path5 = CP_THEMES_PATH2 . "{$name}/config.php";
            }
        }

        $cfg1 = array();
        $cfg2 = array();
        $cfg3 = array();
        $cfg4 = array();
        $cfg5 = array();

        if (file_exists($path1)){
            $cfg1 = require_once($path1);
        }

        if (file_exists($path2)){
            $cfg2 = require_once($path2);
        }

        if (file_exists($path3)){
            $cfg3 = require_once($path3);
        }

        if (file_exists($path4)){
            $cfg4 = require_once($path4);
        }

        if (file_exists($path5)){
            $cfg5 = require_once($path5);
        }

        /***
        since require_once returns boolean if the file is already included,
        we just need to re-check if the returned value is an array
        ***/

        $cfg1 = is_array($cfg1) ? $cfg1 : array();
        $cfg2 = is_array($cfg2) ? $cfg2 : array();
        $cfg3 = is_array($cfg3) ? $cfg3 : array();

        $cfg4 = is_array($cfg4) ? $cfg4 : array();
        $cfg5 = is_array($cfg5) ? $cfg5 : array();

        $cpCfg = Zend_Registry::get('cpCfg');
        $cpCfg = array_merge($cpCfg, $cfg1, $cfg2, $cfg4, $cfg5, $cfg3);
        Zend_Registry::set('cpCfg',$cpCfg);
    }

    /**
     *
     */
    function loadAnyFile($type, $folder, $fileName){
        $const = strtoupper($type);
        $folder = ($folder != '') ? $folder . '/' : '';

        $path1 = constant("CP_{$const}_PATH_COMMON") . $folder . "{$fileName}";
        $path2 = constant("CP_{$const}_PATH"). $folder . "{$fileName}";
        $path3 = constant("CP_{$const}_PATH_LOCAL"). $folder . "{$fileName}";

        $path4 = '';
        $path5 = '';

        if (defined('CP_PATH2')){
            $path4 = constant("CP_{$const}_PATH2_COMMON") . $folder . "{$fileName}";
            $path5 = constant("CP_{$const}_PATH2"). $folder . "{$fileName}";
        }


        if (file_exists($path1)){
            require_once($path1);
        }

        if (file_exists($path4)){
            require_once($path4);
        }

        if (file_exists($path2)){
            require_once($path2);
        }

        if (file_exists($path5)){
            require_once($path5);
        }

        if (file_exists($path3)){
            require_once($path3);
        }
    }
}
