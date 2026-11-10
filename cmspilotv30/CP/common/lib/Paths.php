<?
/**
 *
 */
class CP_Common_Lib_Paths
{
    /**
     *
     * @param <type> $scope
     * @param <type> $forJSS
     * @return <type> 
     */
    function getAdminWWWPath($scope = 'master', $forJSS = false){
        $cpCfg = Zend_Registry::get('cpCfg');

        $path = '';

        if ($scope == 'local') {
            if ($forJSS){
                $path = $cpCfg['cp.localPathAlias'];
            } else {
                $path = $cpCfg['cp.localPath'];
            }
        } else if ($scope == 'master') {
            if ($forJSS){
                $path = $cpCfg['cp.masterPathAlias'];
            } else {
                $path = $cpCfg['cp.masterPath'];
            }
        }

        return $path;
    }

    /**
     *
     * @param <type> $module
     * @param <type> $scope
     * @param <type> $forJSS
     * @return <type>
     */
    function getModuleFolder($module='', $scope = 'master', $forJSS = false){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $path = $this->getAdminWWWPath($scope, $forJSS);
        $modInfo = explode('_', $module);
        $folder1 = ucfirst($modInfo[0]);
        $folder2 = ucfirst($modInfo[1]);
        $folder = "{$path}modules/{$folder1}/{$folder2}/";

        return $folder;
    }

    /**
     *
     * @param <type> $module
     * @param <type> $scope
     * @return <type>
     */
    function getModuleFolderForJSSInc($module='', $scope = 'master'){
        return $this->getModuleFolder($module, $scope, true);
    }

    /**
     *
     * @param <type> $module
     * @param <type> $scope
     * @param <type> $forJSS
     * @return <type>
     * returns module group folder fullpath
     * for ex: /var/www/vhosts/cmsPilot/v2.9.5/admin/modules/mg_trading/css/content.css
     *
     */
    function getModuleGroupFolder($module='', $scope = 'master', $forJSS = false){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $path = $this->getAdminWWWPath($scope, $forJSS);
        $modInfo = explode('_', $module);
        $folder1 = ucfirst($modInfo[0]);
        $folder = "{$path}modules/{$folder1}/";

        return $folder;
    }

    /**
     *
     * @param <type> $module
     * @param <type> $scope
     * @return <type>
     * returns module group folder relative to the web root
     * for ex: /cmspilotv295/admin/modules/mg_trading/css/content.css
     */
    function getModuleGroupFolderForJSSInc($module='', $scope = 'master'){
        return $this->getModuleGroupFolder($module, $scope, true);
    }

    /**
     *
     * @param <type> $widget
     * @param <type> $scope
     * @param <type> $forJSS
     * @return <type>
     */
    function getWidgetFolder($widget, $scope = 'master', $forJSS = false){
        $cpCfg = Zend_Registry::get('cpCfg');

        $path = $this->getAdminWWWPath($scope, $forJSS);
        $widgetInfo = explode('_', $widget);
        $folder1 = ucfirst($widgetInfo[0]);
        $folder2 = ucfirst($widgetInfo[1]);
        $folder = "{$path}widgets/{$folder1}/{$folder2}/";

        return $folder;
    }

    /**
     *
     * @param <type> $widget
     * @param <type> $scope
     * @return <type>
     */
    function getWidgetFolderForJSSInc($widget, $scope = 'master'){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        return $this->getWidgetFolder($widget, $scope, true);
    }

    /**
     *
     * @param <type> $plugin
     * @param <type> $scope
     * @param <type> $forJSS
     * @return <type>
     */
    function getPluginFolder($plugin, $scope = 'master', $forJSS = false){
        $cpCfg = Zend_Registry::get('cpCfg');

        $path = $this->getAdminWWWPath($scope, $forJSS);
        $pluginInfo = explode('_', $plugin);
        $folder1 = ucfirst($pluginInfo[0]);
        $folder2 = ucfirst($pluginInfo[1]);
        $folder = "{$path}plugins/{$folder1}/{$folder2}/";

        return $folder;
    }

    /**
     *
     * @param <type> $plugin
     * @param <type> $module
     * @param <type> $scope
     * @return <type>
     */
    function getPluginFolderForJSSInc($plugin, $module='', $scope = 'master'){
        return $this->getPluginFolder($plugin, $scope, true);
    }

    /**
     *
     * @param <type> $theme
     * @param <type> $scope
     * @param <type> $forJSS
     * @return <type>
     */
    function getThemesFolder($theme, $scope = 'master', $forJSS = false){
        $cpCfg = Zend_Registry::get('cpCfg');

        $path = $this->getAdminWWWPath($scope, $forJSS);
        $theme = ucfirst($theme);
        $folder = "{$path}themes/{$theme}/";

        return $folder;
    }

    /**
     *
     * @param <type> $theme
     * @param <type> $scope
     * @return <type>
     */
    function getThemesFolderForJSSInc($theme, $scope='master'){
        return $this->getThemesFolder($theme, $scope, true);
    }

    /**
     *
     * @param <type> $file
     * @param <type> $scope
     * @return <type>
     */
    function getFilePathByTheme($file, $scope=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $theme = $cpCfg['cp.theme'];

        $themePathMast = $this->getThemesFolder($cpCfg['cp.theme']);
        $themePathLoc  = $this->getThemesFolder($cpCfg['cp.theme'], 'local');
        $themePathDef  = $this->getThemesFolder($cpCfg['cp.defaultTheme']);

        /************************************************************/
        if (file_exists("{$themePathLoc}{$file}")){
            return "{$themePathLoc}{$file}";
        } else if (file_exists("{$themePathMast}{$file}")){
            return "{$themePathMast}{$file}";
        } else {
            return "{$themePathDef}{$file}";
        }
    }

    /**
     *
     */
    function getTheLastFile($type, $name, $fileName, $alias = false){
        $modInfo = explode('_', $name);
        $folder1 = ucfirst($modInfo[0]);
        $folder2 = isset($modInfo[1]) ?  '/' . ucfirst($modInfo[1]) : '';
        $folder = $folder1 . $folder2;
        
        $const = strtoupper($type);
        
        $aliasPostFix = ($alias === true) ? "_ALIAS" : '';
            
        $path1 = constant("CP_{$const}_PATH_COMMON") . $folder . "/{$fileName}";
        $path2 = constant("CP_{$const}_PATH"). $folder . "/{$fileName}";
        $path3 = constant("CP_{$const}_PATH_LOCAL"). $folder . "/{$fileName}";
        
        $path4 = '';
        $path5 = '';
        
        if (defined('CP_PATH2')){
            $path4 = constant("CP_{$const}_PATH2_COMMON") . $folder . "/{$fileName}";
            $path5 = constant("CP_{$const}_PATH2"). $folder . "/{$fileName}";
        }

        $path1Actual = constant("CP_{$const}_PATH_COMMON{$aliasPostFix}") . $folder . "/{$fileName}";
        $path2Actual = constant("CP_{$const}_PATH{$aliasPostFix}"). $folder . "/{$fileName}";
        $path3Actual = constant("CP_{$const}_PATH_LOCAL{$aliasPostFix}"). $folder . "/{$fileName}";
        
        $path4Actual = '';
        $path5Actual = '';
        
        if (defined('CP_PATH2')){
            $path4Actual = constant("CP_{$const}_PATH2_COMMON{$aliasPostFix}") . $folder . "/{$fileName}";
            $path5Actual = constant("CP_{$const}_PATH2{$aliasPostFix}"). $folder . "/{$fileName}";
        }

        /***** Local ******/ 
        if (file_exists($path3)){
            return $path3Actual;
        }

        /***** Master ******/ 
        if (file_exists($path2)){
            return $path2Actual;
        }

        /***** CP2 Master ******/ 
        if (file_exists($path5)){
            return $path5Actual;
        }

        /***** CP Common ******/
        if (file_exists($path1)){
            return $path1Actual;
        }

        /***** CP2 Common ******/
        if (file_exists($path4)){
            return $path4Actual;
        }
    }

    /**
     *
     */
    function getMinFilePath($type = 'absolute'){
        if ($type == 'absolute'){
            $minPath = realpath(CP_LOCAL_PATH) . "/min/";
        } else {
            $minPath = CP_LOCAL_PATH_ALIAS . "min/";
        }
        
        return $minPath;
    }

    function getAbsPathForRelativePath($file) {
        if (strpos($file, CP_CMSROOT_ALIAS) !== false){
            $absPath = str_replace(CP_CMSROOT_ALIAS, CP_CORE_PATH, $file);
        } else {
            $absPath = realpath(substr($file, 1)); /** to remove leading slash **/
        }
        return $absPath;
    }
}
