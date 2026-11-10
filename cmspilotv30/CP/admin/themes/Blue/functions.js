Util.createCPObject('cpt.blue');

cpt.blue.init = function(){
    //show hide description in Help Content - PMS (USS Product)
    $('.contentTitle').livequery('click', function(){
        //$('.contentDescription').css('display','none');
    	var parent = $(this).closest('.helpContentTask');
        $('.contentDescription', parent).slideToggle();
        var parent = $(this).closest('.startedContentTask');
        $('.contentDescription', parent).slideToggle();
    });

    //show hide description in GET STARTED Content - PMS (USS Product)
    /*$('.contentTitle').livequery('click', function(){
        //$('.contentDescription').css('display','none');
    	var parent = $(this).closest('.startedContentTask');
        $('.contentDescription', parent).slideToggle();
    });*/
        window.onload = getStartedContent();
        function getStartedContent() {
            var paymentReminder2 = $("input[name='paymentReminder2']").val();
            if(paymentReminder2 == 1){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=paymentReminder2&showHTML=0';
                var exp = {
                    url: url
                };

                Util.openDialogForLink('Payment Reminder',  600, 200, 0, exp);
            }
        }

    $("#nav .hlist ul li a span").addClass('inner');
    $("#nav .hlist ul li a").blend();

    $(".actionBtns li a").wrap("<div class='button'>");

    $('.contentScroller, .m-common_dashboard .widget div.tableOuter').addClass('scroll-pane');
    /*$('.scroll-pane').jScrollPane(
        {}
    );*/

    if ($('.tplLogin').length > 0){
        var toSubtract = $('#header').outerHeight(true) + $('#footer').outerHeight(true);
        var mainPanelHt = $(window).height() - toSubtract - 20;
        $('#col3_content').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
        $("#col3_content #loginOuter").cp_center();
    }

    (function( $ ) {
         $.widget( "ui.combobox", {
             _create: function() {
                 var self = this,
                     select = this.element.hide(),
                     selected = select.children( ":selected" ),
                     value = selected.val() ? selected.text() : "";
                 var input = this.input = $( "<input>" )
                     .insertAfter( select )
                     .val( value )
                     .autocomplete({
                         delay: 0,
                         minLength: 0,
                         source: function( request, response ) {
                             var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                             response( select.children( "option" ).map(function() {
                                 var text = $( this ).text();
                                 if ( this.value && ( !request.term || matcher.test(text) ) )
                                     return {
                                         label: text.replace(
                                             new RegExp(
                                                 "(?![^&;]+;)(?!<[^<>]*)(" +
                                                 $.ui.autocomplete.escapeRegex(request.term) +
                                                 ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                             ), "<strong>$1</strong>" ),
                                         value: text,
                                         option: this
                                     };
                             }) );
                         },
                         select: function( event, ui ) {
                             ui.item.option.selected = true;
                             self._trigger( "selected", event, {
                                 item: ui.item.option
                             });
                             select.trigger("change");
                         },
                         change: function( event, ui ) {
                             if ( !ui.item ) {
                                 var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
                                     valid = false;
                                 select.children( "option" ).each(function() {
                                     if ( $( this ).text().match( matcher ) ) {
                                         this.selected = valid = true;
                                         return false;
                                     }
                                 });
                                 if ( !valid ) {
                                     // remove invalid value, as it didn't match anything
                                     $( this ).val( "" );
                                     select.val( "" );
                                     input.data( "autocomplete" ).term = "";
                                     return false;
                                 }
                             }
                         }
                     })
                     .addClass( "ui-widget ui-widget-content ui-corner-left" );

                 input.data( "autocomplete" )._renderItem = function( ul, item ) {
                     return $( "<li></li>" )
                         .data( "item.autocomplete", item )
                         .append( "<a>" + item.label + "</a>" )
                         .appendTo( ul );
                 };

                 input.val($(select).find("option:selected").text());

                 this.button = $( "<button type='button'>&nbsp;</button>" )
                     .attr( "tabIndex", -1 )
                     .attr( "title", "Show All Items" )
                     .insertAfter( input )
                     .button({
                         icons: {
                             primary: "ui-icon-triangle-1-s"
                         },
                         text: false
                     })
                     .removeClass( "ui-corner-all" )
                     .addClass( "ui-corner-right ui-button-icon" )
                     .click(function() {
                         // close if already visible
                         if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
                             input.autocomplete( "close" );
                             return;
                         }

                         // pass empty string as value to search for, displaying all results
                         input.autocomplete( "search", "" );
                         input.focus();
                     });
             },

             destroy: function() {
                 this.input.remove();
                 this.button.remove();
                 this.element.show();
                 $.Widget.prototype.destroy.call( this );
             }
         });
     })( jQuery );


    /*$( "table.search td select" ).combobox(
    );*/

    $("table.search td select").change(function() {
        $('#searchTop').submit();
    });
}

// Adding help button pop window in the content list  - PMS (USS Product)
$("a.helpContentTask").livequery('click', function (e){
    var module_name = $(this).attr('module_name');
    var url = 'index.php?module=webBasic_content&_spAction=helpContentTask&module_name=' + module_name + '&showHTML=0';
    var exp = {
        url: url
    };
    Util.openDialogForLink('Help Content',  1000, 500, 0, exp);
});

// Adding GET STARTED Button pop window in the content list  - PMS (USS Product)
$("a.getStartedContentTask").livequery('click', function (e){
    var module_name = $(this).attr('module_name');
    var url = 'index.php?module=webBasic_content&_spAction=startedContentTask&module_name=' + module_name + '&showHTML=0';
    var exp = {
        url: url
    };
    Util.openDialogForLink('Get Started',  1000, 500, 0, exp);
});

