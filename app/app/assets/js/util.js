
function showMessage(elem, msg, type) {
    var className = "message_"+type;
    var id = 'id_message_'+type+elem.id;
    $(elem).css('display', 'none');
    $(elem).html( "<div id='"+id+"' class='"+className+"'>"+msg+"</div>" );
    $(elem).slideDown();
    $("#"+id).bind('mouseout', function () {
        $(this).slideUp(2000);
    });
}

var _buttonRun = {};
function buttonRun() {
    _buttonRun.func();
    var elem = _buttonRun.elem;
    var originalText = _buttonRun.originalText;

    if ( $(elem).get(0).nodeName == 'INPUT' ) {
        $(elem).val(originalText);
    } else {
        $(elem).html(originalText);
    }
    $(elem).attr('disabled', '');
}
function buttonClick(elem, waitText, func) {
    var originalText = '';
    if ( typeof elem == 'string' ) { elem = $(elem); }

    $(elem).attr('disabled', 'disabled');

    if ( $(elem).get(0).nodeName == 'INPUT' ) {
        originalText = $(elem).val();
        $(elem).val(waitText);
    } else {
        originalText = $(elem).html();
        $(elem).html(waitText);
    }

    _buttonRun.func = func;
    _buttonRun.elem = elem;
    _buttonRun.originalText = originalText;
    setTimeout('buttonRun()', 200);
}

