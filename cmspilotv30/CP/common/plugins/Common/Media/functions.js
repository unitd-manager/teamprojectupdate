Util.createCPObject('cpp.common.media');

cpp.common.media = {
    setUploadDragDrop: function(room, record_type, id, sessionID, extraParamObj){
        /** used in admin to re-load the media in the base container **/
        mainDivId = 'media__' + room + '__' + record_type;
        mainDiv = $('#' + mainDivId);

        var uploadifyId = '#uploadifyMedia_' + record_type;
        var frmObj = $(uploadifyId).closest('form')

        var masterPathAlias = $('#masterPathAlias').val();
        var jssPath = $('#jssPath').val();

        var scopeRootAlias = $('#scopeRootAlias').val();

        if (!extraParamObj) {
            extraParamObj = {};
        }

        if (extraParamObj.uploadifyId) {
            uploadifyId = extraParamObj.uploadifyId;
        }

        var progressBarWidth = (extraParamObj.progressBarWidth) ? extraParamObj.progressBarWidth : '100%';
        var autoProcessQueue = (extraParamObj.autoProcessQueue) ? extraParamObj.autoProcessQueue : 'false';
        var width            = (extraParamObj.width) ? extraParamObj.width : 200;
        var height           = (extraParamObj.height) ? extraParamObj.height : 200;
        var sizeLimit        = (extraParamObj.fileSizeLimit) ? extraParamObj.fileSizeLimit : '2';
        var queueSizeLimit   = (extraParamObj.queueSizeLimit) ? extraParamObj.queueSizeLimit : 5;
        var fileTypeExts     = (extraParamObj.fileTypeExts) ? extraParamObj.fileTypeExts : '*.*';
        var fileSizeLimit    = sizeLimit;
        
        $(document).ready(function() {
            var myDropzone = new Dropzone(".dropzone", {
                url: 'index.php?plugin=common_media&_spAction=addMedia&module='+room+'&recordType='+record_type+'&id='+id+'&fileFldName=fileName&showHTML=0',
                paramName: "fileName",
                maxFilesize: fileSizeLimit,
                maxFiles: queueSizeLimit,
                acceptedFiles: "image/*, application/pdf",
                autoProcessQueue: true,
                /*maxfilesexceeded: function(file) {
                    this.removeAllFiles();
                    this.addFile(file);
                },*/
                init: function() {
                    var drop = this;
                    this.on("success", function(file, responseText) {
                        if (this.getUploadingFiles().length > 0 && this.getQueuedFiles().length > 0) {
                            $('.dz-message', mainDiv).hide();
                        }
                        
                        //file.previewElement.parentNode.removeChild(file.previewElement);
                        
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                            $('.dz-message', mainDiv).show();
                            if ($('.mediaFilesDisplayWrap', mainDiv).length != 0){
                                cpp.common.media.reloadMediaFilesDisplay($('.mediaFilesDisplayWrap', mainDiv));
                            } else {
                                mainDivId2 = 'media__' + room + '__' + record_type + '__' + id;
                                mainDiv2 = $('#' + mainDivId2);
                                cpp.common.media.reloadMediaFilesDisplay($('.mediaFileWrap', mainDiv2));
                            }
                        }

                    });

                    this.on("addedfile", function (file) {
                        $('.dz-message', mainDiv).hide();
                    });   

                    this.on("queuecomplete", function (file) {
                        drop.removeAllFiles(true);
                    });
                }
            });

            $('a.startUpload').click(function(e){
                e.preventDefault();       
                myDropzone.processQueue();
            });
        });
    },
    
    setUploadify: function(room, record_type, id, sessionID, extraParamObj){
        queueID   = 'fileQueueMedia_' + record_type;

        /** used in admin to re-load the media in the base container **/
        mainDivId = 'media__' + room + '__' + record_type;
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
        var btnWidth = (extraParamObj.btnWidth) ? extraParamObj.btnWidth : 100;
        var btnHeight = (extraParamObj.btnHeight) ? extraParamObj.btnHeight : 20;
        var sizeLimit = (extraParamObj.fileSizeLimit) ? extraParamObj.fileSizeLimit : '200';
        var queueSizeLimit = (extraParamObj.queueSizeLimit) ? extraParamObj.queueSizeLimit : 5;
        var fileTypeExts = (extraParamObj.fileTypeExts) ? extraParamObj.fileTypeExts : '*.*';

        var fileSizeLimit = sizeLimit + 'MB';

        var swfPath = jssPath + 'jquery/uploadify-3.2/uploadify.swf';
        $(uploadifyId).uploadify({
              'swf'            : swfPath
            , 'uploader'       : scopeRootAlias + 'index.php'
            , 'auto'           : false
            , 'fileObjName'    : 'fileName'
            , 'queueID'        : queueID
            , 'multi'          : allowMultiUpload
            , 'width'          : btnWidth
            , 'height'         : btnHeight
            , 'fileSizeLimit'  : fileSizeLimit
            , 'fileTypeExts'   : fileTypeExts
            , 'queueSizeLimit' : queueSizeLimit
            , 'formData'       : {
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
            , 'onSelect': function(fileObj){
                //alert('The button says ' + $(uploadifyId).uploadify('settings','fileSizeLimit'));
                if(fileObj.size > sizeLimit){
                    //var sizeLimitMB = sizeLimit/(1024 * 1024);
                    //alert('The file size shoulb be less than ' + sizeLimitMB.toFixed(2) + ' MB.');
                    //return false;
                    //$(this).uploadifyCancel(queueID);
                }
            }
            , 'onInit': function(){
                Util.showProgressInd();
            }
            , 'onUploadComplete': function(fileObj){
                //alert (response);
            }
            , 'onQueueComplete': function(data){
                if(onAllCompleteFunction) {
                    onAllCompleteFunction.call();
                } else if ($('.mediaFilesDisplayWrap', mainDiv).length != 0){
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFilesDisplayWrap', mainDiv));
                    $(uploadifyId).closest('div.popcontents').dialog('close');
                    $(uploadifyId).closest('div.popcontents').dialog('destroy');
                } else {
                    /** if files are attached through link grid records **/
                    mainDivId2 = 'media__' + room + '__' + record_type + '__' + id;
                    mainDiv2 = $('#' + mainDivId2);
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFileWrap', mainDiv2));
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
            ,'onUploadError': function (event, ID, fileObj, errorObj) {
                alert(errorObj.type + ' Error: ' + errorObj.info);
            }
            ,'onCancel' : function(file) {
                //alert('The file ' + file.name + ' was cancelled.');
            }
        });

        $(function() {
            $('a.uploadQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('.uploadify', $(this).closest('.uploadWrap')).attr('id');

                /*
                var record_type = $(this).attr('record_type');
                $('#' + fileID).uploadifySettings('scriptData', {'recordType': record_type});
                */

                $('#' + fileID).uploadify('upload','*');
            });

            $('a.clearQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('input[type=file]', $(this).parent()).attr('id');
                $('#' + fileID).uploadifyClearQueue();
            });
        });
    },

    // if uploaded using html5 plugin
    setUploadifive: function(room, record_type, id, sessionID, extraParamObj){
        queueID   = 'fileQueueMedia_' + record_type;

        /** used in admin to re-load the media in the base container **/
        mainDivId = 'media__' + room + '__' + record_type;
        mainDiv = $('#' + mainDivId);

        var uploadifiveId = '#uploadifiveMedia_' + record_type;
        var frmObj = $(uploadifiveId).closest('form')
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

        if (extraParamObj.uploadifiveId) {
            uploadifiveId = extraParamObj.uploadifiveId;
        }

        var sizeLimit = (extraParamObj.fileSizeLimit) ? extraParamObj.fileSizeLimit : '200';
        var queueSizeLimit = (extraParamObj.queueSizeLimit) ? extraParamObj.queueSizeLimit : 5;
        var fileTypeExts = (extraParamObj.fileTypeExts) ? extraParamObj.fileTypeExts : '*.*';

        var fileSizeLimit = sizeLimit + 'MB';

        $(uploadifiveId).uploadifive({
			 'uploadScript'   : scopeRootAlias + 'index.php'
            ,'auto'           : false
            ,'fileObjName'    : 'fileName'
            ,'queueID'        : queueID
            ,'formData'       : {
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
            , 'fileSizeLimit'  : fileSizeLimit
            , 'fileTypeExts'   : fileTypeExts
            , 'queueSizeLimit' : queueSizeLimit
            , 'onSelect': function(queue){
            }
            , 'onInit': function(){
                Util.showProgressInd();
            }
            , 'onUploadComplete': function(fileObj){
                //alert (response);
            }
            , 'onQueueComplete': function(uploads){
                if(onAllCompleteFunction) {
                    onAllCompleteFunction.call();
                } else if ($('.mediaFilesDisplayWrap', mainDiv).length != 0){
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFilesDisplayWrap', mainDiv));
                    $(uploadifiveId).closest('div.popcontents').dialog('close');
                    $(uploadifiveId).closest('div.popcontents').dialog('destroy');
                } else {
                    /** if files are attached through link grid records **/
                    mainDivId2 = 'media__' + room + '__' + record_type + '__' + id;
                    mainDiv2 = $('#' + mainDivId2);
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFileWrap', mainDiv2));
                    $(uploadifiveId).closest('div.popcontents').dialog('close');
                    $(uploadifiveId).closest('div.popcontents').dialog('destroy');
                }
                if (extraParamObj.formSuccessMsg && extraParamObj.formSuccessMsg != '') {
                    var formHt = frmObj.height();
                    frmObj.css('height', formHt + 'px');
                    frmObj.html(extraParamObj.formSuccessMsg);
                    frmObj.hide().slideDown();
                }

                Util.hideProgressInd();
            }
            ,'onError': function (errorType) {
                alert('The error was: ' + errorType);
            }
            ,'onCancel' : function(file) {
            }
        });

        $(function() {
            $('a.uploadifiveQueue').livequery('click', function(e){
                e.preventDefault();
                var fileID = $('.uploadifive', $(this).closest('.uploadWrap')).attr('id');
                $('#' + fileID).uploadifive('upload');
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
        var link = $(this);
        var url  = $('#scopeRootAlias').val() + 'index.php?plugin=common_media&_spAction=deleteMedia&media_id=' + media_id + '&showHTML=0';

        msg = "Are you sure you want to delete this attachment?\nYou cannot undo this action!";

        Util.confirm(msg, function(){
            Util.showProgressInd();

            $.get(url, function(){
                var container = $(link).closest('.mediaFilesDisplayWrap');
                if (container.length == 0){
                    var container = $(link).closest('.mediaFileWrap');
                }
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
            wrapperId: 'editPicture',
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
        if ($('a.cpZoom').length > 0){
            $('a.cpZoom').colorbox();
        }

        $("a[rel='cpZoomGallery']").livequery(function() {
            $("a[rel='cpZoomGallery']").colorbox({'maxHeight': $(window).height(), 'maxWidth': $(window).width()});
        });
    },

    init: function(){
        $(function(){
            $('a.btnSelectMedia').livequery('click', function (e) {
                e.preventDefault();
                Util.openDialogForLink.call(this, 'Upload Media', 400, 265, true);
            });

            $('a.removeMedia').livequery('click', cpp.common.media.deleteMedia);
            $('a.editMedia').livequery('click', cpp.common.media.editMediaPropeties);
            $('a.cropMedia').livequery('click', cpp.common.media.cropMediaForm);
        });

        $('form.uploadWrapHtml').livequery(function() {
            var room = $('input[name=room]', $(this)).val();
            var recordType = $('input[name=recordType]', $(this)).val();

            var mainDivId = 'media__' + room + '__' + recordType;
            var mainDiv = $('#' + mainDivId);
            var formId = $(this).attr('id');

            var exp = {
                 'validate': false
                ,'dataType': 'text'
                ,'callbackNoValidateFn' : function(){
                    cpp.common.media.reloadMediaFilesDisplay($('.mediaFilesDisplayWrap', mainDiv));
                    $('#' + formId).closest('div.popcontents').dialog('close');
                    $('#' + formId).closest('div.popcontents').dialog('destroy');
                    Util.hideProgressInd();
                }
            }

            Util.setUpAjaxFormGeneral(formId, '', '', exp);
    	});
    }
}

$(function(){
    cpp.common.media.init();
    cpp.common.media.setZoomImageDefaults();
});
