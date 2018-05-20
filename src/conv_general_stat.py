#!/usr/bin/env python3
import xlrd
import os
import sys
import json

print(sys.argv[1])
wb = xlrd.open_workbook(sys.argv[1])
general_list = wb.sheet_by_name('장수 목록')

json_general_list = []
for i in range(1, general_list.nrows):
    row = general_list.row_values(i)
    if len(row) < 10:
        continue

    row = row[:13]
    
    #상성
    if row[0] == '':
        row[0] = 0
    else:
        row[0] = int(row[0])

    #이름
    row[1] = row[1].strip()
    
    #전콘
    if row[2] == '':
        row[2] = -1
    else:
        row[2] = int(row[2])
    
    #국가
    if row[3] == '':
        row[3] = 0
    else:
        row[3] = int(row[3])

    #도시    
    if row[4] == '':
        row[4] = None

    #통무지, 생몰
    row[5] = int(row[5])
    row[6] = int(row[6])
    row[7] = int(row[7])
    row[8] = int(row[8])
    row[9] = int(row[9])

    #성격
    if len(row) < 11:
        row.append('')
    row[10] = row[10].strip()
    if row[10] == '':
        row[10] = None
    else:
        row[10] = row[10].strip()
    
    #특기
    if len(row) < 12:
        row.append('')
    row[11] = row[11].strip()
    if row[11] == '':
        row[11] = None
    else:
        row[11] = row[11].strip()

    if len(row) < 13:
        row.append('')
    row[12] = row[12].strip()
    if row[12] == '':
        row.pop()

    row.insert(8, 0)
    print(json.dumps(row, ensure_ascii=False), end=',\n')

