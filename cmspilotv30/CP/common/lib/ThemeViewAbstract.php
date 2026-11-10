<?
abstract class CP_Common_Lib_ThemeViewAbstract
{
    var $controller = null;
    var $model = null;
    var $fns = null;

    var $jssKeys = array();
    var $jsFilesArr = array();
    var $jsFilesArrLocal = array();
    var $jsFilesArrLast = array();

    var $cssFilesArr = array();
    var $cssFilesArrLocal = array();
    var $cssFilesArrLast = array();
    var $cssPatchFilesArr = array();

    /**
     *
     */
    function includeJSSFiles() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');
        $theme = $this->controller->name;

        $viewHelper->setJSSArrays('themes', $theme, 'style.css');
        $viewHelper->setJSSArrays('themes', $cpCfg['cp.theme'], 'functions.js');
        $viewHelper->setJSSArrays('skins', $cpCfg['cp.skin'], 'style.css');

        //print $cpCfg['cp.skin'];
        $viewHelper->setJSSArrays('skins', $cpCfg['cp.skin'], 'functions.js');

        $this->setJssPatchArray();

        CP_Common_Lib_Registry::arrayMerge('jssKeys', $this->jssKeys);
    }

    /**
     *
     */
    function setJssPatchArray() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $cssPatchFilesArr = array();
        $jsFilesArr = array();

        $themePathMaster = CP_THEMES_PATH . $cpCfg['cp.theme'] . '/patch_ie.css';
        if (file_exists($themePathMaster)) {
            $cssPatchFilesArr[] = array('if lte IE 7', CP_THEMES_PATH_ALIAS .
                                                       $cpCfg['cp.theme'] . '/patch_ie.css');
        }

        if ($cpCfg['cp.hasAlternativeCore']){
            $themePathMaster = CP_THEMES_PATH2 . $cpCfg['cp.theme'] . '/patch_ie.css';
            if (file_exists($themePathMaster)) {
                $cssPatchFilesArr[] = array('if lte IE 7', CP_THEMES_PATH2_ALIAS .
                                                           $cpCfg['cp.theme'] . '/patch_ie.css');
            }
        }

        $themePathLocal = CP_THEMES_PATH_LOCAL . $cpCfg['cp.theme'] . '/patch_ie.css';
        if (file_exists($themePathLocal)) {
            $cssPatchFilesArr[] = array('if lte IE 7', CP_THEMES_PATH_LOCAL_ALIAS .
                                                       $cpCfg['cp.theme'] . '/patch_ie.css');
        }

        if($cpCfg['cp.includeFbLite']){
            $jsFilesArr[] = 'firebugLite';
        }

        CP_Common_Lib_Registry::arrayMerge('cssPatchFilesArr', $cssPatchFilesArr);
        CP_Common_Lib_Registry::arrayMerge('jssKeys', $jsFilesArr);
    }

    /**
     *
     */
    function getPagerPanel($linkRecType = '') {
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $pageNavLinksCount = $cpCfg['cp.pageNavLinksCount'];

        $text = $pager->getNavButtons($pageNavLinksCount, '', $linkRecType);

        return $text;
    }

    /**
     *
     */
    function getBodyPanelLink($linkRecType) {
        $tv = Zend_Registry::get('tv');
        $panel = Zend_Registry::get('panel');

        $actionName  = ucfirst($tv['action']);
        $modObj = Zend_Registry::get('currentModule');
        $actionTemp = "get{$actionName}";
        $actionTempLinked = "get{$actionName}Linked";
        $actionTempNotLinked = "get{$actionName}NotLinked";

        if ($linkRecType == 'notLinked' && method_exists($modObj, $actionTempNotLinked)) {
            $innerText = $modObj->$actionTempNotLinked($linkRecType);

        } else if ($linkRecType == 'linked' && method_exists($modObj, $actionTempLinked)) {
            $innerText = $modObj->$actionTempLinked($linkRecType);
        } else {
            if (!method_exists($modObj, $actionTemp)) {
                $clsName = ucfirst($tv['lnkRoom']);

                $error = includeCPClass('Lib', 'Errors', 'Errors');
                $exp = array(
                    'replaceArr' => array(
                         'clsName' => $clsName
                        ,'funcName' => $actionTemp
                    )
                );
                print $error->getError('linkModuleMethodNotFound', $exp);
                exit();

            }
            $innerText = $modObj->$actionTemp($linkRecType);
        }

        $text = "
        {$this->getPagerPanel($linkRecType)}
        <div class=''>
            {$innerText}
        </div>
        <input type='hidden' id='url_{$linkRecType}' value='{$_SERVER['REQUEST_URI']}&linkRecType={$linkRecType}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getLinkThemeOutput($srcRoom = '', $lnkRoom = '') {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchHTMLLink = Zend_Registry::get('searchHTMLLink');
        $linksArray = Zend_Registry::get('linksArray');

        if ($srcRoom != ''){
            $linkName  = $srcRoom . '#' . $lnkRoom;
        } else {
            $linkName  = $tv['srcRoom'] . '#' . $tv['lnkRoom'];
        }

        $linkArray = $linksArray[$linkName];

        $reloadModal = $fn->getReqParam('reloadModal', 0);
        $linkRecType = $fn->getReqParam('linkRecType');

        $searchHTMLLinked = "
        <div class='floatbox'>
            <div class='float_right'>
                {$searchHTMLLink->getSearchHTML($tv['lnkRoom'], 'linked')}
            </div>
        </div>
        ";

        $searchHTMLNotLinked = "
        <div class='floatbox'>
            <div class='float_right'>
                {$searchHTMLLink->getSearchHTML($tv['lnkRoom'], 'notLinked')}
            </div>
        </div>
        ";

        if ($tv['linkSingle'] == 1 && $linkRecType == '') {
            $text = "
            {$fn->getLinkRoomTitleText()}

            <div id='linkLeft' class='linkWrapper'>
                {$searchHTMLNotLinked}
                {$this->getBodyPanelLink('notLinked')}
            </div>
            ";
        } else if ($linkRecType != ''){
            if ($linkRecType == 'linked'){
                $text = "
                {$searchHTMLLinked}
                {$this->getBodyPanelLink('linked')}
                ";
            } else {
                $text = "
                {$searchHTMLNotLinked}
                {$this->getBodyPanelLink('notLinked')}
                ";
            }

        } else {
            $text = "
            <div class='subcolumns'>
                <div class='c50l'>
                    <div class='subcl innerWrap'>
                        {$fn->getLinkRoomTitleText()}

                        <div id='linkLeft' class='linkWrapper'>
                            {$searchHTMLNotLinked}
                            {$this->getBodyPanelLink('notLinked')}
                        </div>
                    </div>
                </div>
                <div class='c50r'>
                    <div class='subcr innerWrap'>
                        <h1>List Linked</h1>
                        <div id='linkRight' class='linkWrapper'>
                            {$searchHTMLLinked}
                            {$this->getBodyPanelLink('linked')}
                        </div>
                    </div>
                </div>
            </div>

            <input type='hidden' id='cpCurrentLinkUrl' value='{$_SERVER['REQUEST_URI']}' />
            ";
        }

        if ($reloadModal == 0 && $tv['searchDone'] == 0 && $linkRecType == ''){
            $width = $linkArray['portalDialogWidthInternal'];
        if ($width !== 'auto') {
            //if just number entered then think it as pixes
            //for ex. if % or something entered then set it as it is
            if (is_numeric($width)) {
                $width = $width . 'px';
            }
        }

        $modalClsss  = $tv['srcRoom'] . '__' . $tv['lnkRoom'];

        $text = "
        <div id='linkModalOuter' class='modal_{$modalClsss}' style='width: {$width}'>
            {$text}
        </div>
        ";
        }

        return $text;
    }
}
