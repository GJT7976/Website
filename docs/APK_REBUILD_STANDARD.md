# APK_REBUILD_STANDARD.md — Compiled Android App Reconstruction Standard

## Purpose
Use this standard when an APK or AAB is the primary reference for rebuilding an application. The goal is a clean, maintainable, production-quality Flutter/Dart reconstruction based on evidence that can legitimately be recovered or observed. A compiled Android package is not the original Flutter source project.

## 1. Automatic mode entry
Enter APK Rebuild Mode when any of these apply:
- `PROJECT_SPEC.md` identifies an APK/AAB as `SOURCE_REFERENCE`;
- the owner asks to rebuild, recreate, migrate, or modernize an app from an APK/AAB;
- no usable original source project is available and a compiled Android package is the best reference.

Read this file first, then load only the normal product/UI, engineering, testing/release, or completion standards needed for the current phase.

## 2. Source priority
Use evidence in this order when available:
1. Original source repository/project supplied by the owner.
2. Owner-supplied requirements, data, images, documents, screenshots, and store information.
3. The APK/AAB and information legitimately observable or extractable from it.
4. Explicit owner decisions made during reconstruction.

Do not discard a usable original source project merely because an APK is also present.

## 3. Initial inspection
Before creating substantial code:
- locate the APK/AAB and record its path;
- calculate and record a file hash when practical so the reference artifact is identifiable;
- identify package/application ID, version information, Android SDK metadata, permissions, declared components, supported architectures, and other useful manifest/package metadata when tools permit;
- inventory recoverable assets, icons, images, fonts, localization resources, bundled data, and configuration evidence;
- identify likely frameworks/libraries only when evidence supports the conclusion;
- inspect screenshots or run the app in a legitimate test environment when available to understand navigation, screens, states, and behavior;
- record findings in `docs/apk_rebuild_analysis.md`.

Do not expose or copy secrets, tokens, private keys, credentials, or personal data discovered during inspection.

## 4. Tooling
Use installed/local analysis tools where appropriate. Examples may include Android SDK tools, `apkanalyzer`, `aapt`/`aapt2`, `adb`, `bundletool`, JADX, or APKTool. Do not assume a tool is installed; detect availability first.

Prefer deterministic tools/scripts for inventories and metadata extraction. Keep raw decompiler output out of the normal Claude context unless a targeted portion is required. Summarize relevant findings instead.

Do not bypass access controls, licensing systems, account authentication, payment systems, or security protections.

## 5. Reconstruction, not false source recovery
Never represent decompiled Java/Kotlin, resources, native libraries, or obfuscated output as the original Flutter/Dart source.

If the original source is unavailable:
- create a new clean Flutter project;
- reproduce legitimate observable behavior and owner-supplied requirements;
- rebuild screens and workflows using maintainable Flutter/Dart code;
- recreate architecture according to the current professional engineering standard rather than imitating decompiler structure;
- document important uncertainties and intentional differences.

Do not attempt byte-for-byte reproduction unless the owner explicitly requires it and it is technically and legally appropriate.

## 6. UI and behavior reconstruction
Create an evidence-based screen/workflow inventory before implementation. For each important screen record, where determinable:
- purpose;
- entry/exit navigation;
- visible content;
- primary/secondary actions;
- forms and validation;
- loading, empty, error, offline, and success states;
- dialogs, menus, sheets, search, filters, favorites, settings, and other interactions;
- responsive behavior that can be observed or is required by the new project specification.

Preserve the product's legitimate identity and user workflows, but correct obvious accessibility, responsiveness, reliability, or maintainability defects when doing so does not contradict explicit owner requirements.

## 7. Data and content
Recover or migrate bundled content only when the owner is authorized to use it. Distinguish:
- static bundled/reference content;
- user-created local data;
- remote/cloud data;
- caches or generated files;
- configuration.

Do not assume user data can be extracted from an APK; normally it is not contained in the installation package. Do not fabricate missing records or content.

When data formats are recoverable, document schemas and create clean import/migration logic where appropriate. Preserve data integrity and make backups before destructive migration work.

When the reference package contains a large image library, inventory it but do not automatically rebundle every recovered full-resolution image into the reconstructed install. Apply the large-content-image rules in `02_ENGINEERING_STANDARD.md`, preserve authorized assets, and choose a delivery/cache/offline strategy appropriate to the rebuilt product.

## 8. Network and backend behavior
Do not infer that an endpoint, API key, account, backend, analytics service, purchase service, or cloud database is safe or authorized merely because references appear in a compiled package.

For networked features:
- identify only what evidence supports;
- require legitimate owner credentials/configuration for protected services;
- do not reuse exposed secrets;
- replace insecure client-embedded secrets with an appropriate secure design;
- ensure privacy disclosures match the rebuilt app's actual behavior.

## 9. Purchases and entitlements
Do not bypass, clone, forge, or disable paid entitlements, subscriptions, license checks, authentication, or store purchase verification. Reimplement monetization through the owner's legitimate Google Play/Microsoft Store configuration and current project requirements.

## 10. Package identity and signing
Do not assume the rebuilt app can be published as an update to the existing store app. Updating an existing Android listing normally requires the correct application/package identity and authorized signing/update credentials.

Never invent or extract signing credentials. If the required signing key or store access is unavailable, record it as a release blocker. A reconstructed app may need a new package identity if the owner cannot legitimately update the existing one.

## 11. Platform expansion
An APK describes an Android build; it does not define Windows or Web behavior. If `PROJECT_SPEC.md` requests Windows or Web/PWA, adapt the reconstructed product professionally for those platforms using the normal standards. Do not simply stretch Android screens.

## 12. Reconstruction plan
After analysis and before major implementation, create/update `docs/apk_rebuild_plan.md` with:
- evidence and source references;
- confirmed app identity;
- screen/workflow inventory;
- recoverable assets/content;
- data/storage findings;
- integrations and unknowns;
- features confirmed, uncertain, or intentionally omitted;
- Flutter architecture plan;
- migration requirements;
- target platforms;
- test plan;
- signing/store blockers;
- phased implementation order.

## 13. Implementation phases
Prefer this order unless the app requires another:
1. Analyze and document the reference app.
2. Establish project identity and clean Flutter architecture.
3. Import authorized assets/content.
4. Rebuild navigation/design system.
5. Rebuild core screens/workflows.
6. Rebuild persistence/data migration.
7. Rebuild legitimate integrations/monetization.
8. Add responsive Windows/Web adaptations if requested.
9. Run QA, accessibility, security, privacy, and regression checks.
10. Produce release artifacts only after applicable completion gates pass.

## 14. Validation against the reference
Where practical, compare the reconstruction with the reference app using an explicit checklist rather than memory. Validate:
- core features and workflows;
- visible content and assets;
- navigation;
- persistence;
- settings;
- error handling;
- offline/network behavior;
- accessibility and responsive improvements;
- release configuration.

Record material differences and unresolved unknowns in `docs/PROJECT_STATUS.md`.

## 15. Token-efficient APK work
- Do not feed entire decompiled trees or massive resource dumps into Claude.
- Generate inventories with scripts and inspect targeted files only.
- Search decompiled output for specific symbols/resources when needed.
- Store durable findings in `docs/apk_rebuild_analysis.md` and the plan.
- Reuse summaries instead of repeatedly reanalyzing the whole APK.
- Use diffs and focused tests during implementation.

## 16. Completion gate
Do not call the rebuild complete until:
- the required observable workflows have been accounted for;
- uncertainties and intentional differences are documented;
- applicable professional standards pass;
- Flutter analysis/tests and requested release builds pass where the environment permits;
- signing/store blockers are truthfully reported;
- no claim is made that reconstructed code is the original source unless the actual original source was recovered from an authorized source repository/project.
