Util.createCPObject('cpw.common.multiUniqueSite');

cpw.common.multiUniqueSite = {

    init: function(){
       $('.w-common-multiUniqueSite select[name=cp_site_id]').change(this.changeSite);
    },

    changeSite: function() {
        var url = 'index.php?widget=common_multiUniqueSite&_spAction=changeSite&showHTML=0';
        var cp_site_id = $(this).val();
        $.get(url, {cp_site_id: cp_site_id}, function(){
            var topRm = $('#cpTopRm').val();
            var cpRoom = $('#cpRoom').val();
            var urlRedirect = "index.php?_topRm=" + topRm +
                      "&module=" + cpRoom;
            document.location = urlRedirect;
        })
    }


}
