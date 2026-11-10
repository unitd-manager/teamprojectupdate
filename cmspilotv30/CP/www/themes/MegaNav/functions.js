Util.createCPObject('cpt.megaNav');

cpt.megaNav.init = function(){
    $(function(){
        $('.megaNav h2').click(function(){
            var parent = $(this).closest('.row');
            var currentDesc = $('.desc', parent);
            $('.megaNav .desc').not(currentDesc).slideUp('slow');
            $('.desc', parent).slideToggle('slow');
        });

        $('.footer #toggleMenu').click(function(e){
            e.preventDefault();
            var obj = $(this);
            var text1 = obj.attr('text1');
            var text2 = obj.attr('text2');

            if (obj.hasClass('clicked')){
                obj.text(text1);
                $('.megaNav .row .wrap').show();
                $('.megaNav').css('width', '100%');
                obj.removeClass('clicked');
            } else {
                obj.text(text2);
                obj.addClass('clicked');
                $('.megaNav .row .wrap').hide();
                $('.megaNav').css('width', '20px');
            }
        });

    });
}
