#!/usr/bin/env python3

import os
import time
import glob
import urllib.request
import concurrent.futures

def run(webPath):
    print(webPath)
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
    for path in glob.glob(basepath+'/*/d_setting/DB.php'):
        servPath = os.path.dirname(os.path.dirname(path))
        if servPath in hiddenList:
            continue

        servRelPath = os.path.relpath(servPath, basepath)
        webPath = webBase + '/' + servRelPath + '/proc.php'
        servList.append(webPath)

    with concurrent.futures.ThreadPoolExecutor(max_workers=len(servList)) as executor:
        waiters=[]
        for _ in range(4):
            for webPath in servList:
                future = executor.submit(run, webPath)
                waiters.append(future)
            time.sleep(15)
        for future in waiters:
            future.done()

if __name__ == "__main__":
    # execute only if run as a script
    main()
