Util.createCPObject('cpw.social.twitter');

cpw.social.twitter = {
    run: function(exp){
        var handle = exp.handle;
        var username = exp.username;
        var count = exp.count;
        var showProfileImages   = exp.showProfileImages;
        var showUserScreenNames = exp.showUserScreenNames;
        var showUserFullNames   = exp.showUserFullNames;
        var showActionReply     = exp.showActionReply;
        var showActionRetweet   = exp.showActionRetweet;
        var showActionFavorite  = exp.showActionFavorite;

        $('#' + handle).jTweetsAnywhere({
             username: username
            ,count: count
            ,showTweetFeed: {
                 showProfileImages: showProfileImages
                ,showUserScreenNames: showUserScreenNames
                ,showUserFullNames: showUserFullNames
                ,showActionReply: showActionReply
                ,showActionRetweet: showActionRetweet
                ,showActionFavorite: showActionFavorite
            }
        });
    }
}