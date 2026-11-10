Util.createCPObject('cpm.account.currencyConvert');

cpm.account.currencyConvert = {
    init: function(){
        $('.updateEveningRate').click(cpm.account.currencyConvert.updateEveningRate);
    },

    updateEveningRate: function(e){
        e.preventDefault();
        var url = 'index.php?module=account_currencyConvert&_spAction=updateEveningRate&showHTML=0';
        $.get(url, function(){
            document.location = document.location;
        });
    }
}