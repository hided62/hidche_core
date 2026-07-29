import fs from 'node:fs';
import {createHash} from 'node:crypto';
import {chromium} from 'playwright';

const baseURL = process.env.REF_BROWSER_URL;
const screenshotPath = process.env.REF_SCREENSHOT_PATH;
const resultPath = process.env.REF_RESULT_PATH;
const username = process.env.REF_USER_ID ?? 's100user01';
const password = fs.readFileSync('/run/secrets/test_user_password', 'utf8').trim();
const expectedReason = '100기 올스타 장수는 능력치 초기화를 사용할 수 없습니다.';
const expectedWarning = '100기 올스타 장수는 장수 전환 시 능력치 성장 기록을 보존하기 위해 능력치 초기화를 사용할 수 없습니다.';

if (!baseURL || !screenshotPath || !resultPath) {
    throw new Error('REF_BROWSER_URL, REF_SCREENSHOT_PATH, and REF_RESULT_PATH are required');
}

const browser = await chromium.launch({headless: true});
const context = await browser.newContext({
    viewport: {width: 1280, height: 960},
    deviceScaleFactor: 1,
});
const browserMessages = [];
const attachPageListeners = targetPage => {
    targetPage.on('console', message => {
        browserMessages.push({type: message.type(), text: message.text()});
    });
    targetPage.on('pageerror', error => {
        browserMessages.push({type: 'pageerror', text: error.message});
    });
};
let page = await context.newPage();
attachPageListeners(page);

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

const gameLoginResponse = await page.request.get(
    new URL('hwe/api.php?path=Global/GetConst', baseURL).href,
);
const gameLoginResult = await gameLoginResponse.json();
if (!gameLoginResponse.ok() || gameLoginResult.result !== true) {
    throw new Error(`game login failed: ${JSON.stringify(gameLoginResult)}`);
}

await page.close();
browserMessages.length = 0;
page = await context.newPage();
attachPageListeners(page);
await page.goto(
    new URL('hwe/v_inheritPoint.php', baseURL).href,
    {waitUntil: 'networkidle', timeout: 60_000},
);
const bodyText = await page.locator('body').innerText();
if (bodyText.includes('#0 /var/www/html/')) {
    throw new Error('PHP stack trace was rendered in the inheritance page');
}
if (!bodyText.includes(expectedWarning)) {
    await page.screenshot({path: screenshotPath, fullPage: true});
    fs.writeFileSync(
        resultPath,
        `${JSON.stringify({
            username,
            url: page.url(),
            bodyText: bodyText.slice(0, 4000),
            browserMessages,
        }, null, 2)}\n`,
        {mode: 0o600},
    );
    throw new Error('S100 stat-reset restriction was not rendered');
}
if (await page.getByRole('button', {name: '능력치 초기화'}).count() !== 0) {
    throw new Error('S100 stat-reset button must not be rendered');
}

const warning = page.getByText(expectedWarning, {exact: true});
await warning.waitFor({state: 'visible', timeout: 60_000});
const warningGeometry = await warning.evaluate(element => {
    const rect = element.getBoundingClientRect();
    const style = getComputedStyle(element);
    return {
        rect: {
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height,
        },
        style: {
            color: style.color,
            fontFamily: style.fontFamily,
            fontSize: style.fontSize,
            lineHeight: style.lineHeight,
        },
    };
});
const beforeState = await page.evaluate(() => ({
    currentStat: staticValues.currentStat,
    previousPoint: staticValues.items.previous,
}));

const resetResponse = await page.request.put(
    new URL('hwe/api.php?path=InheritAction/ResetStat', baseURL).href,
    {
        data: {
            leadership: 55,
            strength: 55,
            intel: 55,
        },
    },
);
const resetResult = await resetResponse.json();
if (!resetResponse.ok()
    || resetResult.result !== false
    || resetResult.reason !== expectedReason
) {
    throw new Error(`unexpected ResetStat response: ${JSON.stringify(resetResult)}`);
}

await page.reload({waitUntil: 'networkidle', timeout: 60_000});
const afterState = await page.evaluate(() => ({
    currentStat: staticValues.currentStat,
    previousPoint: staticValues.items.previous,
}));
if (JSON.stringify(afterState) !== JSON.stringify(beforeState)) {
    throw new Error(`ResetStat rejection changed state: ${JSON.stringify({beforeState, afterState})}`);
}

await page.screenshot({path: screenshotPath, fullPage: true});
fs.writeFileSync(
    resultPath,
    `${JSON.stringify({
        username,
        url: page.url(),
        viewport: {width: 1280, height: 960, deviceScaleFactor: 1},
        warningGeometry,
        resetResult,
        beforeState,
        afterState,
        browserMessages,
    }, null, 2)}\n`,
    {mode: 0o600},
);

await browser.close();
console.log(`S100 inheritance stat-reset guard verified: ${screenshotPath}`);
