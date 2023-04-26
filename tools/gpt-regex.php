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
Match the phrase stock tips.
CONTENT;

// Provide the expected outcome...
$outcome = <<<OUTCOME
stock tips
OUTCOME;

// Provide the language needed...
$language = "PHP";

// This prompt structure seems to work as expected, but may not always return the codeblock needed for execution.
// (A more generic code block example may be needed, as this seems to only return code blocks if the language is PHP.)
$setup = <<<SETUP
As a senior developer with experience in regular expressions, you will help me by giving me a regular expression, as follows:

ORIGINAL CONTENT:
$content

EXPECTED OUTCOME:
$outcome

LANGUAGE:
$language

INSTRUCTIONS:
- Return the regular expression needed to derive the expected outcome from the original content.
- Include any code needed to execute the expression in the language I provided above.

RULES OF ENGAGEMENT:
- Only return the code block with the working code needed to execute the regular expression in the $language language.
- Respond without comments or explanations, as in the example below:

AGENT:
\$original_content = "Match the phrase stock tips.";
preg_match('/stock tips/', \$original_content, \$matches);
echo \$matches[0];

- If you are unable to return the regular expression, please return an error message with the prefix: ERROR- [ERROR MESSAGE GOES HERE]
- If I have made any mistakes in my request, please return an error message with the prefix: ERROR- [ERROR MESSAGE GOES HERE]
SETUP;

// Add the required statements to the payload...
$open_ai->addMessage("system", "You're a senior developer with experience in regular expressions.");
$open_ai->addMessage("user", $setup);

// Submit the payload to ChatGPT and output the request...
echo $open_ai->gpt()->message_content;