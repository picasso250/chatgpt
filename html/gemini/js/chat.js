document.addEventListener("DOMContentLoaded", function () {
    var chatForm = document.getElementById("chatForm");
    var chatHistoryElement = document.getElementById("chatHistory");
    var chatHistory = [];  // Variable to store chat history
    var submitButton = document.getElementById("submitButton");

    chatForm.addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission

        // Get the message input value
        var messageInput = document.getElementById("message");
        var message = messageInput.value;

        // Disable textarea and button
        messageInput.disabled = true;
        submitButton.disabled = true;

        // Show loading indicator
        var loadingIndicator = document.createElement("div");
        loadingIndicator.textContent = "Loading...";
        chatHistoryElement.appendChild(loadingIndicator);

        // Construct the message object to be sent in the request body
        var messageObject = {
            role: 'user',
            parts: [
                {
                    text: message
                }
            ]
        };

        // Use fetch to make a POST request to the /send_message/ endpoint
        fetch("ajax.php?action=send_message", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(chatHistory.concat([messageObject])),
        })
            .then(response => response.json())
            .then(data => {
                // Enable textarea and button
                messageInput.disabled = false;
                submitButton.disabled = false;

                // Hide loading indicator
                chatHistoryElement.removeChild(loadingIndicator);

                chatHistoryElement.innerHTML = '';

                data.forEach(item => {
                    var chatHistoryItem = document.createElement("li");
                
                    // Use marked to convert Markdown to HTML
                    var htmlContent = marked(item.parts[0].text);
                
                    // Set innerHTML to render HTML content
                    chatHistoryItem.innerHTML = `${item.role}: ${htmlContent}`;
                
                    chatHistoryElement.appendChild(chatHistoryItem);
                });

                // Update the local chat history variable
                chatHistory = data;

                // Clear the message input
                messageInput.value = "";
            })
            .catch(error => {
                // Enable textarea and button
                messageInput.disabled = false;
                submitButton.disabled = false;

                // Hide loading indicator
                chatHistoryElement.removeChild(loadingIndicator);

                console.error("Error:", error);
            });
    });
});
