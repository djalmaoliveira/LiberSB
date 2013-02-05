function closeComment(speed) {
	$('#comment_form_area').slideUp(speed, function(){
		$("#comment_form").css('display', 'none');
		$("#comment_form_place").append( $("#comment_form") );
		$("#comment_form_area").remove();
	});
}

function leaveComment(content_id, url, elem) {

	var par = $(elem).parent().get(0);

	if ( !$(par).children('#comment_form_area').get(0) ) {
		closeComment('fast');
	}

	if ( !$("#comment_form_area").get(0) ) {

		$(par).append("<div id='comment_form_area'></div>");
		$("#comment_form_area").css('display', 'none');
		$("#comment_form_area").append( $("#comment_form") );
		$("#comment_form #content_id").val(content_id);
		$("#comment_form #frmComment").attr('action', url);
		$("#comment_form #btnSend").css('display', 'inline');
		$('#comment_form #out').html('');
		if ( $("#frmComment").get(0) ) {
            $("#frmComment").get(0).reset();
        }
		$("#comment_form").css('display', 'block');

		$('#comment_form_area').slideDown(500, function(){

		});
	} else {

	}
}

function sendComment(frm, btn) {
	buttonClick(btn, "Sending...", function(){
        var urlBase = $("#site_title a").attr('href');

		$.get(urlBase+'token', function(resp){
			$("#comment_form #token").val(resp.content.text);
		}, 'json');

        $.post( $(frm).attr('action'), $(frm).serialize(), function(resp) {
            if (resp.status=='ok') {
                $('#out').html(resp.content.text);
                $(btn).css('display', 'none');
                $('#out').addClass('msg_ok');
				setTimeout("closeComment('slow')", 3000);

            } else {
                $('#out').html(resp.content.text);
                $('#out').addClass('msg_error');
            }

        }, 'json');
    });
}