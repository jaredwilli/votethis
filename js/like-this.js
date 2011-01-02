function likeThis( postId ) {
	if ( postId != '' ) {		
		jQuery('#LikeThis-' + postId + ' .counter').addClass('loading');
		jQuery.post(blogUrl + "/wp-content/plugins/like-this/like.php", {
			id: postId 
		}, function(data){
			jQuery('#LikeThis-' + postId + ' .counter').removeClass('counter');
			jQuery('#LikeThis-' + postId + ' .loading').addClass('inactive');
			jQuery('#LikeThis-' + postId + ' .inactive').removeClass('loading');
			jQuery('#LikeThis-' + postId + ' .inactive').text(data);
		});
	}
}