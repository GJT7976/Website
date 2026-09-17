# Project Modes, Quality Gates & Completion Standard

> Extracted from the user's MASTER PROFESSIONAL APP DESIGN, UI/UX, ENGINEERING & DEVELOPMENT STANDARD. Requirements are preserved; this file is loaded only when relevant.

56. NEW APPLICATION MODE
If no existing application is being rebuilt:
Do not run APK-reconstruction procedures.
Instead:
    1. establish requirements
    2. determine app type
    3. define user journeys
    4. define information architecture
    5. define data model
    6. establish design system
    7. design responsive layouts
    8. create architecture
    9. implement
    10. test
    11. harden
    12. build
    13. verify
    14. package
    15. document

57. DO NOT OVER-DESIGN OR OVER-ENGINEER
Professional does not mean complicated.
Avoid unnecessary:
    • microservices
    • cloud services
    • accounts
    • databases
    • analytics
    • dashboards
    • animations
    • onboarding
    • permissions
    • dependencies
    • abstractions
    • premium systems
    • navigation layers
The application should contain exactly the complexity required to deliver an excellent product.

58. CODE QUALITY STANDARD
The maintained source code must be:
    • readable
    • modular
    • testable
    • maintainable
    • documented where useful
    • consistently formatted
    • free of obvious duplication
    • free of dead code
    • free of production placeholders
    • free of unnecessary debug output
    • free of exposed secrets
Separate business rules from UI rendering.
Prefer descriptive names over unnecessary comments explaining confusing code.

59. DEPENDENCY STANDARD
Before adding a dependency determine:
    • whether Flutter/Dart already provides the capability
    • whether the package is actively maintained
    • whether it supports all target platforms
    • whether its license is acceptable
    • whether it adds unnecessary size or complexity
    • whether it introduces privacy/security concerns
Do not accumulate dependencies casually.

60. DATA MIGRATION
If persisted data structures may evolve, provide an intentional migration strategy.
Never silently destroy existing user data simply because the application schema changed.
Test meaningful migrations.

61. BACKUP AND RESTORE
Where user-created data has meaningful value, strongly consider professional backup/restore.
Verify:
    • export creation
    • file integrity
    • restore
    • invalid backup handling
    • version compatibility where required
    • duplicate/conflict behavior
Never report backup as working until restore has also been tested.

62. EMPTY STATE QUALITY
A professional empty state should explain:
    • why the area is empty
    • what the user can do
    • what value will appear there
Provide an appropriate action when useful.
Do not fill empty screens with meaningless decoration.

63. LOADING QUALITY
Use:
    • progress indicators
    • skeletons
    • placeholders
    • delayed loading indicators
according to operation duration and context.
Avoid flashing loading indicators for operations that complete essentially instantly.

64. TRUST AND TRANSPARENCY
Clearly communicate consequential actions such as:
    • purchases
    • deleting data
    • overwriting backups
    • sending data off-device
    • enabling cloud synchronization
    • permissions
    • subscriptions
    • one-time purchases
Specificity builds trust.
Never manipulate users with misleading urgency, disguised controls or obstructive cancellation.

65. USER INVESTMENT
Where appropriate allow users to experience, configure, create or explore useful value before requiring an account or purchase.
Preserve legitimate work.
Do not unnecessarily discard configuration or progress.

66. GOAL PROGRESS
For multi-step processes show meaningful progress.
If legitimate work has already been completed, recognize that progress rather than pretending the user is at zero.
Do not artificially manufacture progress merely for psychological effect.

67. PROFESSIONAL COMPLETION GATE
The application is not complete because:
    • it compiles
    • the home screen opens
    • the main feature works once
    • a debug APK exists
The application is complete only when all applicable project requirements have been implemented and verified as far as the environment allows.
Before completion verify:
FUNCTIONALITY
    • primary workflows work
    • data persists correctly
    • actions do what labels promise
UX
    • information hierarchy is clear
    • workflows are efficient
    • navigation is predictable
    • primary actions are obvious
DESIGN
    • spacing is consistent
    • typography is consistent
    • color is intentional
    • icons are coherent
    • imagery is appropriate
RESPONSIVENESS
    • targeted screen sizes have been tested
    • layouts adapt rather than merely stretch
ACCESSIBILITY
    • accessibility requirements have been tested
DATA
    • validation works
    • backup/restore works where required
    • migrations work where required
SECURITY
    • secrets are not exposed
    • permissions are appropriate
    • sensitive storage is handled correctly
PRIVACY
    • behavior matches disclosures
LOCALIZATION
    • specified languages work completely where required
PERFORMANCE
    • startup and major workflows perform acceptably
    • lists/images do not create obvious performance problems
FAILURE HANDLING
    • empty/loading/error/offline states are implemented where applicable
TESTING
    • applicable tests pass
RELEASE
    • actual target-platform release builds are verified
DOCUMENTATION
    • final documentation matches reality
No incomplete or unverified element may be falsely reported as complete.

68. FINAL SCREEN AUDIT
Before finalizing every major screen ask:
    1. What is this screen for?
    2. Is that purpose obvious?
    3. What is the most important information?
    4. What is the primary action?
    5. Can anything be removed?
    6. Can any interaction be eliminated?
    7. Is anything duplicated?
    8. Is the hierarchy clear?
    9. Is typography readable?
    10. Is spacing consistent?
    11. Is color meaningful?
    12. Are icons understandable?
    13. Does it work with longer translated text?
    14. Does it work with accessibility text scaling?
    15. Does it work on all applicable screen sizes?
    16. Does it work with touch?
    17. Does it work with keyboard/mouse where appropriate?
    18. Is loading handled?
    19. Is empty handled?
    20. Are errors handled?
    21. Does every action give feedback?
    22. Are destructive actions safe?
    23. Does this screen belong in this application at all?
If a screen fails these questions, improve it before considering it complete.

69. FINAL ENGINEERING RULE
Do not blindly obey a generic design pattern when it is inappropriate for the application.
Use professional judgment.
The project requirements define WHAT the application must accomplish.
This master professional standard defines HOW well it must be accomplished.
The application's users, purpose, data, environment, supported platforms and risk determine WHICH professional implementation pattern should be used.
Analyze first.
Choose deliberately.
Design coherently.
Build cleanly.
Test realistically.
Verify release builds.
Never confuse "working" with "finished."
The finished application should be immediately understandable, visually coherent, reliable, accessible, responsive, maintainable and appropriate to its actual purpose.


## 70. WEBSITE-DIRECT-SALE COMPLETION
When `WEBSITE_DIRECT_SALES = true`, completion additionally requires the release structure and separation rules in `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md`. Do not report the project finished until the signed APK, Windows installer, Web/PWA demo build, website upload manifest, and separate private source archive have been verified as far as credentials/environment allow.


## 71. DUAL-CHANNEL COMPLETION GATE
When both direct website sales and Google Play are enabled, completion requires two deliberately separate release variants from the same maintained source:

1. `completed/Website_Delivery/` — free-download website edition. Android delivery is a **production-signed release APK**, built with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`; Pro unlock follows successful Niagara Indie Apps website entitlement validation.
2. `completed/PROLOCKS/` — Google Play edition. The Play upload artifact is an **AAB**, built with `PRO_LOCKS_ENABLED=true`, with the project's approved Pro/Premium entitlement system active.

A website release fails completion if Google Play Billing controls the website edition, if a valid Niagara Indie Apps website entitlement does not unlock Pro, or if an invalid/over-limit entitlement incorrectly unlocks Pro.

A Google Play release fails completion if Pro locks were unintentionally disabled.

Never copy the Play AAB into Website_Delivery, and never use a Pro-locked website artifact as the paid website customer download.
