Util.createCPObject('cpm.accountsg.ledger');

cpm.accountsg.ledger = {
    init: function(){
        cpm.accountsg.ledger.initializeAccountTypeAhead();
        Util.prepopulatedTextbox();
    },

    initializeAccountTypeAhead: function(){
        $('.fld-acc_head').autocomplete({
            source: 'index.php?module=accountsg_accHead&_spAction=accountHeadsAsJSON&showHTML=0',
            select: function(event, ui) {
                var cpTopRm = $('#cpTopRm').val();
                var acc_head_id = ui.item.id;
                var url = "index.php?_topRm=" + cpTopRm + "&module=accountsg_ledger&acc_head_id=" + acc_head_id;
                document.location.href = url;
            }
        });
    }
}