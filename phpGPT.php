<?php

require_once("env.php"); // Set your OpenAI API key as 'OPENAI_API_KEY'

class phpGPT {

    private $payload, $concise = false;
    public $model, $temperature, $top_p, $n, $stream, $stop, $max_tokens;
    public $presence_penalty, $frequency_penalty, $logit_bias, $user, $curl_timeout = 90;

    /**
     * Sets the configuration options for the OpenAI API request. 
     * See https://platform.openai.com/docs/api-reference/chat/create#chat for details.
     * 
     * Parameters supported by this script:
     * 
     *  - model (string): ID of the model to use.
     *  - temperature (float): What sampling temperature to use, between 0 and 2.
     *  - top_p (float): An alternative to sampling with temperature.
     *  - n (int): How many chat completion choices to generate for each input message.
     *  - stream (boolean): If set, partial message deltas will be sent, like in ChatGPT.
     *  - stop (string or array): Up to 4 sequences where the API will stop generating further tokens.
     *  - max_tokens (integer): The maximum number of tokens to generate in the chat completion.
     *  - presence_penalty (float): Number between -2.0 and 2.0.
     *  - frequency_penalty (float): Number between -2.0 and 2.0.
     *  - logit_bias (JSON): Modify the likelihood of specified tokens appearing in the completion.
     *  - user (string): A unique identifier representing the end-user.
     *  - concise (boolean): A custom parameter to instruct ChatGPT to respond concisely.
     *  - tone (string): A custom parameter that can be used instead of 'temperature'.
     * 
     * @param array $par The configuration options for the OpenAI API request. 
     * @throws E_USER_ERROR if a parameter is invalid or outside of the accepted range.
     * @return void
     */
    public function setup($par)
    {
        foreach ($par as $key => $value) {
            
            switch ($key) {

                case "model":
                    
                    // ID of the model to use. See the model endpoint compatibility table for details on which models work with the Chat API.
                    // https://platform.openai.com/docs/models/model-endpoint-compatibility
                    
                    $valid_models = ["gpt-4", "gpt-4-turbo-preview", "gpt-4-vision-preview", "gpt-4-32k", "gpt-3.5-turbo", "gpt-3.5-turbo-16k"];
                    
                    (is_string($value) && in_array($value, $valid_models)) ? $this->payload["model"] = $this->model = $value : 
                        trigger_error("'model' is invalid.", E_USER_ERROR);

                    break;

                case "temperature":
                    
                    // What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower 
                    // values like 0.2 will make it more focused and deterministic.
                    
                    (is_float($value) && $value >= 0.0 && $value <= 2.0) ? $this->payload["temperature"] = $this->temperature = $value : 
                        trigger_error("'temperature' is not a valid float or outside of accepted range.", E_USER_ERROR);

                    break;

                case "top_p":
                    
                    // An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens 
                    // with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered.
                    
                    (is_float($value) && $value >= 0.0 && $value <= 1.0) ? $this->payload["top_p"] = $this->top_p = $value : 
                        trigger_error("'top_p' not a valid float or outside of accepted range.", E_USER_ERROR);

                    break;

                case "n":
                    
                    // How many chat completion choices to generate for each input message.
                    
                    is_int($value) ? $this->payload["n"] = $this->n = $value : 
                        trigger_error("'n' should be a valid integer.", E_USER_ERROR);

                    break;

                case "stream":
                    
                    // If set, partial message deltas will be sent, like in ChatGPT. Tokens will be sent as data-only server-sent events as 
                    // they become available, with the stream terminated by a data: [DONE] message.
                    
                    is_bool($value) ? $this->payload["stream"] = $this->stream = $value : 
                        trigger_error("'stream' should be boolean.", E_USER_ERROR);
                    
                    break;

                case "stop":

                    // Up to 4 sequences where the API will stop generating further tokens.

                    (is_string($value) || (is_array($value) && count($value) <= 4)) ? $this->payload["stop"] = $this->stop = $value : 
                        trigger_error("'stop' must either be a string, or an array with no more than 4 items.", E_USER_ERROR);

                    break;

                case "max_tokens":

                    // The maximum number of tokens to generate in the chat completion. The total length of input tokens and generated 
                    // tokens is limited by the model's context length.

                    is_int($value) ? $this->payload["max_tokens"] = $this->max_tokens = $value : 
                        trigger_error("'max_tokens' should be an integer.", E_USER_ERROR);

                    break;

                case "presence_penalty":

                    // Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, 
                    // increasing the model's likelihood to talk about new topics.

                    (is_float($value) && $value >= -2.0 && $value <= 2.0) ? $this->payload["presence_penalty"] = $this->presence_penalty = $value : 
                        trigger_error("'presence_penalty' is not a valid float or outside of accepted range.", E_USER_ERROR);

                    break;

                case "frequency_penalty":

                    // Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, 
                    // decreasing the model's likelihood to repeat the same line verbatim.

                    (is_float($value) && $value >= -2.0 && $value <= 2.0) ? $this->payload["frequency_penalty"] = $this->frequency_penalty = $value : 
                        trigger_error("'frequency_penalty' is not a valid float or outside of accepted range.", E_USER_ERROR);

                    break;

                case "logit_bias":
                    
                    // Modify the likelihood of specified tokens appearing in the completion.
                    // Accepts a json object that maps tokens (specified by their token ID in the tokenizer) to an associated bias value 
                    // from -100 to 100. Mathematically, the bias is added to the logits generated by the model prior to sampling. The 
                    // exact effect will vary per model, but values between -1 and 1 should decrease or increase likelihood of selection; 
                    // values like -100 or 100 should result in a ban or exclusive selection of the relevant token.

                    (is_array($value)) ? $this->payload["logit_bias"] = $this->logit_bias = $value : trigger_error("'logit_bias' should be an array.", E_USER_ERROR);

                    break;

                case "user":

                    // A unique identifier representing your end-user, which can help OpenAI to monitor and detect abuse.

                    is_string($value) ? $this->payload["user"] = $this->user = $value : 
                        trigger_error("'user' should be a valid string.", E_USER_ERROR);

                    break;

                case "concise":

                    // This is a custom parameter that instructs ChatGPT to return concise answers.

                    is_bool($value) ? (($value !== true) ?: $this->concise = true) : 
                        trigger_error("'concise' should be a boolean.", E_USER_ERROR);

                    break;

                case "tone":

                    // This is a custom parameter that can be used instead of 'temperature'.
                    // Note: This setting overrides 'temperature' and removes 'top_p' if either is set.

                    $tones = ["creative", "balanced", "precise"];

                    if (in_array($value, $tones)) {

                        switch($value) {

                            case "creative":
                                $this->payload["temperature"] = 1.0;
                                break;

                            case "balanced":
                                $this->payload["temperature"] = 0.5;
                                break;

                            case "precise":
                                $this->payload["temperature"] = 0.0;
                                break;
                        }

                    } else { trigger_error("'tone' should be 'creative', 'balanced' or 'precise.", E_USER_ERROR); }

                    break;

                default:
                trigger_error("Unknown parameter.", E_USER_ERROR);
            }
        }
    }

    /**
     * Adds a message to the list of messages to send to the OpenAI API.
     *
     * This function adds a message to the list of messages to send to the OpenAI API for processing.
     * It takes two parameters:
     * - `$role`: a string representing the role of the message sender (e.g. "user" or "bot").
     * - `$content`: a string representing the content of the message.
     * 
     * The function adds the message to the `$this->messages` array using the following format:
     * ```
     * "messages": [
     *     { "role": "user", "content": "Hello, how are you?" },
     *     { "role": "bot", "content": "I'm doing well, thanks for asking!" }
     * ]
     * ```
     * 
     * @param string $role The role of the message sender.
     * @param string $content The content of the message.
     * @return void
     */
    public function addMessage($role, $content)
    {    
        $this->payload["messages"][] = [ "role" => $role, "content" => $content ];
    }

    /**
     * Sends a request to the OpenAI API and returns the response.
     *
     * This function sends a request to the OpenAI API with the current message configuration options.
     * It returns the response from the API as an object.
     * 
     * If any errors occur during the request, the function will throw an exception with a detailed error message.
     *
     * @throws Exception if there is a cURL or HTTP error during the request.
     * @throws E_USER_WARNING if both temperature and top_p are set, which is not recommended.
     * @return object The response from the OpenAI GPT API as an object.
     */
    public function gpt()
    {
        if (!isset($this->model)) {

            // If 'model' is missing, we can't continue...
            die("Model configuration missing.");

        } elseif (!isset($this->payload["messages"][0]["role"]) && !isset($this->payload["messages"][0]["content"])) {

            // We need at least one message with values for both 'role' and 'content'...
            die("Invalid or missing message.");   

        } elseif (isset($this->temperature) && isset($this->top_p)) {

            // OpenAI does not recommend having both 'temperature' and 'top_p' set...
            trigger_error("Setting both 'temperature' and 'top_p' not recommended. If using 'tone', be sure to remove 'top_p'.", E_USER_WARNING);
        }

        if ($this->concise) {

            // Adding this at the end of the array as it caused issues when inserted earlier...
            $content = "Please respond without any additional commentary or explanations."; // <-- Seems to work most of the time!
            $this->payload["messages"][] = [ "role" => "user", "content" => $content ];

        }

        $ch = curl_init();

        $curlOptions = array(
            CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
            CURLOPT_POSTFIELDS => json_encode($this->payload),
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => $this->curl_timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . getenv('OPENAI_API_KEY')
            )
        );

        // If streaming, strip everything except the content and echo the result...
        if ($this->stream) {

            $curlOptions[CURLOPT_WRITEFUNCTION] = function($foo, $data) {
                
                $events = explode("\n\n", $data);

                foreach ($events as $event) {

                    $result = json_decode(str_replace('data: ', '', $event));

                    if (is_null($result) || $result->choices[0]->finish_reason === "stop") { break; }                    
                    if (isset($result->choices[0]->delta->content)) { echo $result->choices[0]->delta->content; }

                    ob_flush();
                    flush();
                }
                
                return strlen($data);
            };
        }
        
        curl_setopt_array($ch, $curlOptions);

        $response = json_decode(curl_exec($ch), false);

        $http_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        if ($curl_error) {
            throw new Exception("cURL error: " . $curl_error);
        } elseif ($http_response_code >= 400) {
            throw new Exception("HTTP error " . $http_response_code . ": " . $response->error->message);
        }

        curl_close($ch);

        return ($this->stream) ?: $this->process($response);
    }

    /**
     * Takes in a response object and returns a new object with selected properties.
     *
     * @param object $response The response object to process.
     * @return object The processed object.
     */
    private function process($response)
    {
        $data = new stdClass();
        $data->id = $response->id;
        $data->object = $response->object;
        $data->created = $response->created;
        $data->model = $response->model;
        
        $data->prompt_tokens = $response->usage->prompt_tokens;
        $data->completion_tokens = $response->usage->completion_tokens;
        $data->total_tokens = $response->usage->total_tokens;
        
        $data->message_role = $response->choices[0]->message->role;
        $data->message_content = $response->choices[0]->message->content;
        
        $data->finish_reason = $response->choices[0]->finish_reason;
        $data->index = $response->choices[0]->index;
        
        return $data;
    }
}