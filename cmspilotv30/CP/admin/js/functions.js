var LoadReady = $.extend(LoadReady, {
});

//=====================================================//
//** extending the master Util class
var UtilAdmin = {
    setLinkPortalHeight: function() {
        $(function() {
            var height = $(window).height();
            height = parseInt(height * 0.60); //50% of the window height
            $('.linkPortalWrapper .linkPortalDataWrapper')
            .css('scroll-y', 'auto')
            .css('max-height', height + 'px');
        });
    },

    fixIEComboWidthCutOff: function(selector) {
        $(function() {
            // commented by Ahmad - if needed change browser checking  sicne deprectaed
            // if ($.browser.msie) {
            //     $(selector)
            //     .bind('mousedown', function() {
            //         $(this)
            //         .css('position', 'absolute')
            //         .css('width', 'auto');
            //     })
            //     .bind('blur', function() {
            //         $(this)
            //         .css('position', '')
            //         .css('width', '');
            //     });
            // }

        });
    },

    showValuelistValuesInModal: function(e) {
        e.preventDefault();

        var url = $(this).attr('link');
        var dlgId = '';

        var fldObj = $(this).parents('.hasNotesRight').find('textarea');

        var exp = {
            url: url
           ,afterOpen: function() {
                $('.popcontents .valuelist-selection input[type=button]').click(function() {
                    var field_name = $('#field_name').val();
                    //get text from TD
                    var valueText = $(this).parent().siblings('.value').html();
                    $(fldObj).val(valueText);
                    $(dlgId).dialog('destroy');
                    $(dlgId).remove();
                });
            }
        };
        dlgId = Util.openDialogForLink('Choose Value',  400, 300, 0, exp);
    }
}

//** extending the master Util class
var Util = $.extend(Util, UtilAdmin);

$(function() {
    Util.fixIEComboWidthCutOff('.yform .type-select select#fld_delivery_address_id, .yform .type-select select#fld_delivery_address_id');

    /*
    $('.yform select, .search select').livequery(function(){
        $(this).selectmenu({
        });
    });
    */

    /** sometimes especially after the single link window is opened,
        the selectmenu was not closed, even when clicked outside
        the below code is added to fix this issue
    $('.yform').livequery('click', function(){
        //$('.ui-selectmenu-menu').css('visibility', 'hidden');
    });
    **/

    $('.yform input[type=text], .yform input[type=password], .yform select')
    .livequery(function(){
        //$(this).addClass('ui-corner-all');
    });

    //valuelist value in modal dialog
    $('.hasNotesRight.valuelist input[type=button]').livequery('click', Util.showValuelistValuesInModal);

});