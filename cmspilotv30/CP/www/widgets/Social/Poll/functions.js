Util.createCPObject('cpw.social.poll');

cpw.social.poll = {
    init: function(){
        cpw.social.poll.setForm();
    },

    setForm: function(){
        $('form#pollForm').livequery(function() {
            var formName = 'pollForm';
            var extraPar = {};
            var cpCSRFToken = $('#cpCSRFToken').val();

            var additionalData = {
                cpCSRFToken: cpCSRFToken
            };

            var options = {
                success: function(json, statusText, xhr, jqFormObj) {
                    Util.hideProgressInd();
                    $('#' + formName).unblock();

                    if (json.errorCount == 0){
                        if (json.extraParam) {
                            if (typeof _gaq != 'undefined' && json.extraParam['gaTrackPageUri']) { //google analytics page tracking
                                _gaq.push(['_trackPageview', json.extraParam['gaTrackPageUri']]);
                            }
                        }
                        document.location = document.location;
                    }
                },
                beforeSubmit: function(frmData) {
                    Util.clearPrepopulatedTextbox('#' + formName, frmData);
                    Util.showProgressInd();
                    $('#' + formName).block({ message: null });
                }
                ,data: additionalData
                ,dataType: 'json'
            };

            $('#' + formName).ajaxForm(options);
    	});
    }
}