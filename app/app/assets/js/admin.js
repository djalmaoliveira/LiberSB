function loadPage(page, query, method) {
    var area = $("#loading_layer").get(0) ;
    var text = $("#text_layer").get(0);
    if ( !area ) {
        area = document.createElement('div');
        $(area).attr('id', "loading_layer");
        texto = document.createElement('div');
        $(text).attr('id', "text_layer");
    }
    var t = 20;
    var h = 500;
    var l = 20;
    var w = 800;

    $(area).css({'display':'none','opacity':'0.7','height':h,'width':w,'position':'absolute','text-align':'center','color':'white','font-size':'1.5em',  'margin':'auto','z-index':'998', 'top':t,'left':l});
    $(text).css({'display':'none','height':h,'width':w,'position':'absolute','text-align':'center','color':'white','font-size':'1.5em', 'font-weight':'bold', 'padding-top':'50px', 'margin':'auto','z-index':'999', 'top':t,'left':l});
    $(text).html('...');
    $(document.body).append(area);
    $(document.body).append(text);
    $(area).fadeIn('slow');
    $(text).fadeIn('slow');

    if (method == 'post') {
        $.post(page, query, function(resp) {
            $("#main_area").html(resp);
            $(area).remove();
            $(text).remove();
        })
    } else {
        $("#main_area").load(page, query, function (data) {
            $(area).remove();
            $(text).remove();
        });
    }
}

