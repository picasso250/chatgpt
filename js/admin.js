
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

$(function () {
    checkLogin();
});
