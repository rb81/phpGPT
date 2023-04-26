# CHATGPT TOOLS

There are numerous "AI tools" popping up all over the place, many of which seem to be little more than predefined prompts behind the scenes. I'll be building a small library of these prompts based on phpGPT. The prompts themselves would work regardless of what language you're building your tool in.

## 01. REGEX GENERATOR

This tool takes ```$content```, ```$outcome```, and ```$language``` and returns a regular expression in the selected language. The prompt template used should return a single code block, but depending on the language may only return the regular expression.
