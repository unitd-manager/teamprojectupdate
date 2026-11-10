Util.createCPObject('cpw.common.multiCountry');

cpw.common.multiCountry = {

    init: function(){
       $('.w-common-multiCountry select[name=cp_country_id]').change(this.changeCountry);
    },

    changeCountry: function() {
        var url = 'index.php?widget=common_multiCountry&_spAction=changeCountry&showHTML=0';
        var cp_country_id = $(this).val();
        $.get(url, {cp_country_id: cp_country_id}, function(){
            var topRm = $('#cpTopRm').val();
            var cpRoom = $('#cpRoom').val();
            var cpAction = $('#cpAction').val();
            if (cpAction == 'list') {
                var urlRedirect = "index.php?_topRm=" + topRm +
                          "&module=" + cpRoom;
                document.location = urlRedirect;
            }
        })
    }


}
