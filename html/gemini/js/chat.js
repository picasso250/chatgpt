document.addEventListener("DOMContentLoaded", function() {
    var chatForm = document.getElementById("chatForm");
    var chatHistoryElement = document.getElementById("chatHistory");
    var chatHistory = [];  // Variable to store chat history

    chatForm.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Get the message input value
        var messageInput = document.getElementById("message");
        var message = messageInput.value;

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
            body: JSON.stringify({ history: chatHistory.concat([messageObject]) }),
        })
        .then(response => response.json())
        .then(data => {

            // Update the chat history element
            data.history.forEach(item => {
                var chatHistoryItem = document.createElement("li");
                chatHistoryItem.innerText = `${item.role}: ${item.text}`;
                chatHistoryElement.appendChild(chatHistoryItem);
            });

            // Update the local chat history variable
            chatHistory = data.history;

            // Clear the message input
            messageInput.value = "";
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
});
