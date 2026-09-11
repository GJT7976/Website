# Demo Deployment

How the live web demo system works, and how to add another app's web build
to it.

## The rule: `public/demo-builds/{slug}/`, never `public/demos/{slug}/`

Static demo builds (a plain HTML/JS/CSS app, a Flutter Web build, etc.) are
placed at:

```
public/demo-builds/{app-slug}/
```

**Do not** use `public/demos/{app-slug}/`. The `/demos` route (the live
demo catalogue page) and a literal `public/demos/` directory collide at the
web-server level: PHP's built-in dev server and, in production, Apache's
`.htaccess` rewrite rules (`RewriteCond %{REQUEST_FILENAME} !-d`) and
Nginx's typical `try_files $uri $uri/ /index.php` block all check "does a
file or directory already exist on disk at this path?" *before* handing the
request to Laravel — a real `public/demos` directory answers "yes" and gets
served (or 404s) directly, and Laravel's `/demos` route never runs. This
was caught and fixed during Phase 1 development; `demo-builds` avoids the
collision entirely because nothing is routed at exactly that path.

## Adding a new app's demo

1. Build (or otherwise produce) the static web app.
2. Copy its files into `public/demo-builds/{slug}/`, e.g.
   `public/demo-builds/niagara-pos/index.html` plus its other assets.
3. In Admin → Apps → (the app) → Demo section:
   - Enable Demo
   - Demo URL: `/demo-builds/{slug}/index.html`
   - Demo type: `static_web` (or `flutter_web`, etc. — informational)
   - Fill in instructions / reset behavior / warning as appropriate
4. Visit `/demo/{slug}` on the public site to confirm it loads inside the
   demo wrapper (Demo Mode badge, Full Screen Demo, Back to App, Get App).

## Flutter Web builds specifically

If a demo is a Flutter Web build (`flutter build web`), Flutter's default
`base href="/"` assumes the app is served from the domain root. Since it's
actually served from `/demo-builds/{slug}/`, rebuild with the base href set
to that subdirectory:

```
flutter build web --base-href /demo-builds/{slug}/
```

Otherwise the build's JS/CSS/asset requests will point at the wrong paths
and the demo will fail to load correctly once deployed under a
subdirectory. This does not apply to Bread Maker (a plain static HTML/JS
app using only relative paths — verified safe to serve from a
subdirectory as-is).

## Demo safety

- Demo builds must only ever use fake/sample data, isolated from any real
  business data — none of the demos wired in so far have a backend to
  connect to real data at all (Bread Maker is a pure client-side
  calculator), which is the simplest way to guarantee this.
- Demos must never be able to charge a real card, send real communications,
  or modify real records. This becomes directly relevant once Phase 2
  (Stripe, orders) exists — a demo build must not be pointed at production
  API endpoints.
- The demo wrapper page always shows a "Demo Mode" indicator and a way back
  to the real app page.

## Bread Maker specifically

Source: `C:\Users\User\Documents\APKs\BreadMaker\completed\web-pwa` (a
baker's percentage calculator PWA). Its `index.html`, `manifest.json`,
`service-worker.js`, and `icon.png` were copied verbatim into
`public/demo-builds/bread-maker/` — all of its internal references are
relative paths, and its service worker's cache scope is limited to its own
directory, so it is safe to serve from a subdirectory without any base-href
changes.
