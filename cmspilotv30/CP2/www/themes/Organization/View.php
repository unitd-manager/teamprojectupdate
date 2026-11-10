<?
class CP_Www_Themes_Organization_View extends CP_Www_Lib_ThemeViewAbstract
{
    /**
     *
     */

    function getMainThemeOutput() {
        $headerPanel     = $this->getHeaderPanel();
        $navPanel        = $this->getNavPanel();
        $leftPanel       = $this->getLeftPanel();
        $rightPanel      = $this->getRightPanel();
        $bodyPanel       = $this->getBodyPanel();
        $mainBottomPanel = $this->getMainBottomPanel();
        $footerPanel     = $this->getFooterPanel();
                         
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');
        $pageCSSClass = $viewHelper->getPageCSSClass();


        $slideshow = '';
        if ($cpCfg['cp.showSlideShowBelowHeader']){
            $wSlideshow = getCPWidgetObj('media_simpleFadeSlideshow');
            $slideshow = $wSlideshow->getWidget(array(
                 'width' => '900'
                ,'height' => '300'
            ));
        }
        
        $slideshowText = "";
        if($tv['secType'] == 'Home'){
            $slideshowText = "
            <div id='homeSlideshowPanel'>
                <div class='page_margins'>
                    <div class='page'>
                        {$slideshow}
                    </div>
                </div>
            </div>
            ";
        }

        $footerText = "
        <footer id='footer'>
            <a id='navigation' name='navigation'></a>
            <div class='page_margins'>
                <div class='page'>
                    {$footerPanel}
                </div>
            </div>
        </footer>
        ";

        $cp_year = $fn->getReqParam('cp_year');
        $logoLink = '/' . $cp_year . '/';

        $mainInner = "
        <div class='mainInner'>
            <aside id='col1'>
                <div class='c25topCurvePanel'></div>
                <div class='c25middleCurvePanel'>
                    <div id='col1_content' class='clearfix'>
                        {$leftPanel}
                    </div>
                </div>
                <div class='c25bottomCurvePanel'></div>
            </aside>
            <aside id='col2'>
                <div class='c25topCurvePanel'></div>
                <div class='c25middleCurvePanel'>
                    <div id='col2_content' class='clearfix'>
                        {$rightPanel}
                    </div>
                </div>
                <div class='c25bottomCurvePanel'></div>
            </aside>
            <div id='col3'>
                <div class='c75topCurvePanel'></div>
                <div class='c75middleCurvePanel'>
                    <div id='col3_content' class='clearfix'>
                        <a id='contentMain' name='contentMain'></a>
                        {$bodyPanel}
                    </div>
                    <div id='ie_clearing'>&nbsp;</div>
                </div>
                <div class='c75bottomCurvePanel'></div>
            </div>
        </div>
        ";

        $text = "
        <header id='header'>
            <div class='page_margins'>
                <div class='page'>
                  {$headerPanel}
                  <a id='logo' href='{$logoLink}'><span class='hideme'>Logo</span></a>
                  {$navPanel}
                </div>
            </div>
        </header>
        {$slideshowText}
        <div id='main' class='{$pageCSSClass} clearfix'>
            <div class='page_margins'>
                <div class='page'>
                    {$mainInner}
                </div>
            </div>
        </div>
        {$footerText}
        ";

        return $text;
    }

    /**
     *
     */
}