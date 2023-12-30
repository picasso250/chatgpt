<?php

require 'GeminiAPI.php';

// Define a custom streaming callback function for testing
function customTestStreamingCallback($ch, $capturedText)
{
    // Custom processing of captured text
    echo "Test Callback: $capturedText\n";
}

// Usage of chatWithStreaming with custom callback in a test scenario
$geminiApi = new GeminiAPI('AIzaSyA5V7OQZfFUkZlSt6TBR1vVLnco8PFS6wc');
// Initial user message to start the conversation
$conversation = array(
    array(
        'role' => 'user',
        'parts' => array(
            array(
                'text' => 'Write the first line of a story about a magic backpack.'
            )
        )
    ),
);

// Call chatWithStreaming with the custom test streaming callback
$geminiApi->chatWithStreaming($conversation, 'customTestStreamingCallback');

// Access the dataCollector property to see the collected data
$collectedData = $geminiApi->getDataCollector();
echo "Collected Data: $collectedData\n";
