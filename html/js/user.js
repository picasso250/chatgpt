// Function to perform the AJAX request and populate the elements
function populateElements() {
    // Check if username exists in query string
    var urlParams = new URLSearchParams(window.location.search);
    var usernameFromQuery = urlParams.get('username');

    // If username exists in query string and is not empty, replace the value in cookie
    if (usernameFromQuery && usernameFromQuery.trim() !== '') {
        $.cookie('username', usernameFromQuery);
    }

    // Get username from cookie
    var usernameFromCookie = $.cookie('username');

    // Prepare the request URL with the username if available in the cookie
    var requestUrl = 'ajax.php?action=Index';
    if (usernameFromCookie) {
        requestUrl += '&username=' + encodeURIComponent(usernameFromCookie);
    }

    $.ajax({
        url: requestUrl, // Use the modified request URL
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.error) {
                layer.msg(data.error);
                return;
            }
            // Populate the elements with the data received from the API
            $('#username').text(data.user.username);

            var conversationList = $('#conversationList');
            conversationList.empty(); // Clear previous list items
            data.conversations.forEach(function (conversation) {
                var li = createConversationElement(conversation.id, conversation.title);
                conversationList.append(li);
            });

            $('#balance').text(data.user.balance);
        },
        error: function (xhr, status, error) {
            // Handle the error if any
            console.error('Error while fetching data: ', error);
        }
    });
}

// Call the function when the page is loaded
$(document).ready(function () {
    populateElements();
});

$(function () {

    var inviteContent = $('#inviteContent').clone(); // Clone the content
    $('#inviteContent').remove();
    inviteContent.show();

    $('#inviteLink').on('click', function () {
        var username = $.cookie('username'); // 从cookie中获取username
        var url = location.origin + '/invite.php?from=' + username; // 拼接url

        // Load the HTML content from the pre-written structure
        layer.open({ title: '邀请链接', content: inviteContent.prop('outerHTML'), btn: [], closeBtn: 1, shadeClose: true });
        $('#urlInput').val(url);

        // 发送ajax请求获取邀请用户数量
        $.ajax({
            url: 'ajax.php?action=GetInvitedUsersCount',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var invitedUsersCount = response.invitedUsersCount;

                // Update the fetched data using find and attr methods
                $('#inviteContent').find('div:first-child').text('已邀请用户数量：' + invitedUsersCount);

            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
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
