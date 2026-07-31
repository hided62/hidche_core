import fs from 'node:fs';
import {createHash} from 'node:crypto';
import {chromium} from 'playwright';

const baseURL = process.env.REF_BROWSER_URL;
const screenshotPath = process.env.REF_SCREENSHOT_PATH;
const resultPath = process.env.REF_RESULT_PATH;
const username = process.env.REF_USER_ID ?? 's100user01';
const password = fs.readFileSync('/run/secrets/test_user_password', 'utf8').trim();

if (!baseURL || !screenshotPath || !resultPath) {
    throw new Error('REF_BROWSER_URL, REF_SCREENSHOT_PATH, and REF_RESULT_PATH are required');
}

const browser = await chromium.launch({headless: true});
const context = await browser.newContext({
    viewport: {width: 1280, height: 960},
    deviceScaleFactor: 1,
});
const page = await context.newPage();

await page.goto(baseURL, {waitUntil: 'domcontentloaded', timeout: 60_000});
const salt = await page.locator('#global_salt').inputValue();
const passwordHash = createHash('sha512')
    .update(salt + password + salt)
    .digest('hex');
const loginResponse = await page.request.post(
    new URL('api.php?path=Login/LoginByID', baseURL).href,
    {data: {username, password: passwordHash}},
);
const loginResult = await loginResponse.json();
if (!loginResponse.ok() || loginResult.result !== true) {
    throw new Error(`login failed: ${String(loginResult.reason ?? loginResponse.status())}`);
}

const myPageURL = new URL('hwe/b_myPage.php', baseURL).href;
const readCooldownText = async () => {
    await page.goto(myPageURL, {waitUntil: 'networkidle', timeout: 60_000});
    const bodyText = await page.locator('body').innerText();
    const lines = bodyText.split('\n').map(line => line.trim());
    const reselect = lines.find(line => line.startsWith('다른 장수 선택 ('));
    const deletion = lines.find(line => line.startsWith('가오픈 기간 내 장수 삭제 ('));
    if (!reselect || !deletion) {
        throw new Error(`cooldown text missing: ${bodyText.slice(0, 4000)}`);
    }
    return {reselect, deletion};
};

const first = await readCooldownText();
await page.waitForTimeout(1100);
const second = await readCooldownText();
await page.waitForTimeout(1100);
const third = await readCooldownText();

if (JSON.stringify(first) !== JSON.stringify(second)
    || JSON.stringify(second) !== JSON.stringify(third)
) {
    throw new Error(`cooldown moved after refresh: ${JSON.stringify({first, second, third})}`);
}

await page.screenshot({path: screenshotPath, fullPage: true});
fs.writeFileSync(
    resultPath,
    `${JSON.stringify({
        username,
        url: page.url(),
        viewport: {width: 1280, height: 960, deviceScaleFactor: 1},
        first,
        second,
        third,
    }, null, 2)}\n`,
    {mode: 0o600},
);

await browser.close();
console.log(`S100 my-page cooldown refresh guard verified: ${screenshotPath}`);
