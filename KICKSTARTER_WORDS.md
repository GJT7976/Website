# KICKSTARTER_WORDS.md

# How to Start Claude Jobs

The root `CLAUDE.md` is the master router. Start a Claude session in the project root and use one of these exact Kickstarter Words at the beginning of your instruction.

## 1. BUILD APP
Use for creating, fixing, completing, rebuilding, or packaging an app.

Example:
`BUILD APP — finish this app according to PROJECT_SPEC.md and create all required completed release builds.`

## 2. UPDATE WEBSITE
Use when the app is already completed and you want it added to or changed on the LOCALHOST Niagara Indie Apps website.

Example:
`UPDATE WEBSITE — add the completed Carnivore app to localhost with its images, description, prices and download/platform choices. Verify it works locally.`

## 3. DEPLOY WEBSITE
Use after localhost is correct and you want the LIVE website updated.

Example:
`DEPLOY WEBSITE — make niagaraindieapps.com match the completed localhost website. Audit GitHub and Hostinger first, deploy safely, and verify the live site before reporting success.`

## 4. GOOGLE PLAY
Use for the Play Store AAB/PROLOCK release.

Example:
`GOOGLE PLAY — prepare the current app for Google Play, preserving the required PROLOCK and generating the correct signed AAB.`

## 5. TEST PROJECT
Use when you want Claude to check work without starting a different major workflow.

Example:
`TEST PROJECT — audit the completed app and report anything missing or broken before release.`

# Recommended sequence for a new app

Usually use:

`BUILD APP`

then:

`TEST PROJECT`

then:

`UPDATE WEBSITE`

then:

`DEPLOY WEBSITE`

Use:

`GOOGLE PLAY`

separately when preparing the Google Play release.

# Important

The Kickstarter Word chooses the job. Text after the word gives Claude the specific details.

Do not use `DEPLOY WEBSITE` merely to build an app. Do not use `BUILD APP` as an instruction to overwrite the live website.

For production website deployment, localhost is the intended website-content source of truth, while production-only data and secrets must be protected.
