
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
        showLoginBox();
    }
}

// 处理登出操作
function logout() {
    // 发送登出的请求
    window.location.href = '?action=logout';
}
$(function () {
    checkLogin();
})// 显示登录框
function showLoginBox() {
    layer.open({
        title: '登录',
        content: `
          <form id="loginForm">
            <label for="username">用户名</label>
            <input type="text" id="username" name="username">
            <br>
            <label for="password">密码</label>
            <input type="password" id="password" name="password">
            <br>
            <button class="btn" type="button" onclick="submitLoginForm()">登录</button>
          </form>
        `
    });
}

// 提交登录表单
function submitLoginForm() {
    var username = $('#username').val();
    var password = $('#password').val();

    $.ajax({
        url: '?action=login', // 登录请求的处理页面
        type: 'POST',
        data: {
            username: username,
            password: password
        },
        success: function (response) {
            // 处理登录结果
            if (response === 'ok') {
                layer.msg('登录成功！');
                location.reload(); // 刷新页面
            } else {
                layer.msg('登录失败，请检查用户名和密码！');
            }
        }
    });
}