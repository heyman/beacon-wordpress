BeaconWordpressPlugin = {
 onNewPost: function(post) {
	// don't show post if we posted it ourself
	if (post.beacon_user == Beacon.options.user)
	    return;
	
	var container = jQuery(".commentlist");
	if (container.length == 0) {
	    var posts = jQuery(".post")
	    if (posts.length == 0)
		return;
	    var lastPost = jQuery(posts[posts.length-1]);
	    var container = jQuery('<div class="commentlist"></div>');
	    container.insertAfter(lastPost);
	}
	
	var notice = jQuery("#beaconCommentNotice");
	if (notice.length == 0) {
	    var notice = jQuery('<div id="beaconCommentNotice"><span id="beaconCommentCount">1</span> new comment has been posted since you loaded the page! Click to reload post.</div>');
	    container.append(notice);
	    notice.click(function() {
		    window.location.reload()
	    });
	} else {
	    var count = jQuery("#beaconCommentCount");
	    count.html(parseInt(count.html()) + 1);
	}
 }
};


