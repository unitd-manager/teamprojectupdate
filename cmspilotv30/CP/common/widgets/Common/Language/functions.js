Util.createCPObject('cpw.common.language');

cpw.common.language.init = function(){
    $('.w-common-language ul li:first').addClass('first');
    $('.w-common-language ul li:last').addClass('last');

    $('.language_menu .title').click(function () {
        $('.language_menu ul').slideToggle('medium');
    });    
}
