Util.createCPObject('cpm.party.partySetup');

cpm.party.partySetup.init = function(){
    $('.party_partySetup__ecommerce_orderLink td.order-amount')
    .livequery('click', function(){
        var amountTd = $(this);
        var amount = $(this).html();
        amount = prompt('Please enter the new amount to change:', amount);
        if (amount) {
            //save amount
            var order_id = $(this).parent('tr').attr('recId');
            var url = 'index.php?module=party_partySetup&_spAction=changeGuestAmount' +
                      '&order_id=' + order_id +
                      '&amount=' + amount +
                      '&showHTML=0';
            Util.showProgressInd();
            $.getJSON(url, function(json) {
                if (json.status === 'error') {
                    Util.hideProgressInd();
                    Util.alert(json.errorMsg);
                    return;
                }
                amountTd.html(amount);
                document.location = Util.getWindowLocationNoHash();
            });
        }
    });
};
