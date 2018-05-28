#!/usr/bin/env python3
import xlrd
import os
import sys
import json


#TODO: 시나리오마다 바뀔 수 있음
nationLevelMap = {
    '황제':7,
    '왕':6,
    '공':5,
    '주목':4,
    '주자사':3,
    '군벌':2,
    '호족':1,
    '방랑군':0
}

print(sys.argv[1])
wb = xlrd.open_workbook(sys.argv[1])

def extractNationList(nationSheet):
    jsonNationList = []

    for i in range(1, nationSheet.nrows):
        row = nationSheet.row_values(i)
        if len(row) < 8:
            continue
        
        if len(row) == 8:
            name, color, gold, rice, desc, tech, nationType, nationLevel = row
            cities = ''
        else:
            row = row[:9]
            name, color, gold, rice, desc, tech, nationType, nationLevel, cities = row


        
        cities = list(map(str.strip, row[8].split(',')))
        
        if not cities:
            nationLevel = '방랑군'
        
        if nationLevel.isdigit():
            nationLevel = int(nationLevel)
        elif nationLevel in nationLevelMap:
            nationLevel = nationLevelMap[nationLevel]
        else:
            nationLevel = 1

        gold = int(gold)
        rice = int(rice)
        tech = int(tech)
        
        jsonNationList.append([name, color, gold, rice, desc, tech, nationType, nationLevel, cities])

    return jsonNationList
    

def extractGeneralList(generalSheet, nationList={}):
    nationInv = {'재야':0}
    for idx, nation in enumerate(nationList, 1):
        nationInv[nation[0]] = idx

    json_general_list = []
    for i in range(1, generalSheet.nrows):
        row = generalSheet.row_values(i)
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
        if row[1] == '':
            continue
        
        #전콘
        row[2] = str(row[2]).strip()
        if row[2].isdigit():
            row[2] = int(row[2])
        elif row[2] == '':
            row[2] = None
        
        #국가
        
        row[3] = str(row[3]).strip()
        if row[3] in nationInv:
            pass
        elif row[3].isdigit() and 0 < int(row[3]) < len(nationList):
            row[3] = nationList[row[3]][0]
        else:
            row[3] = 0

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

        row[10] = str(row[10]).strip()
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
        json_general_list.append(row)
    return json_general_list


nationInfo = extractNationList(wb.sheet_by_name('국가'))

generalList = extractGeneralList(wb.sheet_by_name('장수 목록'), nationInfo)


print('    "nation":[\n        ', end='')
print(',\n        '.join([json.dumps(nation, ensure_ascii=False) for nation in nationInfo]))
print('    ],')

print('    "diplomacy":[],')

print('    "general":[\n        ', end='')
print(',\n        '.join([json.dumps(general, ensure_ascii=False) for general in generalList]))
print('    ],')

if '확장 장수 목록' in wb.sheet_names():
    generalExList = extractGeneralList(wb.sheet_by_name('확장 장수 목록'), nationInfo)
    print('    "general_ex":[\n        ', end='')
    print(',\n        '.join([json.dumps(general, ensure_ascii=False) for general in generalExList]))
    print('    ],')