function loadPage(page, query, method) {
    var area = $("#loading_layer").get(0) ;
    var text = $("#text_layer").get(0);
    if ( !area ) {
        area = document.createElement('div');
        $(area).attr('id', "loading_layer");
        text = document.createElement('div');
        $(text).attr('id', "text_layer");
    }
    var t = $('#main').offset().top;
	var D = document;
	var h = -t + Math.max(Math.max(D.body.scrollHeight, D.documentElement.scrollHeight), Math.max(D.body.offsetHeight, D.documentElement.offsetHeight), Math.max(D.body.clientHeight, D.documentElement.clientHeight));
	var l = $('#main').offset().left;
	var w = Math.max(Math.max(D.body.scrollWidth, D.documentElement.scrollWidth),Math.max(D.body.offsetWidth, D.documentElement.offsetWidth),Math.max(D.body.clientWidth, D.documentElement.clientWidth));

    $(area).css({'display':'none','opacity':'0.7','height':h,'width':w,'position':'absolute','text-align':'center','color':'white','font-size':'1.5em',  'margin':'auto','z-index':'998', 'top':t,'left':l});
    $(text).css({'display':'none','height':h-50,'width':w-50,'position':'absolute','text-align':'center','color':'white','font-size':'1.5em', 'font-weight':'bold', 'padding-top':'50px', 'margin':'auto','z-index':'999', 'top':t,'left':l+25});
    $(text).html('Loading...');
    $(document.body).append(area);
    $(document.body).append(text);
    $(area).fadeIn('slow');
    $(text).fadeIn('slow');

    if (method == 'post') {
        $.post(page, query, function(resp) {
            $("#main").html(resp);
            $(area).remove();
            $(text).remove();
        })
    } else {
        $("#main").load(page, query, function (data) {
            $(area).remove();
            $(text).remove();
        });
    }
}

