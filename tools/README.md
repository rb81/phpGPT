# CHATGPT TOOLS

There are numerous "AI tools" popping up all over the place, many of which seem to be little more than predefined prompts behind the scenes. I'll be building a small library of these prompts based on phpGPT. The prompts themselves would work regardless of what language you're building your tool in.

## 01. REGEX GENERATOR - ```gpt-regex.php```

This tool takes ```$content```, ```$outcome```, and ```$language``` and returns a regular expression in the selected language. The prompt template used should return a single code block, but depending on the language may only return the regular expression.

## 02. DATA TRANSFORMATION - ```gpt-data-transformation.php```

This tool takes ```$content```, ```$outcome```, and ```$language``` and transforms your data with nothing more than an example of the expected outcome. The prompt template used should return only the transformed data with any necessary code blocks (if applicable).

## 03. STORY TELLER WITH CONTROL MECHANISM - ```gpt-story-teller.php```

This tool takes ```$topic```, ```$character``` and ```$age``` and produces a children's story. Once written, the story is then checked for inappropriate content. If the story is found to contain unsuitable content, the process loops until a suitable story is provided.