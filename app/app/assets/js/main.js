function leaveComment(url, elem) {
	var par = $(elem).parent().get(0);
	if ( !$("#comment_form_area").get(0) ) {
		$(par).append("<div id='comment_form_area'>Loading...</div>");
		$.get(url, function(resp) {
			$("#comment_form_area").html(resp);
		});
	} else {
		$("#comment_form_area").remove();
			
	}
}
function sendComment(frm, btn) {
	buttonClick(btn, "Sending...", function(){
        $.post($(frm).attr('action'), $(frm).serialize(), function(resp) {
            if (resp.status=='ok') {
                $('#out').html(resp.content.text);
                $(btn).css('display', 'none');
                $('#out').addClass('msg_ok');
            } else {
                $('#out').html(resp.content.text);
                $('#out').addClass('msg_error');
            }

        }, 'json');
    });
}

