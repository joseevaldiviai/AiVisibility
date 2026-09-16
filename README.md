# AI Visibility for Magento 2

A Magento 2 module that makes your store more visible to AI search/answer engines (ChatGPT, Claude, Perplexity, Gemini).

## Features

- **robots.txt rules** — allow or block AI crawlers (GPTBot, OAI-SearchBot, Claude, Perplexity, Google-Extended, etc.) per store.
- **`/ai/llms_txt` endpoint** — generates an `llms.txt` file listing categories, products and CMS pages so LLMs can understand your catalog.
- **JSON-LD structured data** — adds `Organization`, `BreadcrumbList` and `Product` (with offers and aggregate rating) schema to your pages.

## Installation

```bash
composer require jose/magento2-ai-visibility
bin/magento setup:upgrade
bin/magento cache:flush
```

Or copy the `AiVisibility` folder to `app/code/Jose/AiVisibility`.

> The robots.txt rules are appended by the theme/integration that calls `RobotsTxtGenerator::generateAiRules()` (e.g. via a plugin on the robots.txt output).

## Configuration

Stores → Configuration → **AI Visibility**:

- **Robots.txt — AI Bots**: enable rules and allow/block each bot.
- **llms.txt Generator**: enable endpoint, max products/categories/pages, cron schedule.
- **Structured Data (JSON-LD)**: enable per schema type, rating markup, default item condition.

## Endpoints

| URL | Description |
|-----|-------------|
| `/ai/llms_txt` | Generated `llms.txt` (text/plain, 1h cache) |

## Requirements

- Magento 2.4.x
- PHP 8.1+
