function btnSend(frm, btn) {
    buttonClick(btn, "Sending...", function(){
        $.post($(frm).attr('action'), $(frm).serialize(), function(resp) {
            if (resp.status=='ok') {
                $('#out').html(resp.content.text);
                $(btn).css('display', 'none');
            } else {
                $('#out').html(resp.content.text);
            }

        }, 'json');




    });

}
