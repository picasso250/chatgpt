<?php

class GeminiAPI
{

    private $api_key;
    private $dataCollector = '';
    public $decodedResponse;
    public $history;

    public function __construct($api_key)
    {
        $this->api_key = $api_key;
    }

    public function getDataCollector()
    {
        return $this->dataCollector;
    }


    public function generateContent($text)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->api_key;

        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $text
                        )
                    )
                )
            )
        );

        $this->sendRequest($url, $data);

        return $this->getMostImportantResult();
    }

    public function vision($text, $imageContents)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent?key=' . $this->api_key;

        $imageData = base64_encode($imageContents);

        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $text
                        ),
                        array(
                            'inline_data' => array(
                                'mime_type' => 'image/jpeg',
                                'data' => $imageData
                            )
                        )
                    )
                )
            )
        );

        $this->sendRequest($url, $data);

        return $this->getMostImportantResult();
    }

    public function chat($conversation)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->api_key;

        $data = array(
            'contents' => $conversation
        );

        $this->sendRequest($url, $data);

        $this->history = array_merge(
            $conversation,
            [$this->decodedResponse['candidates'][0]['content']]
        );

        return $this->getMostImportantResult();
    }

    public function chatWithStreaming($conversation, $streamingCallback)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:streamGenerateContent?key=' . $this->api_key;

        $data = array(
            'contents' => $conversation
        );

        // Pass $capturedText to the $streamingCallback function when fired
        $modifiedCallback = function ($ch, $data) use ($streamingCallback) {

            // Collect data in the class property
            $this->dataCollector .= $data;

            // Process the JSON string within the streaming data
            $capturedText = $this->processJsonInData($data);
            if ($$capturedText)
                $streamingCallback($ch, $capturedText);

            // Return the length of the data processed
            return strlen($data);
        };

        $this->sendRequestWithStreaming($url, $data, $modifiedCallback);

        // Adjust the history based on streaming data if needed
        // $this->history = ...

        return $this->getMostImportantResult();
    }

    private function processJsonInData($data)
    {
        // Regular expression to find "text": "(.+)"
        $pattern = '/"text": (".+")/';

        // Check if the pattern exists in the data
        if (preg_match($pattern, $data, $matches)) {
            return $matches[0];
        }

        // Return null if the pattern is not found
        return null;
    }


    public function embedContent($text)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent?key=' . $this->api_key;

        $data = array(
            'model' => 'models/embedding-001',
            'content' => array(
                'parts' => array(
                    array(
                        'text' => $text
                    )
                )
            )
        );

        $this->sendRequest($url, $data);

        return $this->decodedResponse;
    }

    public function batchEmbedContents($texts)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/embedding-001:batchEmbedContents?key=' . $this->api_key;

        $requests = array();

        foreach ($texts as $text) {
            $requests[] = array(
                'model' => 'models/embedding-001',
                'content' => array(
                    'parts' => array(
                        array(
                            'text' => $text
                        )
                    )
                )
            );
        }

        $data = array(
            'requests' => $requests
        );

        $this->sendRequest($url, $data);

        return $this->decodedResponse;
    }

    public function countTokens($text)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:countTokens?key=' . $this->api_key;

        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $text
                        )
                    )
                )
            )
        );

        $this->sendRequest($url, $data);

        return $this->getTotalTokens();
    }

    private function getTotalTokens()
    {
        if (isset($this->decodedResponse['totalTokens'])) {
            return (int)$this->decodedResponse['totalTokens'];
        } else {
            return null;
        }
    }

    private function sendRequest($url, $data)
    {
        $json_data = json_encode($data);

        $headers = array(
            'Content-Type: application/json'
        );

        $ch = curl_init($url);

        // Log the URL
        error_log('URL: ' . $url . "\n", 3, '/tmp/gemini.log');

        // Log the data
        error_log('Data: ' . $json_data . "\n", 3, '/tmp/gemini.log');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));

        $response = curl_exec($ch);

        // Log the response
        error_log('Response: ' . $response . "\n", 3, '/tmp/gemini.log');

        curl_close($ch);

        $this->decodedResponse = json_decode($response, true);

        if ($this->decodedResponse === null) {
            throw new Exception('Unable to decode JSON response');
        }
    }
    private function sendRequestWithStreaming($url, $data, $callback)
    {
        $json_data = json_encode($data);

        $headers = array(
            'Content-Type: application/json'
        );

        $ch = curl_init($url);

        // Log the URL
        error_log('URL: ' . $url . "\n", 3, '/tmp/gemini.log');

        // Log the data
        error_log('Data: ' . $json_data . "\n", 3, '/tmp/gemini.log');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));

        // Set the callback function for streaming
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);

        $response = curl_exec($ch);

        // Log the response
        error_log('Response: ' . $response . "\n", 3, '/tmp/gemini.log');

        curl_close($ch);

        $this->decodedResponse = json_decode($response, true);

        if ($this->decodedResponse === null) {
            throw new Exception('Unable to decode JSON response');
        }
    }

    private function getMostImportantResult()
    {
        if (isset($this->decodedResponse['candidates'][0]['content']['parts'])) {
            $textParts = $this->decodedResponse['candidates'][0]['content']['parts'];

            $resultText = '';

            foreach ($textParts as $part) {
                if (isset($part['text'])) {
                    $resultText .= $part['text'];
                }
            }

            return $resultText;
        } else {
            return null;
        }
    }
}
