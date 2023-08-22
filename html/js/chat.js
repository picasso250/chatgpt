var contextarray = [];
var conversation_list = [];
var is_new = true;
var active_conversation_id = 0;

var defaults = {
    html: false,        // Enable HTML tags in source
    xhtmlOut: false,        // Use '/' to close single tags (<br />)
    breaks: false,        // Convert '\n' in paragraphs into <br>
    langPrefix: 'language-',  // CSS language prefix for fenced blocks
    linkify: true,         // autoconvert URL-like texts to links
    linkTarget: '',           // set target to open link in
    typographer: true,         // Enable smartypants and other sweet transforms
    _highlight: true,
    _strict: false,
    _view: 'html'
};
defaults.highlight = function (str, lang) {
    if (!defaults._highlight || !window.hljs) { return ''; }

    var hljs = window.hljs;
    if (lang && hljs.getLanguage(lang)) {
        try {
            return hljs.highlight(lang, str).value;
        } catch (__) { }
    }

    try {
        return hljs.highlightAuto(str).value;
    } catch (__) { }

    return '';
};
mdHtml = new window.Remarkable('full', defaults);

mdHtml.renderer.rules.table_open = function () {
    return '<table class="table table-striped">\n';
};

mdHtml.renderer.rules.paragraph_open = function (tokens, idx) {
    var line;
    if (tokens[idx].lines && tokens[idx].level === 0) {
        line = tokens[idx].lines[0];
        return '<p class="line" data-line="' + line + '">';
    }
    return '<p>';
};

mdHtml.renderer.rules.heading_open = function (tokens, idx) {
    var line;
    if (tokens[idx].lines && tokens[idx].level === 0) {
        line = tokens[idx].lines[0];
        return '<h' + tokens[idx].hLevel + ' class="line" data-line="' + line + '">';
    }
    return '<h' + tokens[idx].hLevel + '>';
};
function getCookie(name) {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].trim();
        if (cookie.indexOf(name + '=') === 0) {
            return cookie.substring(name.length + 1, cookie.length);
        }
    }
    return null;
}

function isMobile() {
    const userAgent = navigator.userAgent.toLowerCase();
    const mobileKeywords = ['iphone', 'ipod', 'ipad', 'android', 'windows phone', 'blackberry', 'nokia', 'opera mini', 'mobile'];
    for (let i = 0; i < mobileKeywords.length; i++) {
        if (userAgent.indexOf(mobileKeywords[i]) !== -1) {
            return true;
        }
    }
    return false;
}

function insertPresetText() {
    $("#kw-target").val($('#preset-text').val());
    autoresize();
}
var temperature = 0.7;
function onTempChange() {
    temperature = +$('#preset-temp').val();
}
var model = 'gpt-3.5-turbo';
function onModelChange() {
    model = $('#preset-model').val();
}

function initcode() {
}

function copyToClipboard(text) {
    var input = document.createElement('textarea');
    input.innerHTML = text;
    document.body.appendChild(input);
    input.select();
    var result = document.execCommand('copy');
    document.body.removeChild(input);
    return result;
}

function copycode(obj) {
    copyToClipboard($(obj).closest('code').clone().children('button').remove().end().text());
    layer.msg("复制完成！");
}

function autoresize() {
    var textarea = $('#kw-target');
    var width = textarea.width();
    var content = (textarea.val() + "a").replace(/\\n/g, '<br>');
    var div = $('<div>').css({
        'position': 'absolute',
        'top': '-99999px',
        'border': '1px solid red',
        'width': width,
        'font-size': '15px',
        'line-height': '20px',
        'white-space': 'pre-wrap'
    }).html(content).appendTo('body');
    var height = div.height();
    var rows = Math.ceil(height / 20);
    div.remove();
    textarea.attr('rows', rows);
    $("#article-wrapper").height(parseInt($(window).height()) - parseInt($("#fixed-block").height()) - parseInt($(".layout-header").height()) - 80);
}

$(document).ready(function () {
    initcode();
    autoresize();
    $("#kw-target").on('keydown', function (event) {
        if (event.keyCode == 13 && event.shiftKey) {
            $("#kw-target").val($("#kw-target").val() + "\r\n");
            autoresize();
            return false;
        } else if (event.keyCode == 13) {
            send_post();
            return false;
        }
    });

    $(window).resize(function () {
        autoresize();
    });

    $('#kw-target').on('input', function () {
        autoresize();
    });

    $("#ai-btn").click(function () {
        if ($("#kw-target").is(':disabled')) {
            clearInterval(timer);
            $("#kw-target").val("");
            $("#kw-target").attr("disabled", false);
            autoresize();
            $("#ai-btn").html('<i class="iconfont icon-wuguan"></i>发送');
            if (!isMobile()) $("#kw-target").focus();
        } else {
            send_post();
        }
        return false;
    });

    $("#clean").click(function () {
        $("#article-wrapper").html("");
        contextarray = [];
        layer.msg("清理完毕！");
        return false;
    });

    $("#showlog").click(function () {
        let btnArry = ['已阅'];
        layer.open({ type: 1, title: '全部对话日志', area: ['80%', '80%'], shade: 0.5, scrollbar: true, offset: [($(window).height() * 0.1), ($(window).width() * 0.1)], content: '<iframe src="chat.txt?' + new Date().getTime() + '" style="width: 100%; height: 100%;"></iframe>', btn: btnArry });
        return false;
    });

    function send_post() {
        if (($('#key').length) && ($('#key').val().length != 51)) {
            layer.msg("请输入正确的API-KEY", { icon: 5 });
            return;
        }

        var prompt = $("#kw-target").val();

        if (prompt == "") {
            layer.msg("请输入您的问题", { icon: 5 });
            return;
        }

        var loading = layer.msg('正在组织语言，请稍等片刻...', {
            icon: 16,
            shade: 0.4,
            time: false //取消自动关闭
        });

        function draw() {
            $.get("getpicture.php", function (data) {
                layer.close(loading);
                layer.msg("处理成功！");
                answer = randomString(16);
                $("#article-wrapper").append('<li class="article-title" id="q' + answer + '"><pre></pre></li>');
                for (var j = 0; j < prompt.length; j++) {
                    $("#q" + answer).children('pre').text($("#q" + answer).children('pre').text() + prompt[j]);
                }
                $("#article-wrapper").append('<li class="article-content" id="' + answer + '"><img onload="document.getElementById(\'article-wrapper\').scrollTop=100000;" src="pictureproxy.php?url=' + encodeURIComponent(data.data[0].url) + '"></li>');
                $("#kw-target").val("");
                $("#kw-target").attr("disabled", false);
                autoresize();
                $("#ai-btn").html('<i class="iconfont icon-wuguan"></i>发送');
                if (!isMobile()) $("#kw-target").focus();
            }, "json");
        }
        function http_build_query(params) {
            let query = "";
            for (let key in params) {
                if (params.hasOwnProperty(key)) {
                    if (query !== "") {
                        query += "&";
                    }
                    query += encodeURIComponent(key) + "=" + encodeURIComponent(params[key]);
                }
            }
            return query;
        }
        function streaming() {
            var data = {
                temperature: temperature,
                model: model,
                message: prompt,
                conversation_id: active_conversation_id,
            };

            var es = new EventSource("stream.php?" + http_build_query(data));

            var isstarted = true;
            var alltext = "";
            var isalltext = false;
            es.onerror = function (event) {
                console.log('onerror', event)
                layer.close(loading);
                var errcode = getCookie("errcode");
                switch (errcode) {
                    case "invalid_api_key":
                        layer.msg("API-KEY不合法");
                        break;
                    case "context_length_exceeded":
                        layer.msg("问题和上下文长度超限，请重新提问");
                        break;
                    case "rate_limit_reached":
                        layer.msg("同时访问用户过多，请稍后再试");
                        break;
                    case "access_terminated":
                        layer.msg("违规使用，API-KEY被封禁");
                        break;
                    case "no_api_key":
                        layer.msg("未提供API-KEY");
                        break;
                    case "insufficient_quota":
                        layer.msg("API-KEY余额不足");
                        break;
                    case "account_deactivated":
                        layer.msg("账户已禁用");
                        break;
                    case "model_overloaded":
                        layer.msg("OpenAI模型超负荷，请重新发起请求");
                        break;
                    case "insufficient_balance":
                        layer.msg("余额不足");
                        break;
                    case null:
                        layer.msg("OpenAI服务器访问超时或未知类型错误");
                        break;
                    default:
                        layer.msg("OpenAI服务器故障，错误类型：" + errcode);
                }
                es.close();
                if (!isMobile()) $("#kw-target").focus();
                return;
            }
            es.onmessage = function (event) {
                console.log(event.data);
                if (isstarted) {
                    layer.close(loading);
                    $("#kw-target").val("请耐心等待AI把话说完……");
                    $("#kw-target").attr("disabled", true);
                    autoresize();
                    $("#ai-btn").html('<i class="iconfont icon-wuguan"></i>中止');
                    layer.msg("处理成功！");
                    isstarted = false;
                    answer = randomString(16);
                    $("#article-wrapper").append('<li class="article-title" id="q' + answer + '"><pre></pre></li>');
                    for (var j = 0; j < prompt.length; j++) {
                        $("#q" + answer).children('pre').text($("#q" + answer).children('pre').text() + prompt[j]);
                    }
                    $("#article-wrapper").append('<li class="article-content" id="' + answer + '"></li>');
                    let str_ = '';
                    let i = 0;
                    let strforcode = '';
                    timer = setInterval(() => {
                        let newalltext = alltext;
                        let islastletter = false;
                        //有时服务器错误地返回\\n作为换行符，尤其是包含上下文的提问时，这行代码可以处理一下。
                        if (newalltext.split("\n").length == 1) {
                            newalltext = newalltext.replace(/\\n/g, '\n');
                        }
                        if (str_.length < (newalltext.length - 3)) {
                            str_ += newalltext[i++];
                            strforcode = str_;
                            if ((str_.split("```").length % 2) == 0) {
                                strforcode += "\n```\n";
                            } else {
                                strforcode += "_";
                            }
                        } else {
                            if (isalltext) {
                                clearInterval(timer);
                                strforcode = newalltext;
                                islastletter = true;
                                $("#kw-target").val("");
                                $("#kw-target").attr("disabled", false);
                                autoresize();
                                $("#ai-btn").html('<i class="iconfont icon-wuguan"></i>发送');
                                if (!isMobile()) $("#kw-target").focus();
                            }
                        }
                        newalltext = mdHtml.render(strforcode);
                        //newalltext = newalltext.replace(/\\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;');
                        $("#" + answer).html(newalltext);
                        if (islastletter) MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
                        //if (document.querySelector("[id='" + answer + "']" + " pre code")) document.querySelectorAll("[id='" + answer + "']" + " pre code").forEach(el => { hljs.highlightElement(el); });
                        $("#" + answer + " pre code").each(function () {
                            $(this).html("<button onclick='copycode(this);' class='codebutton'>复制</button>" + $(this).html());
                        });
                        document.getElementById("article-wrapper").scrollTop = 100000;
                    }, 20);
                }
                if (event.data == "[DONE]") {
                    isalltext = true;
                    contextarray.push([prompt, alltext]);
                    contextarray = contextarray.slice(-5); //只保留最近5次对话作为上下文，以免超过最大tokens限制
                    es.close();
                    return;
                }

                var json = eval("(" + event.data + ")");
                if (json.choices && json.choices[0].delta.hasOwnProperty("content")) {
                    if (alltext == "") {
                        alltext = json.choices[0].delta.content.replace(/^\n+/, ''); //去掉回复消息中偶尔开头就存在的连续换行符
                    } else {
                        alltext += json.choices[0].delta.content;
                    }
                } else if (json.newBalance) {
                    $("#balance").text(json.newBalance);
                } else if (json.conversation_id) {
                    $('#conversationList .conversation').eq(0).data('id', json.conversation_id);
                    active_conversation_id = json.conversation_id;
                }
            }
        }


        if (prompt.charAt(0) === '画') {
            $.ajax({
                cache: true,
                type: "POST",
                url: "setsession.php",
                data: {
                    message: prompt,
                    context: '[]',
                    key: ($("#key").length) ? ($("#key").val()) : '',
                },
                dataType: "json",
                success: function (results) {
                    draw();
                }
            });
        } else {
            // $.ajax({
            //     cache: true,
            //     type: "POST",
            //     url: "setsession.php",
            //     data: {
            //         temperature: temperature,
            //         model: model,
            //         message: prompt,
            //         context: (!($("#keep").length) || ($("#keep").prop("checked"))) ? JSON.stringify(contextarray) : '[]',
            //         key: ($("#key").length) ? ($("#key").val()) : '',
            //     },
            //     dataType: "json",
            //     success: function (results) {
            streaming();

            if (is_new) {
                var conversationText = prompt.substr(0, 30); // 获取对话的前10个字符
                var conversationItem = {
                    context: contextarray,
                    text: conversationText
                };
                conversation_list.unshift(conversationItem); // 将新对话添加到对话列表的开头

                prependConversation(conversationText);

                is_new = false;
                active_conversation_id = 0;
            }
            // }
            // });
        }

    }

    function randomString(len) {
        len = len || 32;
        var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
        var maxPos = $chars.length;
        var pwd = '';
        for (i = 0; i < len; i++) {
            pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
        }
        return pwd;
    }

    $('#newChatBtn').on('click', newChat);
    $('#conversationList').on('click', '.delete-button', function (event) {
        event.stopPropagation();
        var index = $(this).closest('li').index();
        $(this).closest('li').remove();
        deleteConversation(index);
        console.log('删除对话' + (index + 1));
    });

    $('#conversationList').on('click', 'li', function (event) {

        // 设置被点击的li元素的样式为active
        $(this).closest('li').addClass('active').siblings().removeClass('active');

        // 获取被点击的li元素的索引
        var id = $(this).closest('li').data('id');
        active_conversation_id = id;
        is_new = false;

        $.ajax({
            dataType: "json",
            url: 'ajax.php',
            data: { action: 'GetConversation', conversation_id: $(this).closest('li').data('id') },
            success: function (ret) {
                // 设置#article-wrapper里的内容为conversation_list[active_conversation_id]并显示
                $("#article-wrapper").empty();
                for (var i = 0; i < ret.length; i++) {
                    var message = ret[i];
                    var answer = randomString(16);
                    $("#article-wrapper").append('<li class="article-title" id="q' + answer + '"><pre></pre></li>');
                    // for (var j = 0; j < message.length; j++) {
                    $("#q" + answer).children('pre').text($("#q" + answer).children('pre').text() + message.user_message);
                    // }
                    $("#article-wrapper").append('<li class="article-content" id="' + answer + '"></li>');
                    // for (var j = 0; j < message.length; j++) {
                    $("#" + answer).text($("#" + answer).text() + message.assistant_message);
                    // }

                    newalltext = mdHtml.render(message.assistant_message);
                    $("#" + answer).html(newalltext);
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
                    $("#" + answer + " pre code").each(function () {
                        $(this).html("<button onclick='copycode(this);' class='codebutton'>复制</button>" + $(this).html());
                    });
                }

            }
        });

    });

});

function deleteConversation(index) {
    conversation_list.splice(index, 1); // 从对话列表中移除对应索引的对话项
}

function createConversationElement(id, conversationTitle) {
    var liElement = $('<li class="conversation"></li>').data('id', id);
    liElement.prepend('<span class="conversation-icon">' + '<svg role="img" width="16px" height="16px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>The Conversation</title><path d="M23.996 10.543c-.131-4.91-4.289-8.773-9.2-8.773H9.005a8.997 8.997 0 0 0-5.957 15.746L1.05 22.23l4.942-2.98c.95.36 1.964.524 3.012.524h6.024c5.04 0 9.099-4.156 8.969-9.23zm-8.937 5.958H9.07c-2.587 0-5.205-2.03-5.696-4.583a5.724 5.724 0 0 1 5.63-6.874h5.99c2.586 0 5.205 2.03 5.696 4.582.688 3.667-2.095 6.875-5.63 6.875z"/></svg>      </span>');
    liElement.append($('<span class="conversation-list-item-text"></span>').text(conversationTitle));
    liElement.append('<button class="delete-button" >&times;</button>');
    return liElement;
}

function prependConversation(conversationTitle) {
    var ulElement = $('#conversationList');
    var liElement = createConversationElement(0, conversationTitle);
    ulElement.prepend(liElement);
}

// 新建聊天按钮点击事件
function newChat() {
    // 刷新右侧区域的代码
    $('#article-wrapper').empty();
    is_new = true;
    active_conversation_id = 0;
    contextarray = [];
    $('#conversationList li').removeClass('active');
}
