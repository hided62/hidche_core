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

xlsxpath = sys.argv[1]
print(xlsxpath)
wb = xlrd.open_workbook(xlsxpath)

def parseConfig(configSheet):
    result = {}
    for i in range(1, configSheet.nrows):
        row = configSheet.row_values(i)
        if len(row) < 4:
            continue
        varName = row[0]
        value = row[3]

        if isinstance(value, float) and float(int(value)) == value:
            value = int(value)
        elif value == '':
            continue

        if isinstance(value, str):
            values = value.split('\n')
            if len(values) > 1:
                value = values

        varNames = varName.split('.')
        target = result
        for idx, name in enumerate(varNames):
            if idx + 1 == len(varNames):
                target[name] = value
                break
            if not name in target:
                target[name] = {}
            target = target[name]
    
    if 'useCharSetting' in result:
        if result['useCharSetting'] > 0:
            result['fiction'] = 0
        else:
            result['fiction'] = 1
        del result['useCharSetting']
    return result

def extractNationList(nationSheet):
    jsonNationList = []
    nationChiefInfo = {}

    for i in range(1, nationSheet.nrows):
        row = nationSheet.row_values(i)
        if len(row) < 8:
            continue
        
        if len(row) == 8:
            name, color, gold, rice, desc, tech, nationType, nationLevel = row
            cities = ''
        else:
            name, color, gold, rice, desc, tech, nationType, nationLevel, cities = row[:9]

        nationChiefInfo[name] = {}
        if len(row) >= 10:
            cheif0 = str(row[9])
            if cheif0:
                nationChiefInfo[name][cheif0] = 12
        if len(row) >= 11:
            cheif1 = str(row[10])
            if cheif1:
                nationChiefInfo[name][cheif1] = 11
        
        
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

    return jsonNationList, nationChiefInfo
    

def extractGeneralList(generalSheet, nationList={}, nationChiefInfo={}):
    nationInv = {'재야':0}
    for idx, nation in enumerate(nationList, 1):
        nationInv[nation[0]] = idx

    json_general_list = []
    names = {}
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
        row[1] = str(row[1]).strip()
        if row[1] == '':
            continue
        print(row[1])
        #전콘
        row[2] = str(row[2]).strip()
        if row[2].isdigit():
            row[2] = int(row[2])
        elif row[2] == '':
            row[2] = None
        
        level = 0

        #국가
        
        row[3] = str(row[3]).strip()
        if row[3] in nationInv:
            pass
            level = 1
        elif row[3].isdigit() and 0 < int(row[3]) < len(nationList):
            row[3] = nationList[row[3]][0]
            level = 1
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
        if len(row[12]) > 99:
            row[12] = row[12][:99]
        if row[12] == '':
            row.pop()

        if level and row[3] in nationChiefInfo:
            nationCheifDetail = nationChiefInfo[row[3]]
            if row[1] in nationCheifDetail:
                level = nationCheifDetail[row[1]]
        
        row.insert(8, level)
        json_general_list.append(row)

        if row[1] in names:
            raise RuntimeError('%s가 이미 있습니다!'%row[1])
        names[row[1]] = 1
    return json_general_list, names


if '환경 변수' in wb.sheet_names():
    config = parseConfig(wb.sheet_by_name('환경 변수'))
else:
    config = {
        'title':'타이틀'
    }

if '국가' in wb.sheet_names():
    nationInfo, nationChiefInfo = extractNationList(wb.sheet_by_name('국가'))
else:
    nationInfo = []
    nationChiefInfo = {}

generalList, names = extractGeneralList(wb.sheet_by_name('장수 목록'), nationInfo, nationChiefInfo)

with open('%s.json'%xlsxpath, 'wt', encoding='utf-8') as fp:
    fp.write(json.dumps(config, ensure_ascii=False, indent='    ')[:-2])
    fp.write(',\n')
        
    fp.write('    "nation":[\n        ')
    fp.write(',\n        '.join([json.dumps(nation, ensure_ascii=False) for nation in nationInfo]))
    fp.write('\n    ],\n')

    fp.write('    "diplomacy":[],\n')

    fp.write('    "general":[\n        ')
    fp.write(',\n        '.join([json.dumps(general, ensure_ascii=False) for general in generalList]))
    fp.write('\n    ]')

    names2 = []
    names3 = []
    if '확장 장수 목록' in wb.sheet_names():
        generalExList, names2 = extractGeneralList(wb.sheet_by_name('확장 장수 목록'), nationInfo, nationChiefInfo)

        for name in names2:
            if name in names:
                raise RuntimeError('%s가 일반 장수 및 확장 장수에 모두 있습니다!'%name)

        fp.write(',\n    "general_ex":[\n        ')
        fp.write(',\n        '.join([json.dumps(general, ensure_ascii=False) for general in generalExList]))
        fp.write('\n    ]')

    if '빙의 불가 장수 목록' in wb.sheet_names():
        generalNeutralList, names3 = extractGeneralList(wb.sheet_by_name('빙의 불가 장수 목록'), nationInfo, nationChiefInfo)

        for name in names3:
            if name in names:
                raise RuntimeError('%s가 일반 장수 및 빙의 불가 장수에 모두 있습니다!'%name)
        for name in names3:
            if name in names2:
                raise RuntimeError('%s가 확장 장수 및 빙의 불가 장수에 모두 있습니다!'%name)

        fp.write(',\n    "general_neutral":[\n        ')
        fp.write(',\n        '.join([json.dumps(general, ensure_ascii=False) for general in generalNeutralList]))
        fp.write('\n    ]')

    fp.write('\n}')