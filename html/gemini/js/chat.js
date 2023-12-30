function createChatHistoryItem(message, role) {
    var chatHistoryItem = document.createElement("li");

    // Use marked to convert Markdown to HTML
    var htmlContent = marked(message);

    // Create role and content div elements
    var roleDiv = document.createElement("div");
    roleDiv.className = "role";
    roleDiv.textContent = role;

    var contentDiv = document.createElement("div");
    contentDiv.className = "content";
    contentDiv.innerHTML = htmlContent;

    // Append role and content divs to the chatHistoryItem
    chatHistoryItem.appendChild(roleDiv);
    chatHistoryItem.appendChild(contentDiv);

    // Set data-text attribute to the message
    chatHistoryItem.setAttribute("data-text", message);

    return chatHistoryItem;
}


var chatHistory = [];  // Variable to store chat history

document.addEventListener("DOMContentLoaded", function () {
    var chatForm = document.getElementById("chatForm");
    var chatHistoryElement = document.getElementById("chatHistory");
    var submitButton = document.getElementById("submitButton");

    chatForm.addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission

        // Get the message input value
        var messageInput = document.getElementById("message");
        var message = messageInput.value;

        // Disable textarea and button
        messageInput.disabled = true;
        submitButton.disabled = true;

        chatHistoryElement.appendChild(createChatHistoryItem(message, 'user'));
        var modelLi = createChatHistoryItem('loading...', 'model');
        chatHistoryElement.appendChild(modelLi);

        // Convert message to JSON and base64 encode
        var encodedMessage = btoa(JSON.stringify({
            role: 'user',
            parts: [
                {
                    text: message
                }
            ]
        }));

        // Create an EventSource object
        var eventSource = new EventSource("ajax.php?action=send_message_stream&message=" + encodedMessage);

        var collectedText='';

        // Listen for messages from the server
        eventSource.addEventListener("message", function (event) {
            var data = JSON.parse(event.data);

            // Enable textarea and button
            messageInput.disabled = false;
            submitButton.disabled = false;

            // Update the local chat history variable
            chatHistory = data;

            collectedText+=data.text;

            // Clear the message input
            messageInput.value = "";

            // Get the content div within the model li
            var contentDiv = modelLi.querySelector('.content');

            // Use marked to convert Markdown to HTML
            var htmlContent = marked(collectedText);

            // Set innerHTML to render HTML content in the content div
            contentDiv.innerHTML = `${htmlContent}`;

            // Append the updated model li to the chat history
            chatHistoryElement.appendChild(modelLi);
        });

        // Listen for errors from the server
        eventSource.addEventListener("error", function (error) {
            // Enable textarea and button
            messageInput.disabled = false;
            submitButton.disabled = false;

            console.error("Error:", error);

            // Close the EventSource connection in case of an error
            eventSource.close();
        });
    });
});
