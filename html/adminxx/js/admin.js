
function generateUsers() {
    // 调用后端 API 生成指定数量的用户
    $.ajax({
        url: '?action=gen_user', // 后端处理生成用户的逻辑
        type: 'POST',
        data: { balance: $('#balance').val() * 100, userCount: $('#user-count').val() },
        success: function (response) {
            // 在生成用户的回调函数中，根据后端返回的数据更新用户列表
            // 在 display-area 中显示用户列表
            $('#display-area').html(response);
        },
        error: function () {
            layer.alert('生成用户失败');
        }
    });
}
// 检查登录状态并处理登出逻辑
function checkLogin() {
    // 检查是否有用户变量
    if (typeof user !== 'undefined' && user !== null) {
        // 已登录，显示欢迎信息和登出按钮
        document.getElementById('welcome').innerHTML = '欢迎，' + user.username + '！';
        document.getElementById('logout').style.display = 'block';
        $('#login').hide();
    } else {
        // 未登录，显示登录链接
        location.href = 'adminxlogin.php';
    }
}

$(document).ready(function () {
    checkLogin();
});

$(document).ready(function () {
    // 获取当前浏览器的时区偏移（以分钟为单位）
    var timezoneOffset = new Date().getTimezoneOffset();

    // 遍历所有带有data-time属性的元素
    $('[data-datetime]').each(function () {
        var $element = $(this);
        var utcTime = $element.data('datetime'); // 获取data-time属性的UTC时间
        if (!utcTime) return;
        var localTime = new Date(utcTime);    // 创建本地时间对象
        localTime.setMinutes(localTime.getMinutes() - timezoneOffset); // 调整为当前时区时间

        // 格式化本地时间并更新元素的文本内容
        var formattedLocalTime = localTime.toLocaleString(); // 根据浏览器语言格式化时间
        $element.text(formattedLocalTime);
    });

    $('[data-boolean]').each(function () {
        var value = $(this).attr('data-boolean');
        if (value === '1') {
            $(this).text('是');
        } else if (value === '0') {
            $(this).text('否');
        }
    });
});
$(document).ready(function () {
    $('.recharge-btn').click(function () {
        var button = $(this);
        var originalText = button.text();
        var uid = $(this).data('uid');
        var amount = $(this).prev('input').val();

        button.text('充值中...');

        $.ajax({
            url: 'ajax.php?action=Recharge', // Replace with the actual URL for the recharge action
            type: 'POST',
            data: {
                'user_id': uid,
                'point': amount * 100,
            },
            success: function (response) {
                button.text('充值成功');
                setTimeout(function () {
                    button.text(originalText);
                }, 3000);

                // Update balance value in the table
                var balanceValue = response.data.balance; // Assuming the response has a 'data' object with a 'balance' property
                button.closest('td').siblings('.table-data-balance').text(balanceValue);

            },
            error: function () {
                button.text('充值失败'); // Display an error message if the request fails
                setTimeout(function () {
                    button.text(originalText);
                }, 3000);
            }
        });
    });
});