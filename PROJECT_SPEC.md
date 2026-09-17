# PROJECT\_SPEC.md — Fill Once Per App

This file is the single source of truth for project-specific values. Replace placeholders with real values when known. Do not invent unknown values.

## Identity

APP\_NAME = "<REQUIRED>"
PACKAGE\_ID = "<REQUIRED\_FOR\_ANDROID/STORE\_RELEASE>"
CATEGORY = "<REQUIRED>"
CREATOR = "Niagara Indie Apps" "G Thompson"
PROJECT\_ROOT = "<PATH\_TO\_THIS\_PROJECT>"
SOURCE\_REFERENCE = "<OPTIONAL\_EXISTING\_APP\_APK\_OR\_PATH>"

## Platforms

TARGET\_PLATFORMS = \["Android", "Windows", "Web/PWA"]
ANDROID\_OUTPUTS = \["WEBSITE\_SIGNED\_UNLOCKED\_APK", "GOOGLE\_PLAY\_PROLOCKED\_AAB"]
WINDOWS\_OUTPUTS = \["EXE"] \[installer.exe]
WEB\_PWA = true

## Product

PRIMARY\_APP\_TYPE = "<recipe/reference/tracker/farm/etc. or AUTO-DETERMINE>"
TARGET\_USERS = "<describe>"
PRIMARY\_GOALS = "<describe>"
CORE\_FEATURES = "<describe or reference supplied requirements>"
FEATURES\_TO\_AVOID = "<optional>"

## Data \& Connectivity

OFFLINE\_FIRST = "<true/false/auto>"
CLOUD\_REQUIRED = "<true/false/auto>"
USER\_DATA = "<none/local records/etc.>"
BACKUP\_RESTORE\_REQUIRED = "<true/false/auto>"

## Content Image Architecture

CONTENT\_IMAGE\_COUNT = "<number/estimate/auto>"
CONTENT\_IMAGE\_STORAGE = "<remote/local/bundled/auto>"
BUNDLE\_FULL\_SIZE\_CONTENT\_IMAGES = false
IMAGE\_LAZY\_LOADING = true
IMAGE\_DISK\_CACHE = true
IMAGE\_THUMBNAILS = true
OFFLINE\_IMAGE\_DOWNLOAD\_OPTION = "<true/false/auto>"
IMAGE\_CACHE\_LIMIT = "<size/count/auto>"
IMAGE\_CDN\_OR\_BASE\_URL = "<optional; do not invent>"

For large content libraries, especially about 500+ images, default to remote/on-demand full-size content images rather than packaging the entire library into the APK/AAB. Bundle only essential UI artwork, placeholders, icons, and deliberately selected offline assets unless complete offline image availability is an explicit owner requirement.

## Localization

LANGUAGES = \["English""Spanish(Castellano)""Italian""French""Greek"]
MEASUREMENT\_SYSTEMS = \["Metric", "US Customary"]

## Monetization

MONETIZATION = "<free/paid/one-time unlock/subscription/none/auto>"
PRICE\_OR\_PRODUCT\_IDS = "<if applicable>"

## Release Channels \& Entitlements

WEBSITE\_PRO\_LOCKS\_ENABLED = true
GOOGLE\_PLAY\_PRO\_LOCKS\_ENABLED = true
WEBSITE\_ENTITLEMENT\_CHANNEL = "WEBSITE"
GOOGLE\_PLAY\_ENTITLEMENT\_CHANNEL = "GOOGLE\_PLAY"
WEBSITE\_EDITION = "FREE\_DOWNLOAD\_WITH\_ONE\_TIME\_PRO\_UNLOCK"
GOOGLE\_PLAY\_EDITION = "PRO\_LOCKS\_WITH\_GOOGLE\_PLAY\_BILLING\_WHEN\_CONFIGURED"
WEBSITE\_LICENSE\_TYPE = "PERMANENT\_ONE\_TIME\_UNLOCK"
WEBSITE\_LICENSE\_MAX\_DEVICES = 2
WEBSITE\_LICENSE\_PRICE\_ANDROID = "2.99"
WEBSITE\_LICENSE\_PRICE\_WINDOWS = "2.99"
WEBSITE\_LICENSE\_PRICE\_ANDROID\_WINDOWS\_BUNDLE = "4.99"
WEBSITE\_OUTPUT\_ROOT = "completed/Website\_Delivery"
GOOGLE\_PLAY\_OUTPUT\_ROOT = "completed/PROLOCKS/Google\_Play"
PRO\_LOCK\_FLAG = "PRO\_LOCKS\_ENABLED"
ENTITLEMENT\_CHANNEL\_FLAG = "ENTITLEMENT\_CHANNEL"
WEBSITE\_ANDROID\_ARTIFACT = "PRODUCTION\_SIGNED\_RELEASE\_APK; PRO\_LOCKS\_ENABLED=true; ENTITLEMENT\_CHANNEL=WEBSITE"
WEBSITE\_WINDOWS\_ARTIFACTS = "MSIX\_OR\_APPROVED\_INSTALLER\_AND\_RELEASE\_EXE; PRO\_LOCKS\_ENABLED=true; ENTITLEMENT\_CHANNEL=WEBSITE"
WEBSITE\_WEB\_PWA\_ARTIFACT = "HOSTED\_WEB\_PWA; PRO\_LOCKS\_ENABLED=true; ENTITLEMENT\_CHANNEL=WEBSITE; ACCOUNT\_OR\_SERVER\_ENTITLEMENT\_PREFERRED"
GOOGLE\_PLAY\_ANDROID\_ARTIFACT = "SEPARATE\_RELEASE\_AAB; PRO\_LOCKS\_ENABLED=true; ENTITLEMENT\_CHANNEL=GOOGLE\_PLAY"
WEBSITE\_APK\_SIGNING = "PRODUCTION\_SIGNING\_REQUIRED; DEBUG\_CERTIFICATE\_FORBIDDEN"

Website installers/downloads are free to obtain. Pro/Premium functionality remains locked until the customer's Niagara Indie Apps website entitlement is validated. The default website license is a permanent one-time unlock for up to two activated devices. Android and Windows may accept a website license key; Web/PWA should prefer account/server-side entitlement. Google Play is a separate AAB release and must use the approved Google Play entitlement/billing path rather than the website checkout path. Both editions come from the same maintained source tree and are selected centrally by build-time release-channel configuration.

## Store / Legal

GOOGLE\_PLAY = "<true/false>"
MICROSOFT\_STORE = "<true/false>"
PRIVACY\_POLICY\_URL = "<if applicable>"
SUPPORT\_URL = "<if applicable>"

## Design Direction

BRAND\_OR\_STYLE = "<optional>"
ACCENT\_OR\_BRAND\_COLORS = "<optional>"
ASSET\_SOURCE = "<optional path/repo>"

## Owner Constraints

* Do not add unsupported platforms.
* Do not invent credentials, keys, IDs, legal claims, package IDs, or store data.
* Preserve any additional project-specific constraints below.

ADDITIONAL\_CONSTRAINTS = "<add here>"



## Niagara Inde Apps Website Delivery

WEBSITE\_DIRECT\_SALES = true
WINDOWS\_CUSTOMER\_DELIVERY = "MSIX\_OR\_APPROVED\_INSTALLER"
WEB\_DELIVERY = "HOSTED\_WEB\_PWA\_DEMO\_URL"
SOURCE\_DELIVERY = "PRIVATE\_SEPARATE\_SOURCE\_ARCHIVE\_ONLY"
CUSTOMER\_DOWNLOADS\_MUST\_EXCLUDE = \["AAB", "Flutter source", "Dart source", "keystores", "credentials", "debug builds"]

