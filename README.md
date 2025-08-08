![Deprecated](https://img.shields.io/badge/status-deprecated-red)

**⚠️ DEPRECATED REPOSITORY**
This project is no longer actively maintained and may be out of date.

---

# phpGPT

phpGPT is an unofficial, community-supported PHP wrapper for the OpenAI ChatGPT API, allowing you to easily generate responses to user input using the power of ChatGPT.

## Installation

To use phpGPT, simply clone this repository and include the phpGPT.php file in your project:

```php
require_once("phpGPT.php");
```

## Usage

To use phpGPT, you'll need to have an OpenAI API key. Once you have your key, you can create a new instance of the phpGPT class and set it up with the desired model and temperature:

```php
$open_ai = new phpGPT();
```

```php
$open_ai->setup([ "model" => "gpt-3.5-turbo", "temperature" => 1.0 ]);
```

You can then add messages to the conversation using the ```addMessage``` method:

```php
$open_ai->addMessage("system", "You're a funny comedian.");
$open_ai->addMessage("user", "Tell me a joke.");
```

Finally, you can generate a response using the ```gpt``` method and access the content of the response using the ```message_content``` property:

```php
echo $open_ai->gpt()->message_content;
```

If streaming, simply call the ```gpt``` method to echo the results wherever it's called:

```php
$open_ai->gpt();
```

If you'd like ChatGPT to respond with minimal commentary and explanations, simply set the ```concise``` parameter to ```true```:

```php
$open_ai->setup([ "concise" => true ]);
```

## License

phpGPT is released under the MIT License. See LICENSE for more information.
