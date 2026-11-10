Util.createCPObject('cpw.common.adminTranslation');

cpw.common.adminTranslation = {

    init: function(){
       $('.w-common-adminTranslation select[name=langAdminInt]').change(this.changeLang);
    },

    changeLang: function() {
        var lang = $(this).val();
        
        var urlRedirect = $('#currentUrlNoLang').val() + '&langAdminInt=' + lang;
        document.location = urlRedirect;
        
    }


}
