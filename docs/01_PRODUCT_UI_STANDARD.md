# Product, UX, UI & Data Ownership Standard

> Extracted from the user's MASTER PROFESSIONAL APP DESIGN, UI/UX, ENGINEERING & DEVELOPMENT STANDARD. Requirements are preserved; this file is loaded only when relevant.

MASTER PROFESSIONAL APP DESIGN, UI/UX, ENGINEERING & DEVELOPMENT STANDARD
GOVERNING INSTRUCTION
This document is the authoritative professional-build standard for the application.
Do not merely make the application compile or reproduce a collection of screens.
Build the application as a polished, coherent, production-quality software product using professional standards for:
    • product design
    • UI/UX
    • information architecture
    • interaction design
    • responsive/adaptive layouts
    • accessibility
    • Flutter/Dart architecture
    • clean code
    • data architecture
    • security
    • privacy
    • performance
    • reliability
    • localization
    • offline behavior
    • testing
    • release engineering
    • maintainability
    • platform integration
    • deployment readiness
The final result must look, feel, and behave as though it were designed and engineered by an experienced professional product-development team.

1. PROJECT IDENTITY — SINGLE SOURCE OF TRUTH
Before implementation, establish one authoritative project configuration.
Use:
APP_NAME = ""
PACKAGE_ID = ""
CATEGORY = ""
CREATOR = "G Thompson"
PROJECT_ROOT = ""
SOURCE_REFERENCE = "<OPTIONAL EXISTING APP/APK/PATH>"
TARGET_PLATFORMS = ""
Never maintain competing app names, package identifiers, creator names, version values, paths, or category definitions in separate sections.
If an older section conflicts with these values, these centralized project values override it.
Never invent a package ID, app name, repository, path, credential, API key, legal identity, or other project-specific value when it has not been supplied or legitimately determined.
Use placeholders until the correct value is known.

2. NON-DUPLICATION AND CONFLICT RESOLUTION
Treat this entire document as one unified specification.
When multiple sections express the same requirement:
    1. Implement the requirement once.
    2. Use the most detailed professional version.
    3. Do not create duplicate screens.
    4. Do not create duplicate services.
    5. Do not create duplicate databases.
    6. Do not create duplicate settings.
    7. Do not create duplicate legal pages.
    8. Do not create duplicate navigation destinations.
    9. Do not create duplicate build systems.
    10. Do not create multiple competing implementations of the same feature.
When two requirements genuinely conflict:
    1. Preserve explicit project-owner requirements.
    2. Preserve security, legal and data-integrity requirements.
    3. Prefer the solution providing better usability and accessibility.
    4. Prefer maintainable architecture over fragile shortcuts.
    5. Prefer platform-correct behavior.
    6. Document any material conflict that cannot be reconciled automatically.

3. ANALYZE THE APPLICATION TYPE BEFORE DESIGNING IT
Before designing screens or writing substantial application code, determine what kind of product is being built.
Do not force the same screen structure, navigation, storage architecture, dashboard, monetization system, onboarding, or feature set onto every application.
Classify the application into one or more applicable domains.
Examples include:
    • Recipe / cooking
    • Reference / encyclopedia
    • Educational
    • Productivity
    • Tracker / record keeping
    • Farm / livestock management
    • Breeding / animal management
    • Inventory
    • Financial
    • Health / wellness
    • Scheduling / calendar
    • Media / gallery
    • Utility / calculator
    • Business / CRM
    • E-commerce
    • Community / social
    • Content / publishing
    • Offline reference
    • Data-entry application
    • Dashboard / analytics
    • Location-based
    • Subscription or paid-feature product
    • Game
    • Other specialized domain
An application may legitimately belong to several categories.
Create:
docs/app_type_analysis.md
Record:
    • primary application type
    • secondary application types
    • target users
    • primary user goals
    • most frequent tasks
    • most important information
    • expected environment of use
    • likely session length
    • input requirements
    • data sensitivity
    • offline requirements
    • search requirements
    • accessibility considerations
    • appropriate navigation model
    • appropriate data model
    • appropriate storage method
    • appropriate monetization model, if any
    • platform-specific requirements
    • features that should NOT be added because they do not benefit this product

4. PROFESSIONAL DECISION ENGINE
For every substantial design or engineering decision ask:
Does this choice:
    • reduce user effort?
    • reduce uncertainty?
    • make the next action clearer?
    • reduce unnecessary taps?
    • reduce unnecessary screens?
    • improve accessibility?
    • improve responsiveness?
    • improve trust?
    • improve performance?
    • improve reliability?
    • preserve user data?
    • improve maintainability?
    • behave correctly on the target platform?
    • support future expansion without unnecessary complexity?
When several technically valid solutions exist, choose the one that performs best against these criteria.
Do not choose a design merely because it is visually fashionable.
Do not choose an architecture merely because it is technically elaborate.
Do not add features merely because they are possible.
Use the simplest professional solution that fully supports the actual application.

5. DESIGN FOR THE ACTUAL USER AND ENVIRONMENT
Determine how the application will realistically be used.
Examples:
A cooking application may be used:
    • with dirty or wet hands
    • at kitchen viewing distance
    • while the user is moving between steps
    • with timers running
    • with limited attention
Therefore favor:
    • larger touch targets
    • readable typography
    • strong step progression
    • persistent cooking state
    • visible timers
    • quick ingredient access
    • minimal interaction during Cook Mode
A farm-management application may be used:
    • outdoors
    • in bright light
    • with gloves
    • with intermittent connectivity
    • during fast data entry
Therefore consider:
    • strong contrast
    • large controls
    • offline-first data
    • rapid entry
    • minimal typing
    • synchronization indicators
A reference application may require:
    • excellent search
    • categories
    • filters
    • favorites
    • recent items
    • readable long-form information
    • cross-linking
A record-keeping application may require:
    • reliable local/database persistence
    • history
    • edit tracking
    • sorting/filtering
    • backup/restore
    • export
    • validation
    • data integrity
A business dashboard may require:
    • information density
    • tables
    • filtering
    • keyboard use
    • larger-screen optimization
    • export/reporting
The interface and engineering architecture must respond to the real use case.

6. DESIGN BEFORE CODING
Before implementing a major screen or workflow, determine:
    1. What is the user trying to accomplish?
    2. What information is needed immediately?
    3. What can be delayed until later?
    4. What is the primary action?
    5. What are the secondary actions?
    6. What is the shortest sensible workflow?
    7. What can be automated or defaulted?
    8. What can be removed?
    9. What mistakes are likely?
    10. What happens when no data exists?
    11. What happens while data loads?
    12. What happens when an operation succeeds?
    13. What happens when it fails?
    14. What happens offline?
    15. What happens on a narrow phone?
    16. What happens on a tablet?
    17. What happens on desktop?
    18. What happens with keyboard navigation?
    19. What happens with larger accessibility text?
    20. What happens when the interface is translated into a longer language?
Do not treat these states as afterthoughts.

7. PROFESSIONAL INFORMATION ARCHITECTURE
Organize information according to importance and user intent.
Place the information users most frequently need where they naturally expect it.
Avoid presenting everything simultaneously.
Use:
    • hierarchy
    • grouping
    • progressive disclosure
    • categories
    • search
    • filters
    • sorting
    • favorites
    • recent items
    • collections
    • context-sensitive actions
only when appropriate to the application.
The application should answer likely user questions before unnecessary navigation is required.
Reduce interaction cost.
If useful information can safely be shown one level earlier, consider showing it there.

8. COGNITIVE LOAD
Minimize unnecessary decisions.
Do not make users repeatedly choose values that can safely be inferred or remembered.
Use:
    • sensible defaults
    • recent selections
    • recommended values
    • saved preferences
    • presets
    • autocomplete
    • remembered non-sensitive choices
where appropriate.
Never remove meaningful user control simply to reduce choices.

9. NAVIGATION MODEL MUST MATCH APP COMPLEXITY
Do not automatically use bottom navigation, tabs, sidebars, drawers, or dashboards.
Choose navigation according to the application's structure.
Small application
For a very small application with only a few functions:
Prefer simple direct navigation.
Do not manufacture five navigation destinations merely to fill a navigation bar.
Consumer mobile application
For approximately 3–5 important peer destinations:
Bottom navigation may be appropriate on compact phones.
Tablet / desktop
Where appropriate use:
    • navigation rail
    • sidebar
    • master/detail layout
    • split view
    • multi-column layout
Do not simply stretch the phone interface.
Deep content application
Use:
    • search
    • category hierarchy
    • breadcrumbs where helpful
    • clear back navigation
    • related-content navigation
Workflow application
Keep users moving through the task rather than repeatedly returning them to a dashboard.

10. UI/UX QUALITY STANDARD
Every screen must have:
    • an obvious purpose
    • clear visual hierarchy
    • predictable navigation
    • an identifiable primary action when applicable
    • consistent spacing
    • consistent typography
    • consistent component behavior
    • understandable labels
    • appropriate feedback
    • complete states
The user should not need instructions to understand ordinary application behavior.
Avoid:
    • random layouts
    • excessive cards
    • decorative clutter
    • excessive gradients
    • unnecessary glass effects
    • excessive shadows
    • meaningless animations
    • giant headings that consume usable space
    • decorative icons that reduce clarity
    • arbitrary colors
    • dense dashboards where a simpler screen is better
    • hidden critical actions
    • ambiguous icon-only actions

11. DESIGN SYSTEM
Create a centralized design system.
Define:
    • color tokens
    • typography tokens
    • spacing scale
    • radii
    • elevations
    • icon rules
    • button styles
    • input styles
    • card styles
    • divider styles
    • content widths
    • responsive breakpoints
    • animation durations
    • accessibility states
Use reusable components.
Never scatter magic visual numbers through screen files.

12. TYPOGRAPHY
Typography must prioritize readability.
Use a restrained hierarchy.
Define appropriate styles for:
    • display
    • page title
    • section heading
    • subheading
    • body
    • supporting body
    • label
    • metadata
    • button
    • caption
Use comfortable line height.
Avoid excessively low-contrast body text.
Headings attract attention.
Body copy supports understanding.
Do not make every piece of information compete visually.

13. COLOR
Use a deliberate color system.
Define:
    • primary
    • secondary
    • surface
    • background
    • text
    • muted text
    • success
    • warning
    • error
    • informational
    • outline/divider
    • disabled
    • focus
Color must communicate meaning consistently.
Never rely on color alone to convey an important state.
Maintain accessible contrast.

14. ICONOGRAPHY
Use one coherent icon family wherever possible.
Icons must have understandable meaning.
Pair unfamiliar icons with labels.
Do not use icons merely as decoration.
Platform-specific icons may be used when platform convention makes them more understandable.

15. IMAGES AND MEDIA
Use imagery when it helps the user understand, identify, compare, choose, or enjoy content.
Do not use random decorative stock imagery.
Maintain:
    • appropriate aspect ratios
    • correct cropping
    • predictable containers
    • responsive sizing
    • placeholders
    • loading behavior
    • error handling
    • semantic descriptions where required
Never stretch an image to fill an incompatible aspect ratio.
Where supplied authorized project imagery exists, treat it as authoritative unless instructed otherwise.

16. RESPONSIVE AND ADAPTIVE DESIGN
Responsive design is mandatory for every supported form factor.
Do not merely scale a phone UI.
Design intentionally for applicable:
    • compact Android phones
    • standard Android phones
    • large phones
    • 7-inch tablets
    • 10-inch tablets
    • large tablets
    • Chromebooks
    • Windows laptops
    • Windows desktops
    • resizable Windows windows
    • Web/PWA
    • other explicitly supported devices
Layouts must adapt according to available space.
Use appropriate:
    • columns
    • grids
    • sidebars
    • navigation rails
    • constrained readable content widths
    • split views
    • master/detail layouts
    • rearranged actions
Never allow:
    • clipped text
    • overlapping content
    • horizontal overflow
    • inaccessible controls
    • distorted imagery
    • giant unused empty areas
    • phone-sized narrow content unnecessarily floating on a large desktop screen

17. TOUCH, MOUSE AND KEYBOARD
Mobile interfaces must be finger-friendly.
Important actions should be reachable and comfortably sized.
Consider the thumb zone for frequently used phone actions.
Desktop and Web interfaces must support, where appropriate:
    • mouse
    • trackpad
    • hover
    • keyboard
    • tab focus
    • Enter/Space activation
    • Escape
    • arrow-key navigation
    • shortcuts for frequent productivity actions
Do not design desktop as a touchscreen-only phone emulator.

18. ACCESSIBILITY IS A CORE REQUIREMENT
Accessibility is not optional polish.
Support, where applicable:
    • semantic controls
    • accessible labels
    • screen readers
    • scalable text
    • adequate contrast
    • keyboard navigation
    • visible focus
    • meaningful focus order
    • sufficiently large touch targets
    • reduced motion
    • non-color status indicators
    • meaningful image descriptions
    • accessible validation messages
Test with enlarged text.
Do not assume an interface is accessible merely because it looks clean.

19. LOCALIZATION
All user-facing application text must use the project's localization architecture when multilingual support is required.
Do not hard-code translated UI strings throughout widgets.
Layouts must tolerate longer translated text.
Support locale-aware:
    • dates
    • times
    • numbers
    • decimal separators
    • currencies
    • measurements
where applicable.
Localization requirements should match the actual application specification rather than being added arbitrarily to every unrelated project.

20. FORMS AND DATA ENTRY
Use forms only where necessary.
Reduce typing when safe alternatives exist.
Group related fields.
Use logical ordering.
Use:
    • appropriate input types
    • defaults
    • selectors
    • autocomplete
    • remembered preferences
    • inline validation
    • useful error messages
Preserve valid entered information when another field fails validation.
For long workflows consider steps rather than an overwhelming single form.

21. SEARCH
If content volume makes search useful, create professional search.
Consider:
    • instant suggestions
    • recent searches
    • autocomplete
    • typo tolerance
    • categories
    • filters
    • sort
    • reset
    • useful no-results states
Do not add search merely because every application "should have search."
If the application contains only ten easily visible records, search may create more complexity than benefit.

22. ONBOARDING
Only implement onboarding when the application genuinely benefits from it.
Onboarding must provide value.
Keep it short.
Do not explain obvious UI.
Where possible:
    • let users experience value before registration
    • allow skipping nonessential onboarding
    • preserve legitimate progress
    • recognize work already completed
    • avoid making an experienced user start from zero unnecessarily
Do not hold useful results hostage simply to force account creation.

23. NEW, RETURNING AND ADVANCED USERS
Where useful, adapt the experience without making navigation unpredictable.
A new user may need:
    • orientation
    • starter content
    • helpful defaults
A returning user may benefit from:
    • recent work
    • saved state
    • shortcuts
    • continue actions
An experienced user may benefit from:
    • advanced filters
    • statistics
    • batch actions
    • keyboard shortcuts
    • denser information
Do not overload beginners with expert controls.
Use progressive disclosure where appropriate.

24. FEEDBACK
Every meaningful action must produce appropriate feedback.
Examples:
    • saving
    • deleting
    • restoring
    • importing
    • exporting
    • uploading
    • synchronizing
    • purchasing
    • backing up
    • restoring
    • starting a timer
    • completing a workflow
The user must not wonder whether the action worked.
Use feedback appropriate to the importance of the action.
Do not interrupt the user with a modal dialog for routine successful actions.

25. COMPLETE APPLICATION STATES
For every meaningful data-driven feature, determine applicable states:
    • initial
    • loading
    • content
    • empty
    • no results
    • success
    • warning
    • offline
    • syncing
    • stale
    • permission denied
    • unavailable
    • error
    • disabled
Do not build only the ideal state.

26. ERROR HANDLING
Errors must be understandable.
Where possible explain:
    • what happened
    • what may have caused it
    • what the user can do
    • whether data was preserved
    • whether retry is possible
Never silently discard work.
Avoid exposing raw stack traces or technical exception text to ordinary users.
Technical information may be logged safely for diagnosis.

27. DESTRUCTIVE ACTIONS
Clearly distinguish destructive actions.
Confirmation is appropriate when an action causes meaningful irreversible loss.
Do not create confirmation fatigue for harmless operations.
Use Undo where practical.
Never use deceptive button placement or dark patterns.

28. DATA OWNERSHIP
Users own data they create unless an explicit legitimate service model requires otherwise.
Where applicable provide:
    • backup
    • restore
    • export
    • deletion
Do not hold user-created raw data hostage behind a premium payment.
Premium functionality may provide advanced tools or analyses, but user-owned base records must remain reasonably accessible.
