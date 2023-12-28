// chat.js

document.addEventListener("DOMContentLoaded", function() {
    var chatForm = document.getElementById("chatForm");
    var chatHistoryElement = document.getElementById("chatHistory");
    var chatHistory = [];  // Variable to store chat history

    chatForm.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Get the message input value
        var messageInput = document.getElementById("message");
        var message = messageInput.value;

        // Perform any additional processing or validation if needed

        // Use fetch to make a POST request to the /send_message/ endpoint
        fetch("/gemini/send_message/", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ message: message, history: chatHistory }),
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
