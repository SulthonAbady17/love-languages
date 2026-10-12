---
name: laravel-privacy
always_on: true
---
# Laravel Privacy & Context Optimization Rules

The agent must not index, read, or track high-volume or sensitive Laravel directories:
- node_modules/
- vendor/
- storage/framework/
- storage/logs/
- bootstrap/cache/

Strictly deny access to `.env` variations or secret generation keys.
