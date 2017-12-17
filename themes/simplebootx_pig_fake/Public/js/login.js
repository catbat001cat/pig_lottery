
function login_determine(cur_page, login_page) {
    var token = $.cookie('lyc_token3'),
	tel = $.cookie('lyc_logintel3'),
    openid = $.cookie('openid'),
    code;
    var _authorize = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxab090d48c7fc0568&redirect_uri=http%3a%2f%2flgcb.fe6.jmsjwzdjd.com%2fapp%2f" + cur_page + "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
    code = getQueryStringByName(window.location.href, "code");
    if (!code && !openid) {
        window.location.href = _authorize;
    }
    if (code && !openid) {
        $.ajax({
            type: 'get',
            url: '/wechat/wechatoauth.ashx?code=' + code,
            dataType: "json",
            async: false,
            success: function (data) {
                if (data.openid != '') {
                    $.cookie('openid', data.openid, { path: "/" });
                    openid = data.openid;

                } else {
                    window.location.href = _authorize;
                }
            },
            error: function () {

            }
        });
    }
}

function get_openid(cur_page) {
    var openid = $.cookie('openid');
    var code;
    if (openid) {
        return openid;
    }

    code = getQueryStringByName(window.location.href, "code");
    var _authorize = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxab090d48c7fc0568&redirect_uri=http%3a%2f%2flgcb.fe6.jmsjwzdjd.com%2fapp%2f" + cur_page + "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
    if (!code && !openid) {
        window.location.href = _authorize;
    }
    if (code && !openid) {
        $.ajax({
            type: 'get',
            url: '/wechat/wechatoauth.ashx?code=' + code,
            dataType: "json",
            async: false,
            success: function (data) {
                if (data.openid != '') {
                    $.cookie('openid', data.openid, { path: "/" });
                    openid = data.openid;
                    return openid;
                } else {
                    window.location.href = _authorize;
                }
            },
            error: function () {
                return false;
            }
        });
    }
}


function get_openidsms(cur_page) {
    var openidsms = $.cookie('openidsms');
    //alert(openidsms);
    var code;
    if (openidsms) {
        return openidsms;
    }

    code = getQueryStringByName(window.location.href, "code");
    var _authorize = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxab090d48c7fc0568&redirect_uri=http%3a%2f%2flgcb.fe6.jmsjwzdjd.com%2fapp%2f" + cur_page + "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
    if (!code && !openidsms) {
        window.location.href = _authorize;
    }
    if (code && !openidsms) {
        $.ajax({
            type: 'get',
            url: '/wechat/wechatoauthsms.ashx?code=' + code,
            dataType: "json",
            async: false,
            success: function (data) {
                if (data.openid != '') {
                    $.cookie('openidsms', data.openid, { path: "/" });
                    openidsms = data.openid;
                    return openidsms;
                } else {
                    window.location.href = _authorize;
                }
            },
            error: function () {
                return false;
            }
        });
    }
}



function getQueryStringByName(str, name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var str = window.location.search.substr(1);
    var r = str.match(reg);
    if (r != null) {
        return r[2];
    }
    return null;
};

function marketname(id) {
    if (id == 2)
        return '比特币';
    if (id == 3)
        return '恒生指数';
    if (id == 5)
        return '美白银';
    if (id == 4)
        return '美黄金';
    if (id == 6)
        return '国际原油';
    if (id == 1)
        return '莱特币';

}

function marketname2(id) {
    if (id == 2)
        return '比特币';
    if (id == 3)
        return '恒生指数';
    if (id == 5)
        return '美白银';
    if (id == 4)
        return '美黄金';
    if (id == 6)
        return '国际原油';
    if (id == 1)
        return '莱特币';

}

function marketname3(name) {
    if (name == 'bitcoin')
        return '比特币';
    if (name == 'HSI')
        return '恒生指数';
    if (name == 'SLNC')
        return '美白银';
    if (name == 'GLNC')
        return '美黄金';
    if (name == 'CONC')
        return '国际原油';
    if (name == 'litecoin') {
        return '莱特币';
    }

}
function post(URL, PARAMS) {
    var temp = document.createElement("form");
    temp.action = URL;
    temp.method = "post";
    temp.style.display = "none";
    for (var x in PARAMS) {
        var opt = document.createElement("textarea");
        opt.name = x;
        opt.value = PARAMS[x];
        // alert(opt.name)        
        temp.appendChild(opt);
    }
    document.body.appendChild(temp);
    temp.submit();
    return temp;
}

