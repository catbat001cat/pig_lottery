function get_user_openid() {
	return "11111";
    var openid = $.cookie('user_openid');
    var code;
    if (openid) {
        return openid;
    }

    code = getQueryStringByName(window.location.href, "code");
    var _authorize = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx95d3aa6fb793e834&redirect_uri=http%3a%2f%2fdba.qwchuanmei.com%2f&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
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
                    $.cookie('user_openid', data.openid, { path: "/",expires: 1 });
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

function getQueryStringByName(str, name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var str = window.location.search.substr(1);
    var r = str.match(reg);
    if (r != null) {
        return r[2];
    }
    return null;
};
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

