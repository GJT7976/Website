# CLAUDE CODE CLI — INSTALLATION AND USE

This package is organized for use with Claude Code CLI.

## A. Project-level installation — recommended

Put the entire starter structure in the ROOT of the project Claude will work on.

Example:

```text
F:\Apps\MyApp\
├── CLAUDE.md
├── PROJECT_SPEC.md
├── KICKSTARTER_WORDS.md
├── CLAUDE_CODE_CLI_SETUP.md
├── claude_tasks\
├── docs\
└── .claude\
    └── commands\
```

Open Windows Terminal/PowerShell and change into that project:

```powershell
cd "F:\Apps\MyApp"
claude
```

Claude Code should then see the root `CLAUDE.md` as the project's instructions.

The `.claude/commands/` folder contains reusable project commands:

```text
/build-app
/update-website
/deploy-website
/google-play
/test-project
```

You can add details after a command when supported by your Claude Code version/session, or issue the command and then give the specific task.

Examples:

```text
/build-app
```

Then:
```text
Finish the current app according to PROJECT_SPEC.md.
```

Or use the plain Kickstarter wording if a slash command is unavailable:

```text
BUILD APP — finish the current app according to PROJECT_SPEC.md
```

## B. For the Niagara Indie Apps WEBSITE project

Copy the same routing files into the root of the actual localhost website project.

The important structure is:

```text
<Niagara Indie Apps website root>\
├── CLAUDE.md
├── DEPLOYMENT.md
├── claude_tasks\
├── docs\
└── .claude\commands\
```

Start Claude from THAT directory:

```powershell
cd "<your Niagara Indie Apps website root>"
claude
```

Then use:

```text
/update-website
```

to work on localhost, and:

```text
/deploy-website
```

only when you want the completed localhost site synchronized to production.

## C. Global/personal Claude instructions

Claude Code can also have user-level configuration/instructions. Do NOT put all of these Niagara Indie Apps project rules globally unless you want them affecting unrelated projects.

For this system, project-level `CLAUDE.md`, `claude_tasks/`, `docs/`, and `.claude/` files are preferred.

## D. Verify Claude is using the right project

Before major work, start Claude from the project root and ask:

```text
Tell me which CLAUDE.md applies to this project and summarize the available project commands. Do not change any files.
```

Claude should identify the project's root instructions and the build/update/deploy/test workflows.

## E. Normal workflow for a new app

1. Open terminal in the app/project root.
2. Run `claude`.
3. Use `/build-app`.
4. Use `/test-project`.
5. When the app is ready to be listed on your localhost website, work from the Niagara Indie Apps website project and use `/update-website`.
6. When localhost is correct, use `/deploy-website`.
7. Use `/google-play` separately for the Google Play AAB/PROLOCK release.

## F. Important note about Claude Code versions

Claude Code's command/skill mechanisms can evolve. If your installed version does not recognize the `.claude/commands` slash commands, the root `CLAUDE.md` router still works. Use the Kickstarter Words in `KICKSTARTER_WORDS.md`:

`BUILD APP`
`UPDATE WEBSITE`
`DEPLOY WEBSITE`
`GOOGLE PLAY`
`TEST PROJECT`

This gives you a fallback without changing the workflow.
