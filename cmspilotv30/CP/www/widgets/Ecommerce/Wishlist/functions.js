Util.createCPObject('cpw.ecommerce.wishlist');

cpw.ecommerce.wishlist = {
    init: function(){
        $('.w-ecommerce-wishlist .addToWishBtn').livequery('click', function(e){
            e.preventDefault();
            var url = $(this).attr('url');
            
            Util.showProgressInd();
            $.getJSON(url, function(json){
                Util.hideProgressInd();
                var msg = json.message;
                Util.showSimpleMessageInDialog(msg, true);
            });
        });
        
        $('.w-ecommerce-wishlist .removeFromWishBtn').livequery('click', function(e){
            e.preventDefault();
            var url = $(this).attr('url');
            
            Util.showProgressInd();
            $.getJSON(url, function(json){
                if(json.message != ''){
                    Util.hideProgressInd();
                    var msg = json.message;
                    Util.showSimpleMessageInDialog(msg, true);
                } else {
                    location.reload();
                }
            });
        });
    }
}