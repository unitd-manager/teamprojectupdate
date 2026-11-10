Util.createCPObject('cpm.account.ledger');

cpm.account.ledger = {
    init: function(){
        cpm.account.ledger.initializeAccountTypeAhead();
        Util.prepopulatedTextbox();
    },

    initializeAccountTypeAhead: function(){
        $('.fld-acc_head').autocomplete({
            source: 'index.php?module=account_accHead&_spAction=accountHeadsAsJSON&showHTML=0',
            select: function(event, ui) {
                var cpTopRm = $('#cpTopRm').val();
                var acc_head_id = ui.item.id;
                var url = "index.php?_topRm=" + cpTopRm + "&module=account_ledger&acc_head_id=" + acc_head_id;
                document.location.href = url;
            }
        });
    }
}