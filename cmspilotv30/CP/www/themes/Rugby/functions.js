Util.createCPObject('cpt.rugby');

cpt.rugby.init = function(){
    $('#main .page').wrap("<div class='bgTop' />")
    .wrap("<div class='bgBtm' />")
    .wrap("<div class='bgMiddle' />");

    $('.calloutBottom li:last-child').css('border-right', '0');
    $('#footer li:last-child').css('border-right', '0');
}
