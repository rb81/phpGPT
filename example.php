<?php

require_once("phpGPT.php");

$open_ai = new phpGPT();

$open_ai->setup([ "model" => "gpt-3.5-turbo", "temperature" => 1.0 ]);

$open_ai->addMessage("system", "You're a funny comedian.");
$open_ai->addMessage("user", "Tell me a joke.");

echo $open_ai->gpt()->message_content;