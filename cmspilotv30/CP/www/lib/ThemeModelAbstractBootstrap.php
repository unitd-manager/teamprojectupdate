<?
abstract class CP_Www_Lib_ThemeModelAbstract extends CP_Common_Lib_ThemeModelAbstract
{
    function getTwigParams($twig = null) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $currentModule = Zend_Registry::get('currentModule');
        $template = includeCPClass('Lib', 'template', 'Template');

        $headerTags    = $template->getHTMLHeaderTags();
        $tv = Zend_Registry::get('tv'); // must call $tv after calling getHTMLHeaderTags()
        $hiddenVars    = $template->getHiddenPageValues();
        $footerScripts = $template->getFooterScripts();
        $bodyClass     = $template->getBodyClass();

        /********************* NAVIGATIONS ********************/
        $mainNavArray  = getCPWidgetObj('core_mainNav')->getWidget(array('returnDataOnly' => true));
        $subNavArray   = getCPWidgetObj('core_subNav')->getWidget(array('returnDataOnly' => true));
        $subCatArray   = getCPWidgetObj('core_subCat')->getWidget(array('returnDataOnly' => true));
        $modDataArray  = $currentModule->model->getModuleDataArray();

        $bannerArr     = getCPWidgetObj('media_banner')->getWidget(array('returnDataOnly' => true));

        $mainNavArray = array_filter($mainNavArray, function($row){
            return $row['show_in_nav'] == 1;
        });

        $topMostNavArr = array_filter($mainNavArray, function($row){
            return $row['button_position'] == 'Top Most';
        });

        $topNavArr = array_filter($mainNavArray, function($row){
            return $row['button_position'] == 'Top';
        });

        $leftNavArr = array_filter($mainNavArray, function($row){
            return $row['button_position'] == 'Left';
        });

        $bottomNavArr = array_filter($mainNavArray, function($row){
            return $row['button_position'] == 'Bottom';
        });

        $subNavArray = array_filter($subNavArray, function($row){
            return $row['show_in_nav'] == 1;
        });

        $subCatArray = array_filter($subCatArray, function($row){
            return $row['show_in_nav'] == 1;
        });

        if (isLoggedInWWW()) {
            $tv['isLoggedInWWW']     = true;
            $tv['cpUserNameWWW']     = $_SESSION['cpUserNameWWW'];
            $tv['cpEmail']           = $_SESSION['cpEmail'];
            $tv['cpUserFullNameWWW'] = $_SESSION['cpUserFullNameWWW'];
            $tv['cpLoginTypeWWW']    = $_SESSION['cpLoginTypeWWW'];
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
        }

        // pass all the common data arrays which are needed for any site to the template
        /***********************************************************/
        $params = array(
            'headerTags'    => $headerTags,
            'footerScripts' => $footerScripts,
            'hiddenVars'    => $hiddenVars,
            'bodyClass'     => $bodyClass,
            'mainNavArray'  => $mainNavArray,
            'topMostNavArr' => $topMostNavArr,
            'topNavArr'     => $topNavArr,
            'leftNavArr'    => $leftNavArr,
            'bottomNavArr'  => $bottomNavArr,
            'modDataArray'  => $modDataArray,
            'bannerArr'     => $bannerArr,
            'footer'        => $modDataArray,
            'subNavArray'   => $subNavArray,
            'subCatArray'   => $subCatArray,
            'cpCurrentUrl' => $cpUrl->getCurrentURL(),
            'cpCurrentUriWithNoQstr' => $cpUrl->getUriWithNoQstr(),
        );

        if ($cpCfg['cp.showLangToggleAtTheTop']){
            $wLang = getCPWidgetObj('common_language');
            $langArr = $wLang->getWidget(array('returnDataOnly' => true));
            $params2 = array('langArr' => $langArr);
            $params = array_merge($params, $params2);
        }

        if ($cpCfg['cp.showNavAsMenu']){
            $menuDataArray = getCPWidgetObj('core_mainNav')->model->getMenuDataArray($topNavArr);
            $params2 = array('menuDataArray' => $menuDataArray);
            $params = array_merge($params, $params2);
        }

        if ($cpCfg['cp.showSubNavAsMenu']){
            $catSubCatArr  = getCPWidgetObj('core_mainNav')->model->getMenuDataArrayForCatSubCat($tv['room']);
            $params2 = array('catSubCatArr' => $catSubCatArr);
            $params = array_merge($params, $params2);
        }

        // get any special data arrays from the module and add to the existing array
        // you need to write a function called getTwigParams in module's model.php
        // to pass any extra array variables to the template

        if (method_exists($currentModule->model, 'getTwigParams')) {
            $modParams = $currentModule->model->getTwigParams($twig);
            $params = array_merge($params, $modParams);
        }

        return $params;
    }

    function getTwigParamsSpAction($twig = null) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');

        $actionName = ucfirst($tv['spAction']);
        $modObj = Zend_Registry::get('currentModule');
        $actionTemp = "get{$actionName}";

        $spActionDataArr = '';
        if (method_exists($modObj, $actionTemp)){
            $spActionDataArr = $modObj->$actionTemp();
        }

        // pass all the common data arrays which are needed for any site to the template
        /***********************************************************/
        $params = array(
            'spActionDataArr' => $spActionDataArr,
        );

        if (isLoggedInWWW()) {
            $tv['isLoggedInWWW']     = true;
            $tv['cpUserNameWWW']     = $_SESSION['cpUserNameWWW'];
            $tv['cpEmail']           = $_SESSION['cpEmail'];
            $tv['cpUserFullNameWWW'] = $_SESSION['cpUserFullNameWWW'];
            $tv['cpLoginTypeWWW']    = $_SESSION['cpLoginTypeWWW'];
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
        }

        return $params;
    }

    function getTwigParamsPluginSpAction($twig = null) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $plugin = getReqParam('plugin');

        $actionName = ucfirst($tv['spAction']);
        $plugObj  = getCPPluginObj($plugin);
        $actionTemp = "get{$actionName}";

        if (isLoggedInWWW()) {
            $tv['isLoggedInWWW']     = true;
            $tv['cpUserNameWWW']     = $_SESSION['cpUserNameWWW'];
            $tv['cpEmail']           = $_SESSION['cpEmail'];
            $tv['cpUserFullNameWWW'] = $_SESSION['cpUserFullNameWWW'];
            $tv['cpLoginTypeWWW']    = $_SESSION['cpLoginTypeWWW'];
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
        }

        $spActionDataArr = '';
        if (method_exists($plugObj, $actionTemp)){
            $spActionDataArr = $plugObj->$actionTemp();
        }

        // pass all the common data arrays which are needed for any site to the template
        /***********************************************************/
        $params = array(
            'spActionDataArr' => $spActionDataArr,
        );

        return $params;
    }

    function getTwigParamsWidgetSpAction($twig = null) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $widget = getReqParam('widget');

        $actionName = ucfirst($tv['spAction']);
        $plugObj  = getCPWidgetObj($widget);
        $actionTemp = "get{$actionName}";

        if (isLoggedInWWW()) {
            $tv['isLoggedInWWW']     = true;
            $tv['cpUserNameWWW']     = $_SESSION['cpUserNameWWW'];
            $tv['cpEmail']           = $_SESSION['cpEmail'];
            $tv['cpUserFullNameWWW'] = $_SESSION['cpUserFullNameWWW'];
            $tv['cpLoginTypeWWW']    = $_SESSION['cpLoginTypeWWW'];
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
        }

        $spActionDataArr = '';
        if (method_exists($plugObj, $actionTemp)){
            $spActionDataArr = $plugObj->$actionTemp();
        }

        // pass all the common data arrays which are needed for any site to the template
        /***********************************************************/
        $params = array(
            'spActionDataArr' => $spActionDataArr,
        );

        return $params;
    }
}
