var Globals = {};

var cpm = {}; // global object for modules
var cpp = {}; // global object for plugins
var cpw = {}; // global object for widgets
var cpt = {}; // global object for themes

//------------------------------------------------//
var Util = {
    createCPObject: function(objName){
        var arr = objName.split('.');

        var objStr = '';
        $(arr).each(function(i){
            var objNameTemp = arr[i];
            var objName = objStr + objNameTemp;
            eval("obj = " + objName);
            if (obj == undefined) {
                eval(objName + " = {}");
            }
            objStr += objNameTemp + '.';
        });
    },

    isNumberKey: function(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        } else {
            if(charCode == 190 || charCode == 110 || charCode == 46){
                   return false;
            } else {
                return true;
            }
        }
    },

    /*isDecimalKey: function(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        } else {
            return true;
        }
    },*/

    isDecimalKey: function(el, evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        var number = el.value.split('.');
        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        //just one dot
        if(number.length>1 && charCode == 46){
             return false;
        }
        //get the carat position
        var caratPos = Util.selectionStart(el);
        var dotPos   = el.value.indexOf(".");
        if( caratPos > dotPos && dotPos>-1 && (number[1].length > 1)){
            return false;
        }
        return true;
    },

    selectionStart: function(o) {
        if (o.createTextRange) {
            var r = document.selection.createRange().duplicate()
            r.moveEnd('character', o.value.length)
            if (r.text == '') return o.value.length
            return o.value.lastIndexOf(r.text)
        } else return o.selectionStart
    },

    expose: function(obj){
        if ($('#cpScope').val() == 'www'){
            /*
    	    var obj = obj.expose({color: '#789', lazy: true});

    	    // enable exposing on the wizard
    	    obj.click(function() {
    	    	$(this).expose().load();
    	    });
    	    */
    	}
    },

    addCommasToNumber: function(nStr){
    	nStr += '';
    	x = nStr.split('.');
    	x1 = x[0];
    	x2 = x.length > 1 ? '.' + x[1] : '';
    	var rgx = /(\d+)(\d{3})/;
    	while (rgx.test(x1)) {
    		x1 = x1.replace(rgx, '$1' + ',' + '$2');
    	}
    	return x1 + x2;
    },

    changeLangDropdown: function(url, hasSEO){
        var lang = document.getElementById('lang').value;
        var link = '';
        if (hasSEO == 1){
            link = '/' + lang + '/' + url;
        } else {
            link = url + '&lang=' + lang;
        }
        document.location = link;
    },

    getScrollForUIDialog: function(text, options) {
        var height = options.height;
        height -= 100;
        var selectedSet = $('<div>')
                          .append($('<div>' + text + '</div>')
                                  .css({'overflow-y': 'auto', 'width': '94%', 'height': height + 'px'})
                           );
        return selectedSet;
    },

    initDialog: function() {
        //just to make sure we are not dealing with the unintended (old used) div for the dialog
        $("div.popcontents").closest('.ui-dialog:hidden').remove();
        //$("div.popcontents").remove();

        var dialogCount = $('div.popcontents').length;
        var text = 'dialog';
        if (dialogCount == 0) {
            text = 'dialog';
        } else {
            text = 'dialog' + dialogCount;
        }

        $('body').append("<div id='" + text + "' class='popcontents'><div>");

        //returnd dialog div id
        return '#' + text;
    },

    initDialog2: function(opts) {
        var defaults = {
             cssClass: ''
        };
        opts = $.extend({}, defaults, opts);

        var dialogCount = $('div.popcontents').length;
        var text = 'dialog';
        if (dialogCount == 0) {
            text = 'dialog';
        } else {
            text = 'dialog' + dialogCount;
        }

        var cls = 'popcontents ' + opts.cssClass;
        $('body').append("<div id='" + text + "' class='" + cls + "'><div>");

        //returnd dialog div id
        return '#' + text;
    },

    closeAllDialogs: function() {
        if ($('#dialog').length > 0){
            $('#dialog').dialog('close');
            $('#dialog').dialog('destroy');
        }

        if ($('#dialog1').length > 0){
            $('#dialog1').dialog('close');
            $('#dialog1').dialog('destroy');
        }

        if ($('#dialog2').length > 0){
            $('#dialog2').dialog('close');
            $('#dialog2').dialog('destroy');
        }

        if ($('#dialog3').length > 0){
            $('#dialog3').dialog('close');
            $('#dialog3').dialog('destroy');
        }
    },

    closeTopMostDialog: function() {
        /** this function is not completed **/
        $('div.popcontents').livequery(function(){
            var dialogCount = $(this).length;

            if (dialogCount == 1) {
                dialogId = 'dialog';
            } else {
                dialogId = 'dialog' + dialogCount;
            }

            $('#' + dialogId).dialog('close');
            $('#' + dialogId).dialog('destroy');
        });
    },

    dialogDefaults: {bgiframe: true,
                     modal: true,
                     overlay: {opacity:0.8, background:'red'}
    },

    showProgressInd: function(message) {
        if (message === undefined) {
            message = Lang.get('cp_lbl_pleaseWait', 'Please wait');
        }
        $.showprogress(message);
    },

    showProgressInd2: function(message) {
        if ($('#progressInd').length > 0){
            return;
        }

        if (message === undefined) {
            message = Lang.get('cp_lbl_processing', 'Processing...');
        }
        var width = 100;
        var left = (screen.width-width)/2;
        var top = $(window).scrollTop();

        $('body')
        .append("<div id='progressInd' class='progressInd'><div></div></div>");
        $('#progressInd')
        .addClass('ui-corner-bl')
        .addClass('ui-corner-br');
        $('#progressInd')
        .css('left', left + 'px')
        .css('top', top + 'px');
        $('#progressInd div').html(message);
    },

    hideProgressInd: function() {
        $.hideprogress();
        return;

        $('#progressInd').remove();
        $('.progressInd').each(function(){
            $(this).remove();
        });
    },

    hideProgressInd2: function() {
        $('#progressInd').remove();
        $('.progressInd').each(function(){
            $(this).remove();
        });
    },

    alert: function(text, callback, dialogTitle) {

        if (!dialogTitle){
            dialogTitle = '';
        }

        var opts = {
             cssClass: 'alert-dialog'
        };
        var dialogId = Util.initDialog2(opts);
        $(dialogId).html(text);
        var xButtons = {};
        xButtons['OK'] = function() {
            $(this).dialog('close');
            //$(this).dialog('destroy');
            $(this).remove();

            if (callback) {
                callback.call();
            }
        };

        $(dialogId).dialog(
            $.extend(Util.dialogDefaults, {
                height: 200,
                width: 350,
                title: dialogTitle,
                buttons: xButtons,
                beforeclose: function(){}
            })
        );
    },

    showSimpleMessageInDialog: function(msg, showCloseBtn, opts) {
        var id = Util.initDialog2(); //div container
        $(id).html(msg);

        var xButtons = {};

        opts = opts || {};

        if (showCloseBtn){
            xButtons[Lang.data.cp_lbl_close] = function() {
                $(this).dialog('close');
                //$(this).dialog('destroy');
            };
        }
        if (typeof opts.buttons == 'undefined') {
            opts.buttons = xButtons;
        }

        var x_dialog = $(id).dialog(
            $.extend(Util.dialogDefaults, opts)
        );

        return id;
    },

    confirm: function(text, callback, options) {
        var settings = jQuery.extend({
             btn1Label: 'OK'
            ,btn2Label: 'Cancel'
            ,width: 350
            ,height: 200
            ,useCBForCancel: false //call the callback even the cancelbutton clicked
        }, options);

        var opts = {
             cssClass: 'alert-dialog'
        };
        var dialogId = Util.initDialog2(opts);

        $(dialogId).html(text);

        var xButtons = {};
        xButtons[settings.btn1Label] = function() {
            $(this).dialog('close');
            //$(this).dialog('destroy');

            if (callback) {
                callback.call(this, settings.btn1Label);
            }
        };
        xButtons[settings.btn2Label] = function() {
            $(this).dialog('close');
            //$(this).dialog('destroy');
            if (callback && settings.useCBForCancel) {
                callback.call(this, settings.btn2Label);
            }
        };

        $(dialogId).dialog(
            $.extend(Util.dialogDefaults, {
                height: settings.height,
                width: settings.width,
                buttons: xButtons
            })
        );
    },

    openDialogForLink: function(dialogTitle, w, h, showCloseBtn, extraParamObj) {
        exp = $.extend({
             url: ''
            ,contentHTML: ''
            ,wrapperId: ''
            ,dlgSelector: ''
            ,dlgContentType: 'url' // url / html / selector
            ,beforeCloseFn: null
            ,afterOpen: null
            ,useIframe: false
            ,hideClose: false
            ,onCloseFn: null
            ,validationJsMethod: ''
        }, extraParamObj || {});

       // alert(extraParamObj.wrapperId);
       // alert(exp.wrapperId);

        if (!w){
           w = '800';
        }

        if (!h){
           h = 'auto';
        }

        var xButtons = {};

        if (showCloseBtn){
            xButtons[Lang.data.cp_lbl_close] = function() {
                $(this).dialog('close');
                //$(this).dialog('destroy');
            };
        }

        var url = '';
        if (exp.url) {
            url = exp.url;
        } else {
            if ($(this).attr('url')){
                url = $(this).attr('url');
            } else {
                url = $(this).attr('href');
                if (   url == ''
                    || url == 'javascript:void(0)'
                    || url == 'javascript:void(0);'
                    || url == undefined) {
                    url = $(this).attr('link');
                }
            }
        }

        Util.showProgressInd();
        if (exp.validationJsMethod) {
            eval('var validationJsMethod =' + exp.validationJsMethod);
            var validationSuccess = validationJsMethod.call();
            if (!validationSuccess) {
                Util.hideProgressInd();
                return;
            }
        }

        var dlgOptions = $.extend(Util.dialogDefaults, {
             width: w
            ,height: h
            ,title: dialogTitle
            ,closeOnEscape: false
            ,buttons: xButtons
            ,beforeClose: function(e, ui) {
                if(exp.beforeCloseFn) {
                    exp.beforeCloseFn.call();
                }
        	}
        	,open: function(){
        	    var dialogHt = $(this).closest('.ui-dialog').height();
        	    var windowHt =  $(window).height();
                $(this).parent().attr('id', exp.wrapperId);

        	    if (h == 'auto' && dialogHt > windowHt){
        	        $(this).height(windowHt-50);
                } else if (h == 'auto' && dialogHt < 250){
                    $(this).height(250);
        	    }
                if (exp.hideClose) {
                    $('.ui-dialog .ui-dialog-titlebar-close').hide();
                }
                if(exp.afterOpen) {
                    exp.afterOpen.call();
                }
        	}
            ,close: function() {
                if(exp.onCloseFn) {
                    exp.onCloseFn.call();
                } else {
                    $(this).remove();
                }
            }
        });

        var dlgId = Util.initDialog();
        if (exp.useIframe) {
            var iframeId = dlgId + 'Iframe';
            var iframeText = "<iframe id='" + iframeId + "' width='100%' height='99%' " +
                             "marginWidth='0' marginHeight='0' frameBorder='0' scrolling='auto' src='" + url + "' />";
            $(dlgId).html(iframeText).dialog(dlgOptions);
            //$('#' + iframeId).attr('src', url);
            //console.log(url);
            Util.hideProgressInd();

        } else {
            if (exp.dlgContentType == 'html') {
                $(dlgId).html(exp.contentHTML).dialog(dlgOptions);
                Util.hideProgressInd();

            } else if (exp.dlgContentType == 'selector') {
                $(exp.dlgSelector).dialog(dlgOptions);
                Util.hideProgressInd();

            } else {
                $.get(url, function(data){
                    $(dlgId).html(data);

                    var x_dialog = $(dlgId).dialog(dlgOptions);
                    Util.hideProgressInd();
                });
            }
        }

        return dlgId;
    },

    openFormInDialog: function(formName, dialogTitle, w, h, opts) {
        //initialize empty param vars
        if (!w){
           w = 450;
        }
        if (!h){
           h = 400;
        }
        if (opts == undefined) {
            opts = {};
        }

        var linkObj = $(this);
        var dialogId = Util.initDialog();

        var defaults = {
             beforeCloseFn: null
            ,wrapperId: ''
            ,url: null
            ,validate: true
            ,submitBtnText: opts.submitBtnText != undefined ?
                            opts.submitBtnText :
                            Lang.get('cp_lbl_submit', 'Submit')
            ,cancelBtnText: opts.cancelBtnText != undefined ?
                            opts.cancelBtnText :
                            Lang.get('cp_lbl_cancel', 'Cancel')
            ,buttons: {}
            ,dlgClass: 'dlg-' + formName //ex: dlg-registerForm
        };

        defaults.buttons = [
            {
                 click: function() {
                    $(dialogId).dialog('close');
                    $(dialogId).dialog('destroy');
                    $(dialogId).remove();
                 }
                ,'class': 'btn-cancel'
                ,text: defaults.cancelBtnText
            },
            {
                 click: function() {
                     $('#' + formName).submit();
                 }
                ,'class': 'btn-submit'
                ,text: defaults.submitBtnText
            }
        ];
        opts = $.extend({}, defaults, opts);


        var url = '';
        if (opts.url) {
            url = opts.url;
        } else {
            if ($(this).attr('url')){
                url = $(this).attr('url');
            } else {
                url = $(this).attr('href');
                if (url == "" || url == "javascript:void(0)" || url == "javascript:void(0);"  || url == undefined) {
                    url = $(this).attr('link');
                }
            }
        }
        Util.showProgressInd();

        $.get(url, function(data){
            $(dialogId).html(data);
            var x_dialog = $(dialogId).dialog(
                $.extend(Util.dialogDefaults, {
                     width: w
                    ,height: h
                    ,title: dialogTitle
                    ,buttons: opts.buttons
                    ,close: function() {$(this).remove();}
                    ,beforeclose:function(e, ui){
                        if(opts.beforeCloseFn) {
                            opts.beforeCloseFn.call();
                        }
                    }
                    ,open:function(e, ui){
                        $(this).parent().attr('id', opts.wrapperId);
                        if (opts.onOpenFn) {
                            opts.onOpenFn.call();
                        }
                        if (opts.dlgClass) {
                            $(dialogId).parents('.ui-dialog').addClass(opts.dlgClass);
                        }
                    }
                })
            );

            if (opts.validate) {
                var extraParValid = {};

                if (opts.callbackOnSuccess) {
                    extraParValid.callback = opts.callbackOnSuccess;
                }
                extraParValid.linkObj = linkObj;

                var options = {
                    success: function(json, statusText, xhr, jqFormObj) {
                        Validate.validateFormData(json, statusText, jqFormObj, extraParValid);
                        Util.hideProgressInd();
                    },
                    beforeSubmit: function(frmData) {
                        Util.showProgressInd();
                    },
                    dataType: 'json'
                };

                $('#' + formName).ajaxForm(options);
            }

            Util.hideProgressInd();
        });
    },

    setUpAjaxFormGeneral: function(formName, cbFunction, beforeSubmitFn, opts) {
        $('#' + formName).livequery(function() {
            /****************************************************/
            var extraPar = {};
            var cpCSRFToken = $('#cpCSRFToken').val();

            if (cbFunction) {
                extraPar.callback = cbFunction;
            }

            var additionalData = {
                cpCSRFToken: cpCSRFToken
            };

            if (opts === undefined) {
                opts = {};
            }

            var defaults = {
                 validate: true
                ,dataType: 'json'
                ,progressDlgType: 1
                ,blockFormOnSave: true
            };
            opts = $.extend({}, defaults, opts);

            var showProgName = 'showProgressInd' + (opts.progressDlgType === 1 ? '' : opts.progressDlgType);
            var hideProgName = 'hideProgressInd' + (opts.progressDlgType === 1 ? '' : opts.progressDlgType);

            var options = {
                success: function(json, statusText, xhr, jqFormObj) {
                    // if you want to callback a function without validating the form
                    // when you use this set opts.validate = false
                    if(opts.callbackNoValidateFn) {
                        opts.callbackNoValidateFn.call();
                    }

                    if(opts.validate === true) {
                        Validate.validateFormData(json, statusText, jqFormObj, extraPar);
                        $('#' + formName).unblock();
                    }

                    if (!cbFunction && !opts.callbackNoValidateFn) {
                        Util[hideProgName].call();
                    }
                },
                beforeSubmit: function(frmData) {
                    Util.clearPrepopulatedTextbox('#' + formName, frmData);
                    if (beforeSubmitFn) {
                        // return was added by ahmad .. if beforeSubmitFn has return false then the form should not be submitted
                        var retVal = beforeSubmitFn.call(this, frmData);
                        return retVal;
                    }
                    Util[showProgName].call();
                    if (opts.blockFormOnSave) {
                        $('#' + formName).block({message: null});
                    }
                }
                ,data: additionalData
                ,dataType: opts.dataType
            };

            if(opts.actionUrl){ //to override the action url
                options['url'] = opts.actionUrl;
            }

            $('#' + formName).ajaxForm(options);

        });
    },

    setUpFormWithUploadify: function(formName, exp) {
        $('#' + formName).livequery('submit', function(e) {
            e.preventDefault();

            var formObj = $(this);
            var extraPar = {};

            if (!exp){
                exp = {};
            }

            if (exp.cbFunction) {
                extraPar.callback = exp.cbFunction;
            }

            var formAction = $(this).attr('action');
            Util.showProgressInd();

            // when using block ui, some un-expected errors occured, hence commented out
            // $(formObj).block({ message: null });

            var formContent = $(this).serialize();

            $.post(formAction, formContent, function(json){
                //$(formObj).unblock();
                if ($(formObj).hasClass('cpUploadifyNew') && !exp.cbFunction){
                    extraPar.callback = function(){
                        record_id = json.extraParam.record_id;
                        $('.fileQueueMedia').each(function(){
                            recordType = $(this).attr('id').split('_')[1];
                            $('#uploadifyMedia_' + recordType).uploadifySettings('scriptData', {'id': record_id})
                            $('#uploadifyMedia_'+ recordType).uploadifyUpload();
                        });
                    }
                }

                Util.hideProgressInd();
                Validate.validateFormData(json, '', formObj, extraPar);
            }, 'json');

        });
    },

    prepopulatedTextbox: function(extraParamObj){

        var pptBoxTextColor = $('#pptBoxTextColor').val();
        var pptBoxTextColorFocus = $('#pptBoxTextColorFocus').val();

        extraParamObj = $.extend({
             boxTextColor: (Cfg.data.pptBoxTextColor ? Cfg.data.pptBoxTextColor : '#999'),
             boxTextColorFocus: (Cfg.data.pptBoxTextColorFocus ? Cfg.data.pptBoxTextColorFocus : '#444')
        }, extraParamObj || {});

        $("input:text[rel^='pptxt'],textarea[rel^='pptxt']")
        .livequery(function() {
            e = $("input:text[rel^='pptxt'],textarea[rel^='pptxt']");
            for (i=0;i<$(e).length;i++) {
                if ($(e[i]).val()=='') {
                    t = $(e[i]).attr('rel');
                    t = t.split("pptxt:");
                    $(e[i]).css('color', extraParamObj.boxTextColor);
                    $(e[i]).val(t[1]);
                }
            }

            $(e).focus(function() {
                t = $(this).attr('rel');
                t = t.split("pptxt:");
                var value    = $(this).val().replace(/[\r\n]/g, '');
                var pptxtval = t[1].replace(/[\r\n]/g, '');
                if (value == pptxtval) {
                    $(this).css('color', extraParamObj.boxTextColorFocus);
                    $(this).val('');
                }
            });
            $(e).blur(function() {
                t = $(this).attr('rel');
                t = t.split("pptxt:");
                if ($(this).val()=='') {
                    $(this).css('color', extraParamObj.boxTextColor);
                    $(this).val(t[1]);
                }
            });
        });
    },

    clearPrepopulatedTextbox: function(container, frmData) {
        //frmData is an object containing form data

        var len = $("input:text[rel^='pptxt'],textarea[rel^='pptxt']", $(container)).length;
        $("input:text[rel^='pptxt'],textarea[rel^='pptxt']", $(container))
        .each(function(i) {
            var t = $(this).attr('rel');
            var fieldName = $(this).attr('name');
            t = t.split("pptxt:");
            var value    = $(this).val().replace(/[\r\n]/g, '');
            var pptxtval = t[1].replace(/[\r\n]/g, '');
            if (value == pptxtval) {
                $(this).val('');

                if ($.isArray(frmData)){
                    for (var i=0; i < frmData.length; i++) {
                        if (frmData[i].name == fieldName) {
                            frmData[i].value = '';
                        }
                    }
                }
            }
        });
    },
    // this function has to be called from beforesubmit handler
    setJqFormFldValue: function(frmData, name, value){
        $.each(frmData, function(){
            if(this.name == name){
                this.value = value;
            }
        });
    },

    getCKEditorToolBarsFull: function(){
        toolbar =
        [
            ['Bold','Italic','Underline','Strike'],
            ['Cut','Copy','Paste','PasteText','PasteFromWord','-', 'SpellChecker', 'Scayt'],
            ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
            ['NumberedList','BulletedList', 'Blockquote'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['Link','Unlink','Anchor'],
            ['Image','Flash','Table','Iframe','HorizontalRule','SpecialChar'],
            ['Format','Font','FontSize'],
            ['TextColor'],
            ['Source','-','Code','-','Preview'],
            ['Maximize']
        ];

        return toolbar;
    },

    getCKEditorToolBarsLite: function(){
        toolbar =
        [
            ['Bold','Italic','Underline','Strike']
        ];

        return toolbar;
    },

    showAjaxErrorTemp:  function() {
        Util.alert('There an error while processing the ajax request through the server.' +
                   'Please contact administrator.');
    },

    loadCategoryDropdown: function(){
        $(this).each(function(){
            secId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=webBasic_category&_spAction=categoryJsonBySecId&showHTML=0'

            $.getJSON(url, {section_id: secId}, function(data) {
                $('#frmEdit select#fld_category_id').cp_loadSelect(data);

                if ($('#frmEdit select#fld_sub_category_id').length > 0){
                    Util.loadSubCategoryDropdown();
                }
            });
        });
    },

    loadContactDropdown: function(){
        $(this).each(function(){
            comId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingsg_contact&_spAction=contactJsonByComId&showHTML=0'

            $.getJSON(url, {company_id: comId}, function(data) {
                $('#frmEdit select#fld_contact_id').cp_loadSelect(data);
            });
        });
    },

    loadSubCategoryDropdown: function(){
        $(this).each(function(){
            catId = $('#frmEdit select#fld_category_id').val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=webBasic_subCategory&_spAction=getSubcatJsonByCatId&showHTML=0'

            $.getJSON(url, {category_id: catId}, function(data) {
                $('#frmEdit select#fld_sub_category_id').cp_loadSelect(data);
            });
        });
    },

    loadDropdownByJSON: function(srcFld, srcValue, dstFld, dstRoom, formId, exp){
        var url = $('#scopeRootAlias').val() + 'index.php?room=' + dstRoom + '&_spAction=jsonForDropdown&showHTML=0'

        if (!formId){
            formId = 'frmEdit';
        }

        exp = $.extend({
             extraDestFlds: [] //for ex: we have category (source) and sub_category1, sub_category2, sub_category3 etc as destination fields
        }, exp || {});

        Util.showProgressInd();
        var loadingJSON = [{'value':'', 'caption':'Loading...'}];
        $('#' + formId + ' select#' + dstFld).cp_loadSelect(loadingJSON);
        for (var i = 0; i < exp.extraDestFlds.length; i++) {
            var dstFldExtra = exp.extraDestFlds[i];
            $('#' + formId + ' select#' + dstFldExtra).cp_loadSelect(loadingJSON);
        }
        $.getJSON(url, {srcFld: srcFld, srcValue: srcValue}, function(data) {
            $('#' + formId + ' select#' + dstFld).cp_loadSelect(data);
            for (var i = 0; i < exp.extraDestFlds.length; i++) {
                var dstFldExtra = exp.extraDestFlds[i];
                $('#' + formId + ' select#' + dstFldExtra).cp_loadSelect(data);
            }
            Util.hideProgressInd();
        });
    },

    showIE6RejectMessage: function(){
        var jqRejectPath = $('#jssPath').val() + 'jquery/jReject-0.7-Beta/images/'
        $.reject({
             closeCookie: true // Set cookie to remmember close for this session
            ,imagePath: jqRejectPath // Path where images are located
        });
    },

    goToByScroll: function(id){
        $('html,body').animate({scrollTop: $("#"+id).offset().top},'slow');
    },

    getNumberToWord: function(s){
        // American Numbering System
        var th = ['','thousand','million', 'billion','trillion'];
        // uncomment this line for English Number System
        // var th = ['','thousand','million', 'milliard','billion'];
        var dg = ['zero','one','two','three','four', 'five','six','seven','eight','nine'];var tn = ['ten','eleven','twelve','thirteen', 'fourteen','fifteen','sixteen', 'seventeen','eighteen','nineteen'];var tw = ['twenty','thirty','forty','fifty', 'sixty','seventy','eighty','ninety'];
        s = s.toString();s = s.replace(/[\, ]/g,'');if (s != parseFloat(s)) return 'not a number';var x = s.indexOf('.');if (x == -1) x = s.length;if (x > 15) return 'too big';var n = s.split('');var str = '';var sk = 0;for (var i=0; i < x; i++) {if ((x-i)%3==2) {if (n[i] == '1') {str += tn[Number(n[i+1])] + ' ';i++;sk=1;} else if (n[i]!=0) {str += tw[n[i]-2] + ' ';sk=1;}} else if (n[i]!=0) {str += dg[n[i]] +' ';if ((x-i)%3==0) str += 'hundred ';sk=1;}if ((x-i)%3==1) {if (sk) str += th[(x-i-1)/3] + ' ';sk=0;}}if (x != s.length) {var y = s.length;str += 'point ';for (var i=x+1; i<y; i++) str += dg[n[i]] +' ';}return str.replace(/\s+/g,' ');
    },

    stringToBoolean: function(string){
        switch(string.toLowerCase()){
            case "true": case "yes": case "1":return true;
            case "false": case "no": case "0": case null:return false;
            default:return Boolean(string);
        }
    },

    getOuterHTML: function(selector){
        var html = $('<div>').append($(selector).clone()).remove().html();
        return html;
    },

    addToGoogleAnalytics: function() {
        var url = $(this).attr('href');
        _gaq.push(['_trackPageview', "'" + url + "'"]);
    },

    inArray: function(needle, haystack) {
        var length = haystack.length;
        for(var i = 0; i < length; i++) {
            if(haystack[i] == needle) return true;
        }
        return false;
    },

    getWindowLocationNoHash: function() {
        return window.location.href.split('#')[0];
    },

    /**
     * ex: getArrIndexJson(array1, 'contact_id', contact_id_val);
     */
    getArrIndexJson: function(arr, indFldName, ind) {
        for (var i in arr) {
            var row = arr[i];
            if (row[indFldName] == ind) {
                return i;
            }
        }
        return ind;
    },

    /**
     * ex: getArrIndJson(array1, 'contact_id', contact_id_val);
     */
    getArrIndexJson: function(arr, indFldName, ind) {
        for (var i in arr) {
            var row = arr[i];
            if (row[indFldName] == ind) {
                return i;
            }
        }
        return ind;
    },

    UTF8encode: function(s){
            for(var c, i = -1, l = (s = s.split("")).length, o = String.fromCharCode; ++i < l;
                    s[i] = (c = s[i].charCodeAt(0)) >= 127 ? o(0xc0 | (c >>> 6)) + o(0x80 | (c & 0x3f)) : s[i]
            );
            return s.join("");
    },

    UTF8decode: function(s){
            for(var a, b, i = -1, l = (s = s.split("")).length, o = String.fromCharCode, c = "charCodeAt"; ++i < l;
                    ((a = s[i][c](0)) & 0x80) &&
                    (s[i] = (a & 0xfc) == 0xc0 && ((b = s[i + 1][c](0)) & 0xc0) == 0x80 ?
                    o(((a & 0x03) << 6) + (b & 0x3f)) : o(128), s[++i] = "")
            );
            return s.join("");
    },

    utf8_decode:function (str_data) {
      // http://kevin.vanzonneveld.net
      // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
      // +      input by: Aman Gupta
      // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // +   improved by: Norman "zEh" Fuchs
      // +   bugfixed by: hitwork
      // +   bugfixed by: Onno Marsman
      // +      input by: Brett Zamir (http://brett-zamir.me)
      // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // +   bugfixed by: kirilloid
      // *     example 1: utf8_decode('Kevin van Zonneveld');
      // *     returns 1: 'Kevin van Zonneveld'

      var tmp_arr = [],
        i = 0,
        ac = 0,
        c1 = 0,
        c2 = 0,
        c3 = 0,
        c4 = 0;

      str_data += '';

      while (i < str_data.length) {
        c1 = str_data.charCodeAt(i);
        if (c1 <= 191) {
          tmp_arr[ac++] = String.fromCharCode(c1);
          i++;
        } else if (c1 <= 223) {
          c2 = str_data.charCodeAt(i + 1);
          tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
          i += 2;
        } else if (c1 <= 239) {
          // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
          c2 = str_data.charCodeAt(i + 1);
          c3 = str_data.charCodeAt(i + 2);
          tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
          i += 3;
        } else {
          c2 = str_data.charCodeAt(i + 1);
          c3 = str_data.charCodeAt(i + 2);
          c4 = str_data.charCodeAt(i + 3);
          c1 = ((c1 & 7) << 18) | ((c2 & 63) << 12) | ((c3 & 63) << 6) | (c4 & 63);
          c1 -= 0x10000;
          tmp_arr[ac++] = String.fromCharCode(0xD800 | ((c1>>10) & 0x3FF));
          tmp_arr[ac++] = String.fromCharCode(0xDC00 | (c1 & 0x3FF));
          i += 4;
        }
      }

      return tmp_arr.join('');
    },

    reloadRobotCaptcha: function(){ 
        grecaptcha.reset();
    }
};

var DBUtil = {
    moveRow: function(room, rowID, action){

        var rowObj = document.getElementById("listRow__" + rowID);
        var tableObj = document.getElementById('bodyList');

        if (action == "up") {
            var rowObjAdj = tableObj.rows[rowObj.rowIndex - 1];
        } else if (action == "down") {
            var rowObjAdj = tableObj.rows[rowObj.rowIndex + 1];
        }

        var adjRecordID = rowObjAdj.id.split("__")[1];

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=moveRowData&showHTML=0" ;

        var postData = {
             record_id: rowID
            ,room: room
            ,action: action
            ,adjRecordID: adjRecordID
        };
        $.post(url, postData, function(data){
           /**
           this function will be called after the sort orders are updated at the server end.
           Ref: getMoveRowData function cmspilot/master/admin/pages/main/specialAction.php

           The oRowI is the current row clicked
           The oRowJ is the row the above or below the current row clicked
           If the moveUp arrow is the clicked the adj row is the row above the current row
           If the moveDown arrow is the clicked the adj row is the row below the current row
           **/

            oRowI = rowObj;
            oRowJ = rowObjAdj;
            //*****************************************************************//
            var oTable = oRowI.parentNode;

            if(oRowI.rowIndex == oRowJ.rowIndex+1) {
                oTable.insertBefore(oRowI, oRowJ);
            } else if(oRowJ.rowIndex == oRowI.rowIndex+1) {
                oTable.insertBefore(oRowJ, oRowI);
            } else {
                var tmpNode = oTable.replaceChild(oRowI, oRowJ);
                if(typeof(oRowI) != "undefined") {
                    oTable.insertBefore(tmpNode, oRowI);
                } else {
                    oTable.appendChild(tmpNode);
                }
            }

            //*****************************************************************//
            var oRowIID              = oRowI.id.split("__")[1];

            var fldSortOrderObj       = document.getElementById("fld__sort_order__" + oRowIID);
            var fldSortOrderValue     = fldSortOrderObj.value;

            var tdListRowIndexObj     = document.getElementById("listRowIndex__" + oRowIID);
            var tdListRowIndexHTML    = tdListRowIndexObj.innerHTML;

            //*****************************************************************//
            var oRowJID           = oRowJ.id.split("__")[1];

            var fldSortOrderObjAdj    = document.getElementById("fld__sort_order__" + oRowJID);
            var fldSortOrderValueAdj  = fldSortOrderObjAdj.value;

            var tdListRowIndexObjAdj  = document.getElementById("listRowIndex__" + oRowJID);
            var tdListRowIndexHTMLAdj = tdListRowIndexObjAdj.innerHTML;

            //*****************************************************************//
            fldSortOrderObj.value          = fldSortOrderValueAdj;
            fldSortOrderObjAdj.value       = fldSortOrderValue;

            tdListRowIndexObj.innerHTML    = tdListRowIndexHTMLAdj;
            tdListRowIndexObjAdj.innerHTML = tdListRowIndexHTML;

            DBUtil.resetRowColor(oRowI);
        })
    },

    moveUp: function(room, rowID){

        var rowObj = document.getElementById("listRow__" + rowID);

        if (rowObj.rowIndex == 1){
            return;
        }

        DBUtil.moveRow(room, rowID, "up")
    },

    moveDown: function(room, rowID){
        var rowObj = document.getElementById("listRow__" + rowID);
        var oTable = rowObj.parentNode;
        if (rowObj.rowIndex == oTable.rows.length - 2){
            return;
        }

        DBUtil.moveRow(room, rowID, "down")
    },

    resetRowColor: function(currentRowObj) {
        var tableObj = document.getElementById('bodyList');
        //*** omit first row (header) & last row (footer row)
        for (var i=1; i < tableObj.rows.length - 1; i++) {
            var loopRowObj = tableObj.rows[i];
            if (i%2 == 0) {
                loopRowObj.className = "even";
            }
            else {
                loopRowObj.className = "odd";
            }

            if (currentRowObj.id == loopRowObj.id) {
                loopRowObj.className = "current";
            }

        }
    },

    changeSortBoxValue: function(room, rowID){
        var fldSortOrderObj       = document.getElementById("fld__sort_order__" + rowID);
        var fldSortOrderValue     = fldSortOrderObj.value;

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=changeSortValue&showHTML=0" ;
        var postData = {
             record_id: rowID
            ,room: room
            ,sortValue: fldSortOrderValue
        };
        $.post(url, postData, function(data){
        });
    },

    changeMediaSortValue: function(rowID){

        var fldSortOrderObj       = document.getElementById("fld__sort_order__" + rowID);
        var fldSortOrderValue     = fldSortOrderObj.value;

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=changeSortValue&showHTML=0";

        var room = 'media';
        var postData = {
             record_id: rowID
            ,room: room
            ,sortValue: fldSortOrderValue
        };

        $.post(url, postData, function(data){
        });
    },

    deleteRecordFromList: function(room, rowID){

        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        if ( confirm (msg) )
        {
            var rowObj = document.getElementById("listRow__" + rowID);
            var cpCSFRToken = $('#cpCSFRToken').val();

            var url = $('#scopeRootAlias').val() + "index.php?_spAction=deleteRecordByID&showHTML=0" ;

            var data = {
                 record_id: rowID
                ,room: room
                ,cpCSFRToken: cpCSFRToken
            };
            $.post(url, data, function(json){
                var url = document.location.href;
                document.location = url;
            });
        }

    },

    publishRecordFromList: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=publishRecordByID&showHTML=0";

        var cell = "#txt__published__" + rowID

        $(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });

    },

    flagRecordFromList: function(e){
        e.preventDefault();
        var lnkObj = $(this);
        lnkObj.html('');
        var room = lnkObj.attr('module');
        var record_id = lnkObj.attr('record_id');
        var currentValue = lnkObj.attr('currentValue');
        var color = lnkObj.attr('color');

        var url =  $('#scopeRootAlias').val() + "index.php?_spAction=flagRecordByID"
                + "&record_id=" + record_id
                + "&room=" + room
                + "&currentValue=" + currentValue
                + "&color=" + color
                + "&showHTML=0";

        $.get(url, function (text) {
            $(lnkObj).parent().html(text);
        });
    },

    flagUnflagAllRecordsFromList: function(e){
        e.preventDefault();
        var lnkObj = $(this);

        var action = 'flagAll';
        var actionText = 'flag all';
        var progressText = 'Flagging records';
        if (lnkObj.hasClass('list-unflag-all')) {
            action = 'unflagAll';
            actionText = 'un-flag all';
            progressText = 'Un-flagging records';
        }

        var color = lnkObj.attr('color');

        var msg = "Are you sure to " + actionText + " the records in the list?";
        if (!confirm(msg)){
            return false;
        }

        var idsArr = [];
        $('#bodyList a[record_id]').each(function(ind, obj) {
            idsArr[idsArr.length] = $(obj).attr('record_id');
        });
        var ids = idsArr.join(',');
        var room = $('#cpRoom').val();
        var data = {record_ids: ids, color: color, action: action};
        var url = $('#scopeRootAlias').val() + "index.php?_spAction=flagUnflagAllRecords"
                + "&room=" + room
                + "&showHTML=0";

        Util.showProgressInd(progressText);
        $.post(url, data, function() {
            document.location = document.location;
        });
    },

    publishComment: function(rowID, currentValue){

        var txtFlagObj = document.getElementById("txt__comment__" + rowID);
        txtFlagObj.innerHTML = '';

        var url = $('#scopeRootAlias').val() + "index.php?module=common_comment&_spAction=publishCommentByID" +
        "&record_id=" + rowID + "&currentValue=" + currentValue + "&showHTML=0" ;

        $.get(url, function (data) {
            var txtFlagObj = document.getElementById("txt__comment__" + rowID);

            if (currentValue == 1){
                var currentValue = 0;
            } else {
                var currentValue = 1;
            }
            var imgSrc = '<img src="' + responseText + '\" border=\"0\">';
            var text = '<a href="javascript:DBUtil.publishComment(' + rowID + ',' + currentValue + ')">'+ imgSrc + '</a>';
            txtFlagObj.innerHTML = text;
        });
    }
}

var Actions = {
    addNew: function () {
        $('#frmNew').submit();
    },

    cancelNew: function () {
        msg = "Are you sure you want to cancel?";
        if (confirm (msg)){
            history.back();
        }
    },

    cancelEdit: function () {
        msg = "Are you sure you want to cancel? \n\nYou will lose any changes made";
        if (confirm (msg)){
            history.back();
        }
    },

    edit: function () {
        var url = $(this).attr('url');
        if(url){
            document.location = url;
        }
    },

    save: function (room) {
        $('#frmEdit').submit();
    },

    submitSearchTop: function () {
        $('#searchTop').submit();
    },

    apply: function (room){
        $('form#frmEdit input[name=apply]').val(1);

        Util.setUpAjaxFormGeneral('frmEdit', function(){
            document.location = document.location;
        });
        $('#frmEdit').submit();
    },

    /**
     *
     * save admin edit for automatically
     */
    saveHidden: function(){
        $('form#frmEdit input[name=apply]').val(1);

        opts = {
            progressDlgType: 2
           ,blockFormOnSave: false
        }
        Util.setUpAjaxFormGeneral('frmEdit', null, null, opts);
        $('#frmEdit').submit();
    },

    saveList: function (task) {
        if ($("#listForm input[name^='cid']:checked").length == 0){
            Util.alert("Please tag the record(s) before you proceed");
            return;
        }
        $("#listForm input[name='task']").val(task);
        var cpCSRFToken = $('#cpCSRFToken').val();
        if ( task == "delete" ){
            var msg = "Are you sure to delete the selected record(s)? \n\nYou cannot undo this";
            Util.confirm(msg, function(){
                var url =  $('#listForm').attr('action');
                var data = $('#listForm').serializeArray();
                data.push({name: "cpCSRFToken", value: cpCSRFToken});
                Util.showProgressInd();
                $.post(url, data, function(json){
                    document.location = document.location;
                }, 'json');

            }, {title: 'Delete Records'}
            );

        } else {
            $('#listForm').submit();
        }


    },

    changePassword: function () {
        var url = $(this).attr('url');
        if(!url){
            var url = $(this).attr('href');
        }
        if(url){
            var title = $(this).attr('title');
            Util.openFormInDialog.call(this, 'changePasswordForm', title, 650, 350);
        }
    },

    checkAll: function (fldName) {
        if (!fldName) {
            fldName = 'cid[]';
        }

        var checkedCount = 0;
        $(":checkbox[name='"+ fldName +"']").prop('checked', this.checked);
        $(":checkbox[name='"+ fldName +"']").each(function(index, value){
            var rowID = '#listRow__' + $(this).val();
            if(this.checked){
                $(rowID).addClass('highlight');
                checkedCount++;
            } else {
                $(rowID).removeClass('highlight');
            }
        });


        $("input[name='boxChecked']").val(checkedCount);
    },

    highlightRow: function (checkboxObj, rowID, rowClassName) {
        var rowID = "listRow__" + rowID;
        if (checkboxObj.checked){
            document.getElementById(rowID).className ="highlight";
        } else {
            document.getElementById(rowID).className = rowClassName;
        }
    },

    printListScreen: function (url){
        w = 800;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2

        wind = window.open( url , "printData", windowString);
    },

    printListPDF: function (queryString){
        var room = $('#cpRoom').val();

        var roomNameArr = room.split("_");
        var reportName  = roomNameArr[1];

        var reportName = reportName + 'List';
        var url = $('#scopeRootAlias').val() + 'index.php?_spAction=printReport&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString;
        document.location = url;
    },

    //====================== Print Invoice List to PDF Used in NahviBiz ================================//
    printListPDFInvoice: function (queryString){
        var room = $('#cpRoom').val();

        var roomNameArr = room.split("_");
        var reportName  = roomNameArr[1];

        var reportName = reportName + 'List';
        var url = $('#scopeRootAlias').val() + 'index.php?_topRm=project&module=project_invoice&_spAction=printInvoiceList&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString  + "target='_blank'";
        //document.location = url;
		window.open(url,'_blank');
    },

    //====================== Print Receipt List to PDF Used in NahviBiz ================================//
    printListPDFReceipt: function (queryString){
        var room = $('#cpRoom').val();

        var roomNameArr = room.split("_");
        var reportName  = roomNameArr[1];

        var reportName = reportName + 'List';
        var url = $('#scopeRootAlias').val() + 'index.php?_topRm=project&module=project_receipt&_spAction=printReceiptList&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString  + "target='_blank'";
        //document.location = url;
        window.open(url,'_blank');
    },

    //====================== Print Purchase Order List to PDF Used in All Trading Sites ================================//
    printPOPDFList: function (queryString){
        var room = $('#cpRoom').val();

        var roomNameArr = room.split("_");
        var reportName  = roomNameArr[1];

        var reportName = reportName + 'List';
        var url = $('#scopeRootAlias').val() + 'index.php?_topRm=order&module=tradingsg_purchaseOrder&_spAction=printPurchaseOrder&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString  + "target='_blank'";
        //document.location = url;
		window.open(url,'_blank');
    },

    //====================== Print Invoice to PDF Used in Engex site ================================//
    printInvoicePDFList: function (queryString){
        var room = $('#cpRoom').val();

        var roomNameArr = room.split("_");
        var reportName  = roomNameArr[1];

        var reportName = reportName + 'List';
        var url = $('#scopeRootAlias').val() + 'index.php?_topRm=finance&module=tradingsg_invoice&_spAction=printInvoice&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString  + "target='_blank'";
        //document.location = url;
		window.open(url,'_blank');
    },

    printListPDFPHPExcel: function (queryString){
        var room = $('#cpRoom').val();
        var reportName = room + 'List';
        var url =  $('#scopeRootAlias').val() + 'index.php?_spAction=printReport&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString;
        document.location = url;
    },

    printDetailPDF: function (){
        var room = $('#cpRoom').val();
        var record_id = $('#record_id').val();
        var url =  $('#scopeRootAlias').val() + 'index.php?_spAction=printReport&showHTML=0&roomName=' + room
                  + '&report=' + room
                  + '&record_id=' + record_id
        document.location = url;
    },

    importData: function (room_name, importType) {
        w = 700;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2;

        var record_id = $('#record_id').val();
        var recordIdText = '';
        if (record_id !== undefined) {
            recordIdText = '&record_id=' + record_id;
        }
        var url =  $('#scopeRootAlias').val() + 'index.php?_spAction=importData&room_name=' + room_name
                + '&importType=' + importType
                + recordIdText;
        wind = window.open(url, "importContact", windowString);
    },

    updateData: function (room_name, importType) {
        w = 700;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2;

        var record_id = $('#record_id').val();
        var recordIdText = '';
        if (record_id !== undefined) {
            recordIdText = '&record_id=' + record_id;
        }
        var url =  $('#scopeRootAlias').val() + 'index.php?_spAction=updateData&room_name=' + room_name
                + '&importType=' + importType
                + recordIdText;
        wind = window.open(url, "importContact", windowString);
    },

    deleteRecord: function(room){
        var rowID = $('#record_id').val();

        if (room == ''){
            room = $('#cpRoom').val();
        }
        var cpCSRFToken = $('#cpCSRFToken').val();
        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            var url =  $('#scopeRootAlias').val() + "index.php?_spAction=deleteRecordByID&showHTML=0" ;

            var data = {
                 record_id: rowID
                ,room: room
                ,cpCSRFToken: cpCSRFToken
            };
            $.post(url, data, function(json){
                if(json.status == 'success'){
                    var url = $('#returnToListUrl').val();
                    document.location = url;
                } else {
                    Util.alert(json.message);
                    if(json.refresh){
                        document.location = document.location;
                    }
                }
            }, 'json')

        }, {title: 'Delete Record'}
        );
    },

    duplicateRecord: function (topRoom, room){
        var rowID = $('#record_id').val();
        var msg = "Are you sure you want to duplicate this record?";

        Util.confirm(msg, function(){
            var url = "index.php?_spAction=duplicateRecordByID&showHTML=0";

            var data = {
                 record_id: rowID
                ,room: room
                ,topRoom: topRoom
            };

            $.post(url, data, function(json){
                if(json.status == 'success'){
                    document.location = json.returnUrl;
                } else {
                    Util.alert(json.message);
                }
            }, 'json')

        }, {title: 'Delete Record'}
        );
    },

    printDetailMenu: function (url){
        url += '&_action=detail&_spAction=reportsMenu&showHTML=0';

        var rowID= $('#record_id').val();
        url = url + "&record_id=" + rowID;
        Util.showProgressInd();

        $.get(url, function(data){
            Util.initDialog();
            $('#dialog').html(data);

            var x_dialog = $('#dialog').dialog(
                $.extend(Util.dialogDefaults, {
                    width: 450,
                    height: 230,
                    title: 'Print Menu'
                })
                );
            Util.hideProgressInd();
        });

    },

    openReportsMenu: function(url){
        url += '&_spAction=reportsMenu&showHTML=0';

        var record_id = $('#record_id').val();
        if (record_id){
            url += '&record_id=' + record_id;
        }

        Util.showProgressInd();

        $.get(url, function(data){
            Util.initDialog();
            $('#dialog').html(data);

            var x_dialog = $('#dialog').dialog(
                $.extend(Util.dialogDefaults, {
                    width: 450,
                    height: 230,
                    title: 'Reports Menu'
                })
                );
            Util.hideProgressInd();
        });
    },

    isChecked: function(isItChecked){
        if (isItChecked == true){
            document.list.boxChecked.value++;
        } else {
            document.list.boxChecked.value--;
        }
    }
}



//=====================================================//
var Validate = {

    validateFormData: function(json, statusText, jqFormObj, extraParamObj){
        if (typeof(json) == 'string') {
            eval('var json =' + json);
        }
        var callbackFunction = null;
        var callbackFunctionOnError = null;
        if (extraParamObj) {
            if (extraParamObj.callback) {
                callbackFunction = extraParamObj.callback;
            }

            if (extraParamObj.callbackOnError) {
                callbackFunctionOnError = extraParamObj.callbackOnError;
            }
        }

        $(jqFormObj).find('.progressSpan').removeClass('progress').css('display', 'none');
        var formId = jqFormObj.attr('id');
        var hasErrorDivBox = $(jqFormObj).find('input[name=hasErrorDivBox]').val();

        var errorCount = json.errorCount;
        if(errorCount == 0){
            //no errors
            var successHandler = jqFormObj.attr("success");
            if (successHandler) {
                eval(successHandler);
            }

            //remove any previous error messages
            $(jqFormObj).find('.message').remove();

            var successMsgFld = $('input[name=successMsg]', jqFormObj);
            var dialogMsgFld  = $('input[name=dialogMessage]', jqFormObj);
            var callbackAfterSuccess  = $('input[name=callbackAfterSuccess]', jqFormObj);

            // if you want to use a callback function after the success, then make sure
            // you don't have the fields successMsgFld & dialogMsgFld in the form


            if ($(successMsgFld).length > 0){
                var formHt = $(jqFormObj).height();
                $(jqFormObj).css('height', formHt + 'px');
                $(jqFormObj).html($(successMsgFld).val());
                // $('html,body').animate({scrollTop: $('body').offset().top},'slow'); // commented by BSAS
                $(jqFormObj).animate({scrollTop: $('body').offset().top},'slow');

            } else if ($(dialogMsgFld).length > 0){
                Util.closeAllDialogs();
                Util.alert($(dialogMsgFld).val());

            } else {
                if(callbackFunction) {
                    callbackFunction.call(this, json, statusText, jqFormObj, extraParamObj);


                } else if (json.returnUrl) {
                    document.location = json.returnUrl;
                
                
                } else {
                    var returnUrl = $(jqFormObj).find('[name=returnUrl]').val();
                    if (returnUrl) {
                        document.location = returnUrl;
                    } else if(callbackAfterSuccess) {
                        var callbackAfterSuccessFn = eval(callbackAfterSuccess.val());
                        callbackAfterSuccessFn.call(this, json, statusText, jqFormObj, extraParamObj);
                    } else if(json.returnText) {
                        $(jqFormObj).html(json.returnText);
                    }
                }
            }
        } else {
            //error occurred
            Util.hideProgressInd();
            if(json.hasCSRFTokenError == 1){//CSRF Token Mismatch
                Util.showProgressInd();
                document.location = document.location;
                return;
            }

            if (hasErrorDivBox) {
                var errorText = '';
                $.each(json.errors, function() {
                    errorText  += '<p>' + this.msg + '</p>';
                });
                var errorDivBoxId = $(jqFormObj).find('input[name=errorDivBoxId]').val();
                if (errorDivBoxId) {
                    $('#' + errorDivBoxId).css('display', 'block');
                    $('#' + errorDivBoxId).html(errorText);
                }
            } else {
                //error occurred

                var htmlText  = "";
                htmlText += "<h4>The following errors occured</h4>";

                $(jqFormObj).find('div').removeClass('error');
                $(jqFormObj).find('div strong[class=message]').remove();

                var tmpCount = 0;
                $.each(json.errors, function() {
                    fieldName = this.name;
                    var fieldObj  = $('#fld_' + fieldName,  jqFormObj);

                    var parent = "";

                    if (fieldObj.length > 0){
                        parent = fieldObj.parent('div');
                    } else if (fieldObj.attr('type') == 'radio') {
                        // since each radio is encapsulated in each div we would simply
                        // add the error class to the parent - two level up
                        parent = fieldObj.parent('div').parent('div');

                    } else {
                        /*** radio ***/
                        fieldObj = $('#' + fieldName + '_1',  jqFormObj);
                        if (fieldObj.length > 0){
                            var wrapper = $(fieldObj).closest('.form-row-wrapper');
                            if (wrapper.length > 0){
                                parent = wrapper;
                            } else {
                                parent = fieldObj.parent('div').parent('div');
                            }
                        } else {
                            // checkbox - eg: fld name = interest_id[]
                            fieldObj = $('input[name=' + fieldName + '\\[\\]]',  jqFormObj);
                            if (fieldObj.length > 0){
                                var wrapper = $(fieldObj).closest('.form-row-wrapper');
                                if (wrapper.length > 0){
                                    parent = wrapper;
                                } else {
                                    parent = fieldObj.parent('div').parent('div');
                                }
                            }
                        }
                    }

                    if(parent != ""){
                        parent.addClass('error');

                        if (tmpCount == 0){
                            $('html,body').animate({scrollTop: $(parent).offset().top},'slow');
                        }
                    }

                    if (this.msg) {
                        htmlText  += '<p>' + this.msg + '</p>';

                        if(parent != ''){
                            parent.prepend("<strong class='message'>" + this.msg + "</strong>");
                        }

                        if ($(jqFormObj).hasClass('columnar')){
                            lblWidth = $('label', parent).css('width');
                            $('strong[class=message]', parent).css('margin-left', lblWidth);
                        }
                    }

                    $("#label_" + fieldName).addClass("formFieldLabelError");

                    tmpCount++
                });

                /*
                $(jqFormObj).find("#errorDisplayBox").html(htmlText);
                $(jqFormObj).find("#errorDisplayBox").addClass('errorDisplayBox');
                $(jqFormObj).find("#errorDisplayBox").css('display', 'block');
                */

                if(callbackFunctionOnError) {
                    callbackFunctionOnError.call(this, json, statusText, jqFormObj, extraParamObj);
                    return;
                }

                var customErrorField = $('.customErrorBox', jqFormObj);
                if ($(customErrorField).length > 0){
                    $(jqFormObj).find(".customErrorBox").css('display', 'block');
                }
            }

            if ($('#captcha').length > 0){
                $('#reloadCaptcha').click();
            }
        }
    }
}

//------------------------------------------------//
var Lang = {
    get: function(key, defaultVal) {
        defaultVal = (defaultVal) ? defaultVal : key;
        var data = '';
        if (typeof Lang.data !== 'undefined') {
            data = (Lang.data[key]) ? Lang.data[key] : defaultVal;
        } else {
            data = defaultVal;
        }
        return data;
    }
}

//------------------------------------------------//
var Cfg = {
    get: function(key) {
        return Cfg.data[key];
    }
}

var Links = {
    showEditLinkDialog: function(e) {
        e.preventDefault();
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');
        var validationJsMethod = $(this).attr('validationJsMethod');
        var beforeCloseFnName = $(this).attr('beforeCloseFnName');

        extraPar= {
            beforeCloseFn: function(){
                if (beforeCloseFnName){
                    beforeCloseFnName = eval(beforeCloseFnName);
                    beforeCloseFnName.call();
                }
                Links.reloadPortalRecords(linkName, lnkRoomActual);
            },
            validationJsMethod: validationJsMethod
        }

        Util.openDialogForLink.call(this, 'Edit Link',  $(window).width()-100, $(window).height()-30, 1, extraPar);
    },

    showNewEditPortalForm: function(e) {
        var title = $(this).attr('dialogTitle');
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');


        var portalWidth  = parseInt( $(this).attr('w') );
        var portalHeight = parseInt( $(this).attr('h') );


        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords(linkName, lnkRoomActual);
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, portalWidth, portalHeight, expObj);
    },

    showDetailPortalForm: function(e) {
        var title = $(this).attr('dialogTitle');
        var portalWidth  = parseInt( $(this).attr('w') );
        var portalHeight = parseInt( $(this).attr('h') );

        e.preventDefault();
        var expObj = {
            buttons: {'Close': function() {
                                   $('#dialog').dialog('destroy');
                               }
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, portalWidth, portalHeight, expObj);
    },

    deletePortalRecord: function(e) {
        e.preventDefault();

        var linkName = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');
        var srcRoomId = $(this).attr('srcRoomId');
        var portalDiv = $(this).closest('.linkPortalWrapper');
        var currentPage = $('span.currentPage', portalDiv).text();

        var expReload = {
             portalDiv: portalDiv
            ,currentPage: currentPage
        };

        var url = $(this).attr('link');

        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            Util.showProgressInd();
            $.getJSON(url, function(json){
                if(json.status === 'success'){
                    Links.reloadPortalRecords(linkName, lnkRoomActual, srcRoomId, 'edit', expReload);
                } else {
                    Util.alert(json.message);
                }
            });
        }, {'title': 'Delete Record'});
    },

    addToLinked: function(e) {
        Util.showProgressInd();
        var link = $(this).attr('link');

        $.get(link, function(){
            Links.reloadLinkWindow();
        });
    },

    removeFromLinked: function(e) {
        Util.showProgressInd();
        var link = $(this).attr('link');

        $.get(link, function(){
            Links.reloadLinkWindow();
        });
    },

    addRemoveAll: function(e) {
        Util.showProgressInd();
        var link = $(this).attr('link');

        var wrapper = $(this).closest('.linkWrapper');
        var rows = $('table tbody tr', wrapper);
        var ids = [];
        $.each(rows, function(i){
            ids[i] = $(this).attr('recId');
        });
        $.post(link, {'ids[]': ids}, function(data){
            Links.reloadLinkWindow();
        });
    },

    reloadLinkWindow: function(e) {
        var url1 = $('#url_notLinked').val();
        var url2 = $('#url_linked').val();

        $.get(url1, function(data){
            $('#linkLeft.linkWrapper').hide();
            $('#linkLeft.linkWrapper').html(data);
            $('#linkLeft.linkWrapper').slideDown('3000');
            $.get(url2, function(data){
                $('#linkRight.linkWrapper').hide();
                $('#linkRight.linkWrapper').html(data);
                $('#linkRight.linkWrapper').slideDown('3000');
                Util.hideProgressInd();
            });
        });
    },

    setSingleValue: function() {
        var id = $(this).attr('recId');
        var dialog = $(this).closest('.ui-dialog-content');
        var dialogId = dialog.attr('id');
        var fldName = $('#linkSingleFldId').val();
        $('#' + fldName).val(id);
        $('#' + fldName).trigger('change');

        // fix for selectmenu to re-build the selectmenu with the new value
        //$('#' + fldName).selectmenu();

        $('#' + dialogId).dialog('close');
        $('#' + dialogId).dialog('destroy');
    },

    searchNotLinked: function() {
        var form = $(this);

        var options = {
            success: function(data) {
                $('#linkLeft').hide();
                $('#linkLeft').html(data);
                $('#linkLeft').slideDown('3000');
                Util.hideProgressInd();
            },
            beforeSubmit: function() {
                Util.showProgressInd();
            }
        };

        $(form).ajaxForm(options);

        $('select', form).change(function(){
            form.submit();
        });
    },

    searchLinked: function() {
        var form = $(this);

        var options = {
            success: function(data) {
                $('#linkRight').hide();
                $('#linkRight').html(data);
                $('#linkRight').slideDown('3000');

                Util.hideProgressInd();
            },
            beforeSubmit: function() {
                Util.showProgressInd();
            }
        };

        $(form).ajaxForm(options);

        $('select', form).change(function(){
            form.submit();
        });
    },

    loadDataFromUrl: function(e) {
        Util.showProgressInd();
        var link = $(this).attr('link');
        var wrapper = $(this).closest('.linkWrapper');
        $.get(link, function(data){
            $(wrapper).html(data);
            Util.hideProgressInd();
        });
    },

    filterLinkPortalRecords: function(linkName){
        linkName2 = linkName.replace('#', '__')
        portalDiv = $('.' + linkName2);

        var record_id  = $('#record_id').val();
        var formContent = $('form', portalDiv).serialize();
        formContent += '&linkName=' + linkName + '&record_id=' + record_id;

        var scopeRootAlias = $('#scopeRootAlias').val();
        var url = scopeRootAlias + "index.php?_spAction=linkPortalRecordsByFilter" + "&showHTML=0";

        $.post(url, formContent, function(data){
            $('table', portalDiv).remove();
            $(portalDiv).append(data);
        });
    },

    loadDataByPage: function(){
        var parent = $(this).closest('.linkPortalWrapper');
        var page = $(this).attr('link');
        var pagerUrl = parent.attr('pagerUrl') + '&_page=' + page;
        var formContent = $('form', parent).serialize();

        $.post(pagerUrl, formContent, function(data){
            $('.linkPortalDataWrapper', parent).hide();
            $('.linkPortalDataWrapper', parent).html(data);
            $('.linkPortalDataWrapper', parent).slideDown('slow');
            Util.hideProgressInd();
        });
    },

    reloadPortalRecords: function(linkName, lnkRoomActual, recId, action, extraParamObj){
        Util.showProgressInd();
        linkName2 = linkName.replace('#', '__')

        exp = $.extend({
            portalDiv: null
           ,goToLastPage: false
           ,currentPage: null
        }, extraParamObj || {});

        if(exp.portalDiv){
            portalDiv = exp.portalDiv;
        } else {
            portalDiv = $('.' + linkName2);
        }

        var linkArr = linkName.split("#");
        var srcRoom = linkArr[0];
        var lnkRoom = linkArr[1];

        lnkRoomActual = lnkRoomActual == undefined ? srcRoom : lnkRoomActual;

        var record_id = '';
        if (recId){
            record_id = recId;
        } else if (portalDiv.closest('.childWrapper').length > 0){
            record_id = portalDiv.closest('.childWrapper').attr('parent_id');
        } else {
            record_id = $('#record_id').val();
        }

        if (!action){
            action = $('#cpAction').val();
        }

        var formContent = $('form', portalDiv).serialize();

        var lnkRoomActualText = lnkRoomActual != '' ? '&lnkRoomActual1=' + lnkRoomActual : '';
        var goToLastPage = (exp.goToLastPage) ? '&goToLastPage=1' : '';
        var currentPage = (exp.currentPage) ? '&currentPage=' + exp.currentPage : '';

        formContent += '&record_id=' + record_id
                     + '&_action=' + action
                     + '&srcRoom=' + srcRoom
                     + '&lnkRoom=' + lnkRoom
                     + lnkRoomActualText
                     + goToLastPage
                     + currentPage
        var scopeRootAlias = $('#scopeRootAlias').val();
        var url = scopeRootAlias + "index.php?_spAction=linkPortalRecordsByFilter" + "&showHTML=0";

        $.post(url, formContent, function(data){
            $('.linkPortalDataWrapper', portalDiv).hide();
            $('.linkPortalDataWrapper', portalDiv).html(data);
            $('.linkPortalDataWrapper', portalDiv).slideDown('slow');
            Util.hideProgressInd();
        });
    },

    addNewGridRecord: function(e) {
        e.preventDefault();
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');
        var url = $(this).attr('link');
        var recId = $(this).attr('recId');
        var exp = {
             portalDiv: $(this).closest('.linkPortalWrapper')
            ,goToLastPage: true
        }
        $.post(url, function(data){
            Links.reloadPortalRecords(linkName, lnkRoomActual, recId, 'edit', exp);
        });
    },

    updateGridData: function(){
        var tbl = $(this).closest('table.grid');
        var fldObj = $(this);

        if (fldObj.attr('validation') !== undefined) {
            var validationType = fldObj.attr('validation');
            var fldVal = fldObj.val();
            if (validationType == 'number' && isNaN(fldVal)){
                Util.alert('Please input a valid numeric value');
                fldObj.focus();
                return false;
            }

            if (validationType == 'integer' && (isNaN(fldVal) || !(fldVal+"").match(/^\d+$/))){
                Util.alert('Please input a valid integer value');
                fldObj.focus();
                return false;
            }
        }

        var tr  = $(this).closest('tr');
        var frmObj  = $(this).closest('form');
        var saveUrl = tbl.attr('saveUrl');
        var keyFld  = tbl.attr('keyFld');
        var autoSaveGrid  = tbl.attr('autoSaveGrid');
        if (autoSaveGrid == 1){
            var id  = tr.attr('recId');
            var url = saveUrl + '&' + keyFld + '=' + id;

            //var row={};
            //$(tr).find('input,select,textarea').each(function(){
            //    row[$(this).attr('name')]=$(this).val();
            //});

            row = $(tr).serializeAnything()+ '&' + keyFld + '=' + id;
            Util.showProgressInd2('Saving data...');
            $.post(url, row, function(data){
                Util.hideProgressInd2();
            });
        }
    }
}
