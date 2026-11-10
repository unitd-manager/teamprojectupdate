<?
/**
 *
 */
class CP_Common_Lib_JssArray
{

    var $urlArray = array();
    var $allCssText = '';
    var $allJsText = '';
    var $allJsTextTop = '';

    //==================================================================//
    function getJSSObj($param) {
        $arr = array();

        $arr['loadFromCdn'] = isset($param['loadFromCdn']) ? $param['loadFromCdn'] : false;
        $arr['cdnUrl']      = isset($param['cdnUrl']) ? $param['cdnUrl'] : '';
        $arr['cdnUrlCSS']   = isset($param['cdnUrlCSS']) ? $param['cdnUrlCSS'] : '';
        $arr['fileName']    = $param['fileName'];
        $arr['cssPatch']    = isset($param['cssPatch']) ? $param['cssPatch'] : ''; //if lt IE 9
        $arr['jsPatch']    = isset($param['jsPatch']) ? $param['jsPatch'] : ''; //if lt IE 9

        // whether to load the script file in header or footer
        $arr['position']    = isset($param['position']) ? $param['position'] : 'bottom';

        return $arr;
    }

    /**
     *
     * @param <type> $fileName
     * @return <type>
     */
    function getJSSArray($fileName) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $fileArr = array();
        switch ($fileName) {
            case "jq-1.4.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.4.2.js"
                        ,'loadFromCdn' => true
                        ,'position' => 'top'
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"
                    )
                );
            break;

            case "jq-1.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.5.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jq-1.6.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.6.1.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jq-1.7.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.7.1.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jq-1.7.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.7.2.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"
                        ,'position' => 'top'
                    )
                );
            break;


            case "jq-1.8.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.8.2.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jq-1.9.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.9.1.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"
                        ,'position' => 'top'
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-migrate-1.2.1.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://code.jquery.com/jquery-migrate-1.2.1.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jq-1.9.1-ltIE9":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-1.9.1.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"
                        ,'jsPatch' => "if lt IE 9"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jq-2.0.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-2.0.2.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"
                        ,'position' => 'top'
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-migrate-1.2.1.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://code.jquery.com/jquery-migrate-1.2.1.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "jqUI-1.8.9":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.8.9/theme/{$cpCfg['cp.jqUITheme']}/jquery-ui.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.8.9/jquery-ui.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"
                    )
                );
            break;

            case "jqUI-1.9.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.9.1/theme/{$cpCfg['cp.jqUITheme']}/jquery-ui.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.9.1/jquery-ui.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"
                    )
                );
            break;

            case "jqUI-1.9.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.9.2/theme/{$cpCfg['cp.jqUITheme']}/jquery-ui.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.9.2/jquery-ui.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"
                    )
                );
            break;

            case "jqUI-1.10.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.10.1/theme/{$cpCfg['cp.jqUITheme']}/jquery-ui.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.10.1/jquery-ui.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"
                    )
                );
            break;

            case "jqUI-1.10.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.10.3/theme/{$cpCfg['cp.jqUITheme']}/jquery-ui.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.min.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-ui-1.10.3/jquery-ui.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"
                    )
                );
            break;

            case "underscore-1.5.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}underscore/1.5.1/underscore.min.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.1/underscore-min.js"
                    )
                );
            break;

            case "bootstrap-2.3.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/core/2.3.2/js/bootstrap.min.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"
                    )
                );
            break;

            case "bootstrap-3.0.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/core/3.0.0/js/bootstrap.min.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"
                    )
                );
            break;

            case "bootstrap-3.0.0-css":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/core/3.0.0/css/bootstrap.min.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css"
                    )
                );
            break;

            case "bootstrap-3.0.0-useFontAwesome":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/core/3.0.0/css/bootstrap.no-icons.min.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.no-icons.min.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/font-awesome/{$cpCfg['cp.fontAwesomeVersion']}/css/font-awesome.min.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/font-awesome/{$cpCfg['cp.fontAwesomeVersion']}/css/font-awesome.min.css"
                    )
                );
            break;

            case "bootstrap-3.3.6":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap-3.3.6/js/bootstrap.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap-3.3.6/css/bootstrap.min.css"
                    )
                );
                
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap-3.3.6/css/bootstrap-theme.min.css"
                    )
                );
            break;

            case "fontAwesome-4.1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/font-awesome/4.1.0/css/font-awesome.min.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"
                    )
                );
            break;

            case "fontAwesome-4.2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/font-awesome/4.2.0/css/font-awesome.min.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css"
                    )
                );
            break;

            case "fontAwesome-4.3.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/font-awesome/4.3.0/css/font-awesome.min.css"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://stackpath.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css"
                    )
                );
            break;

            case "jqBootstrapValidation-1.3.6":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/jqBootstrapValidation-1.3.6/jqBootstrapValidation.min.js",
                    )
                );
            break;

            case "jqBootbox-4.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootbox-4.0/bootbox.min.js",
                    )
                );
            break;

            case "jqBootstrapDatepicker-2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/datepicker-2.0/bootstrap-datepicker.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/datepicker-2.0/datepicker.css"
                    )
                );
            break;

            case "bootstrapTimepicker-0.2.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrap-timepicker-0.2.3/bootstrap-timepicker.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrap-timepicker-0.2.3/bootstrap-timepicker.min.css"
                    )
                );
            break;

            case "datetimepicker-0.0.11":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/datetimepicker-0.0.11/bootstrap-datetimepicker.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/datetimepicker-0.0.11/bootstrap-datetimepicker.min.css"
                    )
                );
            break;

            case "bootstrapImageGallery-2.10":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrapImageGallery-2.10/bootstrap-image-gallery.min.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrapImageGallery-2.10/bootstrap-image-gallery.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrapImageGallery-2.10/load-image.min.js"
                    )
                );
            break;

            case "bootstrap-listTree":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrap-listTree/bootstrap-listTree.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrap-listTree/bootstrap-listTree.js"
                    )
                );
            break;

            case "bootstrap-combobox-master":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrap-combobox-master/css/bootstrap-combobox.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/bootstrap-combobox-master/js/bootstrap-combobox.js"
                    )
                );
            break;

            case "modernizr-2.0.6":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}modernizr-2.0.6.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "respond-1.1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}respond-1.1.0.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "respond-1.1.0-ltIE9":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}respond-1.1.0.js"
                        ,'jsPatch' => "if lt IE 9"
                        ,'position' => 'top'
                    )
                );
            break;

            case "html5shiv-3.6.2-ltIE9":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}html5shiv-3.6.2.js"
                        ,'jsPatch' => "if lt IE 9"
                        ,'position' => 'top'
                    )
                );
            break;

            case "css3Finalize-1.45":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}polyfills/css3Finalize-1.45/css3finalize-1.45.min.js"
                        ,'loadFromCdn' => true
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://cdnjs.cloudflare.com/ajax/libs/css3finalize/1.45/jquery.css3finalize.min.js"
                    )
                );
            break;


            case "jqTools-1.2.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqTools-1.2.5/jquery.tools.min.js"
                        ,'loadFromCdn' => false //because it is not supporting https protocol
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqTools-1.2.5/jquery.tools.css"
                    )
                );
            break;

            case "jqTools-1.2.6":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqTools-1.2.6/jquery.tools.min.js"
                        ,'loadFromCdn' => false //because it is not supporting https protocol
                        ,'cdnUrl' => "{$cpCfg['cp.protocol']}://cdn.jquerytools.org/1.2.6/all/jquery.tools.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqTools-1.2.6/jquery.tools.css"
                    )
                );
            break;

            case "flowPlayer-3.2.7":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}flash/flowplayer-3.2.7/flowplayer-3.2.6.min.js"
                    )
                );
            break;

            case "jqCpUtil":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/cputil/jquery.cp.util.js"
                    )
                );
            break;

            case "jqForm-2.69":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/form-2.69/jquery.form.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blockui-2.59/jquery.blockUI.js"
                    )
                );
            break;

            case "jqForm-3.15":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/form-3.15/jquery.form.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blockui-2.59/jquery.blockUI.js"
                    )
                );
            break;

            case "jqForm-20131017":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/form-20131017/jquery.form.min.js"
                    )
                );
            break;

            case "jqLiveQuery-1.03":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/livequery-1.0.3/jquery.livequery.js"
                    )
                );
            break;

            case "jqBlockUI-2.36":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blockui-2.36/jquery.blockUI.js"
                    )
                );
            break;

            case "jslider-1.1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jslider-1.1.0/css/jquery.slider.min.css"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jslider-1.1.0/js/jquery.slider.min.js"
                    )
                );
            break;

            case "jqUITimePickerAddon-0.9.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/ui-timepicker-addon-0.9.3/ui-timepicker-addon.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/ui-timepicker-addon-0.9.3/ui-timepicker-addon.js"
                    )
                );
            break;

            case "jqToolTip-1.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/tooltip-1.3/jquery.tooltip.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/bgiframe-2.1.3/jquery.bgiframe.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dimensions-1.1.2/jquery.dimensions.js"
                    )
                );


                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/tooltip-1.3/jquery.delegate.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/tooltip-1.3/jquery.tooltip.js"
                    )
                );
            break;

            case "jqTip-1.0.0-rc3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/qtip-1.0.0-rc3/jquery.qtip-1.0.0-rc3.min.js"
                    )
                );
            break;

            case "the-tooltip-3.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/the-tooltip-3.0/css/the-tooltip.css"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/the-tooltip-3.0/js/the-tooltip.min.js"
                    )
                );
            break;

            case "tooltipster-2.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/tooltipster-2.1/css/tooltipster.css"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/tooltipster-2.1/js/jquery.tooltipster.min.js"
                    )
                );
            break;

            case "jqScrollTable":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/scrolltable/webtoolkit.jscrollable.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/scrolltable/webtoolkit.scrollabletable.js"
                    )
                );
            break;

            /*case "ckEditor":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}ckeditor/ckeditor.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}ckeditor/adapters/ckeditor_jquery_adapter.js"
                    )
                );
            break;*/

            case "ckEditor":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}ckeditor/ckeditor.js",
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}ckeditor/adapters/jquery.js"
                    )
                );
            break;

            case "jqUploadify2.1.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadify-2.1.4/jquery.uploadify.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadify-2.1.4/swfobject.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadify-2.1.4/uploadify.css"
                    )
                );
            break;

            case "jqUploadify3.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadify-3.2/jquery.uploadify.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadify-3.2/uploadify.css"
                    )
                );
            break;

            case "uploadifive-1.1.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadifive-1.1.2/jquery.uploadifive.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/uploadifive-1.1.2/uploadifive.css"
                    )
                );
            break;

            /************************************************************************/
            case "jqAnythingSlider1.5.6.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/css/anythingslider.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/easing-1.3/jquery.easing.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/js/jquery.anythingslider.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/js/jquery.anythingslider.fx.js"
                    )
                );
            break;

            case "jqAS1564Theme-minimalist-round":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/css/theme-minimalist-round.css"
                    )
                );
            break;

            case "jqAS1564Theme-minimalist-square":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/css/theme-minimalist-square.css"
                    )
                );
            break;

            case "jqAS1564Theme-metallic":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/css/theme-metallic.css"
                    )
                );
            break;

            case "jqAS1564Theme-construction":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/css/theme-construction.css"
                    )
                );
            break;

            case "jqAS1564Theme-cs-portfolio":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/css/theme-cs-portfolio.css"
                    )
                );
            break;

            /************************************************************************/
            case "jqColorbox-1.3.15":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/colorbox-1.3.15/theme1/colorbox.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/colorbox-1.3.15/jquery.colorbox.js"
                    )
                );
            break;

            /************************************************************************/
            case "jqColorbox-1.4.15":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/colorbox-1.4.15/example1/colorbox.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/colorbox-1.4.15/jquery.colorbox-min.js"
                    )
                );
            break;

            case "jqPrettyPhoto-3.1.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/prettyPhoto-3.1.3/css/prettyPhoto.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/prettyPhoto-3.1.3/jquery.prettyPhoto.js"
                    )
                );
            break;

            case "jqPrettyPhoto-3.1.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/prettyPhoto-3.1.5/css/prettyPhoto.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/prettyPhoto-3.1.5/js/jquery.prettyPhoto.js"
                    )
                );
            break;

            case "jqPrettyPhoto-3.1.3_museum":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/prettyPhoto-3.1.3/css/prettyPhoto.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/prettyPhoto-3.1.3/jquery.prettyPhoto_museum.js"
                    )
                );
            break;

            case "fancyBox-2.1.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fancyBox-2.1.4/jquery.fancybox.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fancyBox-2.1.4/jquery.fancybox.pack.js"
                    )
                );
            break;


            case "cufon":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}cufon/cufon.js"
                    )
                );
            break;

            case "cufon-diavlo":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}cufon/diavlo-700.font.js"
                    )
                );
            break;

            case "cufon-eurostile":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}cufon/eurostile-500.font.js"
                    )
                );
            break;

            case "cufon-orbitron":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}cufon/Orbitron_900-Orbitron_700.font.js"
                    )
                );
            break;

            case "cufon-helvetica-neue-light":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}cufon/Helvetica_Neue_300.font.js"
                    )
                );
            break;

            case "cufon-sansation":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}cufon/sansation.font.js"
                    )
                );
            break;

            case "jqElastic-1.6.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/elastic-1.6.4/jquery.elastic.js"
                    )
                );
            break;

            case "jqDdSmoothMenu1.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/ddsmoothmenu-1.4/ddsmoothmenu.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/ddsmoothmenu-1.4/ddsmoothmenu.js"
                    )
                );
            break;

            case "jqContentSlider":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/contentSlider/jquery.contentslider.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/contentSlider/jquery.contentslider.css"
                    )
                );
            break;

            case "jqUISelectMenu":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/selectmenu/selectmenu.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/selectmenu/selectmenu.css"
                    )
                );
            break;

            case "jqGalleria-1.2.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/galleria-1.2.3/galleria.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/galleria-1.2.3/galleria.classic.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/galleria-1.2.3/galleria.classic.css"
                    )
                );
            break;

            case "jqBsCarousel":
                /*$fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/bsCarousel/bootstrap.js"
                    )
                );*/

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/bsCarousel/bootstrap.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/bsCarousel/bootstrap.css"
                    )
                );
            break;

            case "jqNivoSlider-2.5.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nivo-slider-2.5.1/jquery.nivo.slider.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nivo-slider-2.5.1/nivo-slider.css"
                    )
                );
            break;

            case "jqNivoSlider-2.7.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nivo-slider-2.7.1/jquery.nivo.slider.pack.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nivo-slider-2.7.1/themes/default/default.css"

                    )
                );
            break;

            case "jqBackgroundpos-v1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/backgroundpos-v1.0/jquery.backgroundpos.js"
                    )
                );
            break;

            case "jqGrid-4.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqGrid-4.0/js/jqGrid.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqGrid-4.0/js/grid.locale-en.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqGrid-4.0/css/ui.jqgrid.css"
                    )
                );
            break;

            case "jqReject-0.7-Beta": //reject ie6 & other browsers

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jReject-0.7-Beta/jquery.reject.js"
                    )
                );

            break;

            case "jqReject-1.0.2": //reject ie6 & other browsers
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jReject-1.0.2/jquery.reject.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jReject-1.0.2/css/jquery.reject.css"
                    )
                );


            break;

            case "jqTreeview-1.4.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/treeview-1.4.1/jquery.treeview.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/treeview-1.4.1/jquery.treeview.css"
                    )
                );
            break;

            case "jsTree-1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jsTree-1.0/jquery.jstree.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jsTree-1.0/themes/apple/style.css"
                    )
                );
            break;

            case "dyntree-1.2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dyntree-1.2.0/jquery.dynatree.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/cookie/jquery.cookie.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dyntree-1.2.0/skin-vista/ui.dynatree.css"
                    )
                );
            break;

            case "jqCalculation-0.4.08":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/calculation-0.4.08/jquery.calculation.min.js"
                    )
                );
            break;

            case "jqTimer":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/timers/jquery.timers.js"
                    )
                );
            break;

            case "jqTableScroll":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/tablescroll/jquery.tablescroll.js"
                    )
                );
            break;

            case "jqJKey-1.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jkey-1.1/jquery.jkey-1.1.js"
                    )
                );
            break;

            case "jqHotkeys-0.8":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqHotkeys-0.8/jquery.hotkeys.js"
                    )
                );
            break;

            case "jqS3slider-1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/s3slider-1.0/s3Slider.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/s3slider-1.0/s3Slider.css"
                    )
                );
            break;

            case "jqSimpleFadeSlideshow-2.0.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/simple-fade-slideshow-2.0.0/fadeSlideShow-min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/simple-fade-slideshow-2.0.0/style.css"
                    )
                );
            break;

            case "jqSuperSized-3.2.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/easing-1.3/jquery.easing.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.5/supersized.3.2.5.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.5/theme/supersized.shutter.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.5/supersized.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.5/theme/supersized.shutter.css"
                    )
                );
            break;

            case "jqSuperSized-3.2.7":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.7/js/supersized.core.3.2.1.min.js"
                    )
                );


                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.7/css/supersized.core.css"
                    )
                );
            break;

            case "jFontSizer":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jfontsizer/jquery.jfontsizer.min.js"
                    )
                );
            break;

            case "jqSuperfish-1.4.8":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.4.8/superfish.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/hoverintent/hoverIntent.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/bgiframe-2.1.3/jquery.bgiframe.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.4.8/css/superfish.css"
                    )
                );
            break;

            case "jqSuperfish-1.4.8-vertical":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.4.8/superfish.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/hoverintent/hoverIntent.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/bgiframe-2.1.3/jquery.bgiframe.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.4.8/css/superfish.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.4.8/css/superfish-vertical.css"
                    )
                );
            break;

            case "jqSuperfish-1.7.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.7.2/js/superfish.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.7.2/js/hoverIntent.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/superfish-1.7.2/css/superfish.css"
                    )
                );
            break;

            case "jcrop-0.9.8":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jcrop-0.9.8/jcrop.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jcrop-0.9.8/jcrop.css"
                    )
                );
            break;

            case "firebugLite":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}firebug-lite/build/firebug-lite.js"
                    )
                );
            break;

            case "nyroModal-1.6.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nyroModal-1.6.2/js/jquery.nyroModal-1.6.2.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nyroModal-1.6.2/styles/nyroModal.css"
                    )
                );
            break;

            case "nyroModal-2.0.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nyroModal-2.0.0/js/jquery.nyroModal.custom.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nyroModal-2.0.0/styles/nyroModal.css"
                    )
                );
            break;

            case "flexSlider-2.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/flexSlider-2.1/jquery.flexslider-min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/flexSlider-2.1/flexslider.css"
                    )
                );
            break;

            case "carouFredSel-4.5.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/carouFredSel-4.5.1/jquery.carouFredSel-4.5.1-packed.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/carouFredSel-4.5.1/style.css"
                    )
                );
            break;

            case "carouFredSel-5.5.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/carouFredSel-5.5.0/jquery.carouFredSel-5.5.0-packed.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/carouFredSel-5.5.0/style.css"
                    )
                );
            break;

            case "owlCarousel-1.29":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/owlCarousel-1.29/owl.carousel.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/owlCarousel-1.29/owl.carousel.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/owlCarousel-1.29/owl.theme.css"
                    )
                );
            break;

            case "simplyscroll-1.0.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/simplyscroll-1.0.4/jquery.simplyscroll-1.0.4.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/simplyscroll-1.0.4/jquery.simplyscroll-1.0.4.css"
                    )
                );
            break;

            case "nicescroll-3.2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/nicescroll-3.2.0/jquery.nicescroll.min.js"
                    )
                );
            break;

            case "blend-2.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blend-2.2/jquery.blend-min.js"
                    )
                );
            break;

            case "jscrollpane-2.0":
                //$fileArr[] = $this->getJSSObj(
                //    array(
                //        'fileName' => "{$cpCfg['cp.jssPath']}jquery/mousewheel-3.0.4/jquery.mousewheel.js"
                //    )
                //);
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/mousewheel-3.1.3/jquery.mousewheel.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jscrollpane-2.0/jquery.jscrollpane.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jscrollpane-2.0/jquery.jscrollpane.css"
                    )
                );
            break;

            case "mCustomScrollbar-2.8.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/mCustomScrollbar-2.8.2/jquery.mCustomScrollbar.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/mCustomScrollbar-2.8.2/jquery.mousewheel.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/mCustomScrollbar-2.8.2/jquery.mCustomScrollbar.css"
                    )
                );
            break;

            case "jqMegaDropDownMenu-1.3.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-mega-drop-down-menu.1.3.3/jquery.dcmegamenu.1.3.3.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-mega-drop-down-menu.1.3.3/jquery.hoverIntent.minified.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-mega-drop-down-menu.1.3.3/css/dcmegamenu.css"
                    )
                );
            break;

            case "meganizr-1.1.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/meganizr-1.1.0/meganizr.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/meganizr-1.1.0/css/meganizr.css"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'cssPatch' => "if lt IE 9"
                        ,'fileName' => "{$cpCfg['cp.jssPath']}jquery/meganizr-1.1.0/css/meganizr-ie.css"
                    )
                );

            break;

            case "idletimeout-1.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/idletimeout-1.2/jquery.idletimeout.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/idletimeout-1.2/jquery.idletimer.js"
                    )
                );
            break;

            case "idletimer-0.9":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/idletimer-0.9/jquery.idle-timer.js"
                    )
                );
            break;

            case "syntaxhighlighter-3.0.83":
                /**
                 * http://alexgorbatchev.com/SyntaxHighlighter/manual/installation.html
                 * add var $jssKeys = array('syntaxhighlighter-3.0.83'); to your theme view file
                 * add the below code to your theme init()
                 * SyntaxHighlighter.defaults['toolbar'] = false;
                 * SyntaxHighlighter.all();
                 */
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/styles/shCore.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/styles/shThemeDefault.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/scripts/shCore.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/scripts/shBrushCss.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/scripts/shBrushPhp.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/scripts/shBrushJScript.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/scripts/shBrushBash.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}syntaxhighlighter-3.0.83/scripts/shBrushSql.js"
                    )
                );
            break;

            case "starrating-3.13":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/starrating-3.13/jquery.rating.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/starrating-3.13/jquery.rating.pack.js"
                    )
                );
            break;

            case "starrating-3.14":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/starrating-3.14/jquery.rating.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/starrating-3.14/jquery.rating.pack.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/starrating-3.14/jquery.MetaData.js"
                    )
                );
            break;

            case "frameready-1.2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/frameready-1.2.0/jquery.frameready.js"
                    )
                );
            break;

            case "countdown-1.5.11":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/countdown-1.5.11/jquery.countdown.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/countdown-1.5.11/jquery.countdown.css"
                    )
                );
            break;

            case "jeditable":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jeditable/jquery.jeditable.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jeditable/css/jquery.autocomplete.css"
                    )
                );
            break;

            case "googleMap":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "//maps.google.com/maps/api/js?sensor=false&libraries=drawing"
                    )
                );
            break;

            case "pinIt":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "http://assets.pinterest.com/js/pinit.js"
                    )
                );
            break;

            case "facebookSDK":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "//connect.facebook.net/en_US/all.js"
                    )
                );
            break;

            case "fullcalendar-1.5.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-1.5.3/fullcalendar.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-1.5.3/fullcalendar.css"
                    )
                );
            break;

            case "fullcalendar-1.5.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-1.5.4/fullcalendar.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-1.5.4/fullcalendar.css"
                    )
                );
            break;

            case "fullcalendar-1.6.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-1.6.4/fullcalendar/fullcalendar.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-1.6.4/fullcalendar/fullcalendar.css"
                    )
                );
            break;

            case "baBBQ":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/ba-bbq/jquery.ba-bbq.min.js"
                    )
                );
            break;

            case "browserstate-history.js":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/browserstate-history.js/jquery.history.js"
                    )
                );
            break;

            case "searchabledropdown-1.0.7":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/searchabledropdown-1.0.7/jquery.searchabledropdown-1.0.7.min.js"
                    )
                );
            break;

            case "angular-1.0.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}angular-1.0.1/angular.min.js"
                    )
                );
            break;

            case "angular-1.1.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}angular-1.1.5/angular.min.js"
                    )
                );
            break;

            case "jPlayer-2.2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jPlayer-2.2.0/jplayer.min.js"
                    )
                );
            break;

            case "spritely-0.6.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/spritely-0.6.1/jquery.spritely.js"
                    )
                );
            break;

            // twitter
            case "jTweetsAnywhere-1.3.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jTweetsAnywhere-1.3.1/jquery.jtweetsanywhere-1.3.1.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jTweetsAnywhere-1.3.1/jquery.jtweetsanywhere-1.3.1.css"
                    )
                );
            break;

            case "noty-2.0.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/noty-2.0.3/jquery.noty.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/noty-2.0.3/layouts/all.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/noty-2.0.3/themes/default.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/noty-2.0.3/themes/buttons.css"
                    )
                );
            break;

            case "autocomplete-1.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/autocomplete-1.1/jquery.autocomplete.min.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/autocomplete-1.1/jquery.autocomplete.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/autocomplete-1.1/jquery.autocomplete.css"
                    )
                );
            break;

            case "jwplayer-6.3":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}flash/jwplayer-6.3/jwplayer.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "selectBoxIt-3.3.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/selectBoxIt-3.3.0/jquery.selectBoxIt.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/selectBoxIt-3.3.0/css/jquery.selectBoxIt.css"
                    )
                );
            break;

            case "jqzoom-1.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqzoom-1.2/jqzoom.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jqzoom-1.2/jqzoom.css"
                    )
                );
            break;

            case "elevateZoom-2.5.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/elevateZoom-2.5.5/jquery.elevateZoom-2.5.5.min.js"
                    )
                );
            break;

            case "wookmark-1.3.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/wookmark-1.3.0/jquery.wookmark.min.js"
                    )
                );
            break;

            case "favico-0.3.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}favico-0.3.2/favico-0.3.2.min.js"
                    )
                );
            break;

            case "blueimp.fileupload-5.32.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/vendor/jquery.ui.widget.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/jquery.iframe-transport.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/jquery.fileupload.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/jquery.fileupload-process.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/jquery.fileupload-image.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/jquery.fileupload-validate.js"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/js/jquery.fileupload-ui.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/css/style.css"
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/blueimp.fileupload-5.32.0/css/jquery.fileupload-ui.css"
                    )
                );

            break;

            case "instafeed":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}instafeed/instafeed.min.js"
                        ,'position' => 'top'
                    )
                );
            break;

            case "bxslider":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery.bxslider/jquery.bxslider.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery.bxslider/jquery.bxslider.css"
                    )
                );
            break;

            case "jscolor-1.4.4":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/jscolor-1.4.4/jscolor.js"
                    )
                );
            break;

            case "fullcalendar-2.3.2":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-2.3.2/fullcalendar.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-2.3.2/fullcalendar.css"
                    )
                );

                 $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/fullcalendar-2.3.2/fullcalendar.print.css"
                    )
                );
            break;

            case "dropdown-newType":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropdown-newType/js/modernizr.custom.79639.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropdown-newType/css/style.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropdown-newType/css/demo.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropdown-newType/css/noJS.css"
                    )
                );
            break;

            case "Flexisel-2.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/Flexisel-2.0/js/jquery.flexisel.js",
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}bootstrap/Flexisel-2.0/css/style.css"
                    )
                );
            break;

            case "chosen-1.5.1":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/chosen.jquery.js"

                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/docsupport/prism.js"

                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/docsupport/style.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/docsupport/prism.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/chosen.css"
                    )
                );
            break;

            case "slick-1.6.0":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/chosen.jquery.js"

                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/docsupport/prism.js"

                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/docsupport/style.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/docsupport/prism.css"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/chosen-1.5.1/chosen.css"
                    )
                );
            break;

            case "jQueryHtmlTextEditor":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jQueryHtmlTextEditor/js/jquery.classyedit.js"

                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jQueryHtmlTextEditor/css/jquery.classyedit.css"

                    )
                );
            break;

            case "jQueryMapHighlight":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jQueryMapHighlight/js/jquery.maphilight.min.js"

                    )
                );
            break;

            case "jquery-timepicker-1.3.5":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-timepicker-1.3.5/jquery.timepicker.min.js"

                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-timepicker-1.3.5/jquery.timepicker.min.css"

                    )
                );
            break;

            case "radialIndicator":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/radialIndicator/radialIndicator.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/radialIndicator/radialIndicator.min.js"
                    )
                );
            break;

            case "cookieAcceptBar":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/cookieAcceptBar/js/jquery.cookieBar.min.js",
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/cookieAcceptBar/css/jquery.cookieBar.min.css"
                    )
                );
            break;

            case "bootstrap-tagsinput":
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/bootstrap-tagsinput/js/bootstrap-tagsinput.js",
                    )
                );
                $fileArr[] = $this->getJSSObj(
                    array(
                         'fileName' => "{$cpCfg['cp.jssPath']}jquery/bootstrap-tagsinput/css/bootstrap-tagsinput.css"
                    )
                );
            break;

            case "plupload-2.3.6":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/plupload-2.3.6/js/plupload.full.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/plupload-2.3.6/js/themeswitcher.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/plupload-2.3.6/js/jquery.ui.plupload/jquery.ui.plupload.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/plupload-2.3.6/js/jquery.ui.plupload/css/jquery.ui.plupload.css"
                    )
                );
            break;

            case "dropzone":
                /*$fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropzone/js/upload.js"
                    )
                );*/

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropzone/dropzone/dropzone.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/dropzone/dropzone/dropzone.css"
                    )
                );
            break;

            case "custom-scrollbar":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/custom-scrollbar/jquery.custom-scrollbar.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/custom-scrollbar/jquery.custom-scrollbar.css"
                    )
                );
            break;

            case "smooth-products":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-Smooth-Products/js/smoothproducts.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/jquery-Smooth-Products/css/smoothproducts.css"
                    )
                );
            break;

            case "Counting-Up-Plugin":
                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/Counting-Up-Plugin/jquery.counterup.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/Counting-Up-Plugin/jquery.counterup.min.js"
                    )
                );

                $fileArr[] = $this->getJSSObj(
                    array(
                        'fileName' => "{$cpCfg['cp.jssPath']}jquery/Counting-Up-Plugin/waypoints.min.js"
                    )
                );
            break;
        }

        return $fileArr;
    }

    /**
     *
     * @param <type> $filesArr
     * @return <type>
     */
    function setJSSFilesText(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $jssArr = Zend_Registry::get('jssArr');
        $cpPaths = Zend_Registry::get('cpPaths');

        $text      = "";
        $cssText   = "";
        $jsTextTop = "";
        $jsText    = "";
        $patchText = "";

        $minFileExists = false;

        if ($cpCfg['cp.useMinFiles']){
            /*** check whether min files exists ***/
            $minDir = realpath(CP_LOCAL_PATH) . "/min";
            $jsFileMin = $minDir . "/cp.js";
            $cssFileMin = $minDir . "/cp.css";

            /** try to generate the min file if it does not exists **/
            if (!file_exists($jsFileMin) || !file_exists($cssFileMin)) {
                $spActionObj = includeCPClass('Lib', 'SpecialAction');
                $spActionObj->getGenerateMinFiles();
            }

            /** the above lines would have created the files now, so check again **/
            if (file_exists($jsFileMin) && file_exists($cssFileMin)) {
                $minFileExists = true;
            }
        }

        $jssKeys         = Zend_Registry::get('jssKeys');

        $jsFilesArr      = Zend_Registry::get('jsFilesArr');
        $jsFilesArrLocal = Zend_Registry::get('jsFilesArrLocal');
        $jsFilesArrLast  = Zend_Registry::get('jsFilesArrLast');

        $cssFilesArrFirst = Zend_Registry::get('cssFilesArrFirst');
        $cssFilesArr      = Zend_Registry::get('cssFilesArr');
        $cssFilesArrLocal = Zend_Registry::get('cssFilesArrLocal');
        $cssFilesArrLast  = Zend_Registry::get('cssFilesArrLast');
        $cssPatchFilesArr = Zend_Registry::get('cssPatchFilesArr');

        $jsFilesByKey = array();
        $jsFilesByKeyTop = array();
        $jsPatchFilesTop = array();
        $cssFilesByKey = array();

        /*** SET ALL PAGE SPECIFIC JS & CSS FILES IN ARRAYS ***/
        foreach($jssKeys as $jssKey){
            $filesArr2 = $this->getJSSArray($jssKey);

            foreach($filesArr2 as $jssObj){
                if ($jssObj['loadFromCdn'] && $cpCfg['cp.useCdn']){
                    $fileName = $jssObj['cdnUrl'];
                } else {
                    $fileName = $jssObj['fileName'];
                }

                if (substr($fileName, -4) == ".css"){
                    if($jssObj['cssPatch'] != ''){
                        $cssPatchFilesArr[] = array($jssObj['cssPatch'], $fileName);
                    } else {
                        $cssFilesByKey[] = $fileName;
                    }
                } else {
                    if($jssObj['jsPatch'] != ''){
                        $jsPatchFilesTop[] = array($jssObj['jsPatch'], $fileName);
                    } else {
                        if ($jssObj['position'] == 'top'){
                            $jsFilesByKeyTop[]  = $fileName;
                        } else {
                            $jsFilesByKey[]  = $fileName;
                        }
                    }
               }
            }
        }

        if ($minFileExists){
            $minCSSArr = array($cpPaths->getMinFilePath('alias') . 'cp.css');
            $cssFilesAll = array_merge($cssFilesByKey, $minCSSArr);
        } else {
            $cssFilesAll = array_merge($cssFilesArrFirst, $cssFilesByKey, $cssFilesArr, $cssFilesArrLocal, $cssFilesArrLast);
        }

        $jssIncludedAlready = array();

        if ($cpCfg['cp.googleFontsString'] != '' && $cpCfg['cp.useCdn']){
            $cssText .= "<link rel='stylesheet' href='{$cpCfg['cp.protocol']}://fonts.googleapis.com/css?family={$cpCfg['cp.googleFontsString']}'>\n";
        }

        $version = ($cpCfg['cp.assetVersion'] != '') ? "?v={$cpCfg['cp.assetVersion']}" : '';

        /*** Loop through the CSS files array and form the include text ***/
        foreach($cssFilesAll as $key => $value){
            if (!in_array($value, $jssIncludedAlready) ){
                $jssIncludedAlready[] = $value;
                $cssText .= "<link rel='stylesheet' href='{$value}{$version}'>\n";
            }
        }

        if ($minFileExists){
            $minJSArr = array($cpPaths->getMinFilePath('alias') . 'cp.js');
            $jsFilesAll = array_merge($jsFilesByKey, $minJSArr);
        } else {
            $jsFilesAll = array_merge($jsFilesByKey, $jsFilesArr, $jsFilesArrLocal, $jsFilesArrLast);
        }

        /*** Loop through the JS Patch Files array and form the include text ***/
        foreach($jsPatchFilesTop as $value){
            $filename = (is_array($value)) ? $value[1] : $value;

            if (is_array($value)){
                $patchText .= "
                <!--[{$value[0]}]>
                    <script src=\"{$value[1]}{$version}\"></script>\n
                <![endif]-->
                ";
            }
        }

        /*** Loop through the JS files for loading into header ***/
        foreach($jsFilesByKeyTop as $key => $value){
            if (!in_array($value, $jssIncludedAlready) ){
                $jssIncludedAlready[] = $value;
                $jsTextTop .= "<script src=\"{$value}{$version}\"></script>\n";
            }
        }

        /*** Loop through the JS files array and form the include text ***/
        foreach($jsFilesAll as $key => $value){
            if (!in_array($value, $jssIncludedAlready) ){
                $jssIncludedAlready[] = $value;
                $jsText .= "<script src=\"{$value}{$version}\"></script>\n";
            }
        }

        /*** Loop through the CSS Patch Files array and form the include text ***/
        $patchIncludedAlready = array();

        foreach($cssPatchFilesArr as $value){
            $filename = (is_array($value)) ? $value[1] : $value;

            if (in_array($filename, $patchIncludedAlready) ){
                continue;
            }

            if (is_array($value)){
                $patchText .= "
                <!--[{$value[0]}]>
                    <link rel=\"stylesheet\" href=\"{$value[1]}{$version}\">
                <![endif]-->
                ";
                $patchIncludedAlready[] = $value[1];

            } else {
                $patchText .= "
                <!--[if lte IE 7]>
                    <link rel=\"stylesheet\" href=\"{$value}{$version}\">
                <![endif]-->
                ";
                $patchIncludedAlready[] = $value;
            }
        }

        $this->allCssText = $cssText. "\n". $patchText;
        $this->allJsTextTop = $jsTextTop;
        $this->allJsText = $jsText;

        //$text .= $cssText . "\n". $jsText . "\n". $patchText;
        //return $text;
    }
}
