# CLAUDE4_GOOGLE_PLAY.md — GOOGLE PLAY WORKFLOW

Kickstarter Word: `GOOGLE PLAY`

Use this workflow for Google Play preparation and release work.

## Required reading
Read:
- `claude_tasks/CLAUDE0_LEGACY_MASTER.md`
- `PROJECT_SPEC.md`
- `docs/06_RELEASE_CHANNEL_CONTRACT.md`
- other Android/build/testing documents referenced by the project.

## Scope
Prepare the Google Play release according to the existing project rules.

Preserve the critical channel separation:
- Direct website Android delivery uses the signed APK rules defined by the project.
- Google Play uses the AAB and the project's required PROLOCK/Play billing configuration.
- Do not accidentally remove the Google Play PROLOCK because the direct-sale website build has different rules.

Validate versioning, signing, target SDK/build requirements, bundle generation, and release readiness before reporting completion.
