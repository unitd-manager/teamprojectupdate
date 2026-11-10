var LoadReady = {
    makeScrollableTable: function(){
        var toSubtract1 = $('#header').outerHeight(true) + $('#nav').outerHeight(true) + $('#nav2').outerHeight(true) + $('#extended').outerHeight(true) + $('#footer').outerHeight(true);
        var toSubtract2 = parseInt($('#main').css('padding-top')) + parseInt($('#main').css('padding-bottom'));
        var toSubtract3 = parseInt($('#col3_content').css('padding-top')) + parseInt($('#col3_content').css('padding-bottom'));
        var toSubtract4 = parseInt($('#main .page').css('padding-top')) + parseInt($('#main .page').css('padding-bottom'));
        toSubtract4 = (toSubtract4 || 0);
        var mainPanelHt = $(window).height() - toSubtract1 - toSubtract2 - toSubtract3 - toSubtract4 - 20;
        $('.contentScroller').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
        $('.contentScroller').css({'height' : mainPanelHt + 'px', overflow: 'auto'});
        $('#col1').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
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
        $('div.header[expanded=0]').each(function(){
            var parent = $(this).closest('.linkPortalWrapper');
            $('.mediaFilesDisplayWrap, .linkPortalDataWrapper', parent).hide();
        });

        $('div.header[expanded=0] div.toggle')
        .removeClass('plus, minus').addClass('plus');
    },

    setupLinks: function () {
        $(function() {
            $('a.editLink').livequery('click', function (e) {
                var chooseLinkValidateJsMethod = $(this).attr('chooseLinkValidateJsMethod');
                var result = true;
                if (chooseLinkValidateJsMethod) {
                    chooseLinkValidateJsMethod = eval(chooseLinkValidateJsMethod);
                    var recId = $(this).attr('recId');
                    var exp = {
                        'recId': recId
                    };
                    result = chooseLinkValidateJsMethod.call(this, exp);

                }
                if (result) {
                    Links.showEditLinkDialog.call(this, e);
                }
            });

            $('a.newPortalRecord, a.editPortalRecord').livequery('click', function (e){
                var editLinkItemValidateJsMethod = $(this).attr('editLinkItemValidateJsMethod');
                var result = true;
                if (editLinkItemValidateJsMethod) {
                    editLinkItemValidateJsMethod = eval(editLinkItemValidateJsMethod);
                    var recId = $(this).attr('recId');
                    var exp = {
                        'recId': recId
                    };
                    result = editLinkItemValidateJsMethod.call(this, exp);

                }
                if (result) {
                    Links.showNewEditPortalForm.call(this, e);
                }
            });

            $('a.newGridRecord').livequery('click', function (e){
                Links.addNewGridRecord.call(this, e);
            });

            $('a.viewPortalRecord').livequery('click', Links.showDetailPortalForm);

            $('a.deletePortalRecord').livequery('click', function(e) {
                Links.deletePortalRecord.call(this, e);
            });

            $('a.editLinkSingle').livequery('click', function (e) {
                e.preventDefault();
                var width = $(this).attr('w');
                if (!width) {
                    width = 600;
                }
                Util.openDialogForLink.call(this, 'Edit Link',  width, $(window).height()-100, 1);
            });


            $('#linkModalOuter a.addToLinked').livequery('click', function(e) {
                e.preventDefault();
                Links.addToLinked.call(this);
            });

            $('#linkModalOuter a#addAll').livequery('click', function(e) {
                e.preventDefault();
                Links.addAll.call(this);
            });

            $('#linkModalOuter a#addRemoveAll').livequery('click', function(e) {
                e.preventDefault();
                Links.addRemoveAll.call(this);
            });

            $('#linkModalOuter a.removeFromLinked').livequery('click', function(e) {
                e.preventDefault();
                Links.removeFromLinked.call(this);
            });

            $('#linkModalOuter a.btnSort, #linkModalOuter .pagelinks a').livequery('click', function(e) {
                e.preventDefault();
                Links.loadDataFromUrl.call(this);
            });

            $('#linkModalOuter a.btnSort, #linkModalOuter .pagelinks a').livequery('click', function(e) {
                e.preventDefault();
                Links.loadDataFromUrl.call(this);
            });

            $('input.setSingleValue').livequery('click', function(){
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
              '.linkPortalDataWrapper .grid textarea').livequery('change', function(e) {
                e.preventDefault();
                Links.updateGridData.call(this);
            });

            $(".linkPortalDataWrapper .pagelinks a").livequery('click', function(e) {
                e.preventDefault();
                Links.loadDataByPage.call(this);
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

    $(".vlist li:last-child")
    .livequery(function() {
        $(this).addClass('last');
    });

    $(".vlist li:first-child")
    .livequery(function() {
        $(this).addClass('first');
    });

    $(".linkPortalWrapper tr td:first, .linkPortalWrapper tr th:first")
    .livequery(function() {
        //$(this).css('border-left', 0);
    });

    $("form#searchTop select").livequery('change', function() {
        Actions.submitSearchTop();
    });

    $("form#searchTop").submit(function(e) {
        Util.clearPrepopulatedTextbox($(this));
        return true;
    });

    var dateObj = new Date();
    var currentYear = dateObj.getFullYear() + 1;
    $(".fld_date").livequery(function(){
        var yearStart  = $(this).attr('yearStart');
        var yearEnd    = $(this).attr('yearEnd');
        var minDate    = $(this).attr('minDate');
        var maxDate    = $(this).attr('maxDate');
        var dateFormat = $(this).attr('dateFormat');

        if (!dateFormat){
            dateFormat = 'yy-mm-dd';
        }
        if (!yearStart){
            yearStart = currentYear - 10;
        }

        if (!yearEnd){
            yearEnd = currentYear;
        }

        $(this).datepicker({
            showOn: 'button'
           ,buttonImage: masterRootPathAlias + 'common/images/icons/calendar_icon.gif'
           ,dateFormat: dateFormat
           ,buttonImageOnly: true
           ,changeYear: true
           ,yearRange: yearStart + ':' + yearEnd
           ,minDate: minDate
           ,maxDate: maxDate
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

    $(".fld_datetime").livequery(function(){
        $(this).datetimepicker({
        	dateFormat: 'yy-mm-dd',
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

    $(".yform").livequery('click', function() {
        Util.expose($(this).parent());
    });

    $("form#frmKeyword").livequery('click', function() {
        Util.expose($(this));
    });

    $("input[name='toggle']").livequery('change',function() {
        Actions.checkAll.call(this);
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

    //save form automatically in admin edit form
    if ($('#hasAutoSave').val() === '1') {
        var sel = 'form[name=edit] input, form[name=edit] select, form[name=edit] textarea';
        $(sel).change(Actions.saveHidden);
    }

    //Auto grow for text area
    if( $.fn.elastic) {
        $("textarea").livequery(function(){
            $(this).elastic();
        });
    }

    $("a.editFromList").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');
        var w = $(this).attr('w');
        var h = $(this).attr('h');

        if (!w){
            w = 400;
        }

        if (!h){
            h = 300;
        }

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
			    window.location.reload(true);
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, w, h, expObj);
    });

    $("a.delFromList").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');
        var rowID = $(this).attr('recID');
        var cpCSRFToken = $('#cpCSRFToken').val();
        var cpRoom = $('#cpRoom').val();

        msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            var url = "index.php?_spAction=deleteRecordByID&showHTML=0" ;

            var data = {
                 record_id: rowID
                ,room: cpRoom
                ,cpCSRFToken: cpCSRFToken
            };
            $.post(url, data, function(json){
                if(json.status == 'success'){
                    window.location.reload(true);
                } else {
                    Util.alert(json.message);
                    if(json.refresh){
                        document.location = document.location;
                    }
                }
            }, 'json')

        }, {title: 'Delete Record'}
        );
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

        //CKEDITOR.replace( fldId, {
        //    toolbar : toolbar,
        //    bodyClass: 'cpCkEdiorBody',
        //    filebrowserBrowseUrl : jssPath + 'ckfinder/ckfinder.html',
        //    filebrowserImageBrowseUrl: jssPath + 'ckfinder/ckfinder.html?Type=Images',
        //    filebrowserFlashBrowseUrl: jssPath + 'ckfinder/ckfinder.html?Type=Flash',
        //    filebrowserUploadUrl: jssPath + 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
        //    filebrowserImageUploadUrl: jssPath + 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
        //    filebrowserFlashUploadUrl: jssPath + 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
        //});

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

        //var editor = $(this).ckeditorGet();
        //if (includeStylesheet){
        //    //editor.config.contentsCss = stylesheetPath;
        //}

        //editor.config.baseHref = 'http://www.example.com/path/';

    });

    if(!!window.ActiveXObject && !window.XMLHttpRequest){
        Util.showIE6RejectMessage();
    }


    //convert select box to type ahead
    $('select.searchable').livequery(function() {
        $('select.searchable').searchable({
            zIndex: 'auto'
          });
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

    $('a.jqui-dialog').livequery('click', function(e) {
        e.preventDefault();
        var title = $(this).attr('title');
        var width = $(this).attr('dlg-w');
        var height = $(this).attr('dlg-h');
        var afterOpen = $(this).attr('afterOpen');
        var wrapperId = $(this).attr('wrapperId');
        var showCloseBtn = $(this).attr('showCloseBtn') === '0' ? false : true;

        var exp = {};
        if (afterOpen != '') {
            exp.afterOpen = eval(afterOpen);
        }

        if (wrapperId != '') {
            exp.wrapperId = wrapperId;
        }

        Util.openDialogForLink.call(this, title, width, height, showCloseBtn, exp);
        //$('html,body').animate({scrollTop: $('body').offset().top},'slow');
    });

    $('.jqui-dialog-form').livequery('click', function(e) {
        e.preventDefault();
        var title = $(this).attr('title');
        var formName = $(this).attr('formId');
        var callbackOnSuccess = $(this).attr('callback');

        var exp = {};
        if (callbackOnSuccess != '') {
            exp.callbackOnSuccess = eval(callbackOnSuccess);
        }

        if (!formName) {
            formName = $(this).attr('formid');
        }
        var w = $(this).attr('w');
        var h = $(this).attr('h');
        Util.openFormInDialog.call(this, formName, title, w, h, exp);
    });

    $('form.cpJqForm').livequery(function() {
        var formId = $(this).attr('id');
        var callback = $(this).attr('callback');
        var beforeSubmit = $(this).attr('beforeSubmit');
        var validate = $(this).attr('validate');
        var callbackNoValidateFn = $(this).attr('callbackNoValidateFn');

        if (callback != '') {
            callback = eval(callback);
        }
        if (beforeSubmit != '') {
            beforeSubmit = eval(beforeSubmit);
        }

        var opts = {
        };

        if (validate != '') {
            opts.validate = validate;
        }

        if (callbackNoValidateFn != '') {
            opts.callbackNoValidateFn = eval(callbackNoValidateFn);
        }

        Util.setUpAjaxFormGeneral(formId, callback, beforeSubmit, opts);
    });

    $('.cpStarRating').livequery(function(){
        $('.cpStarRating').rating({
            focus: function(value, link){
                var parent = $(this).closest('.row_rating');
                var tip = $('.rating-hover-tips span', parent);
                tip[0].data = tip[0].data || tip.html();
                tip[0].clickedData = tip[0].data || tip.html();
                tip.html(link.title || 'value: '+value);
            },
            blur: function(value, link){
                var parent = $(this).closest('.row_rating');
                var tip = $('.rating-hover-tips span', parent);
                var onCount = $('.star-rating-on', parent).length;
                if (onCount == 0){
                    tip.html(tip[0].data || '');
                } else {
                    tip.html(tip[0].clickedData || '');
                }
            },
            callback: function(value, link){
                var parent = $(this).closest('.row_rating');
                var tip = $('.rating-hover-tips span', parent);
                tip.html(link.title || 'value: '+value);
                tip[0].clickedData = tip.html();
            }
        });
    });

    $("form input[cpAutoSuggest=1]").livequery(function(){
        var textBoxObj = $(this);
        var fldName = textBoxObj.attr('name');
        var autoSgstModule = textBoxObj.attr('autoSgstModule');
        var autoSgstSrchFld = textBoxObj.attr('autoSgstSrchFld');
        var autoSgstCallBack = textBoxObj.attr('autoSgstCallBack');
        var autoSgstActualFld = textBoxObj.attr('autoSgstActualFld');

        textBoxObj.focus(function(){
            this.select();
        });

        var url = $('#scopeRootAlias').val() + 'index.php?room=' + autoSgstModule +
                  '&srchFld=' + autoSgstSrchFld + '&_spAction=jsonForAutoSuggest&showHTML=0'
    	$(textBoxObj).autocomplete({
             source : url
            ,minLength : 2
    		,select: function(event, ui) {
    			var selectedObj = ui.item;
    			var selected_id = selectedObj.id
    			if (autoSgstActualFld){
    			    var autoSgstActualFldObj = $("input[name='" + autoSgstActualFld + "']");
    			    autoSgstActualFldObj.val(selected_id)
    			}

    			if (autoSgstCallBack){
                    autoSgstCallBackFn = eval(autoSgstCallBack);
    			    autoSgstCallBackFn.call(this, ui);
    			}
    		}
    	});
    });

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

    $('a.cpPrint').click(function(){
        window.print();
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

    $('div.cpwLoading').livequery(function(){
        $('div.cpwLoading > div').show();
        $('div.cpwLoading').removeClass('cpwLoading');
    });

    $(window).load(function(){
       $(".centered").cp_center();
    });

    //flagging from list
    $('.list-flag').livequery('click', function(e){
        DBUtil.flagRecordFromList.call(this, e);
    });

    //edit field from list

    if($('.cp-editable').length > 0){
        var module = $('#cpRoom').val();
        var url = $('#scopeRootAlias').val() + 'index.php?module=' + module
                + '&_spAction=saveSingleField'
                + '&showHTML=0';
        $('.cp-editable').editable(url,{
            indicator: '<img src="/cmspilotv30/library/jss/jquery/jeditable/img/indicator.gif">'
           ,onblur: 'submit'
        });
    }

    //list flag and unflag all.
    $('.list-flag-all,.list-unflag-all').livequery('click', DBUtil.flagUnflagAllRecordsFromList);

    Util.prepopulatedTextbox();

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

$.fn.clearForm = function() {
  return this.each(function() {
    var type = this.type, tag = this.tagName.toLowerCase();
    if (tag == 'form')
      return $(':input',this).clearForm();
    if (type == 'text' || type == 'password' || tag == 'textarea')
      this.value = '';
    else if (type == 'checkbox' || type == 'radio')
      this.checked = false;
    else if (tag == 'select')
      this.selectedIndex = -1;
  });
};

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
	var ah = $(this).height();
	var ph = $(this).parent().height();
	var mh = Math.ceil((ph-ah) / 2);
	mh = mh + opts.marginTopDiff;
	$(this).css('padding-top', mh);
	});
};
})(jQuery);

// JScript File
(function ($) {
    $.showprogress = function(message)
    {
        $.hideprogress();
        if (typeof message == 'undefined') {
            message = 'Please wait';
        }

        $("BODY").append('<div id="processing_overlay"></div>');
            $("BODY").append(
		      '<div id="processing_container">' +
		        //'<div id="processing_title">This is title</div>' +
		        '<div id="processing_content">' +
		                '<br><br>' + message +
			    '</div>' +
		      '</div>');

		var pos = (!!window.ActiveXObject && !window.XMLHttpRequest) ? 'absolute' : 'fixed';

		$("#processing_container").css({
			position: pos,
			zIndex: 99999,
			padding: 0,
			margin: 0
		});

		$("#processing_container").css({
			minWidth: $("#processing_container").outerWidth(),
			maxWidth: $("#processing_container").outerWidth()
		});

		var top = (($(window).height() / 2) - ($("#processing_container").outerHeight() / 2)) + (-75);
		var left = (($(window).width() / 2) - ($("#processing_container").outerWidth() / 2)) + 0;
		if( top < 0 ) top = 0;
		if( left < 0 ) left = 0;

		// IE6 fix
		if(!!window.ActiveXObject && !window.XMLHttpRequest) top = top + $(window).scrollTop();

		$("#processing_container").css({
			top: top + 'px',
			left: left + 'px'
		});
		$("#processing_overlay").height( $(document).height() );
    },

    $.hideprogress = function()
    {
        $("#processing_container").remove();
        $("#processing_overlay").remove();
    },

    $.showmsg = function(msgEle,msgText,msgClass,msgIcon,msgHideIcon,autoHide){
        var tblMsg;

        tblMsg = '<table width="100%" cellpadding="1" cellspacing="0" border="0" class="' + msgClass + '"><tr><td style="width:30px;" align="center" valign="middle">' + msgIcon + '</td><td>' + msgText + '</td><td style="width:30px;" align="center" valign="middle"><a href="javascript:void(0);" onclick="$(\'#' + msgEle + '\').toggle(400);">' + msgHideIcon + '</a></td></tr></table>';

        $("#" + msgEle).html(tblMsg);
        $("#" + msgEle).show();
        if(autoHide)
        {
            setTimeout(function(){
                $('#' + msgEle).fadeOut('normal')},10000
	        );
        }
    }
})(jQuery);

