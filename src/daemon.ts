#!/usr/bin/env node
/* eslint-disable no-console */

import fs from "node:fs/promises";
import path from "node:path";
import { setTimeout as delay } from "node:timers/promises";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
//const __dirname = path.dirname(__filename);

// ============ 환경설정 ============
const REQUIRE_PUBLIC = (process.env.REQUIRE_PUBLIC ?? "true").toLowerCase() === "true";
const FETCH_TIMEOUT_MS = nenv("FETCH_TIMEOUT_MS", 60_000);
const IF_LOCKED_WAIT_MS = nenv("IF_LOCKED_WAIT_MS", 10_000);
const IF_UPDATED_WAIT_MS = nenv("IF_UPDATED_WAIT_MS", 1_000);
const DEFAULT_WAIT_MS = nenv("DEFAULT_WAIT_MS", 8_000);
const BASE_SCAN_DEPTH_ONE = (process.env.BASE_SCAN_DEPTH_ONE ?? "true").toLowerCase() === "true";
const RESCAN_INTERVAL_MS = nenv("RESCAN_INTERVAL_MS", 300_000); // 5분
const SHUTDOWN_TIMEOUT_MS = nenv("SHUTDOWN_TIMEOUT_MS", 15_000);
const AUTO_RESET_JITTER_MS = nenv("AUTO_RESET_JITTER_MS", 0); // 0초 직후 지연(밀리초)

function nenv(key: string, def: number): number {
    const v = process.env[key];
    if (!v) return def;
    const num = Number(v);
    return Number.isFinite(num) ? num : def;
}

// ============ 유틸 ============
type Json = Record<string, unknown>;
type ProcResult = {
    result: boolean;
    updated: boolean;
    locked: boolean;
    lastExecuted?: string;
};

function nowStr() {
    const d = new Date();
    const pad = (n: number) => n.toString().padStart(2, "0");
    const ms = d.getMilliseconds().toString().padStart(3, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}.${ms}`;
}
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function log(...args: any[]) { console.log(nowStr(), ...args); }

async function fileExists(fp: string): Promise<boolean> {
    try { await fs.access(fp, fs.constants.F_OK); return true; } catch { return false; }
}

async function readFileSafe(fp: string): Promise<string | null> {
    try { return await fs.readFile(fp, "utf8"); } catch { return null; }
}

async function listDirsOnceAsync(base: string): Promise<string[]> {
    const out: string[] = [];
    const names = await fs.readdir(base);
    for (const name of names) {
        const p = path.join(base, name);
        try { if ((await fs.stat(p)).isDirectory()) out.push(p); } catch { /* empty */ }
    }
    return out;
}

function joinUrl(base: string, suffix: string): string {
    if (base.endsWith("/") && suffix.startsWith("/")) return base + suffix.slice(1);
    if (!base.endsWith("/") && !suffix.startsWith("/")) return base + "/" + suffix;
    return base + suffix;
}

// 분 0초 정렬 대기 시간 계산: 지금 시각에서 다음 분의 0초(+지터)까지 남은 ms
function msUntilNextMinuteZero(jitterMs = 0): number {
    const now = Date.now();
    const nextMinuteStart = Math.ceil(now / 60_000) * 60_000;
    const target = nextMinuteStart + Math.max(0, jitterMs);
    return Math.max(0, target - now);
}

// ============ 경로/루트 URL ============
const basepath = path.dirname(path.dirname(path.resolve(__filename)));
async function parseWebBaseFromCommonPath(base: string): Promise<string> {
    const jsPath = path.join(base, "d_shared", "common_path.js");
    const text = await readFileSafe(jsPath);
    if (!text) { log("[WARN] common_path.js 읽기 실패 → fallback=http://127.0.0.1"); return "http://127.0.0.1"; }
    for (const line of text.split(/\r?\n/)) {
        if (line.includes("root:")) {
            let v = line.slice(line.indexOf("root:") + 5).trim();
            v = v.replace(/^[\s,'"`]+/, "").replace(/[\s,'"`,]+$/, "");
            if (v) return v;
        }
    }
    log("[WARN] root: 항목 없음 → fallback=http://127.0.0.1");
    return "http://127.0.0.1";
}
const webBase = process.env.PUBLIC_CHECK_URL || await parseWebBaseFromCommonPath(basepath);
log("BASEPATH =", basepath);
log("WEBBASE  =", webBase);

// ============ 서버 모델 ============
type ServerEntry = {
    name: string;                 // servRelPath
    abs: string;                  // 절대 경로
    isHidden: () => Promise<boolean>;      // .htaccess 존재 여부
    procUrl: string;
    autoresetUrl: string;
};

async function scanOnce(): Promise<ServerEntry[]> {
    const dirs = await listDirsOnceAsync(basepath); // 깊이는 1단계만
    const out: ServerEntry[] = [];
    for (const dir of dirs) {
        if (BASE_SCAN_DEPTH_ONE === false) {
            // 필요하면 확장 로직 배치
        }
        const dbphp = path.join(dir, "d_setting", "DB.php");
        if (!await fileExists(dbphp)) continue;

        const name = path.relative(basepath, dir);
        const isHidden = () => fileExists(path.join(dir, ".htaccess"));

        out.push({
            name,
            abs: dir,
            isHidden,
            procUrl: joinUrl(webBase, `/${name}/proc.php`),
            autoresetUrl:joinUrl(webBase, `/${name}/j_autoreset.php`),
        });
    }
    return out;
}

// ============ 공개 접근 체크 ============
async function isPublicReachable(signal?: AbortSignal): Promise<boolean> {
    if (!REQUIRE_PUBLIC) return true;
    const ctrl = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), Math.min(5_000, FETCH_TIMEOUT_MS));
    const composite = anySignal([ctrl.signal, signal].filter(Boolean) as AbortSignal[]);
    try {
        const res = await fetch(webBase, { method: "GET", signal: composite });
        if (!res.ok) { log(`[PUBLIC] 응답 ${res.status} → 비공개로 간주`); return false; }
        return true;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (e: any) {
        if (e?.name !== "AbortError") log(`[PUBLIC] 체크 실패: ${e?.name || e}`);
        return false;
    } finally {
        clearTimeout(timer);
    }
}

// ============ HTTP ============
async function httpGetJson<T extends Json>(url: string, outerSignal?: AbortSignal): Promise<T | null> {
    const ctrl = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), FETCH_TIMEOUT_MS);
    const signal = anySignal([ctrl.signal, outerSignal].filter(Boolean) as AbortSignal[]);

    const t0 = performance.now();
    try {
        const res = await fetch(url, { signal, cache: "no-store" });
        const dt = Math.round(performance.now() - t0);
        if (!res.ok) { log("HTTPError:", res.status, url); return null; }
        try {
            const data = (await res.json()) as T;
            log(url, `${dt}ms`);
            return data;
        } catch {
            await res.arrayBuffer().catch(() => { /* empty */ });
            log("ParseError: JSON 변환 실패", url);
            return null;
        }
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (e: any) {
        if (e?.name !== "AbortError") log(e?.name || "Exception", e?.message || String(e), url);
        return null;
    } finally {
        clearTimeout(timer);
    }
}

function anySignal(signals: AbortSignal[]): AbortSignal {
    if (signals.length === 1) return signals[0];
    const ctrl = new AbortController();
    for (const s of signals) {
        if (s.aborted) { ctrl.abort(); break; }
        s.addEventListener("abort", () => ctrl.abort(), { once: true });
    }
    return ctrl.signal;
}

// ============ 서버 러너 ============
class ServerRunner {
    #entry: ServerEntry;
    #task?: Promise<void>;
    #stopCtrl = new AbortController();
    #stopped = false;
    #lastAutoResetAt = 0;

    constructor(entry: ServerEntry) {
        this.#entry = entry;
    }

    name() { return this.#entry.name; }

    start() {
        if (!this.#task) {
            log(`[${this.name()}] 루프 시작`);
            this.#task = this.#run();
        }
        return this.#task;
    }

    async stop(): Promise<void> {
        if (this.#stopped) return;
        this.#stopped = true;
        this.#stopCtrl.abort();
        try { await this.#task; } catch { /* empty */ }
        log(`[${this.name()}] 루프 종료`);
    }

    async #run() {
        while (!this.#stopCtrl.signal.aborted) {
            try {
                // 공개 접근 체크
                const pub = await isPublicReachable(this.#stopCtrl.signal);
                if (!pub) { await delay(15_000, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ }); continue; }

                const hidden = await this.#entry.isHidden();

                if (hidden) {
                    // ★ 매 분 0초에 맞춰 호출
                    const waitMs = msUntilNextMinuteZero(AUTO_RESET_JITTER_MS);
                    if (waitMs > 0) {
                        // 신호 안전하게 대기
                        await delay(waitMs, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
                        if (this.#stopCtrl.signal.aborted) break;
                    }
                    log(`[${this.name()}] 닫힘 - (정렬) autoreset 호출 @ mm:00${AUTO_RESET_JITTER_MS ? `+${AUTO_RESET_JITTER_MS}ms` : ""}`);
                    await httpGetJson<Json>(this.#entry.autoresetUrl, this.#stopCtrl.signal);
                    // 같은 분에 중복 호출 방지: 0초 직후 잠깐 쉬고 다음 루프는 자연스럽게 다음 분으로 정렬됨
                    await delay(200, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
                    continue;
                }


                // 열림: proc
                const data = await httpGetJson<ProcResult>(this.#entry.procUrl, this.#stopCtrl.signal);
                if (!data) {
                    await delay(IF_LOCKED_WAIT_MS, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
                    continue;
                }

                if (data.result === false || data.locked === true) {
                    log(`[${this.name()}] result=${data.result} locked=${data.locked} → ${IF_LOCKED_WAIT_MS}ms 대기`);
                    await delay(IF_LOCKED_WAIT_MS, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
                    continue;
                }

                if (data.updated === true) {
                    log(`[${this.name()}] updated=true → ${IF_UPDATED_WAIT_MS}ms 후 재호출 (last=${data.lastExecuted ?? "-"})`);
                    await delay(IF_UPDATED_WAIT_MS, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
                    continue;
                }

                await delay(DEFAULT_WAIT_MS, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
            } catch (e: any) {
                if (this.#stopCtrl.signal.aborted) break;
                log(`[${this.name()}] 루프 예외:`, e?.message || String(e));
                await delay(5_000, undefined, { signal: this.#stopCtrl.signal }).catch(() => { /* empty */ });
            }
        }
    }
}

// ============ 매니저: 재스캔 & 종료 ============
class DaemonManager {
    #runners = new Map<string, ServerRunner>();
    #rescanCtrl = new AbortController();

    async start() {
        log("턴 데몬 시작");
        await this.#rescanOnce(); // 초기 스캔
        this.#scheduleRescan();   // 주기적 재스캔
    }

    async shutdown(timeoutMs = SHUTDOWN_TIMEOUT_MS) {
        log("[SHUTDOWN] 신호 수신 → 종료 절차 시작");
        this.#rescanCtrl.abort();

        const stops: Promise<void>[] = [];
        for (const r of this.#runners.values()) stops.push(r.stop());

        // 타임아웃 대기
        const done = Promise.allSettled(stops);
        const timeout = delay(timeoutMs).then(() => { throw new Error("Shutdown timeout"); });
        try { await Promise.race([done, timeout]); }
        catch { log("[SHUTDOWN] 타임아웃 도달 → 강제 종료"); }
        log("[SHUTDOWN] 종료 완료");
    }

    #scheduleRescan() {
        // 반복 타이머(AbortSignal과 함께)
        const loop = async () => {
            while (!this.#rescanCtrl.signal.aborted) {
                await delay(RESCAN_INTERVAL_MS, undefined, { signal: this.#rescanCtrl.signal }).catch(() => { /* empty */ });
                if (this.#rescanCtrl.signal.aborted) break;
                try { await this.#rescanOnce(); }
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                catch (e: any) { log("[RESCAN] 예외:", e?.message || String(e)); }
            }
        };
        loop().catch(() => { /* empty */ });
    }

    async #rescanOnce() {
        const entries = await scanOnce();
        log(`[RESCAN] 서버 ${entries.length}개 발견`);

        // 추가/갱신/삭제 판정
        const seen = new Set<string>();

        for (const e of entries) {
            seen.add(e.name);
            if (!this.#runners.has(e.name)) {
                // 신규 서버
                const runner = new ServerRunner(e);
                this.#runners.set(e.name, runner);
                runner.start().catch(() => { /* empty */ });
                log(`[RESCAN] 추가: ${e.name}`);
            } else {
                // 이미 러너 있음 → 경로/URL 변경 가능성 거의 없지만, 필요시 갱신 로직 여기에
            }
        }

        // 사라진 서버 중지
        for (const name of Array.from(this.#runners.keys())) {
            if (!seen.has(name)) {
                log(`[RESCAN] 제거: ${name}`);
                const r = this.#runners.get(name);
                if (!r) continue;
                this.#runners.delete(name);
                r.stop().catch(() => {  /* empty */ });
            }
        }
    }
}

// ============ 메인 ============
const manager = new DaemonManager();

async function main() {
    // 신호 처리: SIGINT/SIGTERM → graceful shutdown
    const onSignal = async (sig: NodeJS.Signals) => {
        log(`[SIGNAL] ${sig} 수신`);
        // 중복 호출 방지
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        process.off("SIGINT", onSignal as any);
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        process.off("SIGTERM", onSignal as any);
        await manager.shutdown().catch(() => {  /* empty */ });
        process.exit(0);
    };
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    process.on("SIGINT", onSignal as any);
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    process.on("SIGTERM", onSignal as any);

    await manager.start();
}

main().catch((e) => {
    log("치명적 예외:", e);
    process.exitCode = 1;
});