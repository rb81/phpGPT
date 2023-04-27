<?php

require_once("../phpGPT.php");

$open_ai = new phpGPT();

// Configure the required model and temperature...
$open_ai->setup([
    
    "model" => "gpt-3.5-turbo",
    "temperature" => 0.0

]);

// Provide the original content...
$content = <<<CONTENT
UK = United Kingdom of Great Britain and Northern Ireland
US = United States of America
AE = United Arab Emirates
CONTENT;

// Provide the expected outcome...
$outcome = <<<OUTCOME
{
    "countries" : [
        {
            "uk" : "United Kingdom of Great Britain and Northern Ireland",
            ...
        }
    ]
}
OUTCOME;

// Provide the language needed...
$language = "JSON";

// This prompt structure seems to work as expected, but may not always return the codeblock needed for execution.
// (A more generic code block example may be needed, as this seems to only return code blocks if the language is PHP.)
$setup = <<<SETUP
As a senior developer with experience in transforming data, you will help me by transforming my data, as follows:

ORIGINAL CONTENT:
$content

EXPECTED OUTCOME:
$outcome

LANGUAGE:
$language

INSTRUCTIONS:
- Transform the data from the original content into the expected outcome depicted above.

RULES OF ENGAGEMENT:
- Only return the transformed data with any code required by the $language language.
- Respond without comments or explanations.
- If you are unable to return the regular expression, please return an error message with the prefix: ERROR- [ERROR MESSAGE GOES HERE]
- If I have made any mistakes in my request, please return an error message with the prefix: ERROR- [ERROR MESSAGE GOES HERE]
SETUP;

// Add the required statements to the payload...
$open_ai->addMessage("system", "You're a senior developer with experience in data transformation.");
$open_ai->addMessage("user", $setup);

// Submit the payload to ChatGPT and output the request...
echo $open_ai->gpt()->message_content;