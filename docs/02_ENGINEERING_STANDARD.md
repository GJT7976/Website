# Flutter/Dart Engineering, Data, Security & Platform Standard

> Extracted from the user's MASTER PROFESSIONAL APP DESIGN, UI/UX, ENGINEERING & DEVELOPMENT STANDARD. Requirements are preserved; this file is loaded only when relevant.

29. APPLICATION DATA ARCHITECTURE
Choose storage according to actual requirements.
Do not install a database merely because databases are common.
Static/reference application
Bundled structured assets may be sufficient.
Local personal-data application
Use appropriate local persistence.
Large structured offline dataset
Use an indexed local database appropriate to query needs.
Synchronized application
Use local-first persistence with clearly designed synchronization behavior where appropriate.
Cloud collaborative application
Use authenticated server-backed storage with appropriate authorization.
Sensitive application
Apply stronger security, minimization, encryption and access-control requirements.
Document why the chosen storage approach fits the application.

30. ARCHITECTURE
Use maintainable professional Flutter/Dart architecture appropriate to project complexity.
Do not overengineer a tiny application.
Do not underengineer a large application.
Maintain sensible separation between:
    • presentation
    • state
    • business/domain logic
    • data access
    • persistence
    • external services
Use features/modules where useful.
Avoid:
    • giant screen files
    • giant service classes
    • duplicated logic
    • duplicated styles
    • hard-coded user-facing strings
    • global mutable state
    • fragile absolute positioning
    • unnecessary dependencies
    • abandoned packages
    • circular dependencies
    • architecture created merely for ceremony

31. STATE MANAGEMENT
Choose state management according to application needs and existing project conventions.
Do not introduce several state-management systems unnecessarily.
Separate:
    • transient widget state
    • application state
    • persisted state
    • remote/synchronized state
State must remain predictable and testable.

32. PERFORMANCE AS USER EXPERIENCE
Performance is part of product quality.
Optimize:
    • startup
    • navigation
    • scrolling
    • large lists
    • image decoding
    • database queries
    • search
    • memory use
    • unnecessary rebuilds
    • expensive synchronous work
Use:
    • lazy loading
    • pagination
    • caching
    • thumbnails
    • background processing
    • optimized image assets
when appropriate.
Do not optimize blindly before measuring obvious bottlenecks, but do not knowingly implement inefficient architecture.

32A. LARGE CONTENT IMAGE LIBRARIES
When an application contains a large image library, especially about 500 or more content images, treat image delivery as an explicit architecture concern.
Unless complete offline image availability is an explicit owner requirement, do not bundle the entire full-resolution content image library into the APK/AAB or other installation package.
Prefer:
    • remote or externally hosted full-size content images retrieved on demand
    • appropriately sized thumbnails for lists, grids and search results
    • lazy loading so only visible or near-visible images are requested/decoded
    • persistent disk caching for previously viewed images
    • bounded cache size with predictable eviction/cleanup behavior
    • placeholders while images load
    • retry and error states for failed downloads
    • memory-efficient decoding sized to the actual display target
    • reuse of cached files rather than repeated downloads
The install package should normally contain only:
    • essential application artwork
    • icons, logos and splash assets
    • placeholders/fallback images
    • deliberately selected offline content assets
    • small thumbnails only when justified by startup/offline requirements
Do not package thousands of high-resolution images merely because they are available in an assets folder.
Do not decode or hold an entire large image catalog in memory at once.
For recipe, reference, encyclopedia, media, catalog or gallery applications with large libraries, keep structured metadata/search data local where practical so browsing and searching remain responsive even if full-size images are remote.
If connectivity is unavailable:
    • show a cached image when available
    • otherwise show the local placeholder/fallback without breaking the underlying content screen
If an owner requires offline images, prefer an explicit user-controlled download/offline-pack mechanism rather than forcing every user to install the full library.
Document the chosen image hosting, thumbnail, caching, offline and cleanup strategy.
Never invent a CDN, storage bucket, URL, API key or hosting credential; require legitimate project configuration.

33. OFFLINE AND CONNECTIVITY BEHAVIOR
First determine whether the application actually requires internet access.
If it does not, do not introduce network dependency unnecessarily.
If internet access is required:
    • clearly communicate connection state when relevant
    • preserve user work during temporary failures
    • avoid destructive synchronization
    • support retry
    • distinguish local and synchronized state where necessary
Offline-first behavior is particularly valuable for applications expected to be used in unreliable connectivity environments.

34. SECURITY
Use secure-development practices appropriate to the application's actual risk.
Never commit:
    • passwords
    • signing passwords
    • API secrets
    • authentication tokens
    • private keys
    • sensitive credentials
to public source control.
Validate untrusted input.
Request only necessary permissions.
Use secure storage for sensitive secrets.
Follow least privilege.
Protect external APIs and authentication correctly.
Do not claim client-side feature hiding is equivalent to server-side authorization.

35. PRIVACY
Collect only information the application genuinely requires.
Do not add analytics, advertising, crash reporting, tracking, accounts or cloud synchronization simply because they are available.
If analytics or diagnostics are appropriate:
    • minimize collected data
    • do not log sensitive records
    • update privacy disclosures
    • comply with applicable store declarations
    • preserve user expectations
Privacy claims in the app, store listing and Privacy Policy must match actual application behavior.

36. MONETIZATION MUST MATCH THE PRODUCT
Do not automatically add subscriptions, purchases, advertising, login gates or premium features.
First determine whether monetization is part of the project requirements.
If monetization is required, determine the model appropriate to the application.
Possible models include:
    • completely free
    • one-time lifetime unlock
    • feature upgrade
    • content pack
    • subscription
    • paid application
    • other legitimate model
Do not use a subscription where a one-time purchase better fits a primarily static application unless explicitly required.
Premium functionality must provide genuine additional value.
Do not deliberately cripple the core application.
Do not lock user-owned raw data behind payment.
Do not use dark patterns.
If purchases exist, implement entitlement restoration appropriately.

37. APP-TYPE-SPECIFIC PROFESSIONAL MODULES
After determining the application type, activate only applicable modules.
Recipe / Cooking
Consider:
    • recipe images
    • categories
    • cuisines
    • search
    • favorites
    • serving scaling
    • ingredient grouping
    • shopping list
    • Cook Mode
    • step timers
    • nutrition
    • substitutions
    • storage
    • reheating
    • chef tips
    • variations
    • common mistakes
    • related recipes
    • related sauces
    • kitchen-distance readability
Do not fabricate nutritional values or community ratings.
Breeding / Animal Management
Consider:
    • animal profiles
    • pedigree
    • breeding records
    • genetic test records
    • health records
    • mating history
    • pregnancy/litter tracking
    • offspring records
    • reminders
    • document/photo storage
    • ethical breeding guidance
    • breed-specific requirements
    • export/backup
Health/genetic information must distinguish factual records from recommendations.
Hobby Farm / Livestock Management
Consider:
    • animal groups
    • inventory
    • feed
    • expenses
    • production
    • breeding
    • harvest/replenishment scheduling
    • pasture/pen allocation
    • reminders
    • location-specific configuration
    • offline field use
    • backup/export
Reference / Encyclopedia
Prioritize:
    • taxonomy
    • search
    • categories
    • cross-links
    • favorites
    • high-quality readable content
    • images
    • offline availability where practical
Tracker
Prioritize:
    • fastest possible entry
    • history
    • trends
    • summaries
    • filtering
    • editing
    • backup
    • export
    • data integrity
Productivity
Prioritize:
    • task completion speed
    • keyboard support on desktop
    • shortcuts
    • bulk actions where useful
    • undo
    • reliable persistence
Financial
Prioritize:
    • precision
    • auditability
    • privacy
    • secure storage
    • validation
    • clear calculations
    • appropriate financial disclaimers
    • careful formatting
Never silently round or alter important financial data.
Health / Wellness
Prioritize:
    • accessibility
    • privacy
    • clear units
    • accurate records
    • careful claims
    • appropriate disclaimers
    • data export/delete
Do not present wellness information as medical diagnosis unless the product is legitimately designed, validated and authorized to do so.
E-Commerce
Prioritize:
    • product discovery
    • search
    • comparison
    • cart clarity
    • transparent pricing
    • checkout efficiency
    • payment security
    • order status
    • returns/help
Never hide costs until the last moment.
Media / Gallery
Prioritize:
    • fast thumbnails
    • caching
    • correct image/video aspect ratios
    • browsing
    • filtering
    • memory efficiency
    • fullscreen presentation
Dashboard / Analytics
Prioritize:
    • information hierarchy
    • clear KPI context
    • filtering
    • time ranges
    • tables
    • charts
    • responsive density
    • desktop use
    • export
Do not use charts when ordinary text or a table communicates the information more clearly.

38. RECIPE APP PROFESSIONAL OVERRIDE
When and only when CATEGORY or the discovered product domain identifies a recipe/cooking application, apply the detailed recipe-specific requirements elsewhere in this master specification.
This includes the existing requirements for:
    • recipe detail information architecture
    • responsive recipe imagery
    • ingredient quantities
    • serving-size recalculation
    • Cook Mode
    • timers
    • shopping list
    • storage/reheating
    • chef tips
    • variations
    • common mistakes
    • ratings/notes rules
    • related sauces/recipes
Do not apply these requirements to unrelated applications.

39. FEATURE NECESSITY TEST
Before adding a proposed feature ask:
    1. Does it solve a real user problem?
    2. Will users reasonably understand it?
    3. Does it fit the application's primary purpose?
    4. Is its value greater than the complexity it adds?
    5. Can it be implemented reliably?
    6. Does it create ongoing maintenance?
    7. Does it increase privacy or permission requirements?
    8. Does it require internet access?
    9. Does it complicate localization?
    10. Does it create store-policy obligations?
If a feature provides little benefit and substantial complexity, omit it unless explicitly required.

40. PROFESSIONAL SETTINGS DESIGN
Create only settings that the application genuinely needs.
Potential groups include:
    • General
    • Appearance
    • Language & Region
    • Units
    • Notifications
    • Data & Backup
    • Privacy
    • Legal
    • About
    • Advanced
Do not create empty settings categories.
Keep infrequently used technical settings away from ordinary users.

41. PLATFORM APPROPRIATENESS
Maintain one coherent brand while respecting platform behavior.
Android should feel appropriate on Android.
Windows should behave like a proper resizable desktop application.
Web/PWA should support browser expectations.
Do not force one platform's interaction pattern onto all other platforms where it reduces usability.

42. DESIGN SYSTEM RESPONSIVENESS
Components must adapt, not merely screens.
Test:
    • buttons
    • fields
    • cards
    • dialogs
    • menus
    • navigation
    • tables
    • images
    • lists
    • grids
    • typography
    • empty states
    • modals
at multiple widths.
Avoid hard-coded sizes that work only on one device.

43. COMPONENT STATES
Reusable interactive components must define applicable:
    • default
    • hover
    • focus
    • pressed
    • selected
    • disabled
    • loading
    • error
    • success
states.

44. MOTION
Use animation only when it:
    • communicates hierarchy
    • explains transition
    • provides feedback
    • improves orientation
    • reinforces state change
Animation must never slow frequent workflows merely for visual effect.
Respect reduced-motion preferences where applicable.

45. TESTING STRATEGY
Testing must match application risk.
Use appropriate:
    • unit tests
    • widget tests
    • integration tests
    • data migration tests
    • validation tests
    • responsive tests
    • localization tests
    • accessibility checks
    • backup/restore tests
    • purchase tests
    • offline tests
    • error-state tests
    • release-build tests
Do not write meaningless tests solely to increase a test count.
Prioritize important user journeys and business/domain logic.
