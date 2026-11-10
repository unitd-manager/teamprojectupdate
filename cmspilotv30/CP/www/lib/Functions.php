<?
class CP_Www_Lib_Functions extends CP_Common_Lib_Functions {

    /**
     *
     */
    function getFirstRoom(){
        $roomsArray = Zend_Registry::get('roomsArray');
        $cpCfg = Zend_Registry::get('cpCfg');

        foreach ($roomsArray as $key => $value) {
            if ($cpCfg['cp.hasMemberOnlySections'] == 1 && $value['member_only'] == 1 && !isLoggedInWWW()){
                continue;
            }
            
            if ($cpCfg['cp.hasPublicOnlySections'] == 1 && $value['non_member_only'] == 1 && isLoggedInWWW()){
                continue;
            }
            
            return $key;
        }
    }

    /**
     *
     */
    function getModuleNameByType($type){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.moduleNamesByTypeArr'];
        $module = isset($arr[$type]) ? $arr[$type] : 'webBasic_content';
        return $module;
    }

    /**
     *
     */
    function getPageTitle($tv = ''){
        
        // $tv value is passed from bootstrap/setNavigationArraysWww
        if ($tv == ''){
            $tv = Zend_Registry::get('tv');
        }

        $title = "";

    	if ($tv['sub_category_id'] > 0 || $tv['subCat'] > 0 ) {
    	    $title = $tv['subCatTitle'];
    	} else if ($tv['subRoom'] > 0) {
    	    $title = $tv['catTitle'];
    	} else if ($tv['room'] != ''){
    	    $title = $tv['secTitle'];
    	}

        return $title;
    }


    /**
     *
     * @return <type>
     */
    function getFieldLabelWithValue($displayTitle, $fieldValue, $extraParam = array()) {
        $exp = &$extraParam;
        $rowCls        = isset($exp['rowCls'])        ? " {$exp['rowCls']}" : '';
        $fieldLabelCls = isset($exp['fieldLabelCls']) ? " {$exp['fieldLabelCls']}'" : '';

        $text = '';

        if ($fieldValue != '') {
            $text = "
            <div class='floatbox{$rowCls}'>
                <div class='fldLabel{$fieldLabelCls}'>
                    <strong>
                        {$displayTitle}
                    </strong>
                </div>
                <div class='float_left'>
                    {$fieldValue}
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getAnalyticsCode(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if(CP_ENV == 'production'){

            $site_ga_id = '';
            if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
                $site_ga_id = $fn->getIssetParam($siteRec, 'google_analytics_id');
            }

            $ga_id  = ($site_ga_id != '') ? $site_ga_id : $fn->getIssetParam($cpCfg, 'cp.analytics.google.accountId');
            $gaText = ($ga_id != '') ? $this->getGoogleAnalyticsText($ga_id) : '';

            $text = "
            {$gaText}
            {$this->getAdditionalAnalyticsScripts()}
            ";

            return $text;
        }

    }

    /**
     *
     */
    function getGoogleAnalyticsText($account_id){
        $text = "
        <script type='text/javascript'>
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', '{$account_id}']);
          _gaq.push(['_trackPageview']);
        
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        </script>
        ";

        return $text;
    }

    /**
     *
     */
    function getGoogleTagManagerCode(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if(CP_ENV == 'production'){
            $site_ga_id = '';
            if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
                $site_ga_id = $fn->getIssetParam($siteRec, 'google_tag_manager_id');
            }

            $ga_id  = ($site_ga_id != '') ? $site_ga_id : $fn->getIssetParam($cpCfg, 'cp.analytics.google.tagManagerAccountId');
            $gaText = ($ga_id != '') ? $this->getGoogleTagManagerText($ga_id) : '';

            $text = "
            {$gaText}
            ";

            return $text;
        }

    }

    /**
     *
     */
    function getGoogleTagManagerText($account_id){
        $text = "
        <!-- Google Tag Manager -->
        <script type='text/javascript'>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{$account_id}');
        </script>
        <!-- End Google Tag Manager -->
        ";

        return $text;
    }

    /**
     *
     */
    function getGoogleTagManagerBodyCode(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        if(CP_ENV == 'production'){
            $site_ga_id = '';
            if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
                $site_ga_id = $fn->getIssetParam($siteRec, 'google_tag_manager_id');
            }

            $ga_id  = ($site_ga_id != '') ? $site_ga_id : $fn->getIssetParam($cpCfg, 'cp.analytics.google.tagManagerAccountId');

            $text = "";
            if ($ga_id) {
                $text = "
                <!-- Google Tag Manager (noscript) -->
                <noscript><iframe src='https://www.googletagmanager.com/ns.html?id={$ga_id}'
                height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
                <!-- End Google Tag Manager (noscript) -->
                ";
            }

            return $text;
        }
    }

    /**
     *
     */
    function getHotJarTrackingCode(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if(CP_ENV == 'production'){
            $site_hj_id = '';
            if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
                $site_hj_id = $fn->getIssetParam($siteRec, 'hotjar_tracking_id');
            }

            $hj_id  = ($site_hj_id != '') ? $site_hj_id : $fn->getIssetParam($cpCfg, 'cp.hotjar.tracking.code');
            $hjText = ($hj_id != '') ? $this->getHotJarTrackingText($hj_id) : '';

            $text = "
            {$hjText}
            ";

            return $text;
        }

    }

    /**
     *
     */
    function getHotJarTrackingText($tracking_code){
        $text = "
        <!-- Hotjar Tracking Code Manager -->
        <script>
            (function(h,o,t,j,a,r){
                h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                h._hjSettings={hjid:{$tracking_code},hjsv:6};
                a=o.getElementsByTagName('head')[0];
                r=o.createElement('script');r.async=1;
                r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                a.appendChild(r);
            })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
        </script>
        <!-- End Hotjar Tracking Code Manager -->
        ";

        return $text;
    }

    /**
     *
     */
    function getAdditionalAnalyticsScripts(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
            $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
            $text = $fn->getIssetParam($siteRec, 'additional_analytics_script');
        }

        return $text;
    }

    /**
     *
     */
    function setLogoForMultiUniqueSite(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        if ($cpCfg['cp.hasMultiUniqueSites']){
            $siteRec = $this->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
            $logArr = $media->getMediaFilesArray('common_site', 'logo', $siteRec['site_id']);
            if (count($logArr) > 0){
                $script = "
                <script>
                    $('#header').css('background-image', 'url({$logArr[0]['file_normal']})');
                </script>
                ";
                return $script;
            }
        }
    }

    function getModuleConfigValue($conf) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $recType = lcfirst($tv['currentViewRecType']);
        $recType = $cpUrl->getUrlText($tv['currentViewRecType']);

        $module = str_replace('_', '.', $tv['module']);
        //ex: $cpCfg['m.webBasic.content__press-release.sortOrder'] = 'c.content_date DESC';

        $confKey = "m.{$module}__{$recType}.{$conf}";
        $confKey2 = "m.{$module}.{$conf}";

        $value = '';
        if (isset($cpCfg[$confKey])) {
            $value = $cpCfg[$confKey];
        } else if (isset($cpCfg[$confKey2])) {
            $value = $cpCfg[$confKey2];
        }

        return $value;
    }

    function getVersionUrl($url) {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.assetVersion'] != '') {
            $url .= '?v' . $cpCfg['cp.assetVersion'];

        }
        return $url;
    }
}
