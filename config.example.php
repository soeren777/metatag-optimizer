<?php

return [

    // ── AI-Provider ───────────────────────────────────────────────────────────
    // 'none'        → rule-based generation (free, no API key required)
    // 'anthropic'   → Claude (claude-sonnet-4-5 or similar)
    // 'openai'      → GPT-4o
    // 'google'      → Gemini
    // 'perplexity'  → Perplexity API
    // 'grok'        → xAI Grok
    // 'ownai'       → any OpenAI-compatible endpoint
    'ai_provider' => 'none',
    'ai_api_key'  => '',           // your API key here
    'ai_model'    => '',           // leave empty to use provider default
    'ai_api_url'  => '',           // only for 'ownai': https://your-endpoint.com/v1/chat/completions

    // ── Crawler ───────────────────────────────────────────────────────────────
    'crawler_timeout'    => 10,       // seconds
    'crawler_user_agent' => 'MetaTagOptimizer/1.0 (+https://your-domain.com)',
    'max_content_length' => 50000,    // characters passed to AI

    // ── Rate Limiting (simple, file-based) ────────────────────────────────────
    'rate_limit_requests' => 10,      // max requests
    'rate_limit_window'   => 60,      // per X seconds per IP
    'rate_limit_dir'      => __DIR__ . '/tmp',

];
