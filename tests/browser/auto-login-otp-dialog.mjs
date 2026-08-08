import assert from 'node:assert/strict';
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const artifactDir = process.env.REF_AUTO_LOGIN_OTP_ARTIFACT_DIR;
if (!artifactDir) {
    throw new Error('REF_AUTO_LOGIN_OTP_ARTIFACT_DIR is required');
}

const playwrightRequire = createRequire(process.env.PLAYWRIGHT_REQUIRE_FROM ?? import.meta.url);
let chromium;
try {
    ({chromium} = playwrightRequire('playwright'));
} catch {
    ({chromium} = playwrightRequire('@playwright/test'));
}

const bundleDir = path.join(projectRoot, 'dist_js/gateway');
const loginToken = [1, [321, 'browser-test-token'], Date.now()];
let autoLoginCount = 0;
let manualLoginBody = null;
let otpBody = '';
let otpAccepted = false;

const loginHTML = `<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>자동로그인 OTP 검증</title>
  <script>var kakao_oauth_client_id = ''; var kakao_oauth_redirect_uri = '';</script>
  <link rel="stylesheet" href="/dist_js/gateway/common_ts.css">
  <link rel="stylesheet" href="/dist_js/gateway/login.css">
  <script src="/dist_js/gateway/vendors.js"></script>
  <script src="/dist_js/gateway/login.js"></script>
</head>
<body>
  <main class="container" style="margin-top:120px;max-width:450px">
    <form id="main_form" method="post" action="#">
      <label for="username">계정명</label>
      <input id="username" name="username" type="text">
      <label for="password">비밀번호</label>
      <input id="password" name="password" type="password">
      <input id="global_salt" name="global_salt" type="hidden" value="browser-test-salt">
      <button type="submit">로그인</button>
    </form>
    <button id="btn_kakao_login" type="button">카카오 로그인</button>
    <a id="oauth_change_pw" href="#">비밀번호 초기화</a>
  </main>
  <div class="modal fade" id="modalOTP" tabindex="-1" role="dialog" aria-labelledby="otp-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form id="otp_form" method="post" action="#">
          <div class="modal-header">
            <h5 class="modal-title" id="otp-title">인증 코드 필요</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">×</button>
          </div>
          <div class="modal-body">
            <div>인증 코드가 필요합니다.<br><br>카카오톡의 '나와의 채팅'란을 확인해 주세요.</div>
            <label for="otp_code">인증 코드</label>
            <input type="number" class="form-control" name="otp" id="otp_code" placeholder="인증 코드">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
            <button type="submit" class="btn btn-primary">제출</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>`;

const json = (response, body) => {
    response.writeHead(200, {'content-type': 'application/json; charset=utf-8'});
    response.end(JSON.stringify(body));
};

const readBody = async request => {
    const chunks = [];
    for await (const chunk of request) {
        chunks.push(chunk);
    }
    return Buffer.concat(chunks).toString('utf8');
};

const server = http.createServer(async (request, response) => {
    const requestURL = new URL(request.url ?? '/', 'http://127.0.0.1');
    if (requestURL.pathname === '/') {
        response.writeHead(200, {'content-type': 'text/html; charset=utf-8'});
        response.end(otpAccepted ? '<!doctype html><p id="logged-in">로그인 완료</p>' : loginHTML);
        return;
    }
    if (requestURL.pathname.startsWith('/dist_js/gateway/')) {
        const fileName = path.basename(requestURL.pathname);
        const filePath = path.join(bundleDir, fileName);
        const contentType = fileName.endsWith('.css') ? 'text/css' : 'text/javascript';
        response.writeHead(200, {'content-type': `${contentType}; charset=utf-8`});
        response.end(fs.readFileSync(filePath));
        return;
    }
    if (requestURL.pathname === '/api.php') {
        const apiPath = requestURL.searchParams.get('path');
        const rawBody = await readBody(request);
        if (apiPath === 'Login/ReqNonce') {
            json(response, {result: true, loginNonce: `nonce-${autoLoginCount + 1}`});
            return;
        }
        if (apiPath === 'Login/LoginByToken') {
            autoLoginCount += 1;
            json(response, {
                result: false,
                silent: false,
                reqOTP: true,
                reason: '인증 코드를 입력해주세요',
            });
            return;
        }
        if (apiPath === 'Login/LoginByID') {
            manualLoginBody = JSON.parse(rawBody);
            json(response, {result: false, reqOTP: false, reason: '수동 로그인 요청 확인'});
            return;
        }
    }
    if (requestURL.pathname === '/oauth_kakao/j_check_OTP.php') {
        otpBody = await readBody(request);
        otpAccepted = true;
        json(response, {result: true, reset: false, validUntil: '2026-08-18 00:00:00'});
        return;
    }
    response.writeHead(404);
    response.end('not found');
});

await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const address = server.address();
assert(address && typeof address !== 'string');
const baseURL = `http://127.0.0.1:${address.port}/`;

const browser = await chromium.launch({headless: true});
try {
    const context = await browser.newContext({
        viewport: {width: 1280, height: 960},
        deviceScaleFactor: 1,
        locale: 'ko-KR',
    });
    await context.addInitScript(token => {
        if (!localStorage.getItem('sammo_login_token')) {
            localStorage.setItem('sammo_login_token', JSON.stringify(token));
        }
    }, loginToken);
    const page = await context.newPage();

    const openAndAcceptOtpPrompt = async navigation => {
        const dialogPromise = page.waitForEvent('dialog');
        const navigationPromise = navigation();
        const dialog = await dialogPromise;
        assert.equal(dialog.message(), '인증 코드 입력이 필요합니다.');
        await dialog.accept();
        await navigationPromise;
        await page.locator('#modalOTP.show').waitFor({state: 'visible'});
        await page.waitForFunction(() => document.activeElement?.id === 'otp_code');
    };

    await openAndAcceptOtpPrompt(() => page.goto(baseURL, {waitUntil: 'domcontentloaded'}));
    assert.equal(autoLoginCount, 1);
    assert.deepEqual(JSON.parse(await page.evaluate(() => localStorage.getItem('sammo_login_token'))), loginToken);

    fs.mkdirSync(artifactDir, {recursive: true});
    const screenshotPath = path.join(artifactDir, 'auto-login-otp-modal.png');
    await page.screenshot({path: screenshotPath, fullPage: true});
    const modalGeometry = await page.locator('#modalOTP .modal-dialog').evaluate(element => {
        const rect = element.getBoundingClientRect();
        const style = getComputedStyle(element);
        return {
            rect: {x: rect.x, y: rect.y, width: rect.width, height: rect.height},
            display: style.display,
            opacity: style.opacity,
        };
    });

    await page.getByRole('button', {name: '취소'}).click();
    await page.locator('#modalOTP').waitFor({state: 'hidden'});
    assert.deepEqual(JSON.parse(await page.evaluate(() => localStorage.getItem('sammo_login_token'))), loginToken);

    await openAndAcceptOtpPrompt(() => page.reload({waitUntil: 'domcontentloaded'}));
    assert.equal(autoLoginCount, 2);
    await page.getByRole('button', {name: '취소'}).click();
    await page.locator('#modalOTP').waitFor({state: 'hidden'});
    await page.waitForTimeout(250);

    await page.locator('#username').fill('manual-user');
    await page.locator('#password').fill('manual-password');
    await page.waitForTimeout(100);
    const manualDialogPromise = page.waitForEvent('dialog');
    await page.locator('#main_form').evaluate(form => form.requestSubmit());
    const manualDialog = await manualDialogPromise;
    assert.equal(manualDialog.message(), '수동 로그인 요청 확인');
    await manualDialog.accept();
    assert.equal(manualLoginBody?.username, 'manual-user');
    assert.match(manualLoginBody?.password ?? '', /^[0-9a-f]{128}$/);

    await openAndAcceptOtpPrompt(() => page.reload({waitUntil: 'domcontentloaded'}));
    assert.equal(autoLoginCount, 3);
    await page.locator('#otp_code').fill('1234');
    const successDialogPromise = page.waitForEvent('dialog');
    await page.getByRole('button', {name: '제출'}).click();
    const successDialog = await successDialogPromise;
    assert.equal(successDialog.message(), '로그인되었습니다. 2026-08-18 00:00:00까지 유효합니다.');
    await successDialog.accept();
    await page.locator('#logged-in').waitFor({state: 'visible'});
    assert.match(otpBody, /name="otp"/);
    assert.match(otpBody, /\r\n1234\r\n/);

    const resultPath = path.join(artifactDir, 'auto-login-otp-result.json');
    fs.writeFileSync(resultPath, `${JSON.stringify({
        url: baseURL,
        viewport: {width: 1280, height: 960, deviceScaleFactor: 1},
        autoLoginCount,
        preservedTokenAfterCancel: true,
        reopenedAfterReload: true,
        manualLoginSubmitted: true,
        otpSuccessNavigated: true,
        modalGeometry,
        screenshotPath,
    }, null, 2)}\n`, {mode: 0o600});

    console.log(`automatic-login OTP Chromium flow verified: ${resultPath}`);
} finally {
    await browser.close();
    await new Promise((resolve, reject) => server.close(error => error ? reject(error) : resolve()));
}
