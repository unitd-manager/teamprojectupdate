Util.createCPObject('cpm.lawNews.contact');

cpm.lawNews.contact = {
    init: function() {
        $('.myClippingsList ul li:first').css('border', 0);

        $('.btnRegister').removeClass('button').addClass('btn');//IE fix
        $('.btnRegister').parent().css('overflow', 'visible');//IE fix

        $('.myAccount ul li:nth-child(2n)').css('margin-right', 0);

        $('.myClippingsList .delete').livequery('click', function(e){
            cpm.lawNews.contact.deleteClip.call(this, e);
        });
    },
    deleteClip:function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        if (url == '' || url == 'javascript:void(0)'){
            url = $(this).attr('link');
        }
        if(url != ''){
            document.location = url;
        }
    }
}
