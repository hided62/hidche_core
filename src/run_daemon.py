#!/usr/bin/env python3

import os
import time
import glob
import urllib.request
import concurrent.futures
from datetime import datetime

ONE_FILE_LOOP_SEC = 60
ONE_LOOP_TIME_SEC = 8
RETRY_SEC = 2

def getCurrentMillisecTime():
    return int(time.time() * 1000)


def run(webPath):
    for _ in range(5):
        now = datetime.now()
        print(now.strftime("%Y-%m-%d %H:%M:%S"), webPath, flush=True)
        startTime = getCurrentMillisecTime()

        obj = urllib.request.urlopen(webPath)
        obj.read()

        timeGap = getCurrentMillisecTime() - startTime
        if timeGap < RETRY_SEC * 1000:
            break
        print(webPath, timeGap, 'retry')
    return getCurrentMillisecTime()

def main():
    startTime = getCurrentMillisecTime()

    basepath = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    hiddenList = []

    for path in glob.glob(basepath+'/*/.htaccess'):
        hiddenList.append(os.path.dirname(path))

    webBase = 'http://127.0.0.1'
    for line in open(basepath+'/d_shared/common_path.js'):
        if not "root:" in line:
            continue
        webBase = line[line.find('root:')+5:-1]
        webBase = webBase.strip(' \'",')
        break

    servList = []
    autoResetList = []
    for path in glob.glob(basepath+'/*/d_setting/DB.php'):
        servPath = os.path.dirname(os.path.dirname(path))
        servRelPath = os.path.relpath(servPath, basepath)

        resetAbsPath = servPath + '/j_autoreset.php'
        resetPath = webBase + '/' + servRelPath + '/j_autoreset.php'
        if os.path.exists(resetAbsPath):
            autoResetList.append((servRelPath, resetPath))

        if servPath in hiddenList:
            continue

        webPath = webBase + '/' + servRelPath + '/proc.php'
        
        servList.append((servRelPath,webPath))

    with concurrent.futures.ThreadPoolExecutor(max_workers=max(1,len(servList))) as executor:
        waiters={}
        
        waitTick = 1.0 / max(len(autoResetList), 1)
        for servRelPath, resetPath in autoResetList:
            future = executor.submit(run, resetPath)
            waiters[servRelPath] = future
            time.sleep(waitTick)

        waitTick = ONE_LOOP_TIME_SEC / max(len(servList), 1)
        doLoop = True
        while doLoop:
            for servRelPath, webPath in servList:
                if servRelPath in waiters and not waiters[servRelPath].done():
                    continue
                waiters[servRelPath] = executor.submit(run, webPath)
                time.sleep(waitTick)
                currTime = getCurrentMillisecTime()
                if currTime - startTime >= ONE_FILE_LOOP_SEC*1000:
                    doLoop = False
                    break
        
        for future in waiters.values():
            future.result()
    now = datetime.now()
    print('Done', now.strftime("%Y-%m-%d %H:%M:%S"))

if __name__ == "__main__":
    # execute only if run as a script
    main()
