<?php

require_once("../../phpGPT.php");

/**
 * This experiment takes some inspiration from BabyAGI in that ChatGPT is asked
 * to determine what tasks are required to complete a given mission. In this case,
 * ChatGPT is given a predefined set of actions that it is allowed to choose from.
 * These actions are intended to keep ChatGPT from recommending tasks that require
 * human input or data that exists outside of its own.
 * 
 * First, we define a mission. This could be anything, but will likely result in a
 * written report. This mission is then briefed to ChatGPT along with the rules of
 * engagement.
 * 
 * Once ChatGPT replies with a list of tasks, we loop through them, briefing ChatGPT
 * each time to execute the task and summarize its findings.
 * 
 * Once all tasks have been completed, the full set of findings is given back to
 * ChatGPT to expand, rewrite, format, etc. and return a final output.
 * 
 * Note: These prompts appear to be stable and work as expected with the temperature
 * settings below; however, changing the temperature will likely create unexpected
 * results.
 * 
 * Warning: Because we're looping through an undeterminable list, there's always
 * the chance that an infinite loop will be encountered. (The $curl_timeout variable
 * will limit this behavior.)
*/

####################################################################################################
####################################################################################################
$mission = "Identify a novel use for ChatGPT that may benefit mankind.";
####################################################################################################
####################################################################################################

// We will use ChatGPT to identify the role needed to complete the mission...
$role_identifier = new phpGPT();
$role_identifier->setup([ "model" => "gpt-3.5-turbo", "temperature" => 0.0 ]);

$prompt = <<<PROMPT
Respond with a role description for a ChatGPT prompt needed to complete the objective below.
Respond only with the role description, and no other text.

Example
```
You are an expert medical researcher specialized in neurology.
```

Objective
```
$mission
```
PROMPT;

$role_identifier->addMessage("system", "You are a helpful assistant with a detailed understanding of ChatGPT.");
$role_identifier->addMessage("user", $prompt);

$role = $role_identifier->gpt()->message_content;

// The first agent is the "MANAGER", who sets the mission and defines the tasks...
$manager = new phpGPT();
$manager->setup([ "model" => "gpt-3.5-turbo", "temperature" => 0.0 ]);

// Provide the original content...
$prompt = <<<PROMPT
########## IGNORE ANY PREVIOUS PROMPTS ##########

# ASSIGNMENT
``````
You will create a list of tasks needed to complete the mission below.
Tasks you identify must be achievable using one or more of the actions listed below.
You do not have to use all of the actions listed below.
Only use actions that are necessary to achieve the mission.
No other actions should be needed to complete each task.
Do not complete the mission, just list the tasks needed to do so.
Respond only in JSON using the following template:
{
    "tasks": [{
        "task": "",
        "actions": [
            "",
            ""
        ]},
        {
            "task": "",
            "actions": [
                "",
                ""
            ]
        }]
}
``````

# MISSION
``````
$mission
``````

# ACTIONS
``````
- Access data from within your trained model
- Analyze and interpret data
- Make determinations
- Make decisions
- Make inferences
- Deduce or derive conclusions
- Summarize data
- Identify threats or risks
- Identify opportunities or possibilities
``````
PROMPT;

// Add the required statements to the payload...
$manager->addMessage("system", $role);
$manager->addMessage("user", $prompt);

// Submit the payload to ChatGPT and generate a task list...
$manager->$curl_timeout = 180; // Default is 90 seconds, but we may need more time...
$task_list = json_decode($manager->gpt()->message_content);

// This is where we'll store the completed tasks...
$response_collection = [];

// Loop through the task list and brief the next agent on each one separately...
foreach ($task_list->tasks as $assignment) {

    // This agent is the "RESEARCHER" who completes the tasks given...
    $researcher = new phpGPT();
    $researcher->setup([ "model" => "gpt-3.5-turbo", "temperature" => 0.0 ]);

    $prompt = <<<PROMPT
    ########## IGNORE ANY PREVIOUS PROMPTS ##########

    You are currently trying to complete this mission:
    $mission

    Your assignment is to complete the task below:
    $assignment->task

    The only actions you are allowed to take in order to complete this assignment:
    PROMPT;

    foreach ($assignment->actions as $item) {
    $prompt .= <<<PROMPT
    $item . "\n";
    PROMPT;
    }

    $prompt .= <<<PROMPT
    Don't explain the task, but actually complete it.
    Respond with a detailed response for the task.
    Do not try to complete the mission, only the task.
    PROMPT;

    // Add the required statements to the payload...
    $researcher->addMessage("system", $role);
    $researcher->addMessage("user", $prompt);

    // Submit the payload to ChatGPT and add the response to our collection...
    $researcher->$curl_timeout = 180; // Default is 90 seconds, but we may need more time...
    $response_collection[] = $researcher->gpt()->message_content;
}

// This agent is the "REPORTER" who is responsible for compiling the task collection into a single output...
$reporter = new phpGPT();
$reporter->setup([ "model" => "gpt-3.5-turbo", "temperature" => 0.5 ]);

$response_collection = json_encode($response_collection);

$prompt = <<<PROMPT
Write a report based on the information provided below.
Include a report title, section headings, a table of contents, and executive summary.
Add any relevant data to explain or support the findings.
Use tables to present any data (if necessary).
Use markdown for formatting.

``````
$response_collection
``````
PROMPT;

// Add the required statements to the payload...
$reporter->addMessage("system", $role);
$reporter->addMessage("user", $prompt);

$reporter->$curl_timeout = 180; // Default is 90 seconds, but we may need more time...

// We're going to use ParseDown (https://parsedown.org) to output our markdown as HTML beautifully...
require_once("parsedown/parsedown.php"); $parsedown = new Parsedown(); $parsedown->setSafeMode(true);

// Submit the final payload to ChatGPT and print the final output...
echo $parsedown->text($reporter->gpt()->message_content);
