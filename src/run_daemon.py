#!/usr/bin/env python3

import os
import glob
import urllib.request

basepath = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
hiddenList = []

for path in glob.glob(basepath+'/*/.htaccess'):
    hiddenList.append(os.path.dirname(path))

webBase = 'http://127.0.0.1'
for line in open(basepath+'/d_shared/common_path.js'):
    if not "root:" in line:
        continue
    webBase = line[line.find('root:')+5:-1]
    webBase = webBase.strip('\'",')
    break

for path in glob.glob(basepath+'/*/d_setting/DB.php'):
    servPath = os.path.dirname(os.path.dirname(path))
    if servPath in hiddenList:
        continue

    servRelPath = os.path.relpath(servPath, basepath)

    webPath = webBase + '/' + servRelPath + '/proc.php'
    print(webPath)




    obj = urllib.request.urlopen(webPath)
    res = obj.read()
