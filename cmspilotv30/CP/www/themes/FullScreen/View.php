<?
class CP_Www_Themes_FullScreen_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqSuperSized-3.2.5', 'jscrollpane-2.0', 'idletimer-0.9', 'jqPrettyPhoto-3.1.3');

    function getHeaderPanel(){
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');

        $wLang = getCPWidgetObj('common_language');

        $pSiteSearch = getCPPluginObj('common_siteSearch');

        if ($tv['catType'] == 'Flipbook Form') {
            $text = "
            ";
        } else {
            $text = "
            <div id='fb-root'></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = '//connect.facebook.net/en_GB/all.js#xfbml=1';
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>

            <div id='rightSlide'>
                <div class='inner'>
                    {$wLang->getWidget(array(
                        'moveCurrentLangToEnd' => true
                    ))}

                    <div class='search wrap'>
                        {$pSiteSearch->view->getSearchBox()}
                    </div>

                    <div class='socialMediaIcons wrap'>
                        <div class='handle'></div>
                            <div id='showShareIcons'><div>{$ln->gd('cp.lbl.shareProject')}</div></div>
                    </div>
                </div>
            </div>
            ";
    	}

        $facebookUrl = 'http://www.facebook.com/rlphongkong';
        $twitterUrl = 'http://www.twitter.com/rlphongkong';
        $weiboUrl = 'http://weibo.com/ronaldluhk';
        $pinterestUrl = 'http://www.pinterest.com/rlphongkong';
        $instagramUrl = 'http://instagram.com/rlphongkong';
        //$linkedinUrl = 'http://www.linkedin.com/company/ronald-lu-&-partners-hong-kong-ltd';
        $linkedinUrl = 'http://www.linkedin.com/company/1117787';
        

        $text = "
        <div id='stHolder'>
            <span>
                <a title='Facebook' target='_blank'
                   href='{$facebookUrl}'><img src='/www/images/share-icons/facebook.png'></a>
            </span>
            <span>
                <a title='Twitter' target='_blank'
                   href='{$twitterUrl}'><img src='/www/images/share-icons/twitter.png'></a>
            </span>
            <span>
                <a title='Weibo' target='_blank'
                   href='{$weiboUrl}'><img src='/www/images/share-icons/sina.png'></a>
            </span>
            <span>
                <a title='Pinterest' target='_blank'
                   href='{$pinterestUrl}'><img src='/www/images/share-icons/pinterest.png'></a>
            </span>
            <span>
                <a title='Instagram' target='_blank'
                   href='{$instagramUrl}'><img src='/www/images/share-icons/instagram.png'></a>
            </span>
            <span>
                <a title='Linked In' target='_blank'
                   href='{$linkedinUrl}'><img src='/www/images/share-icons/linkedin.png'></a>
            </span>
            <span class='st_email_custom'>
                <img src='/www/images/share-icons/email.png'>
            </span>
        </div>

        {$text}
        <script type='text/javascript' src='http://w.sharethis.com/button/buttons.js'></script>

        <script type='text/javascript'>
            stLight.options({
                publisher: 'cf848352-4718-42bd-92c6-aaf80bd8a1a5',
                doNotHash: false,
                doNotCopy: false,
                hashAddressBar: false,
                offsetLeft:'15000',
            });</script>
        <script>
            var options={
                'position': 'right',
                'publisher': 'cf848352-4718-42bd-92c6-aaf80bd8a1a5',
                'ad': { 'visible': false, 'openDelay': 5, 'closeDelay': 0},
                'chicklets': { 'items': ['email']},
            };
            var st_hover_widget = new sharethis.widgets.hoverbuttons(options);
        </script>
        ";

        return $text;
    }

    function getSocialMediaIconszz(){
        $text = "
        <div class='shareIcons'>
            <div class='desc'>
                <div class='fb-like-temp-disabled' data-send='false' data-layout='button_count'
                     data-width='125' data-show-faces='false' data-font='lucida grande'>
                     <a href='#'><img src='/www/images/fb-like.png'></a>
                </div>
                <div class='mt5'>
                    <a href='https://twitter.com/share' class='twitter-share-button'>Tweet</a>
                    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>
                </div>

                <div class='mt5'>
                    <g:plusone annotation='inline' width='100'></g:plusone>
                    <script type='text/javascript'>
                      (function() {
                        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                      })();
                    </script>
                </div>

                <div class='mt5'>
                    <script type='text/javascript' charset='utf-8'>
                    (function(){
                      var _w = 72 , _h = 24;
                      var param = {
                        url:location.href,
                        type:'2',
                        count:'1', /**是否?示分享?，1?示(可?)*/
                        appkey:'', /**您申?的?用appkey,?示分享?源(可?)*/
                        title:'', /**分享的文字?容(可?，默??所在?面的title)*/
                        pic:'', /**分享?片的路?(可?)*/
                        ralateUid:'', /**??用?的UID，分享微博?@?用?(可?)*/
                        language:'zh_cn', /**?置?言，zh_cn|zh_tw(可?)*/
                        rnd:new Date().valueOf()
                      }
                      var temp = [];
                      for( var p in param ){
                        temp.push(p + '=' + encodeURIComponent( param[p] || '' ) )
                      }
                      document.write(\"<iframe allowTransparency='true' frameborder='0' scrolling='no' src='http://hits.sinajs.cn/A1/weiboshare.html?\" +
                                     temp.join('&') + \"' width='\" + _w + \"' height='\" + _h + \"'></iframe>\")
                    })()
                    </script>
                </div>
            </div>
        </div>
        ";
        return $text;

    }

    function getFullSizeBanner(){
        $tv = Zend_Registry::get('tv');

        $wSuperSized = getCPWidgetObj('media_supersized');


        if ($tv['secType'] == 'Project' && $tv['subCatType'] != 'People2' && $tv['record_id'] > 0){
            $text = "
            {$wSuperSized->getWidget(array(
                 'showThumbnail'   => false
                ,'showProgressBar' => false
                ,'showControlBar'  => false
                ,'module'          => 'gallery_project'
                ,'mediaType'       => 'picture'
                ,'recordId'        => $tv['record_id']
                ,'thumbTrayDiv'    => '#supersizeThumbTray'
                ,'autoPlay'        => 0
            ))}
            ";

        } else if (($tv['catType'] == 'People' || $tv['subCatType'] == 'People2') && $tv['record_id'] > 0){
            $text = "
            {$wSuperSized->getWidget(array(
                 'showThumbnail'   => false
                ,'showProgressBar' => false
                ,'showControlBar'  => false
                ,'module'          => 'webBasic_content'
                ,'mediaType'       => 'picture'
                ,'recordId'        => $tv['record_id']
            ))}
            ";

        } else {
            $text = "
            {$wSuperSized->getWidget(array(
                 'showThumbnail' => false
                ,'showProgressBar' => false
                ,'showControlBar' => false
            ))}
            ";
        }

        return $text;
    }
    /**
     *
     */
    function getLeftPanel(){
        $subNav = Zend_Registry::get('subNav');
        $tv = Zend_Registry::get('tv');

        $topText = '';
        $title = ($tv['secType'] != 'Home') ? "<h6 class='secTitle'>{$tv['secTitle']}</h6>" : '';

        if ($tv['catType'] == 'Flipbook Form') {
            $text = "
            ";
        } else {
            $text = "
            {$title}
            {$topText}
            {$subNav->getWidget(array(
                'showSubCat' => true
            ))}
            ";
        }
        return $text;
    }

    /**
     *
     */
    function getRightPanel(){
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');


        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            print "<h3>{$clsName}->{$actionTemp} does not exist";
            exit();
        }

        $enqForm = '';
        $text = "
        <div class='bodyPanel'>
            {$clsInst->getController()}
            {$enqForm}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');

        $text = "
        <!-- <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
            </div>
        </div>-->
        ";

        return $text;
    }

    /**
     *
     */
    function getLastPanelOutsideTemplate(){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $currentModule = Zend_Registry::get('currentModule');

        $btmPanel = '';
        if ($tv['secType'] == 'Home'){
            $homeContent = '';
            foreach ($currentModule->model->dataArray as $row){
                $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
                $desc = $ln->gfv($row, 'description');

                $homeContent = "
                <div class='bottomContent'>
                    {$title}
                    {$desc}
                </div>
                ";
            }

            $btmPanel = "
            <div id='bottomContentWrapper'>
                {$homeContent}
            </div>
            <div id='homeBtmOuter'></div>
            ";
        } else if ($tv['secType'] == 'Project' && $tv['record_id'] > 0){
            $btmPanel = "
            <div id='projectBtmDummyWrapper'>
            </div>
            ";
        }

        $text = "
        {$this->getFullSizeBanner()}
        {$btmPanel}
        ";
        return $text;
    }

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $text = '';
        if ($cpCfg['cp.showMainNavPanelAtTop']){

            $extraClass = '';
            if ($cpCfg['cp.showNavAsMenu']){
                $superFish = getCPWidgetObj('menu_superFish');
                $widget = "{$superFish->getWidget(array(
                    'btnPos' => 'Top'
                ))}
                ";

                $extraClass = 'hasMenu clearfix';
                $text = "
                <nav id='nav' class='hasMenu clearfix'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                </nav>
                ";
            } elseif ($cpCfg['cp.showNavAsMegaMenu']){
                $megaMenu = getCPWidgetObj('menu_megaMenu');
                $widget = "{$megaMenu->getWidget(array(
                ))}
                ";

                $extraClass = 'hasMenu clearfix';
                $text = "
                <nav id='nav' class='hasMenu clearfix'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                </nav>
                ";
            } else {
                $mainNav = Zend_Registry::get('mainNav');
                $hasSlidingDoorBtn = $cpCfg['w.core_mainNav.hasSlidingDoorBtn'];

                $widget = "{$mainNav->getWidget(array(
                     'btnPos' => 'Top'
                    ,'hasSlidingDoorBtn' => $hasSlidingDoorBtn
                ))}
                ";
            }

            if($cpCfg['cp.fullWidthTemplte'] && !$cpCfg['cp.placeNavInsideHeaderTag']){
                $text = "
                <nav id='nav' role='navigation'>
                    <a id='navigation' name='navigation'></a>
                    <div class='page_margins'>
                        <div class='page'>
                            {$widget}
                        </div>
                    </div>
                </nav>
                ";
            } else {
                if ($tv['catType'] == 'Flipbook Form') {
                    $text = "
                    ";
                } else {
                    $text = "
                    <nav id='nav' class='{$extraClass}'>
                        <a id='navigation' name='navigation'></a>
                        {$widget}
                    </nav>
                    ";
                }
            }

        }
        return $text;
    }
}