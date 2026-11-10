Util.createCPObject('cpt.restaurant');

cpt.restaurant = {
    init: function(){
        $('form#search select').change(function(){
            $('form#search').submit();
        });
        
        $('form#frmSignupTop a.send').click(function(){
            $('form#frmSignupTop').submit();
        });
        
        $(".sec_type_modal a").colorbox({'maxHeight': $(window).height(), 'maxWidth': $(window).width()});

    }
}
