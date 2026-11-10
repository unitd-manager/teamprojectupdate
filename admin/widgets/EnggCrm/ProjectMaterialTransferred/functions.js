$(function(){

});

var projectMaterialTransferred = {
    reloadMaterialTransferredPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectMaterialTransferred&_spAction=materialTransferredPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#materialTransferLinkPortal').html(html);
        });
    },
}