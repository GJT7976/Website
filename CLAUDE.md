# CLAUDE.md — MASTER TASK ROUTER

This file is the automatic project entry point. Keep it short. Its job is to route a clearly requested job to the correct task instruction file.

## Mandatory routing rule

When the user's command begins with, or clearly invokes, one of the Kickstarter Words below:

1. Read the matching file in `claude_tasks/` completely.
2. Read every supporting `docs/` file that task file requires.
3. Perform only that task plus dependencies genuinely required to complete it.
4. Do not silently start another major workflow.
5. If the command is ambiguous, ask which Kickstarter Word the user wants.
6. Existing project specifications, release-channel rules, security rules, and user-specific requirements remain binding unless the selected task file explicitly narrows them.

## Kickstarter Words

- `BUILD APP` → `claude_tasks/CLAUDE1_APP_BUILD.md`
- `UPDATE WEBSITE` → `claude_tasks/CLAUDE2_WEBSITE.md`
- `DEPLOY WEBSITE` → `claude_tasks/CLAUDE3_DEPLOY.md`
- `GOOGLE PLAY` → `claude_tasks/CLAUDE4_GOOGLE_PLAY.md`
- `TEST PROJECT` → `claude_tasks/CLAUDE5_TESTING.md`

The user may add details after the Kickstarter Word, for example:

`BUILD APP — finish the recipe search and create release builds`

`UPDATE WEBSITE — add the completed Carnivore app to localhost`

`DEPLOY WEBSITE — synchronize localhost with niagaraindieapps.com`

`GOOGLE PLAY — prepare the new AAB release`

`TEST PROJECT — audit the app and website before release`

## Important separation

Building an application, adding it to the localhost website, deploying the website, preparing Google Play, and testing are separate workflows. Do not confuse `Website_Delivery` application artifacts with deployment of the Niagara Indie Apps website itself.

## Legacy instructions

The detailed instructions that previously lived in this file are preserved in:

`claude_tasks/CLAUDE0_LEGACY_MASTER.md`

Selected task files may require Claude to read that file so no existing build requirements are lost.
