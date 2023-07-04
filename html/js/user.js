$(function(){

    $('#username').click(function () {
        layer.prompt({
            title: '请输入新的用户名',
            formType: 0,
            btn: ['确定', '取消'],
            yes: function (index, layero) {
                var newUsername = layero.find('.layui-layer-input').val();
    
                $.cookie('user_cookie', newUsername);
    
                $('#balance').text('载入中...');
                $('#username').text(newUsername);
    
                var url = location.href + '?invite_from=' + newUsername; // 拼接url
                $('#urlInput').val(url);
    
                layer.close(index);
                $.ajax({
                    url: '?action=UserInfo',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        // 更新#balance元素
                        $('#balance').text(response.balance / 100);
                    },
                    error: function () {
                        // 处理错误情况
                    }
                });
            }
        });
    });

    $('#inviteLink').on('click', function () {
        var username = $.cookie('user_cookie'); // 从cookie中获取username
        var url = location.href + '?invite_from=' + username; // 拼接url
        var content = '<div>邀请一个人可以获取10个积分</div>' +
            '<div><input type="text" value="' + url + '" id="urlInput" readonly></div>' +
            '<div><button id="copyBtn" class="btn">复制邀请链接</button></div>'; // 弹层内容
        layer.open({ title: '邀请链接', content: content, btn: [], closeBtn: 1, shadeClose: true, });
    });
    $(document).on('click', '#copyBtn', function () {
        var input = document.getElementById('urlInput');
        input.select();
        document.execCommand('copy');
        layer.msg('已复制到剪贴板');
    });
});

$(document).ready(function () {
    $("#rechargeButton").click(function () {
        layer.open({
            type: 1,
            title: false,
            closeBtn: 1,
            area: ['300px', '150px'],
            shadeClose: true,
            content: $('#qqGroup').html()
        });
    });
});
