# Claude Professional App Starter — How to Use

This folder is designed to be copied into the root of every new Flutter/Dart app project.

## Fast use
1. Extract the ZIP.
2. Copy the contents of `Claude_App_Starter` into the root of the Flutter project — the same folder that contains `pubspec.yaml`.
3. Open `PROJECT_SPEC.md` and fill the values you know. Leave unknown values as placeholders rather than guessing.
4. Start Claude Code from the project root.
5. Give Claude the app requirements. Do not paste all files into the chat. `CLAUDE.md` routes Claude to the relevant supporting standard automatically.

## What loads when
`CLAUDE.md` is the short controller. It tells Claude to read only the document needed for the current job:
- product/UI work -> `docs/01_PRODUCT_UI_STANDARD.md`
- engineering/data/security -> `docs/02_ENGINEERING_STANDARD.md`
- testing/release/store work -> `docs/03_TEST_RELEASE_STANDARD.md`
- project mode/final completion -> `docs/04_PROJECT_MODES_COMPLETION.md`
- APK/AAB rebuild/reconstruction -> `docs/APK_REBUILD_STANDARD.md`
- token/workflow optimization -> `docs/TOKEN_EFFICIENCY.md`
- Niagara Indie Apps website publishing/deployment -> `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md`

## Reuse for another app
Keep one untouched copy of this ZIP. For each new app, extract/copy it again and edit only `PROJECT_SPEC.md` for that app.

## Optional PowerShell installer
From the extracted starter folder:

```powershell
.\install-to-project.ps1 -ProjectPath "C:\Path\To\Your\FlutterApp"
```

The installer copies the controller, standards, status templates, and scripts without overwriting existing files unless `-Force` is supplied.

## Important
This starter pack does not contain credentials or signing secrets. Keep keystores, passwords, API keys, and tokens out of public source control.

## Rebuilding from an APK/AAB
Put the APK/AAB somewhere inside or alongside the project where Claude can access it, and set `SOURCE_REFERENCE` in `PROJECT_SPEC.md` to that path. When Claude sees that the primary reference is an APK/AAB, `CLAUDE.md` automatically routes it to `docs/APK_REBUILD_STANDARD.md`. You do not manually paste the APK standard into chat.

The rebuild workflow treats the compiled package as reference evidence, not as original Flutter source. If you also have the original Flutter project or GitHub repository, supply that too because original source takes priority.


## Niagara Inde Apps website releases
This starter is configured for direct sales from the Niagara Inde Apps website. Read `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md`. Customer delivery is: signed Android APK, Windows installer, and a hosted Web/PWA demo build. AAB files are internal Google Play artifacts only when explicitly enabled. Flutter source is kept in a separate private source archive and never mixed with website/customer downloads.


## Website_Delivery vs PROLOCKS

This starter produces two deliberately different commercial release channels:

- `completed/Website_Delivery/` = direct website-sale edition, built with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`; the installer is free and Pro unlock is purchased through Niagara Indie Apps.
- `completed/PROLOCKS/Google_Play/` = Google Play AAB only, built separately with `PRO_LOCKS_ENABLED=true`, with approved Pro/Premium purchase locks active.
- Website_Delivery contains Pro-locked website-channel builds only; they must use the Niagara Indie Apps website entitlement system, never Google Play Billing.

Never interchange these artifacts.


## Niagara Indie Apps production deployment
App release packaging and live website deployment are separate jobs. `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md` and `docs/06_RELEASE_CHANNEL_CONTRACT.md` govern the app artifacts and entitlement channels. `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md` governs synchronizing the completed localhost website through the established GitHub/Hostinger deployment path to `https://niagaraindieapps.com` and verifying the live site.

The actual Niagara Indie Apps website project should also have a root-level `DEPLOYMENT.md` containing its audited repository, branch, Hostinger, build, backup, and rollback details, with no secrets.


## Claude Task Router

This starter now uses the root `CLAUDE.md` as a task router. Read `KICKSTARTER_WORDS.md` for the exact commands to start app building, localhost website updates, production deployment, Google Play work, and testing.


## Claude Code CLI

For installation, folder placement, slash commands, and the normal workflow, read `CLAUDE_CODE_CLI_SETUP.md`.
