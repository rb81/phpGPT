<?php

# BASIC USAGE WITH MINIMAL SETTINGS.
# RESPONSES ARE RETURNED IN FULL, WHICH MAY TAKE TIME.

require_once("../phpGPT.php");

$open_ai = new phpGPT();

$open_ai->setup([
    
    "model" => "gpt-3.5-turbo",
    "temperature" => 1.0 // This can be substituted with: "tone" => "creative"

]);

$open_ai->addMessage("system", "You're an environmental researcher.");
$open_ai->addMessage("user", "Write a 500 word essay on the impact AI has on the environment.");

echo $open_ai->gpt()->message_content;