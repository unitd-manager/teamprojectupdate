
/** /cmspilotv30/CP/common/js/functions.js **/
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

    initDialog2: function() {
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

    closeAllDialogs: function() {
        $('#dialog').dialog('close');
        $('#dialog').dialog('destroy');
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
        });
        //$('#' + dialogId).dialog('close');
        //$('#' + dialogId).dialog('destroy');
    },

    dialogDefaults: {bgiframe: true,
                     modal: true,
                     overlay: {opacity:0.8, background:'red'}
    },

    showProgressInd: function(message) {

        if ($('#progressInd').length > 0){
            return;
        }

        if (message == undefined) {
            message = 'Processing...';
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
        $('#progressInd').remove();
        $('.progressInd').each(function(){
            $(this).remove();
        });
    },

    alert: function(text, callback, dialogTitle) {

        if (!dialogTitle){
            dialogTitle = '';
        }

        var dialogId = Util.initDialog2();
        $(dialogId).html(text);
        var xButtons = {};
        xButtons['OK'] = function() {
            $(this).dialog('close');
            $(this).dialog('destroy');

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

    showSimpleMessageInDialog: function(msg) {

        var id = Util.initDialog2();
        $(id).html(msg);

        var xButtons = {};

        xButtons[Lang.data.cp_lbl_close] = function() {
            $(this).dialog('close');
            $(this).dialog('destroy');
        };

        var x_dialog = $(id).dialog(
            $.extend(Util.dialogDefaults, {
                buttons: xButtons
            })
        );
    },

    openDialogForLink: function(dialogTitle, w, h, showCloseBtn, extraParamObj) {
        exp = $.extend({
             url: ''
            ,beforeCloseFn: null
            ,afterOpen: null
            ,useIframe: false
            ,validationJsMethod: ''
        }, extraParamObj || {});

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
                $(this).dialog('destroy');
            };
        }

        if (exp.url) {
            url = exp.url;
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
            ,beforeclose: function(e, ui) {
                if(exp.beforeCloseFn) {
                    exp.beforeCloseFn.call();
                }
        	}
        	,open: function(){
        	    var dialogHt = $(this).closest('.ui-dialog').height();
        	    var windowHt =  $(window).height();

        	    if (h == 'auto' && dialogHt > windowHt){
        	        $(this).height(windowHt-50);
                } else if (h == 'auto' && dialogHt < 250){
                    $(this).height(250);
        	    }
        	}
            ,close: function() { $(this).remove(); }
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
            $.get(url, function(data){
                $(dlgId).html(data);

                if(exp.afterOpen) {
                    exp.afterOpen.call();
                }

                var x_dialog = $(dlgId).dialog(dlgOptions);
                Util.hideProgressInd();
            });
        }
    },

    confirm: function(text, callback, options) {
        var settings = jQuery.extend({
             btn1Label: 'OK'
            ,btn2Label: 'Cancel'
        }, options);

        var dialogId = Util.initDialog();
        $(dialogId).html(text);

        var xButtons = {};
        xButtons[settings.btn1Label] = function() {
            $(this).dialog('close');
            $(this).dialog('destroy');

            if (callback) {
                callback.call(this, settings.btn1Label);
            }
        };
        xButtons[settings.btn2Label] = function() {
            $(this).dialog('close');
            $(this).dialog('destroy');
        };

        $(dialogId).dialog(
            $.extend(Util.dialogDefaults, {
                height: 200,
                width: 350,
                buttons: xButtons
            })
        );
    },

    openFormInDialog: function(formName, dialogTitle, w, h, extraParamObj) {

        if (!w){
           w = 450;
        }

        if (!h){
           h = 400;
        }

        if (!extraParamObj){
            extraParamObj = {};
        }

        if (!extraParamObj.validate){
            extraParamObj.validate = true;
        }

        if (extraParamObj.url) {
            url = extraParamObj.url;
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
            var dialogId = Util.initDialog();
            $(dialogId).html(data);

            var beforeCloseFunction = null;
            if (extraParamObj) {
                if (extraParamObj.beforeCloseFn) {
                    beforeCloseFunction = extraParamObj.beforeCloseFn;
                }
            }

            var xButtons = {};
            if (extraParamObj.buttons) {
                xButtons = extraParamObj.buttons;
            } else {
                var submitBtnText = (extraParamObj.submitBtnText) ? extraParamObj.submitBtnText : 'Submit';
                var cancelBtnText = (extraParamObj.cancelBtnText) ? extraParamObj.cancelBtnText : 'Cancel';
                xButtons[submitBtnText] = function() {
                    $('#' + formName).submit();
                };

                xButtons[cancelBtnText] = function() {
                    $(dialogId).dialog('close');
                    $(dialogId).dialog('destroy');
                    $(dialogId).remove();
                };
            }

            var x_dialog = $(dialogId).dialog(
                $.extend(Util.dialogDefaults, {
                     width: w
                    ,height: h
                    ,title: dialogTitle
                    ,buttons: xButtons
                    ,close: function() { $(this).remove(); }
                    ,beforeclose:function(e, ui){
                        if(beforeCloseFunction) {
                            beforeCloseFunction.call();
                        }
                    }
                    ,open:function(e, ui){
                        if (extraParamObj.onOpenFn) {
                            extraParamObj.onOpenFn.call();
                        }
                    }
                })
            );

            if (extraParamObj.validate) {
                var extraParValid = {};

                if (extraParamObj.callbackOnSuccess) {
                    extraParValid.callback = extraParamObj.callbackOnSuccess;
                }

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

    setUpAjaxFormGeneral: function(formName, cbFunction, beforeSubmitFn) {
        $('#' + formName).livequery(function() {
            /****************************************************/
            var extraPar = {};

            if (cbFunction) {
                extraPar.callback = cbFunction;
            }

            var options = {
                success: function(json, statusText, xhr, jqFormObj) {
                    Validate.validateFormData(json, statusText, jqFormObj, extraPar);
                    Util.hideProgressInd();
                    $('#' + formName).unblock();
                },
                beforeSubmit: function(frmData) {
                    if (beforeSubmitFn) {
                        beforeSubmitFn.call(this, frmData);
                        extraPar.callback = cbFunction;
                    }
                    Util.showProgressInd();
                    $('#' + formName).block({ message: null });
                },
                dataType: 'json'
            };

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

        extraParamObj = $.extend({
             boxTextColor: '#999'
            ,boxTextColorFocus: '#444'
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
            ['Source','-','Preview'],
            '/',
            ['NumberedList','BulletedList', 'Blockquote'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['Link','Unlink','Anchor'],
            ['Image','Flash','Table','HorizontalRule','SpecialChar'],
            '/',
            ['Format','Font','FontSize'],
            ['TextColor'],
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

    loadSubCategoryDropdown: function(){
        $(this).each(function(){
            catId = $('#frmEdit select#fld_category_id').val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=webBasic_subCategory&_spAction=getSubcatJsonByCatId&showHTML=0'

            $.getJSON(url, {category_id: catId}, function(data) {
                $('#frmEdit select#fld_sub_category_id').cp_loadSelect(data);
            });
        });
    },

    loadDropdownByJSON: function(srcFld, srcValue, dstFld, dstRoom){
        var url = $('#scopeRootAlias').val() + 'index.php?room=' + dstRoom + '&_spAction=jsonForDropdown&showHTML=0'

        $.getJSON(url, {srcFld: srcFld, srcValue: srcValue}, function(data) {
            $('#frmEdit select#' + dstFld).cp_loadSelect(data);
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

    apply: function (room){
        $('form#frmEdit input[name=apply]').val(1);

        Util.setUpAjaxFormGeneral('frmEdit', function(){
            document.location = document.location;
        });
        $('#frmEdit').submit();
    },

    saveList: function (task) {
        frmObj = document.forms['list'];

        if (frmObj.boxChecked.value == 0){
            alert("Please tag the record(s) before you proceed");
            return;
        }

        if ( task == "delete" ){
            var msg = "Are you sure to delete the selected record(s)? \n\nYou cannot undo this";
            if ( !confirm (msg)){
                return;
            }
        }

        frmObj.task.value = task;
        frmObj.submit();
    },

    changePassword: function () {
        var url = $(this).attr('url');
        if(url){
    		var title = $(this).attr('title');
            Util.openFormInDialog.call(this, 'changePasswordForm', title, 650, 350);
        }
    },

    checkAll: function (n, fldName) {
        if (!fldName) {
            fldName = 'cb';
        }
        var f = document.list;
        var c = f.toggle.checked;
        var n2 = 0;

        for (i=0; i < n; i++) {
            cb = eval( 'f.' + fldName + '' + i );
            if (cb) {
                cb.checked = c;
                n2++;
            }

            if ( i%2 == 0){
                Actions.highlightRow(cb, cb.value, 'odd');
            } else {
                Actions.highlightRow(cb, cb.value, 'even');
            }

        }

        if (c) {
            document.list.boxChecked.value = n2;
        } else {
            document.list.boxChecked.value = 0;
        }
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
        var reportName = room + 'List';
        var url = 'index.php?_spAction=printReport&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString;
        document.location = url;
    },

    printListPDFPHPExcel: function (queryString){
        var room = $('#cpRoom').val();
        var reportName = room + 'List';
        var url = 'index.php?_spAction=printReport&showHTML=0&roomName=' + room + '&report=' + reportName +
        '&' + queryString;
        document.location = url;
    },

    printDetailPDF: function (){
        var room = $('#cpRoom').val();
        var record_id = $('#record_id').val();
        var url = 'index.php?_spAction=printReport&showHTML=0&roomName=' + room
                  + '&report=' + room
                  + '&record_id=' + record_id
        document.location = url;
    },

    importData: function (room_name, importType) {
        w = 700;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( "index.php?_spAction=importData&room_name=" + room_name + '&importType=' + importType, "importContact", windowString);
    },

    deleteRecord: function(room){
        var rowID= $('#record_id').val();
        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            var url = "index.php?_spAction=deleteRecordByID" +
            "&record_id=" + rowID + "&room=" + room + "&showHTML=0" ;

            $.get(url, function(data){
                var url = $('#returnToListUrl').val();
                document.location = url;
            })

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

    duplicateRecord: function(topRm, room){

        var rowID= document.getElementById('record_id').value;

        msg = "Are you sure you want to duplicate this record?";

        if ( confirm (msg)){
            var url = "index.php?_spAction=duplicate&_topRm=" + topRm +
            "&record_id=" + rowID + "&module=" + room + "&showHTML=0" ;
            document.location = url;
        }

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

        var errorCount = json.errorCount;
        if(errorCount == 0){
           var successHandler = jqFormObj.attr("success");
            if (successHandler) {
                eval(successHandler);
            }

            var successMsgFld = $('input[name=successMsg]', jqFormObj);
            var dialogMsgFld  = $('input[name=dialogMessage]', jqFormObj);

            if ($(successMsgFld).length > 0){
                var formHt = $(jqFormObj).height();
                $(jqFormObj).css('height', formHt + 'px');
                $(jqFormObj).html($(successMsgFld).val());
            } else if ($(dialogMsgFld).length > 0){
                Util.closeAllDialogs();
                Util.alert($(dialogMsgFld).val());
            } else if (returnUrl) {
                document.location = returnUrl;
            } else {
                returnUrl = json.returnUrl;
                if (returnUrl) {
                    document.location = returnUrl;
                } else {
                    var returnUrl = $(jqFormObj).find('[name=returnUrl]').val();
                    if (returnUrl) {
                        document.location = returnUrl;
                    } else if(callbackFunction) {
                        callbackFunction.call(this, json, statusText, jqFormObj, extraParamObj);
                    }
                }

            }
        } else {

            var htmlText  = "";
            htmlText += "<h4>The following errors occured</h4>";

            $(jqFormObj).find('div').removeClass('error');
            $(jqFormObj).find('div strong[class=message]').remove();

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
                    var fieldObj = $('#' + fieldName + '_1',  jqFormObj);
                    if (fieldObj.length > 0){
                        var wrapper = $(fieldObj).closest('.form-row-wrapper');
                        if (wrapper.length > 0){
                            parent = wrapper;
                        } else {
                            parent = fieldObj.parent('div').parent('div');
                        }
                    }
                }

                if(parent != ""){
                    parent.addClass('error');
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

            if ($('#captcha').length > 0){
                $('#reloadCaptcha').click();
            }
        }
    }
}


//------------------------------------------------//
var Lang = {
    get: function(key) {
        return Lang.data[key];
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

        extraPar= {
            beforeCloseFn: function(){
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

        var url = $(this).attr('link');

        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            $.get(url, function(){
                Links.reloadPortalRecords(linkName, lnkRoomActual, srcRoomId, 'edit');
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
            $('#linkLeft').hide();
            $('#linkLeft').html(data);
            $('#linkLeft').slideDown('3000');

            $.get(url2, function(data){
                $('#linkRight').hide();
                $('#linkRight').html(data);
                $('#linkRight').slideDown('3000');
                Util.hideProgressInd();
            });
        });
    },

    setSingleValue: function() {
        var id = $(this).attr('recId');
        var fldName = $('#linkSingleFldId').val();
        $('#' + fldName).val(id);
        $('#' + fldName).trigger('change');

        // fix for selectmenu to re-build the selectmenu with the new value
        //$('#' + fldName).selectmenu();
        $('#dialog').dialog('close');
        $('#dialog').dialog('destroy');
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

    reloadPortalRecords: function(linkName, lnkRoomActual, recId, action){
        linkName2 = linkName.replace('#', '__')
        portalDiv = $('.' + linkName2);

        var linkArr = linkName.split("#");
        var srcRoom = linkArr[0];
        var lnkRoom = linkArr[1];

        lnkRoomActual = lnkRoomActual == undefined ? srcRoom : lnkRoomActual;

        if (recId){
            var record_id = recId;
        } else  if (portalDiv.closest('.childWrapper').length > 0){
            var record_id = portalDiv.closest('.childWrapper').attr('parent_id');
        } else {
            var record_id = $('#record_id').val();
        }

        if (!action){
            var action = $('#cpAction').val();
        }

        var formContent = $('form', portalDiv).serialize();

        var lnkRoomActualText = lnkRoomActual != '' ? '&lnkRoomActual1=' + lnkRoomActual : '';
        formContent += '&record_id=' + record_id
                     + '&_action=' + action
                     + '&srcRoom=' + srcRoom
                     + '&lnkRoom=' + lnkRoom
                     + lnkRoomActualText

        var scopeRootAlias = $('#scopeRootAlias').val();
        var url = scopeRootAlias + "index.php?_spAction=linkPortalRecordsByFilter" + "&showHTML=0";

        $.post(url, formContent, function(data){
            //alert($('.linkPortalDataWrapper', $('.broadcast__testRecipient')).length);

            $('.linkPortalDataWrapper', portalDiv).hide();
            $('.linkPortalDataWrapper', portalDiv).html(data);
            $('.linkPortalDataWrapper', portalDiv).slideDown('slow');
        });
    },

    addNewGridRecord: function(e) {
        e.preventDefault();
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');

        var url = $(this).attr('link');
        var recId = $(this).attr('recId');

        $.post(url, function(data){
            Links.reloadPortalRecords(linkName, lnkRoomActual, recId, 'edit');
        });
    },

    updateGridData: function(){
        var tbl = $(this).closest('table.grid');
        var tr  = $(this).closest('tr');
        var frmObj  = $(this).closest('form');
        var saveUrl = tbl.attr('saveUrl');
        var keyFld  = tbl.attr('keyFld');
        var id  = tr.attr('recId');
        var url = saveUrl + '&' + keyFld + '=' + id;

        //var row={};
        //$(tr).find('input,select,textarea').each(function(){
        //    row[$(this).attr('name')]=$(this).val();
        //});

        row = $(tr).serializeAnything();

        $.post(url, row, function(data){
        });
    }
}

/** /cmspilotv30/CP/common/js/load_ready.js **/
var LoadReady = {
    makeScrollableTable: function(tblType){
        
        var toSubtract = $('#header').height() + $('#nav').height() + $('#nav2').height() + $('#footer').height();
        var windowHt = $(window).height() - toSubtract - 100;
        
        /*** temporary ***/
        $('.listTblWrapper').css({'height' : windowHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
        
        return;
        var table = $('table.list[scrollabletable=1]');
        var tableHt  = table.height();
        var tableWd  = $('.listTblWrapper').outerWidth();

        if (tableHt > windowHt){
        	table.tableScroll({
        	     height: windowHt,
        	     width: tableWd
        	});

            $('.tablescroll_head').addClass('list').attr('cellspacing', 1);
            $('.tablescroll_foot').addClass('list').attr('cellspacing', 1);
            var newWidth = (tableWd) + 'px';
            //table.css('width', newWidth);
            //$('.tablescroll_head').addClass('list').attr('cellspacing', 1).css('width', newWidth);
            //$('.tablescroll_body').css('width', $('.tablescroll_body').width() - 20 + 'px');
            //$('.tablescroll_foot').addClass('list').css('width', newWidth);
            //$('.tablescroll_wrapper').css('width', newWidth);
        }
    },

    showHideRightPanels: function(){
        var toggleBtn = $('div.toggle').click(function(){
            var parent = $(this).closest('.linkPortalWrapper');
            var container = $('.mediaFilesDisplayWrap, .linkPortalDataWrapper', parent);

            $(this).css('background', 'transparent');
            $(this)
            .removeClass('plus, minus')
            .addClass(
                (!container.is(':hidden')) ? 'plus' :
                'minus');

            $('.mediaFilesDisplayWrap, .linkPortalDataWrapper', parent).toggle('fast');
        });

        //*** for initial hide. if the section is hidden by default
        $('div.header[expanded=0]').next().hide();
        $('div.header[expanded=0] div.toggle')
        .removeClass('plus, minus').addClass('plus');
    },

    setupLinks: function () {
        $(function() {
            $('a.editLink').livequery('click', function (e) {
                Links.showEditLinkDialog.call(this, e);
            });

            $('a.newPortalRecord, a.editPortalRecord').live('click', function (e){
                Links.showNewEditPortalForm.call(this, e);
            });

            $('a.newGridRecord').live('click', function (e){
                Links.addNewGridRecord.call(this, e);
            });

            $('a.viewPortalRecord').live('click', Links.showDetailPortalForm);

            $('a.deletePortalRecord').live('click', function(e) {
                Links.deletePortalRecord.call(this, e);
            });

            $('a.editLinkSingle').live('click', function (e) {
                e.preventDefault();
                Util.openDialogForLink.call(this, 'Edit Link',  600, $(window).height()-100, 1);
            });


            $('#linkModalOuter a.addToLinked').live('click', function(e) {
                e.preventDefault();
                Links.addToLinked.call(this);
            });

            $('#linkModalOuter a#addAll').live('click', function(e) {
                e.preventDefault();
                Links.addAll.call(this);
            });

            $('#linkModalOuter a#addRemoveAll').live('click', function(e) {
                e.preventDefault();
                Links.addRemoveAll.call(this);
            });

            $('#linkModalOuter a.removeFromLinked').live('click', function(e) {
                e.preventDefault();
                Links.removeFromLinked.call(this);
            });

            $('#linkModalOuter a.btnSort, #linkModalOuter .pagelinks a').live('click', function(e) {
                e.preventDefault();
                Links.loadDataFromUrl.call(this);
            });

            $('#linkModalOuter a.btnSort, #linkModalOuter .pagelinks a').live('click', function(e) {
                e.preventDefault();
                Links.loadDataFromUrl.call(this);
            });

            $('input.setSingleValue').live('click', function(){
                Links.setSingleValue.call(this);
            });

            $('#linkModalOuter form#searchNotLinked').livequery(function(){
                Links.searchNotLinked.call(this);
            });

            $('#linkModalOuter form#searchLinked').livequery(function(){
                Links.searchLinked.call(this);
            });

            $(".portalSearch select").livequery('change', function() {
                var linkName = $(this).closest('.linkPortalWrapper').attr('id');
                var linkNameActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');
                Links.reloadPortalRecords(linkName, linkNameActual)
            });

            $('.linkPortalDataWrapper .grid input,'  +
              '.linkPortalDataWrapper .grid select,' +
              '.linkPortalDataWrapper .grid textarea').live('change', function(e) {
                e.preventDefault();
                Links.updateGridData.call(this);
            });
        });
    }
}

/******/
$(function() {

    var masterPathAlias = $('#masterPathAlias').val();
    var masterRootPathAlias = $('#masterRootPathAlias').val();

    $("#ui-datepicker-div")
    .livequery(function() {
        $("#ui-datepicker-div").addClass("ui-datepicker-zindex");
    });

    $(".hlist li:last-child")
    .livequery(function() {
        $(this).addClass('last');
    });

    $(".hlist li:first-child")
    .livequery(function() {
        $(this).addClass('first');
    });

    $(".linkPortalWrapper tr td:first, .linkPortalWrapper tr th:first")
    .livequery(function() {
        //$(this).css('border-left', 0);
    });

    $("form#searchTop select").livequery('change', function() {
        $('#searchTop').submit();
    });

    var dateObj = new Date();
    var currentYear = dateObj.getFullYear() + 1;
    $(".fld_date").livequery(function(){
        $(this).datepicker({
            showOn: 'button',
            buttonImage: masterRootPathAlias + 'common/images/icons/calendar_icon.gif',
            dateFormat: 'yy-mm-dd',
            buttonImageOnly: true,
            changeYear: true,
            yearRange: '2001:' + currentYear
        })
        .focus(function(){
            var allowEdit = $(this).attr('allowEdit');
            if (!allowEdit && allowEdit != 1){
                $(this).blur();
            } else {
                 this.select();
            }
        });

        $('#ui-datepicker-div').draggable();
    });

    $(".fld_time").livequery(function(){
        $(this).timepicker({
        	timeFormat: 'hh:mm:ss'
        })
    });

    $("#actBtn_cancelNew").livequery('click', function(e) {
        e.preventDefault();
        Actions.cancelNew()
    });

    $("#actBtn_addNew").livequery('click', function(e) {
        e.preventDefault();
        Actions.addNew();
    });

    $("#actBtn_save").livequery('click', function(e) {
        e.preventDefault();
        Actions.save();
    });

    $("#actBtn_cancel").livequery('click', function(e) {
        e.preventDefault();
        Actions.cancelEdit();
    });

    $("input#actBtn_edit").livequery('click', function(e) {
        e.preventDefault();
        Actions.edit.call(this);
    });

    $("#actBtn_changePassword").livequery('click', function(e) {
        e.preventDefault();
        Actions.changePassword.call(this);
    });


    if ($('.yform.detail').length > 0){
        $('fieldset').each(function(){
            var lastDiv = $('div:last', $(this));

            if (lastDiv.hasClass('type-check')){
                lastParent = lastDiv.closest('.form-row-wrapper');
            } else {
                lastParent = lastDiv.parent();
            }

            lastParent.css('border-bottom', '1px solid #ddd');
        });
    }

    $('.yform.detail .openLink').hide();
    $('.yform .actBtnsInNewForm').show();

    //Auto grow for text area
    if( $.fn.elastic) {
        $("textarea").livequery(function(){
            $(this).elastic();
        });
    }

    $("a.editFromList").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                document.location = document.location;
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

    $('.cp_ckeditor').livequery(function(){
        includeStylesheet = $(this).attr('includeStylesheet');
        stylesheetPath = $(this).attr('stylesheetPath');
        var jssPath = $('#jssPath').val();

        var fldId = $(this).attr('id');

        var instance = CKEDITOR.instances[fldId];
        if(instance){
            CKEDITOR.remove(instance);
        }

        if ($('#cpScope').val() == 'www'){
            toolbar = Util.getCKEditorToolBarsLite();
        } else {
            toolbar = Util.getCKEditorToolBarsFull();
        }

        $(this).ckeditor(function() {
        },
        {
            toolbar : toolbar,
            bodyClass: 'cpCkEdiorBody',
            filebrowserBrowseUrl : jssPath + 'ckfinder/ckfinder.html',
            filebrowserImageBrowseUrl: jssPath + 'ckfinder/ckfinder.html?Type=Images',
            filebrowserFlashBrowseUrl: jssPath + 'ckfinder/ckfinder.html?Type=Flash',
            filebrowserUploadUrl: jssPath + 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
            filebrowserImageUploadUrl: jssPath + 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
            filebrowserFlashUploadUrl: jssPath + 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
        })

        var editor = $(this).ckeditorGet();
        if (includeStylesheet){
            editor.config.contentsCss = stylesheetPath;
        }
    });

});

/***
 * Pacth for dialog-fix ckeditor problem [ by ticket #4727 ]
 * 	http://dev.jqueryui.com/ticket/4727
 */

$(function() {
    $.extend($.ui.dialog.overlay, { create: function(dialog){
    	if (this.instances.length === 0) {
    		// prevent use of anchors and inputs
    		// we use a setTimeout in case the overlay is created from an
    		// event that we're going to be cancelling (see #2804)
    		setTimeout(function() {
    			// handle $(el).dialog().dialog('close') (see #4065)
    			if ($.ui.dialog.overlay.instances.length) {
    				$(document).bind($.ui.dialog.overlay.events, function(event) {
    					var parentDialog = $(event.target).parents('.ui-dialog');
    					if (parentDialog.length > 0) {
    						var parentDialogZIndex = parentDialog.css('zIndex') || 0;
    						return parentDialogZIndex > $.ui.dialog.overlay.maxZ;
    					}

    					var aboveOverlay = false;
    					$(event.target).parents().each(function() {
    						var currentZ = $(this).css('zIndex') || 0;
    						if (currentZ > $.ui.dialog.overlay.maxZ) {
    							aboveOverlay = true;
    							return;
    						}
    					});

    					return aboveOverlay;
    				});
    			}
    		}, 1);

    		// allow closing by pressing the escape key
    		$(document).bind('keydown.dialog-overlay', function(event) {
    			(dialog.options.closeOnEscape && event.keyCode
    					&& event.keyCode == $.ui.keyCode.ESCAPE && dialog.close(event));
    		});

    		// handle window resize
    		$(window).bind('resize.dialog-overlay', $.ui.dialog.overlay.resize);
    	}

    	var $el = $('<div></div>').appendTo(document.body)
    		.addClass('ui-widget-overlay').css({
    		width: this.width(),
    		height: this.height()
    	});

    	(dialog.options.stackfix && $.fn.stackfix && $el.stackfix());

    	this.instances.push($el);
    	return $el;
    }});

    $('a.jqui-dialog').live('click', function(e) {
		e.preventDefault();
		var title = $(this).attr('title');
        Util.openDialogForLink.call(this, title);
	});

    $('a.jqui-dialog-form').live('click', function(e) {
		e.preventDefault();
		var title = $(this).attr('title');
		var formName = $(this).attr('formId');
		var w = $(this).attr('w');
		var h = $(this).attr('h');
        Util.openFormInDialog.call(this, formName, title, w, h);
	});

    if ($('form.cpJqForm').length > 0){
        $('form.cpJqForm').livequery(function() {
            var formId = $(this).attr('id');
            Util.setUpAjaxFormGeneral(formId);
    	});
    }

    if ($('form.cpUploadifyForm').length > 0){
        $('form.cpUploadifyForm').livequery(function() {
            var formId = $(this).attr('id');
            Util.setUpFormWithUploadify(formId);
    	});
    }

    $('form[autoSubmitOnChange=1] select').change(function(){
        Util.showProgressInd();
        $(this).closest('form').submit();
        Util.hideProgressInd();
    });

    $('a.cpBack').click(function(){
        history.back();
    });

    $.extend($.ui.dialog.prototype, {
        'addbutton': function(buttonName, func) {
            var buttons = this.element.dialog('option', 'buttons');
            buttons[buttonName] = func;
            this.element.dialog('option', 'buttons', buttons);
        }
    });

    $.extend($.ui.dialog.prototype, {
        'removebutton': function(buttonName) {
            var buttons = this.element.dialog('option', 'buttons');
            delete buttons[buttonName];
            this.element.dialog('option', 'buttons', buttons);
        }
    });
});

/* @projectDescription jQuery Serialize Anything - Serialize anything (and not just forms!)
 * @author Bramus! (Bram Van Damme)
 * @version 1.0
 * @website: http://www.bram.us/
 * @license : BSD
*/
(function($) {
$.fn.serializeAnything = function() {
    var toReturn = [];
    var els   = $(this).find(':input').get();
    $.each(els, function() {
        if (this.name && !this.disabled && (this.checked || /select|textarea/i.test(this.nodeName) || /text|hidden|password/i.test(this.type))) {
            var val = $(this).val();
            toReturn.push( encodeURIComponent(this.name) + "=" + encodeURIComponent( val ) );
        }
    });
    return toReturn.join("&").replace(/%20/g, "+");
}
})(jQuery);
/** /cmspilotv30/CP/www/js/functions.js **/
$(function(){
    $('.fatList .row:last').addClass('last');
});
/** /www/js/functions.js **/
//=====================================================//
//** extending the master Util class
var Util = $.extend(Util, {

});

//=====================================================//
var Validate = $.extend(Validate, {

});


/** /cmspilotv30/CP/common/plugins/Common/Media/functions.js **/
Util.createCPObject('cpp.common.media');

cpp.common.media = {
    setUploadify: function(room, record_type, id, sessionID, extraParamObj){
        queueID   = 'fileQueueMedia_' + record_type;

        /** used in admin to re-load the media in the base container **/
        mainDivId = "media__" + record_type;
        mainDiv = $('#' + mainDivId);

        var uploadifyId = '#uploadifyMedia_' + record_type;
        var frmObj = $(uploadifyId).closest('form')

        var masterPathAlias = $('#masterPathAlias').val();
        var jssPath = $('#jssPath').val();

        var scopeRootAlias = $('#scopeRootAlias').val();

        var onAllCompleteFunction = null;
        var allowMultiUpload = true;

        if (!extraParamObj) {
            extraParamObj = {};
        }

        var lang = extraParamObj.lang;
        if (extraParamObj.onAllCompleteFunction) {
            onAllCompleteFunction = extraParamObj.onAllCompleteFunction;
        }

        if (extraParamObj.allowMultiUpload) {
            allowMultiUpload = extraParamObj.allowMultiUpload;
        }

        if (extraParamObj.uploadifyId) {
            uploadifyId = extraParamObj.uploadifyId;
        }

        var browseButtonImg = (extraParamObj.browseButtonImg) ? extraParamObj.browseButtonImg: masterPathAlias + 'images/icons/btn_browse.png';
        var btnWidth = (extraParamObj.btnWidth) ? extraParamObj.btnWidth : 50;
        var btnHeight = (extraParamObj.btnHeight) ? extraParamObj.btnHeight : 20;
        var sizeLimit = (extraParamObj.fileSizeLimit) ? extraParamObj.fileSizeLimit : 1024 * 1000;
        var queueSizeLimit = (extraParamObj.queueSizeLimit) ? extraParamObj.queueSizeLimit : 5;

        $(uploadifyId).uploadify({
              'uploader'       : jssPath + 'jquery/uploadify-2.1.4/uploadify.swf'
            , 'buttonImg'      : browseButtonImg
            , 'script'         : scopeRootAlias + 'index.php'
            , 'fileDataName'   : 'fileName'
            , 'cancelImg'      : jssPath + 'jquery/uploadify-2.1.4/cancel.png'
            , 'queueID'        : queueID
            , 'multi'          : allowMultiUpload
            , 'width'          : btnWidth
            , 'height'         : btnHeight
            , 'sizeLimit'      : sizeLimit
            , 'queueSizeLimit' : queueSizeLimit
            , 'scriptData'     : {
                                   'plugin'       :'common_media'
                                  ,'_spAction'   :'addMedia'
                                  ,'room'        :room
                                  ,'recordType'  :record_type
                                  ,'id'          :id
                                  ,'showHTML'    :'0'
                                  ,'sessionIDCP' :sessionID
                                  ,'successText' :'success'
                                  ,'lang'        : lang
                                 }
            , 'onSelect'         : function(e, queueID, fileObj){
                if(fileObj.size > sizeLimit){
                    var sizeLimitMB = sizeLimit/(1024 * 1024);
                    alert('The file size shoulb be less than ' + sizeLimitMB.toFixed(2) + ' MB.');
                    $(this).uploadifyCancel(queueID);
                }
            }
            , 'onOpen': function(){
                Util.showProgressInd();
            }
            , 'onComplete': function(e, queueID, fileObj, response){
                //alert (response);
            }
            , 'onAllComplete': function(e, data){
                if(onAllCompleteFunction) {
                    onAllCompleteFunction.call();
                } else if ($('.mediaFilesDisplayWrap', mainDiv).length != 0){
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFilesDisplayWrap', mainDiv));
                    $(uploadifyId).closest('div.popcontents').dialog('close');
                    $(uploadifyId).closest('div.popcontents').dialog('destroy');
                }
                if (extraParamObj.formSuccessMsg && extraParamObj.formSuccessMsg != '') {
                    var formHt = frmObj.height();
                    frmObj.css('height', formHt + 'px');
                    frmObj.html(extraParamObj.formSuccessMsg);
                    frmObj.hide().slideDown();
                }

                Util.hideProgressInd();
            }
        });

        $(function() {
            $('a.uploadQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('input[type=file]', $(this).closest('.uploadWrap')).attr('id');
                //alert($('#' + fileID).length);

                /*
                var record_type = $(this).attr('record_type');
                $('#' + fileID).uploadifySettings('scriptData', {'recordType': record_type});
                */

                $('#' + fileID).uploadifyUpload();
            });

            $('a.clearQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('input[type=file]', $(this).parent()).attr('id');
                $('#' + fileID).uploadifyClearQueue();
            });
        });
    },

    reloadMediaFilesDisplay: function(container) {
        var url = $(container).attr('url');
        $.get(url, function(data){
                $(container).hide();
                $(container).html(data);
                $(container).slideDown(1000);
                Util.hideProgressInd();
            }
        );
    },

    deleteMedia: function(e){
        e.preventDefault();
        var media_id    = $(this).attr('id');
        var link        = $(this);
        var url         = 'index.php?plugin=common_media&_spAction=deleteMedia&media_id=' + media_id + '&showHTML=0';

        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            Util.showProgressInd();

            $.get(url, function(){
                var container = $(link).closest('.mediaFilesDisplayWrap');
                cpp.common.media.reloadMediaFilesDisplay(container);
            });
        });
    },

    editMediaPropeties: function(e){
        e.preventDefault();
        var media_id    = $(this).attr('id');
        var link        = this;
        var container   = $(this).closest('.mediaFilesDisplayWrap');

        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                cpp.common.media.reloadMediaFilesDisplay(container);
                $('#frmEditMediaProp').closest('div.popcontents').dialog('close');
                $('#frmEditMediaProp').closest('div.popcontents').dialog('destroy');
            }
        }
        Util.openFormInDialog.call(link, 'frmEditMediaProp', 'Edit Media Properties', 600, 400, expObj);
    },

    cropMediaForm: function(e){
        e.preventDefault();
        var media_id  = $(this).attr('id');
        var cropWidth  = $(this).attr('cropWidth');
        var cropHeight = $(this).attr('cropHeight');
        var aspectRatio = cropWidth/cropHeight;

        var link      = this;
        var container = $(this).closest('.mediaFilesDisplayWrap');

        xButtons = {};
        xButtons['Crop'] = function() {
            $('#frmCropMedia').submit();
        };

        xButtons['Cancel'] = function() {
            $(this).dialog('close');
            $(this).dialog('destroy');
            $(this).remove();
        };
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                cpp.common.media.reloadMediaFilesDisplay(container);
                $('#frmCropMedia').closest('div.popcontents').dialog('close');
                $('#frmCropMedia').closest('div.popcontents').dialog('destroy');
            },
            onOpenFn: function(){
                var initialCropX = 10;
                var initialCropY = 10;
                $('#cropX').val(initialCropX);
                $('#cropY').val(initialCropY);
                $('#cropW').val(cropWidth);
                $('#cropH').val(cropHeight);                
				$('#cropbox').Jcrop({
					aspectRatio: aspectRatio,
                    setSelect: [initialCropX, initialCropX, cropWidth, cropHeight],
					onSelect: function (c){
                        $('#cropX').val(c.x);
                        $('#cropY').val(c.y);
                        $('#cropW').val(c.w);
                        $('#cropH').val(c.h);
                    }
				});
                Util.hideProgressInd();
            },
            buttons: xButtons
        }
        Util.openFormInDialog.call(link, 'frmCropMedia', 'Crop Media', 800, 560, expObj);
    },

    setZoomImageDefaults: function(){
        if ($("a.cpZoom").length > 0){
            $("a.cpZoom").colorbox();
        }

        if ($("a[rel='cpZoomGallery']").length > 0){
            $("a[rel='cpZoomGallery']").colorbox({'maxHeight': $(window).height(), 'maxWidth': $(window).width()});
        }
    },

    init: function(){
        $(function(){
            $("a.btnSelectMedia").live('click', function (e) {
                e.preventDefault();
                Util.openDialogForLink.call(this, 'Upload Media', 400, 265, 1);
            });

            $("a.removeMedia").live('click', cpp.common.media.deleteMedia);
            $("a.editMedia").live('click', cpp.common.media.editMediaPropeties);
            $("a.cropMedia").live('click', cpp.common.media.cropMediaForm);
        });
    }
}

$(function(){
    cpp.common.media.init();
    cpp.common.media.setZoomImageDefaults();
});

/** /cmspilotv30/CP/www/widgets/Core/MainNav/functions.js **/
Util.createCPObject('cpw.core.mainNav');

cpw.core.mainNav.animageBg = function(){
    $('.hlist.animateBg ul li a').hover(
		function(){
		    if (!$(this).closest('li').hasClass('active')){
                $(this).css({backgroundPosition: 'left bottom'}).animate({backgroundPosition: 'right top'}, 1000, 'easeOutBounce'); 
            }
		},
		function(){
		    if (!$(this).closest('li').hasClass('active')){
                $(this).css({backgroundPosition: 'right top'}).animate({backgroundPosition: 'left bottom'}, 1000, 'easeOutBounce'); 
            }
		}
	);
}

$(function(){
});

/** /cmspilotv30/CP/www/widgets/Core/SubNav/functions.js **/
Util.createCPObject('cpw.core.subNav');

cpw.core.subNav.useJqUiStyle = function(){
    $('.subNavTab').addClass('ui-tabs ui-widget ui-widget-content ui-corner-all');
    $('.subNavTab .w-core-subNav ul').addClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all');
    $('.subNavTab .w-core-subNav ul.ui-tabs-nav li').addClass('ui-state-default ui-corner-top');
    $('.subNavTab .w-core-subNav ul.ui-tabs-nav li.active').addClass('ui-tabs-selected ui-state-active');
    $('.subNavTab .ui-widget-header').css('height', '32px');
    $('.subNavTab .subNavTab .ui-tabs-nav li.ui-tabs-selected a').css('cursor', 'pointer');
    $('.subNavTab .ui-tabs-panel').css('min-height', '200px');
}

$(function(){
});

/** /cmspilotv30/CP/common/modules/WebBasic/Content/functions.js **/
Util.createCPObject('cpm.webBasic.content');

$(function(){
    $('#frmEdit select#fld_section_id').livequery('change', function(){
       Util.loadCategoryDropdown.call(this);
    });

    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
});

/** /cmspilotv30/CP/common/plugins/Common/Comment/functions.js **/
Util.createCPObject('cpp.common.comment');

cpp.common.comment.init = function(){
    $(function(){
        Util.setUpAjaxFormGeneral('frmComment', function(){
            cpp.common.comment.reload();
        });
            
        $('#toggleComments, #cancelComment').live('click', function(){
            $('#commentForm').toggle();
        });

        $('a.editComment').live('click', function(e) {
            var record_id = $(this).attr('record_id');
            var scopeRootAlias = $('#scopeRootAlias').val();
            var url  = scopeRootAlias + "index.php?_spAction=edit" + "&record_id=" + record_id + "&plugin=common_comment&showHTML=0" ;
            extraPar = {
                validate: true
               ,url: url
               ,callbackOnSuccess: function(){
                    $('#dialog').dialog('close');
                    $('#dialog').dialog('destroy');
                    cpp.common.comment.reload();
               }
            };

            Util.openFormInDialog('editComment', 'Edit Notes', 300, 300, extraPar)
        });

        $('a.deleteComment').live('click', function(e) {
            e.preventDefault();

            var record_id = $(this).attr('record_id');
            var contact_module = $(this).attr('contact_module');
            msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

            Util.confirm(msg, function(){
                var url = "index.php?_spAction=delete" +
                "&record_id=" + record_id + '&contact_module=' + contact_module + "&plugin=common_comment&showHTML=0" ;
                $.get(url, function(){
                    cpp.common.comment.reload();
                });
            });
        });

        $('#addComment').livequery('click', function(e){
            e.preventDefault();
            $('#frmComment').submit();
        });
    });
}

cpp.common.comment.reload = function(){
    var url = $('input[name=p-common-comment_url]').val();
    $.get(url, function(data){
        $('.p-common-comment').addClass('to-remove');
        $(data).insertAfter('.p-common-comment');
        $('.p-common-comment.to-remove').remove();
        $('#frmComment').resetForm();
        $('#commentForm').hide('slow');
    });
}

$(function(){
    cpp.common.comment.init();
});

/** /cmspilotv30/CP/common/plugins/Common/Media/functions.js **/
Util.createCPObject('cpp.common.media');

cpp.common.media = {
    setUploadify: function(room, record_type, id, sessionID, extraParamObj){
        queueID   = 'fileQueueMedia_' + record_type;

        /** used in admin to re-load the media in the base container **/
        mainDivId = "media__" + record_type;
        mainDiv = $('#' + mainDivId);

        var uploadifyId = '#uploadifyMedia_' + record_type;
        var frmObj = $(uploadifyId).closest('form')

        var masterPathAlias = $('#masterPathAlias').val();
        var jssPath = $('#jssPath').val();

        var scopeRootAlias = $('#scopeRootAlias').val();

        var onAllCompleteFunction = null;
        var allowMultiUpload = true;

        if (!extraParamObj) {
            extraParamObj = {};
        }

        var lang = extraParamObj.lang;
        if (extraParamObj.onAllCompleteFunction) {
            onAllCompleteFunction = extraParamObj.onAllCompleteFunction;
        }

        if (extraParamObj.allowMultiUpload) {
            allowMultiUpload = extraParamObj.allowMultiUpload;
        }

        if (extraParamObj.uploadifyId) {
            uploadifyId = extraParamObj.uploadifyId;
        }

        var browseButtonImg = (extraParamObj.browseButtonImg) ? extraParamObj.browseButtonImg: masterPathAlias + 'images/icons/btn_browse.png';
        var btnWidth = (extraParamObj.btnWidth) ? extraParamObj.btnWidth : 50;
        var btnHeight = (extraParamObj.btnHeight) ? extraParamObj.btnHeight : 20;
        var sizeLimit = (extraParamObj.fileSizeLimit) ? extraParamObj.fileSizeLimit : 1024 * 1000;
        var queueSizeLimit = (extraParamObj.queueSizeLimit) ? extraParamObj.queueSizeLimit : 5;

        $(uploadifyId).uploadify({
              'uploader'       : jssPath + 'jquery/uploadify-2.1.4/uploadify.swf'
            , 'buttonImg'      : browseButtonImg
            , 'script'         : scopeRootAlias + 'index.php'
            , 'fileDataName'   : 'fileName'
            , 'cancelImg'      : jssPath + 'jquery/uploadify-2.1.4/cancel.png'
            , 'queueID'        : queueID
            , 'multi'          : allowMultiUpload
            , 'width'          : btnWidth
            , 'height'         : btnHeight
            , 'sizeLimit'      : sizeLimit
            , 'queueSizeLimit' : queueSizeLimit
            , 'scriptData'     : {
                                   'plugin'       :'common_media'
                                  ,'_spAction'   :'addMedia'
                                  ,'room'        :room
                                  ,'recordType'  :record_type
                                  ,'id'          :id
                                  ,'showHTML'    :'0'
                                  ,'sessionIDCP' :sessionID
                                  ,'successText' :'success'
                                  ,'lang'        : lang
                                 }
            , 'onSelect'         : function(e, queueID, fileObj){
                if(fileObj.size > sizeLimit){
                    var sizeLimitMB = sizeLimit/(1024 * 1024);
                    alert('The file size shoulb be less than ' + sizeLimitMB.toFixed(2) + ' MB.');
                    $(this).uploadifyCancel(queueID);
                }
            }
            , 'onOpen': function(){
                Util.showProgressInd();
            }
            , 'onComplete': function(e, queueID, fileObj, response){
                //alert (response);
            }
            , 'onAllComplete': function(e, data){
                if(onAllCompleteFunction) {
                    onAllCompleteFunction.call();
                } else if ($('.mediaFilesDisplayWrap', mainDiv).length != 0){
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFilesDisplayWrap', mainDiv));
                    $(uploadifyId).closest('div.popcontents').dialog('close');
                    $(uploadifyId).closest('div.popcontents').dialog('destroy');
                }
                if (extraParamObj.formSuccessMsg && extraParamObj.formSuccessMsg != '') {
                    var formHt = frmObj.height();
                    frmObj.css('height', formHt + 'px');
                    frmObj.html(extraParamObj.formSuccessMsg);
                    frmObj.hide().slideDown();
                }

                Util.hideProgressInd();
            }
        });

        $(function() {
            $('a.uploadQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('input[type=file]', $(this).closest('.uploadWrap')).attr('id');
                //alert($('#' + fileID).length);

                /*
                var record_type = $(this).attr('record_type');
                $('#' + fileID).uploadifySettings('scriptData', {'recordType': record_type});
                */

                $('#' + fileID).uploadifyUpload();
            });

            $('a.clearQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('input[type=file]', $(this).parent()).attr('id');
                $('#' + fileID).uploadifyClearQueue();
            });
        });
    },

    reloadMediaFilesDisplay: function(container) {
        var url = $(container).attr('url');
        $.get(url, function(data){
                $(container).hide();
                $(container).html(data);
                $(container).slideDown(1000);
                Util.hideProgressInd();
            }
        );
    },

    deleteMedia: function(e){
        e.preventDefault();
        var media_id    = $(this).attr('id');
        var link        = $(this);
        var url         = 'index.php?plugin=common_media&_spAction=deleteMedia&media_id=' + media_id + '&showHTML=0';

        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            Util.showProgressInd();

            $.get(url, function(){
                var container = $(link).closest('.mediaFilesDisplayWrap');
                cpp.common.media.reloadMediaFilesDisplay(container);
            });
        });
    },

    editMediaPropeties: function(e){
        e.preventDefault();
        var media_id    = $(this).attr('id');
        var link        = this;
        var container   = $(this).closest('.mediaFilesDisplayWrap');

        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                cpp.common.media.reloadMediaFilesDisplay(container);
                $('#frmEditMediaProp').closest('div.popcontents').dialog('close');
                $('#frmEditMediaProp').closest('div.popcontents').dialog('destroy');
            }
        }
        Util.openFormInDialog.call(link, 'frmEditMediaProp', 'Edit Media Properties', 600, 400, expObj);
    },

    cropMediaForm: function(e){
        e.preventDefault();
        var media_id  = $(this).attr('id');
        var cropWidth  = $(this).attr('cropWidth');
        var cropHeight = $(this).attr('cropHeight');
        var aspectRatio = cropWidth/cropHeight;

        var link      = this;
        var container = $(this).closest('.mediaFilesDisplayWrap');

        xButtons = {};
        xButtons['Crop'] = function() {
            $('#frmCropMedia').submit();
        };

        xButtons['Cancel'] = function() {
            $(this).dialog('close');
            $(this).dialog('destroy');
            $(this).remove();
        };
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                cpp.common.media.reloadMediaFilesDisplay(container);
                $('#frmCropMedia').closest('div.popcontents').dialog('close');
                $('#frmCropMedia').closest('div.popcontents').dialog('destroy');
            },
            onOpenFn: function(){
                var initialCropX = 10;
                var initialCropY = 10;
                $('#cropX').val(initialCropX);
                $('#cropY').val(initialCropY);
                $('#cropW').val(cropWidth);
                $('#cropH').val(cropHeight);                
				$('#cropbox').Jcrop({
					aspectRatio: aspectRatio,
                    setSelect: [initialCropX, initialCropX, cropWidth, cropHeight],
					onSelect: function (c){
                        $('#cropX').val(c.x);
                        $('#cropY').val(c.y);
                        $('#cropW').val(c.w);
                        $('#cropH').val(c.h);
                    }
				});
                Util.hideProgressInd();
            },
            buttons: xButtons
        }
        Util.openFormInDialog.call(link, 'frmCropMedia', 'Crop Media', 800, 560, expObj);
    },

    setZoomImageDefaults: function(){
        if ($("a.cpZoom").length > 0){
            $("a.cpZoom").colorbox();
        }

        if ($("a[rel='cpZoomGallery']").length > 0){
            $("a[rel='cpZoomGallery']").colorbox({'maxHeight': $(window).height(), 'maxWidth': $(window).width()});
        }
    },

    init: function(){
        $(function(){
            $("a.btnSelectMedia").live('click', function (e) {
                e.preventDefault();
                Util.openDialogForLink.call(this, 'Upload Media', 400, 265, 1);
            });

            $("a.removeMedia").live('click', cpp.common.media.deleteMedia);
            $("a.editMedia").live('click', cpp.common.media.editMediaPropeties);
            $("a.cropMedia").live('click', cpp.common.media.cropMediaForm);
        });
    }
}

$(function(){
    cpp.common.media.init();
    cpp.common.media.setZoomImageDefaults();
});

/** /cmspilotv30/CP/www/plugins/Common/SiteSearch/functions.js **/
$(function() {
    $('#siteSearch form').submit(function(e){
        e.preventDefault();
        Util.clearPrepopulatedTextbox($(this));
        $(this).unbind('submit');
        $(this).trigger('submit');
    });
});
/** /cmspilotv30/CP/www/widgets/Core/MainNav/functions.js **/
Util.createCPObject('cpw.core.mainNav');

cpw.core.mainNav.animageBg = function(){
    $('.hlist.animateBg ul li a').hover(
		function(){
		    if (!$(this).closest('li').hasClass('active')){
                $(this).css({backgroundPosition: 'left bottom'}).animate({backgroundPosition: 'right top'}, 1000, 'easeOutBounce'); 
            }
		},
		function(){
		    if (!$(this).closest('li').hasClass('active')){
                $(this).css({backgroundPosition: 'right top'}).animate({backgroundPosition: 'left bottom'}, 1000, 'easeOutBounce'); 
            }
		}
	);
}

$(function(){
});

/** /cmspilotv30/CP/www/widgets/Core/SubNav/functions.js **/
Util.createCPObject('cpw.core.subNav');

cpw.core.subNav.useJqUiStyle = function(){
    $('.subNavTab').addClass('ui-tabs ui-widget ui-widget-content ui-corner-all');
    $('.subNavTab .w-core-subNav ul').addClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all');
    $('.subNavTab .w-core-subNav ul.ui-tabs-nav li').addClass('ui-state-default ui-corner-top');
    $('.subNavTab .w-core-subNav ul.ui-tabs-nav li.active').addClass('ui-tabs-selected ui-state-active');
    $('.subNavTab .ui-widget-header').css('height', '32px');
    $('.subNavTab .subNavTab .ui-tabs-nav li.ui-tabs-selected a').css('cursor', 'pointer');
    $('.subNavTab .ui-tabs-panel').css('min-height', '200px');
}

$(function(){
});

/** /cmspilotv30/CP/www/widgets/Media/AnythingSlider/functions.js **/
Util.createCPObject('cpw.media.anythingSlider');

cpw.media.anythingSlider.init = function(exp){
    var speed    = exp.speed;
    var showNav  = exp.showNav;
    var theme    = exp.theme;
    var handle   = exp.handle;
    var width    = exp.width;
    var height   = exp.height;
    var resizeContents   = exp.resizeContents;

    $('#' + handle).anythingSlider({
        width: width,
        height: height,
        resizeContents: resizeContents,
        theme: theme,
		autoPlayLocked  : true,  // If true, user changing slides will not stop the slideshow
		resumeDelay     : 6000, // Resume slideshow after user interaction, only if autoplayLocked is true (in milliseconds).
		buildNavigation: showNav,
		delay: speed * 1000,
		easing: 'swing'
    })
    
    .anythingSliderFx({
         '.caption-top'    : [ 'caption-Top', '50px' ]
        ,'.caption-right'  : [ 'caption-Right', '130px' ]
        ,'.caption-bottom' : [ 'caption-Bottom', '50px' ]
        ,'.caption-left'   : [ 'caption-Left', '130px' ]
    
        // // base FX definitions can be mixed and matched in here too.
        // '.fade' : [ 'fade' ],
        // 
        // // for more precise control, use the "inFx" and "outFx" definitions
        // // inFx = the animation that occurs when you slide "in" to a panel
        // inFx : {
        //     'img'  : { opacity: 1, top  : 0, time: 400, easing : 'swing' }
        // },
        // // out = the animation that occurs when you slide "out" of a panel
        // // (it also occurs before the "in" animation)
        // outFx : {
        //     'img': { opacity: 0, top: '-100px', time: 350 }
        // }

        //,inFx: {
        //    'img' : { opacity: 1, time: 500 }
        //},
        //outFx: {
        //    'img' : { opacity: 0, time: 0 }
        //}

    })
    .find('div[class*=caption]')
    .css({ position: 'absolute' })
    .prepend('<span class="close">x</span>')
    .find('.close').click(function(){
      var cap = $(this).parent(),
       ani = { bottom : -50 }; // bottom
      if (cap.is('.caption-top')) { ani = { top: -50 }; }
      if (cap.is('.caption-left')) { ani = { left: -150 }; }
      if (cap.is('.caption-right')) { ani = { right: -150 }; }
      cap.animate(ani, 400, function(){ cap.hide(); } );
    });
}

$(function(){
});

/** /cmspilotv30/CP/www/themes/Default/functions.js **/
Util.createCPObject('cpt.def');

cpt.def = {
    init: function(){
        $(function(){ });
    }
}

$(function(){
    cpt.def.init();
});
