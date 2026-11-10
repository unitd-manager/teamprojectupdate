<?
abstract class CP_Www_Lib_ThemeViewAbstract extends CP_Common_Lib_ThemeViewAbstract
{
    /**
     *
     */
    function getMainThemeOutput() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $plugin = getReqParam('plugin');
        $widget = getReqParam('widget');

        if ($plugin != ''){
            $modInfo = explode('_', $plugin);
            $folderPfx = 'plugins';
        } else if ($widget != ''){
            $modInfo = explode('_', $widget);
            $folderPfx = 'widgets';
        } else {
            $modInfo = explode('_', $tv['module']);
            $folderPfx = 'modules';
        }

        $folder1 = ucfirst($modInfo[0]);
        $folder2 = isset($modInfo[1]) ?  '/' . ucfirst($modInfo[1]) : '';
        $folder = $folder1 . $folder2;

        $localThemeDir       = CP_THEMES_PATH_LOCAL . $cpCfg['cp.theme'] . '/templates';
        $masterThemeDir      = CP_THEMES_PATH . $cpCfg['cp.theme'] . '/templates';

        // create this folder when you want to organize all common files in a folder
        // eg: /www/themes/BootDefault/templates/shared
        $localThemeSharedDir = CP_THEMES_PATH_LOCAL . $cpCfg['cp.theme'] . '/templates/shared/';

        // create these folders when you want to extend module templates into the theme folder
        // eg: /www/themes/BootDefault/templates/modules or widgets or plugins
        // used when one site is using different themes in which you have a common fileset in modules folder and extend here
        $localThemeModDir    = CP_THEMES_PATH_LOCAL . $cpCfg['cp.theme'] . "/templates/{$folderPfx}/{$folder}/";

        // eg: CP/www/modules/WebBasic/Home/templates/
        $masterModuleDir = constant('CP_' . strtoupper($folderPfx) . '_PATH'). $folder . '/templates';

        // eg: /www/modules/WebBasic/Home/templates/
        $localModuleDir = constant('CP_' . strtoupper($folderPfx) . '_PATH_LOCAL'). $folder . '/templates';

        // eg: CP/www/templates/
        $baseDirMaster       = CP_MASTER_PATH . 'templates';

        // eg: /www/templates/
        $baseDirCommon       = CP_COMMON_PATH . 'templates';

        if ($plugin != ''){
            $modTemplateName = 'p-' . str_replace('_', '-', $plugin) . '-' . $tv['spAction'] . '.phtml';
        } else if ($widget != ''){
            $modTemplateName = 'w-' . str_replace('_', '-', $widget) . '-' . $tv['spAction'] . '.phtml';
        } else if ($tv['spAction'] != ''){
            $modTemplateName = 'm-' . str_replace('_', '-', $tv['module']) . '-' . $tv['spAction'] . '.phtml';
        } else {
            $modTemplateName = 'm-' . str_replace('_', '-', $tv['module']) . '-' . $tv['action'] . '.phtml';
        }


        $pageType = $tv['currentViewRecType'];
        $pageArr = $fn->getIssetParam($cpCfg['cp.assetsByPageTypeArr'], $pageType);
        $pageArrView = $fn->getIssetParam($cpCfg['cp.assetsByPageTypeArr'], $pageType . '-' . $tv['action']);

        if($pageArrView){
            $tplName = $fn->getIssetParam($pageArrView, 'tplName');

            if ($tplName != ''){
                $modTemplateName = $tplName;
            }

        } else if($pageArr){
            $tplName = $fn->getIssetParam($pageArr, 'tplName');

            if ($tplName != ''){
                $modTemplateName = $tplName;
            }
        }

        //print $modTemplateName;

        require_once $cpCfg['cp.libPhpPath'] . 'Twig/Autoloader.php';
        Twig_Autoloader::register();

        // folder search order in terms of priority:
        // 1) local theme 2)master theme 3) master module 4) master templates

        $loader = new Twig_Loader_Filesystem(array($baseDirCommon));
        $loader->prependPath($baseDirMaster);

        if (file_exists($masterModuleDir)){
            $loader->prependPath($masterModuleDir);
        }

        if (file_exists($localModuleDir)){
            $loader->prependPath($localModuleDir);
        }

        // this is needed if you want to access template from other modules
        // eg write a function in the local theme view file as below
        /***
            function prependPathsToTwig($loader) {
                $cpCfg = Zend_Registry::get('cpCfg');
                $tv = Zend_Registry::get('tv');

                $localThemeModDir = CP_THEMES_PATH_LOCAL . $cpCfg['cp.theme'] . "/templates/modules/";

                if ($tv['secType'] == 'Guides'){
                    $loader->prependPath($localThemeModDir . 'Business/');
                }
            }
        ***/

        if (method_exists($this, "prependPathsToTwig")) {
            $this->prependPathsToTwig($loader);
        }

        if (file_exists($masterThemeDir)){
            $loader->prependPath($masterThemeDir);
        }

        if (file_exists($localThemeDir)){
            $loader->prependPath($localThemeDir);
        }

        if (file_exists($localThemeSharedDir)){
            $loader->prependPath($localThemeSharedDir);
        }

        if (file_exists($localThemeModDir)){
            $loader->prependPath($localThemeModDir);
        }

        $twig = new Twig_Environment($loader, array(
            'debug' => true
        ));
        $twig->addExtension(new Twig_Extension_Debug());
        $template = $twig->loadTemplate($modTemplateName);

        Zend_Registry::set('twig', $twig);

        if ($plugin != ''){
            $params = $this->model->getTwigParamsPluginSpAction($twig);
        } else if ($widget != ''){
            $params = $this->model->getTwigParamsWidgetSpAction($twig);
        } else if ($tv['spAction'] != ''){
            $params = $this->model->getTwigParamsSpAction($twig);
        } else {
            $params = $this->model->getTwigParams($twig);
        }


        $tv = Zend_Registry::get('tv'); // reget the tv for the updated values
        $twig->addGlobal('tv', $tv);
        $twig->addGlobal('cpCfg', $cpCfg);
        $twig->addGlobal('ln', $ln->langText);
        $twig->addGlobal('session', $_SESSION);
        $twig->addGlobal('request', $_REQUEST);
        $tv = Zend_Registry::get('tv'); // must call $tv again to get the updated values
        
        $text = $template->render($params);

        return $text;
    }
}
