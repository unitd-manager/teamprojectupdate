<?
class CP_Www_Themes_SimpleWhite_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqForm-3.15', 'jqReject-0.7-Beta');

    function getHeaderPanel(){
        $wRecord = getCPWidgetObj('content_record');
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        $wLang = getCPWidgetObj('common_language');

        $text = "
        {$wLang->getWidget()}
        {$pSiteSearch->view->getSearchBox(array('useCssGoBtn' => true))}
        <div class='socialMediaIcons'>
            {$wRecord->getWidget(array(
                'contentType' => 'Social Media Icons'
            ))}
        </div>
        ";
        return $text;
    }

    function getBannerPanel(){
        $banner = parent::getBannerPanel();

        return $banner;
    }


}