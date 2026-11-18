/* Browser regression checks. The fixture password is supplied only over stdin. */
const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;
const fs = require('fs');
const path = require('path');
const assert = require('node:assert/strict');

const fixtureMode = process.argv.includes('--fixtures');
const settings = fixtureMode ? JSON.parse(fs.readFileSync(0, 'utf8')) : { base: 'http://127.0.0.1:8080/' };
const base = settings.base;
assert(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Browser tests are local-only.');
const output = path.resolve('artifacts/qa');
fs.mkdirSync(output, { recursive: true });
const results = [];
const failures = [];

async function inspect(page, label, width, screenshot = true) {
  await page.waitForLoadState('networkidle');
  await page.evaluate(() => document.fonts.ready);
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1);
  const audit = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
  const violations = audit.violations.map(item => ({ id: item.id, impact: item.impact, nodes: item.nodes.map(node => ({ html: node.html, summary: node.failureSummary })) }));
  const overflowElements = overflow ? await page.evaluate(() => [...document.querySelectorAll('body *')].filter(element => {const r=element.getBoundingClientRect();return r.width>0 && r.right>innerWidth+1 && getComputedStyle(element).position==='absolute';}).map(element=>({tag:element.tagName,classes:element.className,right:element.getBoundingClientRect().right})).slice(0,10)) : [];
  results.push({ label, width, overflow, overflowElements, violations });
  if (overflow || violations.length) failures.push({ label, width, overflow, overflowElements, violations });
  if (screenshot) await page.screenshot({ path: path.join(output, `${label}-${width}.png`), fullPage: true });
}

async function login(page, username) {
  await page.goto(base + 'login.php', { waitUntil: 'networkidle' });
  await page.getByLabel('Username', { exact: true }).fill(username);
  await page.getByLabel('Password', { exact: true }).fill(settings.password);
  await page.getByRole('button', { name: 'Show', exact: true }).click();
  assert.equal(await page.locator('#password').getAttribute('type'), 'text');
  await page.getByRole('button', { name: 'Hide', exact: true }).click();
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await page.waitForURL(url => !url.pathname.endsWith('/login.php'));
}

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--enable-unsafe-swiftshader'] });
  try {
    if (!fixtureMode) {
      for (const width of [1440, 768, 390, 320]) {
        const context = await browser.newContext({ viewport: { width, height: width === 1440 ? 960 : 844 }, reducedMotion: 'reduce' });
        const page = await context.newPage();
        page.on('pageerror', error => failures.push({ label: 'javascript', width, error: error.message }));
        page.on('console', message => { if (message.type() === 'error') failures.push({ label: 'console', width, error: message.text() }); });
        for (const route of ['home.php', 'package.php', 'about.php', 'login.php', 'login.php?mode=signup', 'booking.php', 'privacy.php']) {
          const response = await page.goto(base + route, { waitUntil: 'networkidle' });
          assert.equal(response.status(), 200);
          if (route === 'home.php') {
            await page.waitForFunction(() => window.__ready === true);
            assert.equal(await page.locator('[data-washer]').getAttribute('data-render-mode'), null);
            await page.getByRole('button', { name: 'Rotate washing machine', exact: true }).click();
            assert.equal(await page.getByRole('button', { name: 'Play illustration animation' }).count(), 1);
          }
          await inspect(page, route.replace(/[^a-z0-9]/gi, '-'), width, width === 1440 || width === 390);
          if (route === 'home.php' && width === 1440) await page.screenshot({ path: path.join(output, 'home-viewport.png') });
        }
        if (width <= 768) {
          await page.getByRole('button', { name: 'Open navigation' }).click();
          assert.equal(await page.locator('[data-menu-toggle]').getAttribute('aria-expanded'), 'true');
          await page.keyboard.press('Escape');
          assert.equal(await page.locator('[data-menu-toggle]').getAttribute('aria-expanded'), 'false');
        }
        await context.close();
      }
      const fallbackContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
      await fallbackContext.addInitScript(() => {
        const original = HTMLCanvasElement.prototype.getContext;
        HTMLCanvasElement.prototype.getContext = function (type, ...args) { return type.startsWith('webgl') ? null : original.call(this, type, ...args); };
      });
      const fallback = await fallbackContext.newPage();
      await fallback.goto(base, { waitUntil: 'networkidle' });
      await fallback.waitForFunction(() => window.__ready === true);
      assert.equal(await fallback.locator('.washer-fallback').isVisible(), true);
      assert.equal(await fallback.locator('.washer-fallback').evaluate(image => image.complete && image.naturalWidth > 0), true);
      await fallbackContext.close();
    } else {
      const context = await browser.newContext({ viewport: { width: 1440, height: 960 }, reducedMotion: 'reduce' });
      const page = await context.newPage();
      page.on('pageerror', error => failures.push({ label: 'javascript-private', error: error.message }));
      await login(page, 'alice_test');
      for (const width of [1440, 768, 390, 320]) {
        await page.setViewportSize({ width, height: width === 1440 ? 960 : 844 });
        for (const route of ['my_orders.php', 'profile.php', 'booking.php']) {
          await page.goto(base + route, { waitUntil: 'networkidle' });
          await inspect(page, 'customer-' + route.replace('.php', ''), width, width === 1440 || width === 390);
        }
      }
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(base + 'booking.php', { waitUntil: 'networkidle' });
      assert.equal(await page.locator('#booking-submit').isDisabled(), true);
      const first = page.locator('[data-option]').first();
      const unitCents = Number(await first.getAttribute('data-cents'));
      const label = await first.getAttribute('data-name');
      await page.getByRole('button', { name: 'Add one ' + label, exact: true }).click();
      await page.getByRole('button', { name: 'Add one ' + label, exact: true }).click();
      assert.equal(await first.inputValue(), '2');
      assert.equal(await page.locator('#booking-total').textContent(), new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(unitCents * 2 / 100));
      const minimum = await page.locator('#pickup_date').getAttribute('min');
      await page.getByLabel('Pickup date', { exact: true }).fill(minimum);
      await page.getByLabel('Pickup address', { exact: true }).fill('Browser fixture pickup address');
      await page.getByRole('button', { name: 'Confirm pickup', exact: true }).click();
      await page.waitForURL('**/my_orders.php');
      assert.match(await page.locator('main').textContent(), /Browser fixture pickup address/);
      await page.screenshot({ path: path.join(output, 'booking-success-mobile.png'), fullPage: true });
      await context.close();

      const adminContext = await browser.newContext({ viewport: { width: 1440, height: 960 }, reducedMotion: 'reduce' });
      const admin = await adminContext.newPage();
      admin.on('pageerror', error => failures.push({ label: 'javascript-admin', error: error.message }));
      await login(admin, 'admin_test');
      for (const width of [1440, 768, 390, 320]) {
        await admin.setViewportSize({ width, height: width === 1440 ? 960 : 844 });
        for (const tab of ['overview', 'orders', 'users', 'services', 'account']) {
          await admin.goto(base + 'admin/adminDash.php?tab=' + tab, { waitUntil: 'networkidle' });
          if (tab === 'services') await admin.locator('.admin-services>details').first().locator('summary').click();
          await inspect(admin, 'admin-' + tab, width, width === 1440 || width === 390);
        }
      }
      await adminContext.close();
    }
  } finally {
    await browser.close();
    fs.writeFileSync(path.join(output, fixtureMode ? 'private-audit.json' : 'public-audit.json'), JSON.stringify({ results, failures }, null, 2));
  }
  if (failures.length) { console.error(JSON.stringify(failures, null, 2)); process.exitCode = 1; }
  else console.log(`${results.length} browser page/viewport checks passed${fixtureMode ? ', including a complete mobile booking' : ', with reduced motion, menu controls, 3D controls, and a WebGL fallback'}.`);
})().catch(error => { console.error(String(error).replaceAll(settings.password || '__no_password__', '[redacted]')); process.exitCode = 1; });
