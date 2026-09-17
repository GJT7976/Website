# TOKEN_EFFICIENCY.md — Claude Code Workflow Rules

## Goal
Reduce unnecessary context and compute without lowering app quality.

## Context discipline
- Keep `CLAUDE.md` short and operational; keep detailed documentation in separate files.
- Load supporting documents only when the current task requires them.
- Prefer a targeted file search/read over loading entire repositories or long documents.
- Prefer diffs, changed ranges, error excerpts, and focused logs over full outputs.
- Keep unrelated work in separate sessions when practical.
- When context becomes large, use Claude Code's available context-management/compaction features rather than carrying irrelevant history indefinitely.

## Input compression
- Strip irrelevant boilerplate before feeding large logs/reports to the model.
- For failures, surface the failing command, first useful error, related stack portion, and relevant files before attaching full logs.
- For code review, begin with changed files/diff; open dependencies only as required.
- For repository discovery, use scripts/search to produce concise inventories.

## Output discipline
- Default to concise responses: what changed, what was verified, blockers, artifact paths, and next required action.
- Do not repeat the full project specification or standards in responses.
- Do not echo large code files after editing unless explicitly requested.

## Model/subagent discipline
When the installed Claude Code version supports model selection or subagents:
- Use stronger reasoning for architecture, complex debugging, security, migrations, and ambiguous product decisions.
- Use a lower-cost capable model for isolated summarization, formatting, inventory, straightforward tests, and mechanical checks.
- Give subagents only the context/files needed for their isolated task.
- Avoid overlapping subagents performing the same work.
- Prefer one focused subagent over many broad agents.

## Script-first discipline
Use deterministic scripts/tools for repeatable operations such as:
- file inventories and changed-file lists;
- formatting and static analysis;
- test execution;
- build commands;
- asset dimension checks;
- package/artifact collection;
- Git status/diff summaries;
- simple text extraction/filtering;
- release folder preparation.
Use AI reasoning for choices that actually require judgment.

## Durable memory inside the project
- Store current progress in `docs/PROJECT_STATUS.md`.
- Store durable decisions in `docs/DECISIONS.md`.
- Do not keep re-explaining solved project history in chat.
- After a major milestone, update these files with a compact summary.

## Avoid token traps
- Do not preload every standard file.
- Do not repeatedly read generated build folders.
- Do not inspect binary files as text.
- Do not dump entire dependency trees unless diagnosing dependency resolution.
- Do not repeatedly run broad repository searches after the relevant area is known.
- Do not send verbose success logs when exit status plus a short summary is enough.
- Do not use a frontier model for a task that deterministic tooling or a simpler capable model can complete reliably.

## Owner/operator habits
These are user-invoked because Claude cannot force them globally:
- Start a new session or clear unrelated context when changing to a different project/task family.
- Use context/status commands available in the installed Claude Code version to watch context growth.
- Use compaction when the active context becomes large but the task should continue.
- Keep global/user-level Claude instructions minimal; move app-specific rules into the project.
- Remove unused MCP servers/skills/plugins from the global setup when they are not needed.

## Safety
Token savings never override correctness, security, privacy, testing, data integrity, accessibility, owner requirements, or release validation.
Do not install third-party token-compression tools automatically. Evaluate them separately before adding a new dependency or hook.
