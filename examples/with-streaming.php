<?php

# BASIC USAGE WITH MINIMAL SETTINGS.
# RESPONSES ARE STREAMED.

require_once("../phpGPT.php");

$open_ai = new phpGPT();

$open_ai->setup([
    
    "model" => "gpt-3.5-turbo",
    "tone" => "creative", // This can be substituted with: "temperature" => 1.0
    "stream" => true

]);

$open_ai->addMessage("system", "You're an environmental researcher.");
$open_ai->addMessage("user", "Write a 500 word essay on the impact AI has on the environment.");

$open_ai->gpt()->message_content;