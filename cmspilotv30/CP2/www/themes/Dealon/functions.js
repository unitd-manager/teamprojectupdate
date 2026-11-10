Util.createCPObject('cpt.dealon');

cpt.dealon = {
    init: function(){
        window.onload = cpt.dealon.openSingupModal;
    }, 
    
    openSingupModal: function(){
        if ($('#cpSignupModalDisplayedAlready').val() == 1) {
            return;
        }
        var url = '/index.php?_theme=dealon&_spAction=popupForm&showHTML=0';
        extraPar = {
            url: url
           ,callbackOnSuccess: function(){
           //     var msg = 'Newsletter subscribed successfully';
           //     Util.alert(msg, function(){
                    Util.closeAllDialogs();
           //     });
            }
        };

        Util.openFormInDialog.call(this, 'newsletterFormPopup', 'Subscribe for More Deals', 540, 385, extraPar);        
    }
}