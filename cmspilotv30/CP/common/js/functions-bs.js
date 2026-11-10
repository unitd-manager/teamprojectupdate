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

    showProgressInd: function(message) {
        if (!message) {
            message = "Processing.. Please wait...";
        }
        message = "<h5>" + message + "</h5>";

        var htmlString = "\
        <div id='cp-progress' class='modal'>\
            <div class='modal-dialog'>\
                <div class='modal-content'>\
                    <div class='modal-body'>\
                        <p class='cp-loading'>" + message + "</p>\
                    </div>\
                </div>\
            </div>\
        </div>\
        ";

        if ($('#cp-progress').length > 0){
            $('#cp-progress').remove();
        }

        $('body').append(htmlString);

        $('#cp-progress').modal({
            keyboard: false,
            backdrop: 'static'
        });
    },

    alert: function(message) {
        var htmlString = "\
        <div id='cp-alert' class='modal fade'>\
            <div class='modal-dialog'>\
                <div class='modal-content'>\
                    <div class='modal-body'>\
                        <div class='modal-body'>\
                            " + message + "\
                        </div>\
                        <div class='modal-footer'>\
                            <button type='button' class='btn btn-primary' data-dismiss='modal'>Close</button>\
                        </div>\
                    </div>\
                </div>\
            </div>\
        </div>\
        ";
        $('body').append(htmlString);

        $('#cp-alert').modal({
        });
    },

    hideProgressInd: function() {
        $('#cp-progress').modal('hide');
        $('#cp-progress').on('hidden', function () {
            $('#cp-progress').remove();
        });
    },

    openModalForLink: function(exp) {
        exp = $.extend({
             url: ''
            ,title: ''
            ,isJson: ''
            ,width: ''
            ,showOuterOnly: false
        }, exp || {});

        var linkObj = $(this);
        var modalId= ($(this).attr('id')) ? $(this).attr('id') + '_modal' : 'cpModal';
        var callbackFn = linkObj.attr('callbackFn');

        url = exp.url ? exp.url : $(this).attr('url');
        if (!url){
            url = $(this).attr('href');
        }
        title  = exp.title ? exp.title  : $(this).attr('title');
        isJson = exp.isJson? exp.isJson : $(this).attr('isJson');
        width  = exp.width? exp.width : $(this).attr('w');

        var dataType = isJson ? 'json' : 'html';

        var showModal = function(data, status, xhr){
            var htmlResponse = data;

            if (isJson) {
                htmlResponse = data.html;
            }

            if (exp.showOuterOnly){
                var htmlString = "\
                <div id='" + modalId + "' class='modal fade'>\
                    <div class='modal-dialog'>\
                        <div class='modal-content'>\
                            " + htmlResponse + "\
                        </div>\
                    </div>\
                </div>\
                ";
            } else {
                var htmlString = "\
                <div id='" + modalId + "' class='modal fade'>\
                    <div class='modal-dialog'>\
                        <div class='modal-content'>\
                            <div class='modal-body'>\
                                <div class='modal-header'>\
                                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'><i class='icon-remove-sign icon-2x'></i></button>\
                                    <h4>" + title + "</h4>\
                                </div>\
                                <div class='modal-body'>\
                                    " + htmlResponse + "\
                                </div>\
                            </div>\
                        </div>\
                    </div>\
                </div>\
                ";
            }

            if ($('#' + modalId).length > 0){
                $('#' + modalId).remove();
            }

            $('body').append(htmlString);
            $('#' + modalId).modal({
            });

            $('#' + modalId).modal({
            });

            $('#' + modalId).on('shown', function () {
                $('.modal-body').scrollTop(0);
            });

            var modalDlgObj = $('#' + modalId + ' .modal-dialog');
            var modalBodyObj = $('.modal-body', modalDlgObj);
            if (!width){
                width = parseInt(modalDlgObj.css('width'));
            }

            modalDlgObj.css({
                'width': width + 'px'
            });

            //modalBodyObj.css({
            //    'max-height': '500px',
            //    'overflow': 'auto'
            //});

            if (callbackFn){
                var callbackFnName = eval(callbackFn);
                callbackFnName.call(this, linkObj, data);
            }

            Util.hideProgressInd();
        }

        $.get(url, showModal, dataType);
    },

    goToByScroll: function(id){
        $('html,body').animate({scrollTop: $("#"+id).offset().top},'slow');
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

    unique: function(arr) {
        var result = [];
        $.each(arr, function(i, e) {
            if ($.inArray(e, result) == -1) result.push(e);
        });
        return result;
    }
}

//------------------------------------------------//
var Forms = {

    validate: function(frmId){ // this function is kept for backward compatibility use setupBsForm instead
        Forms.setupBsForm(frmId);
    },

    setupBsForm: function(frmId){
        frmObj = $('#' + frmId);
        var showProgBar = frmObj.attr('showProgBar');

        $(frmObj).find('input,select,textarea').not('[type=submit]').jqBootstrapValidation({
            submitSuccess: function ($form, event) {
                if(showProgBar != 0){
                    Util.showProgressInd();
                }
                $.ajax({
                    type: 'POST',
                    url: $form.attr('action'),
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(json){
                        Forms.validateBsForm($form, json);
                    }
                });

                // will not trigger the default submission in favor of the ajax function
                event.preventDefault();
            }
        });
    },

    setupJqForm: function(frmId){
        frmObj = $('#' + frmId);
        var showProgBar = frmObj.attr('showProgBar');
        $(frmObj).find('input,select,textarea').not('[type=submit]').jqBootstrapValidation({
            submitSuccess: function ($form, event) {
                if(showProgBar != 0){
                    Util.showProgressInd();
                }
                var options = {
                    success: function(json, statusText, xhr, $form){
                        Forms.validateBsForm($form, json);
                    },
                    dataType: 'json'
                };
                $form.ajaxSubmit(options);
                // will not trigger the default submission in favor of the ajax function
                event.preventDefault();
            }
        });
    },

    validateBsForm: function(frmObj, json){
        var showProgBar = frmObj.attr('showProgBar');
        if(json.errorCount && json.errorCount > 0){
            Util.hideProgressInd();
            Forms.alertErrorsText(frmObj, json);
            $('.reloadCaptcha', frmObj).click();
        } else {
            var notclearAfterSubmit = frmObj.attr('notclearAfterSubmit');
            if (notclearAfterSubmit !== '1') {
                frmObj.clearForm();
            }

            Forms.alertSuccessText(frmObj, json);
            if (json.returnUrl !== "") {
                frmObj.closest('.modal').modal('hide');
                document.location = json.returnUrl;
            } else {
                Util.hideProgressInd();
            }
        }
    },

    alertErrorsText: function(frmObj, json){
        $('.alert-block', frmObj).hide();

        errorText = "<h4 class='alert-heading'>The following errors occured</h4><br>";

        $.each(json.errors, function() {
            errorText += '<p>' + this.msg + '</p>';
        });

        var htmlString = "\
        <div class='alert alert-danger alert-dismissable fade in'>\
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>\
            " + errorText + "\
        </div>\
        ";
        
        var frmId = frmObj.attr('id');
        frmObj.prepend(htmlString);
        $('html,body').animate({scrollTop: $("#"+frmId).offset().top - 50},'slow');
    },

    alertSuccessText: function(frmObj, json){
        $('.alert', frmObj).hide();
        var successMsgFld = $('input[name=successMsg]', frmObj);
        var dialogMsgFld = $('input[name=dialogMsg]', frmObj);
        var callbackFnFld = $('input[name=callbackFn]', frmObj);

        if ($(dialogMsgFld).length > 0){
            $('.modal').modal('hide');
            Util.alert(dialogMsgFld.val());
        }

        if ($(successMsgFld).length > 0){
            var msg = $(successMsgFld).val();
            var htmlString = "\
            <div class='alert alert-success alert-dismissable fade in'>\
                <button type='button' class='close' data-dismiss='alert'>&times;</button>\
                " + msg + "\
            </div>\
            ";

            if (successMsgFld.attr('position') == 'bottom'){
                frmObj.append(htmlString);
            } else {
                frmObj.prepend(htmlString);
            }
            Util.goToByScroll(frmObj.attr('id'));
        }

        if ($(callbackFnFld).length > 0){
            $('.modal').modal('hide');
            var callbackFn = eval(callbackFnFld.val());
            callbackFn.call(this, json);
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

$(function() {
    if ($('form.bsForm').length > 0){
        var formId = $('form.bsForm').attr('id');
        Forms.setupBsForm(formId);
    }

    if ($('form.cpJqForm').length > 0){
        var formId = $('form.cpJqForm').attr('id');
        Forms.setupJqForm(formId);
    }

    $('.reloadCaptcha').click(function(e){
        e.preventDefault();
        var captchaId = $(this).attr('captcha_id');
        var captchaCode = $(this).parents('form').find('input[name=captcha_code]');
        captchaCode.val('');

        reloadUrl = $('#libraryPathAlias').val() + 'lib_php/securimage/securimage_show.php?' + Math.random();
        $('#' + captchaId).attr('src', reloadUrl);
    });

    $(document).on('click', '.cpModal', function(e){
        e.preventDefault();
        Util.openModalForLink.call(this)
    });

    $.fn.clearForm = function() {
        return this.each(function() {
            var type = this.type, tag = this.tagName.toLowerCase();
            if (tag == 'form')
                return $(':input',this).clearForm();
            if (type == 'text' || type == 'password' || type == 'textarea' || type == 'email')
                this.value = '';
            else if (type == 'checkbox' || type == 'radio')
                this.checked = false;
            else if (type == 'select')
                this.selectedIndex = -1;
        });
    };

    //form time picker (bootstrap-datetimepicker plugin)
    if ($('.cpDateTimepicker').length > 0) {
        $('.cpDateTimepicker').datetimepicker({
            language: 'en'
        });
    }

    if ($('.cpDateTimeDateOnly').length > 0) {
        $('.cpDateTimeDateOnly').datetimepicker({
            pickTime: false
        });
    }

    if ($('.cpTimepicker').length > 0) {
        $('.cpTimepicker').datetimepicker({
            language: 'en',
            pickDate: false,
            pickSeconds: false
        });
    }

    $('.submitBsForm').on('click', function(e){
        e.preventDefault();
        $('form.bsForm').submit();
    });

    $('.cpBack').on('click', function(e){
        e.preventDefault();
        history.back();
    });

    if ($(".fancybox").length > 0){
    	$(".fancybox").fancybox({
    	});
    }
});

(function ($) {
// VERTICALLY ALIGN FUNCTION
$.fn.vAlign = function(opts) {
    opts = opts || {};

    var defaults = {
        marginTopDiff: 0
    }
    opts = $.extend({}, defaults, opts);

	return this.each(function(i){
        opts.marginTopDiff;
    	var ah = $(this)[0].height;
    	var ph = $(this).parent().height();
    	var mh = Math.ceil((ph-ah) / 2);
    	mh = mh + opts.marginTopDiff;
        $(this).animate({
            'padding-top' : mh
          }, "slow");
    });
};
})(jQuery);

/** Expected markup:
<div id="someId" data-name="someName" class="select someClass">
    <div class="option selected" data-value="1"> Item 1 <i class="icon-ok"></i></div>
    <div class="option" data-value="2"> Item 2 <span>some html</span></div>
    <div class="option" data-value="3"> Item 3 <img src="little.img"></div>
</div>
*
*/
jQuery(function($){
    $('.select').each(function(i, e){
        if (!($(e).data('convert') == 'no')) {
            $(e).hide().removeClass('select');
            var current = $(e).find('.option.selected').html() || '&nbsp;';
            var val   =   $(e).find('.option.selected').data('value');
            var name  =   $(e).data('name') || '';

            $(e).replaceWith('<div class="btn-group" id="select-group-' + i + '" />');
            var select = $('#select-group-' + i);
            select.html('<a class="btn ' + $(e).attr('class') + '" type="button">' + current + '</a><a class="btn dropdown-toggle ' + $(e).attr('class') + '" data-toggle="dropdown" href="#"><span class="caret"></span></a><ul class="dropdown-menu"></ul><input type="hidden" value="' + val + '" name="' + name + '" id="' + $(e).attr('id') + '" class="' + $(e).attr('class') + '" />');
            $(e).find('.option').each(function(o,q) {
                select.find('.dropdown-menu').append('<li><a href="#" data-value="' + $(q).data('value') + '">' + $(q).html() + '</a></li>');
            });
            select.find('.dropdown-menu a').click(function(e) {
                select.find('input[type=hidden]').val($(this).data('value')).change();
                select.find('.btn:eq(0)').html($(this).html());
                e.preventDefault();
            });
        }
    });
});

(function($) {
    $.fn.cp_emptySelect = function() {
      return this.each(function(){
        if (this.tagName=='SELECT') this.options.length = 0;
      });
    }

    $.fn.cp_loadSelect = function(optionsDataArray) {
      return this.cp_emptySelect().each(function(){
        if (this.tagName=='SELECT') {
          var selectElement = this;
          $.each(optionsDataArray,function(index,optionData){
            var option = new Option(optionData.caption,
                                    optionData.value);
            if ($.browser.msie) {
              selectElement.add(option);
            }
            else {
              selectElement.add(option,null);
            }
          });
        }
      });
    }
})(jQuery);
