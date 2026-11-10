Util.createCPObject('cpm.edukite.dailyActivity');

cpm.edukite.dailyActivity = {
    init: function(){
        $('a#btnSaveRecord').click(function(e){
            e.preventDefault();
            $('#frmEdit').submit();
            Util.showProgressInd('Progressing');
            alert('Record Submitted Successfully')
        });
    }

}