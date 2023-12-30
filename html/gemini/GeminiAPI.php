<?php

class GeminiAPI
{

    private $api_key;
    public $decodedResponse;
    public $history;

    public function __construct($api_key)
    {
        $this->api_key = $api_key;
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

        // Set $this->history only for the chat method
        $this->history = $this->decodedResponse['candidates'][0]['content'];

        return $this->getMostImportantResult();
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

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));

        $response = curl_exec($ch);

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
