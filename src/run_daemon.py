#!/usr/bin/env python3

import os
import time
import glob
import urllib.request
import concurrent.futures
from datetime import datetime

def run(webPath):
    now = datetime.now()
    print(now.strftime("%Y-%m-%d %H:%M:%S"), webPath)
    obj = urllib.request.urlopen(webPath)
    obj.read()

def main():
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
            autoResetList.append(resetPath)

        if servPath in hiddenList:
            continue

        webPath = webBase + '/' + servRelPath + '/proc.php'
        
        servList.append(webPath)

    with concurrent.futures.ThreadPoolExecutor(max_workers=max(1,len(servList))) as executor:
        waiters=[]
        for resetPath in autoResetList:
            future = executor.submit(run, resetPath)
            waiters.append(future)
        for idx in range(4):
            for webPath in servList:
                future = executor.submit(run, webPath)
                waiters.append(future)
            if idx == 3:
                break
            time.sleep(15)
        for future in waiters:
            future.done()

if __name__ == "__main__":
    # execute only if run as a script
    main()
