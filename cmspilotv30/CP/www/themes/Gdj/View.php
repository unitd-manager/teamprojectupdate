<?
class CP_Www_Themes_Gdj_View extends CP_Www_Lib_ThemeViewAbstract
{
    /**
     *
     */
    function getBodyPanel() {
        $clsInst = Zend_Registry::get('currentModule');

        $wBreadcrumb = getCPWidgetObj('common_breadcrumb');
        $breadcrumb = "
        {$wBreadcrumb->getWidget(array(
             'hideInHome' => true
            ,'showPrefixText' => false
            ,'showRecordTitle' => false
        ))}
        ";

        $text = "
        <div class='bodyPanel'>
            {$breadcrumb}
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');

        if (method_exists($clsInst->view, 'getLeftPanel')) {
            $text = $clsInst->view->getLeftPanel();
        } else {
            $text = "
            <h6 class='vlist'>{$tv['secTitle']}</h6>
            {$subNav->getWidget()}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getBannerPanel() {
        $wBanner = getCPWidgetObj('media_banner');
        $arr = array('autoSlideShowWidth' => 793, 'autoSlideShowHeight' => '249');
        $text = $wBanner->getWidget($arr);

        if ($text != ''){
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                $('body').addClass('hasBannerBelowHeader');
            "));
        }

        return $text;
    }
}