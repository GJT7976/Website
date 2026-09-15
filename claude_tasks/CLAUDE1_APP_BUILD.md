# CLAUDE1_APP_BUILD.md — APP BUILD WORKFLOW

Kickstarter Word: `BUILD APP`

Use this workflow when the user asks Claude to create, modify, rebuild, finish, package, or release an application.

## Required reading
Before making changes, read:
- `claude_tasks/CLAUDE0_LEGACY_MASTER.md`
- `PROJECT_SPEC.md` when present
- all existing `docs/` standards referenced by those files, especially product/UI, engineering, testing, completion, website direct-sales release, and release-channel contract documents.

## Scope
This workflow owns application source code and application release artifacts.

It does NOT automatically publish or synchronize `niagaraindieapps.com`.

When the app is complete, create the release outputs required by the existing project rules, including the correct separation between direct website delivery and Google Play/PROLOCK artifacts.

If the user also wants the app listed on the localhost Niagara Indie Apps website, that is the `UPDATE WEBSITE` workflow. If the user also wants the live .com updated, that is the `DEPLOY WEBSITE` workflow.
