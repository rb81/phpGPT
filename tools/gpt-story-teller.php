<?php

require_once("../phpGPT.php");

/**
 * STORY TELLER WITH CONTROL MECHANISM
 * 
 * This tool asks ChatGPT to write a children's story based on a topic and 
 * character we provide it with, appropriate for a specific age. Once the story
 * is written, we prompt a new instance of ChatGPT with a request to confirm
 * that the story does not contain anything inappropriate for the age we
 * requested, including profantiy or unsuitable topics.
 * 
 * These instances are named "story teller" and "story checker".
 * 
 * WARNING: This tool uses a loop, so if the story checker instance does not
 * respond with 'true' then we assume the story is inappopriate and we prompt
 * the story teller instance with another request. In case ChatGPT responds
 * with anything other than 'true' in JSON, this could potentially loop
 * forever, consuming your tokens.
 */

// Setup the story teller instance...
$story_teller = new phpGPT();

// We want some creativity for the story, but not too much...
$story_teller->setup([ "model" => "gpt-3.5-turbo", "temperature" => 0.5 ]);

// Setup the story checker instance...
$story_checker = new phpGPT();

// We don't want any creativity here, since we only want a confirmation...
$story_checker->setup([ "model" => "gpt-3.5-turbo", "temperature" => 0.0 ]);

// Dictate the parameters of the story...
$topic = "A young girl who discovers the wonders of the ocean.";
$character = "Layla";
$age = "8";

// Setup the story teller prompt...
$story_teller_prompt = <<<PROMPT
REQUEST:
I want you to tell me a story about "$topic". The main character's name is "$character".

RULES OF ENGAGEMENT:
- The story should be no more than 3 paragraphs or 500 words or 2,500 characters long.
- The story should be suitable for children $age year's old and below.
- The story should begin with "Once upon a time,"
- The story should end with "The End."
- The story should not contain any profanity or adult topics.
- The story should not contain topics that may be inappropriate for children $age year's old and below.
PROMPT;

// Add the required statements to the payload...
$story_teller->addMessage("system", "You are an experienced teller of children's stories.");
$story_teller->addMessage("user", $story_teller_prompt);

// Setting things up for the loop...
$confirmation = false;

// Here's where things can go loopy...
while ($confirmation === false) {

    // Request the story from the story teller instance...
    $story = $story_teller->gpt()->message_content;

    // Setup the story checker prompt with the newly written story...
    $story_checker_prompt = <<<PROMPT
    REQUEST:
    I want you to check the story below to ensure that it is appropriate for children aged $age and below.

    RULES OF ENGAGEMENT:
    - Make sure that the story does not contain any profanity or adult topics.
    - Make sure that the story does not contain any GPT hallucinations.
    - Make sure that the story makes sense and is readable.

    RESPONSE FORMAT:
    Respond true or false if the story is appropriate or not. Only include the content delimited by ``` below:

    ```
    {
        "response" : {BOOLEAN}
    }
    ```

    STORY TO CHECK:
    $story
    PROMPT;

    // Add the required statements to the payload...
    $story_checker->addMessage("system", "You are a Senior Editor and experienced children's story writer.");
    $story_checker->addMessage("user", $story_checker_prompt);

    // Get the confirmation from the story checker instance...
    $confirmation = json_decode($story_checker->gpt()->message_content)->response;

}

// We've broken out of our loop, which is a good thing...
echo nl2br($story); // Output the confirmed story!