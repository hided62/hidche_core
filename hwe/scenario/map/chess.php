<?php

namespace sammo;

class CityConst extends CityConstBase{
    public static $regionMap = [
        '킹'=>1,1=>'킹',
        '퀸'=>2,2=>'퀸',
        '룩'=>3,3=>'룩',
        '나이트'=>4,4=>'나이트',
        '비숍'=>5,5=>'비숍',
        '폰'=>6,6=>'폰',
        '빈칸'=>7,7=>'빈칸',
    ];

    protected static $initCity = [
        //id,  도시, 규모, 인구, 농,  상, 치, 성, 수,(x100)지역, x,  y, 연결도시
        [ 1, "A1", "중",  8000,  80,  80,  80,  80,  80,     "룩", 350.2, 420.2, ["A2", "B1", "B2"]],
        [ 2, "B1", "중",  8000,  80,  80,  80,  80,  80, "나이트", 392.7, 395.6, ["A1", "A2", "B2", "C1", "C2"]],
        [ 3, "C1", "중",  8000,  80,  80,  80,  80,  80,   "비숍", 435.2, 371.1, ["B1", "B2", "C2", "D1", "D2"]],
        [ 4, "D1", "특", 12000, 120, 120, 120, 120, 120,     "킹", 477.7, 346.5, ["C1", "C2", "D2", "E1", "E2"]],
        [ 5, "E1", "대", 10000, 100, 100, 100, 100, 100,     "퀸", 520.2, 322.0, ["D1", "D2", "E2", "F1", "F2"]],
        [ 6, "F1", "중",  8000,  80,  80,  80,  80,  80,   "비숍", 562.7, 297.5, ["E1", "E2", "F2", "G1", "G2"]],
        [ 7, "G1", "중",  8000,  80,  80,  80,  80,  80, "나이트", 605.2, 272.9, ["F1", "F2", "G2", "H1", "H2"]],
        [ 8, "H1", "중",  8000,  80,  80,  80,  80,  80,     "룩", 647.7, 248.4, ["G1", "G2", "H2"]],
        [ 9, "A2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 307.7, 395.6, ["A1", "A3", "B1", "B2", "B3"]],
        [10, "B2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 350.2, 371.1, ["A1", "A2", "A3", "B1", "B3", "C1", "C2", "C3"]],
        [11, "C2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 392.7, 346.5, ["B1", "B2", "B3", "C1", "C3", "D1", "D2", "D3"]],
        [12, "D2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 435.2, 322.0, ["C1", "C2", "C3", "D1", "D3", "E1", "E2", "E3"]],
        [13, "E2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 477.7, 297.5, ["D1", "D2", "D3", "E1", "E3", "F1", "F2", "F3"]],
        [14, "F2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 520.2, 272.9, ["E1", "E2", "E3", "F1", "F3", "G1", "G2", "G3"]],
        [15, "G2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 562.7, 248.4, ["F1", "F2", "F3", "G1", "G3", "H1", "H2", "H3"]],
        [16, "H2", "소",  6000,  60,  60,  60,  60,  60,     "폰", 605.2, 223.8, ["G1", "G2", "G3", "H1", "H3"]],
        [17, "A3", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 265.2, 371.1, ["A2", "A4", "B2", "B3", "B4"]],
        [18, "B3", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 307.7, 346.5, ["A2", "A3", "A4", "B2", "B4", "C2", "C3", "C4"]],
        [19, "C3", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 350.2, 322.0, ["B2", "B3", "B4", "C2", "C4", "D2", "D3", "D4"]],
        [20, "D3", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 392.7, 297.5, ["C2", "C3", "C4", "D2", "D4", "E2", "E3", "E4"]],
        [21, "E3", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 435.2, 272.9, ["D2", "D3", "D4", "E2", "E4", "F2", "F3", "F4"]],
        [22, "F3", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 477.7, 248.4, ["E2", "E3", "E4", "F2", "F4", "G2", "G3", "G4"]],
        [23, "G3", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 520.2, 223.8, ["F2", "F3", "F4", "G2", "G4", "H2", "H3", "H4"]],
        [24, "H3", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 562.7, 199.3, ["G2", "G3", "G4", "H2", "H4"]],
        [25, "A4", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 222.7, 346.5, ["A3", "A5", "B3", "B4", "B5"]],
        [26, "B4", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 265.2, 322.0, ["A3", "A4", "A5", "B3", "B5", "C3", "C4", "C5"]],
        [27, "C4", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 307.7, 297.5, ["B3", "B4", "B5", "C3", "C5", "D3", "D4", "D5"]],
        [28, "D4", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 350.2, 272.9, ["C3", "C4", "C5", "D3", "D5", "E3", "E4", "E5"]],
        [29, "E4", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 392.7, 248.4, ["D3", "D4", "D5", "E3", "E5", "F3", "F4", "F5"]],
        [30, "F4", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 435.2, 223.8, ["E3", "E4", "E5", "F3", "F5", "G3", "G4", "G5"]],
        [31, "G4", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 477.7, 199.3, ["F3", "F4", "F5", "G3", "G5", "H3", "H4", "H5"]],
        [32, "H4", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 520.2, 174.8, ["G3", "G4", "G5", "H3", "H5"]],
        [33, "A5", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 180.2, 322.0, ["A4", "A6", "B4", "B5", "B6"]],
        [34, "B5", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 222.7, 297.5, ["A4", "A5", "A6", "B4", "B6", "C4", "C5", "C6"]],
        [35, "C5", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 265.2, 272.9, ["B4", "B5", "B6", "C4", "C6", "D4", "D5", "D6"]],
        [36, "D5", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 307.7, 248.4, ["C4", "C5", "C6", "D4", "D6", "E4", "E5", "E6"]],
        [37, "E5", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 350.2, 223.8, ["D4", "D5", "D6", "E4", "E6", "F4", "F5", "F6"]],
        [38, "F5", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 392.7, 199.3, ["E4", "E5", "E6", "F4", "F6", "G4", "G5", "G6"]],
        [39, "G5", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 435.2, 174.8, ["F4", "F5", "F6", "G4", "G6", "H4", "H5", "H6"]],
        [40, "H5", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 477.7, 150.2, ["G4", "G5", "G6", "H4", "H6"]],
        [41, "A6", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 137.7, 297.5, ["A5", "A7", "B5", "B6", "B7"]],
        [42, "B6", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 180.2, 272.9, ["A5", "A6", "A7", "B5", "B7", "C5", "C6", "C7"]],
        [43, "C6", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 222.7, 248.4, ["B5", "B6", "B7", "C5", "C7", "D5", "D6", "D7"]],
        [44, "D6", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 265.2, 223.8, ["C5", "C6", "C7", "D5", "D7", "E5", "E6", "E7"]],
        [45, "E6", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 307.7, 199.3, ["D5", "D6", "D7", "E5", "E7", "F5", "F6", "F7"]],
        [46, "F6", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 350.2, 174.8, ["E5", "E6", "E7", "F5", "F7", "G5", "G6", "G7"]],
        [47, "G6", "수",  4000,  40,  40,  40,  40,  40,   "빈칸", 392.7, 150.2, ["F5", "F6", "F7", "G5", "G7", "H5", "H6", "H7"]],
        [48, "H6", "진",  4000,  40,  40,  40,  40,  40,   "빈칸", 435.2, 125.7, ["G5", "G6", "G7", "H5", "H7"]],
        [49, "A7", "소",  6000,  60,  60,  60,  60,  60,     "폰",  95.2, 272.9, ["A6", "A8", "B6", "B7", "B8"]],
        [50, "B7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 137.7, 248.4, ["A6", "A7", "A8", "B6", "B8", "C6", "C7", "C8"]],
        [51, "C7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 180.2, 223.8, ["B6", "B7", "B8", "C6", "C8", "D6", "D7", "D8"]],
        [52, "D7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 222.7, 199.3, ["C6", "C7", "C8", "D6", "D8", "E6", "E7", "E8"]],
        [53, "E7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 265.2, 174.8, ["D6", "D7", "D8", "E6", "E8", "F6", "F7", "F8"]],
        [54, "F7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 307.7, 150.2, ["E6", "E7", "E8", "F6", "F8", "G6", "G7", "G8"]],
        [55, "G7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 350.2, 125.7, ["F6", "F7", "F8", "G6", "G8", "H6", "H7", "H8"]],
        [56, "H7", "소",  6000,  60,  60,  60,  60,  60,     "폰", 392.7, 101.1, ["G6", "G7", "G8", "H6", "H8"]],
        [57, "A8", "중",  6000,  60,  60,  60,  60,  60,     "룩",  52.7, 248.4, ["A7", "B7", "B8"]],
        [58, "B8", "중",  8000,  80,  80,  80,  80,  80, "나이트",  95.2, 223.8, ["A7", "A8", "B7", "C7", "C8"]],
        [59, "C8", "중",  8000,  80,  80,  80,  80,  80,   "비숍", 137.7, 199.3, ["B7", "B8", "C7", "D7", "D8"]],
        [60, "D8", "특",  8000,  80,  80,  80,  80,  80,     "킹", 180.2, 174.8, ["C7", "C8", "D7", "E7", "E8"]],
        [61, "E8", "대", 12000, 120, 120, 120, 120, 120,     "퀸", 222.7, 150.2, ["D7", "D8", "E7", "F7", "F8"]],
        [62, "F8", "중", 10000, 100, 100, 100, 100, 100,   "비숍", 265.2, 125.7, ["E7", "E8", "F7", "G7", "G8"]],
        [63, "G8", "중",  8000,  80,  80,  80,  80,  80, "나이트", 307.7, 101.1, ["F7", "F8", "G7", "H7", "H8"]],
        [64, "H8", "중",  8000,  80,  80,  80,  80,  80,     "룩", 350.2,  76.6, ["G7", "G8", "H7"]]
    ];
}