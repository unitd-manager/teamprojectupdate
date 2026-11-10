<?
class CP_Www_Themes_Finance_View extends CP_Www_Lib_ThemeViewAbstract
{
    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');

        if (method_exists($clsInst, 'getLeftPanel')) {
            $text = $clsInst->getLeftPanel();
        } else {
            $mainNav = getCPWidgetObj('core_mainNav');
            $subNav  = getCPWidgetObj('core_subNav');

            $wLang = getCPWidgetObj('common_language');
            $text = "
            {$subNav->getWidget(array(
                'showSubCat' => False
            ))}
            <div id='btnsBottom'>
                <div class='vlist'>
                    <ul class='noDefault'>
                        {$mainNav->getWidget(array(
                             'btnPos' => 'Left'
                            ,'surroundUl' => false
                        ))}
                    </ul>
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $clsInst = Zend_Registry::get('currentModule');
        $ln = Zend_Registry::get('ln');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                     'clsName' => $clsName
                    ,'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $wBanner = getCPWidgetObj('media_banner');
        $bannerArr = $wBanner->getWidget(array(
            'returnDataOnly' => true
        ));
        
        $pageTitle = '';

        $extraCss = '';
        if (count($bannerArr) == 0){
            
            if ($tv['secType'] == 'Newsletter Signup'){
                $pageTitle = $ln->gd('signUp');
            } else if ($tv['catType'] == 'Goals'){
                $pageTitle = $ln->gd('goalsPageTitle');
            } else {
                $pageTitle = $fn->getPageTitle();
            }

            $pageTitle = "
            <div class='pageTitle'>
                {$pageTitle}
            </div>
            ";
            $extraCss = ' noBanner';
        }

        $content = $clsInst->getController();
        $rightSubCat = '';

        $wSubCat = getCPWidgetObj('core_subCat');
        $subCatText = $wSubCat->getWidget(array(
            'heading' => $ln->gd('w.core.subCat.heading')
        ));

        if ($subCatText != ''){
            $content = "
            <div class='subcolumns'>
                <div class='c75l'>
                    <div class='subcl'>
                        {$content}
                    </div>
                </div>
                <div class='c25r'>
                    <div class='subcr mt20'>
                        {$subCatText}
                    </div>
                </div>
            </div>
            ";
        }

        $text = "
        <div class='bodyPanel{$extraCss}'>
            {$pageTitle}
            {$content}
            <div class='toTop'>
                <a href='#header'>{$ln->gd('top')}</a>
            </div>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $mainNav = getCPWidgetObj('core_mainNav');
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                <div class='float_left'>{$ln->gd('cp.footer.rightText')}</div>
                <div class='float_left'>
                    {$mainNav->getWidget(array(
                         'btnPos' => 'Bottom'
                        ,'class' => 'footerSection'
                    ))}               
                </div>
            </div>
        </div>
        ";

        return $text;
    }

}