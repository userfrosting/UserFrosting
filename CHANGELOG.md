# Change Log

## v0.3.1

- Improved initialization routine as middleware
- Implemented "remember me" for persistent sessions - see https://github.com/gbirke/rememberme
- Converted page templates to inheritance architecture, using Twig `extends`
- Start using the `.twig` extension for template files
- All content is now part of a theme, and site can be configured so that one theme is the default theme for unauthenticated users
- User session stored via user_id, rather than the entire User object
- Data model is now built on Eloquent, instead of in-house
- Cleaned up some of the per-page Javascript, refactoring repetitive code