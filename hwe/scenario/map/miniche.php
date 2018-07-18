<?php

namespace sammo;

class CityConst extends CityConstBase{
    protected static $initCity = [
        //id,  도시, 규모, 인구, 농,  상, 치, 성, 수,(x100)지역, x,  y, 연결도시
        [1,  '낙양', '특', 8357,117,120,100,121,124, '중원', 279, 176, ['하내', '홍농', '호로']],
        [2,  '성도', '특', 6525,123,125,100,125,123, '서촉',  28, 288, ['덕양', '강주']],
        [3,  '건업', '특', 6386,116,123,100,115,119, '오월', 507, 303, ['광릉', '합비', '오']],
        [4,  '업',   '특', 6205,125,113,100,117,122, '하북', 347, 131, ['하내', '거록', '남피', '제남', '진류']],
        [5,  '장안', '특', 5923,116,123,100,120,118, '서북', 162, 173, ['안정', '오장원', '한중', '홍농']],
        [6,  '허창', '특', 5876,121,124,100,117,125, '중원', 317, 218, ['호로', '진류', '초', '여남', '완']],
        [7,  '양양', '특', 5837,120,126,100,115,117, '초',   259, 287, ['신야', '강릉', '강하']],
        [8,  '시상', '대', 5252, 98,100, 80, 99, 96, '오월', 350, 367, ['적벽', '여강', '단양', '상동', '장사']],
        [9,  '수춘', '대', 5143, 99, 96, 80, 99, 95, '중원', 381, 270, ['여남', '초', '하비', '합비']],
        [10, '한중', '대', 5137, 96,101, 80,102,103, '서촉', 130, 218, ['무도', '오장원', '장안', '상용', '자동']],
        [11, '남피', '대', 5032, 99,101, 80,101,105, '하북', 405,  93, ['계', '북평', '평원', '업', '거록']],
        [12, '위례', '대', 4926,100, 93, 80, 98,103, '동이', 615, 143, ['평양', '북해', '웅진', '계림']],
        [13, '북평', '대', 4862,102, 95, 80,103, 99, '하북', 442,  53, ['계', '요동', '남피']],
        [14, '강릉', '대', 4850,105, 96, 80, 95, 96, '초',   243, 336, ['이릉', '양양', '적벽', '장사', '무릉']],
        [15, '완',   '대', 4724,103,100, 80,101, 99, '중원', 270, 236, ['허창', '여남', '신야']],
        [16, '장사', '대', 4710, 97, 99, 80,100,105, '초',   258, 373, ['강릉', '시상', '계양', '무릉']],
        [17, '오',   '중', 4355, 77, 81, 60, 77, 76, '오월', 512, 345, ['건업', '단양', '회계', '탐라']],
        [18, '하비', '중', 4278, 85, 83, 60, 82, 78, '중원', 451, 245, ['패', '북해', '광릉', '수춘']],
        [19, '복양', '중', 4185, 80, 83, 60, 82, 80, '중원', 412, 175, ['제남', '진류', '패']],
        [20, '웅진', '중', 4157, 77, 79, 60, 78, 80, '동이', 619, 205, ['위례', '계림', '탐라']],
        [21, '강주', '중', 4126, 79, 80, 60, 84, 81, '서촉',  73, 311, ['성도', '덕양', '영안', '주제', '월수']],
        [22, '무도', '중', 4027, 77, 84, 60, 80, 85, '서촉',  50, 191, ['저', '한중', '자동']],
        [23, '국내', '중', 3982, 78, 80, 60, 83, 78, '동이', 596,  48, ['요동', '오환', '평양']],
        [24, '진류', '중', 3957, 82, 80, 60, 80, 83, '중원', 365, 178, ['업', '복양', '패', '초', '허창', '호로']],
        [25, '계양', '중', 3955, 83, 80, 60, 81, 77, '초',   232, 418, ['영릉', '장사', '상동']],
        [26, '계림', '중', 3911, 80, 74, 60, 81, 78, '동이', 656, 198, ['위례', '웅진', '왜']],
        [27, '계',   '중', 3885, 75, 80, 60, 78, 81, '하북', 386,  55, ['진양', '북평', '남피']],
        [28, '무위', '중', 3874, 77, 79, 60, 83, 80, '서북',  53,  81, ['강', '안정', '천수', '저']],
        [29, '제남', '중', 3831, 77, 81, 60, 84, 77, '하북', 402, 132, ['업', '평원', '복양']],
        [30, '남해', '중', 3803, 82, 76, 60, 80, 81, '오월', 259, 474, ['상동', '산월', '교지']],
        [31, '덕양', '중', 3803, 81, 84, 60, 79, 77, '서촉',  73, 276, ['자동', '영안', '강주', '성도']],
        [32, '하내', '중', 3736, 77, 81, 60, 81, 80, '하북', 291, 140, ['진양', '업', '낙양', '하동']],
        [33, '상용', '중', 3687, 78, 76, 60, 77, 81, '서촉', 188, 226, ['한중', '신야']],
        [34, '초',   '소', 3286, 60, 62, 40, 62, 57, '중원', 372, 233, ['허창', '진류', '패', '수춘', '여남']],
        [35, '운남', '소', 3258, 62, 60, 40, 64, 61, '남중',  33, 413, ['월수', '건녕', '남만']],
        [36, '대',   '소', 3256, 60, 62, 40, 57, 60, '오월', 446, 472, ['산월', '회계', '왜']],
        [37, '하동', '소', 3208, 60, 60, 40, 62, 55, '서북', 231, 140, ['흉노', '진양', '하내', '홍농']],
        [38, '무릉', '소', 3196, 58, 63, 40, 63, 58, '초',   200, 357, ['강릉', '장사', '영릉']],
        [39, '교지', '소', 3195, 58, 59, 40, 58, 59, '남중', 136, 472, ['남만', '남해']],
        [40, '단양', '소', 3183, 62, 64, 40, 58, 57, '오월', 432, 354, ['여강', '오', '건안', '시상']],
        [41, '영안', '소', 3153, 62, 59, 40, 58, 59, '서촉', 116, 287, ['덕양', '이릉', '강주']],
        [42, '북해', '소', 3146, 55, 63, 40, 63, 58, '하북', 466, 155, ['평원', '위례', '하비']],
        [43, '합비', '진',  998, 20, 19, 20, 39, 41, '중원', 413, 294, ['수춘', '건업', '여강']],
        [44, '이릉', '진',  968, 18, 19, 20, 39, 41, '초',   188, 282, ['영안', '강릉']],
        [45, '건녕', '소', 3082, 58, 59, 40, 63, 56, '남중',  75, 396, ['주제', '장가', '운남']],
        [46, '강하', '소', 3074, 55, 56, 40, 57, 60, '초',   316, 303, ['양양', '적벽', '여강']],
        [47, '진양', '소', 3074, 56, 59, 40, 64, 59, '하북', 310,  79, ['흉노', '하동', '하내', '거록', '계']],
        [48, '평원', '소', 3074, 62, 65, 40, 61, 63, '하북', 444, 118, ['남피', '제남', '북해']],
        [49, '회계', '소', 3005, 64, 59, 40, 62, 64, '오월', 480, 394, ['오', '건안', '대']],
        [50, '천수', '소', 2985, 59, 64, 40, 60, 58, '서북',  76, 145, ['무위', '안정', '오장원', '저']],
        [51, '평양', '소', 2939, 55, 59, 40, 60, 58, '동이', 596,  97, ['국내', '위례']],
        [52, '요동', '소', 2937, 63, 59, 40, 59, 63, '동이', 543,  26, ['북평', '오환', '국내']],
        [53, '거록', '소', 2936, 61, 57, 40, 64, 58, '하북', 355, 100, ['진양', '남피', '업']],
        [54, '여강', '소', 2905, 56, 58, 40, 60, 55, '오월', 392, 331, ['합비', '단양', '시상', '강하']],
        [55, '패',   '소', 2877, 64, 58, 40, 58, 59, '중원', 429, 220, ['진류', '복양', '하비', '초']],
        [56, '자동', '소', 2870, 57, 55, 40, 60, 58, '서촉',  62, 245, ['무도', '한중', '덕양']],
        [57, '광릉', '소', 2867, 61, 55, 40, 60, 62, '오월', 474, 273, ['하비', '건업']],
        [58, '장가', '소', 2853, 59, 62, 40, 58, 57, '남중', 136, 403, ['건녕', '영릉', '남만']],
        [59, '영릉', '소', 2849, 62, 58, 40, 62, 62, '초',   192, 394, ['무릉', '계양', '장가']],
        [60, '월수', '소', 2828, 60, 59, 40, 58, 63, '남중',  39, 349, ['강주', '주제', '운남']],
        [61, '건안', '소', 2802, 57, 62, 40, 58, 63, '오월', 435, 427, ['단양', '회계', '산월']],
        [62, '신야', '소', 2786, 60, 62, 40, 58, 55, '초',   240, 262, ['상용', '완', '양양']],
        [63, '탐라', '수', 1130, 22, 21, 20, 43, 41, '동이', 614, 264, ['웅진', '왜', '오']],
        [64, '상동', '소', 2767, 58, 59, 40, 62, 58, '초',   285, 411, ['계양', '시상', '남해']],
        [65, '안정', '소', 2764, 57, 59, 40, 57, 62, '서북', 130, 131, ['강', '무위', '천수', '장안']],
        [66, '여남', '소', 2749, 63, 56, 40, 64, 64, '중원', 330, 259, ['완', '허창', '초', '수춘']],
        [67, '홍농', '소', 2748, 57, 63, 40, 58, 63, '서북', 213, 175, ['하동', '낙양', '장안']],
        [68, '주제', '소', 2746, 58, 61, 40, 61, 58, '남중',  93, 357, ['강주', '월수', '건녕']],
        [69, '남만', '이', 2378, 40, 42, 20, 43, 45, '남중',  83, 454, ['운남', '장가', '교지']],
        [70, '산월', '이', 2275, 40, 37, 20, 43, 38, '오월', 373, 447, ['건안', '대', '남해']],
        [71, '오환', '이', 2153, 42, 37, 20, 43, 40, '동이', 628,  19, ['요동', '국내']],
        [72, '강',   '이', 2095, 40, 42, 20, 43, 40, '서북', 154,  77, ['무위', '안정']],
        [73, '왜',   '이', 2065, 39, 37, 20, 43, 41, '동이', 681, 292, ['계림', '탐라', '대']],
        [74, '흉노', '이', 2064, 40, 41, 20, 40, 38, '서북', 227,  79, ['진양', '하동']],
        [75, '저',   '이', 1957, 40, 42, 20, 43, 42, '서북',  21, 123, ['무위', '천수', '무도']],
        [76, '호로', '관',  958, 17, 19, 20, 95, 96, '중원', 317, 182, ['낙양', '진류', '허창']],
        [77, '오장원','진',1005, 19, 18, 20, 41, 40, '서북', 104, 180, ['천수', '장안', '한중']],
        [78, '적벽', '수', 1117, 23, 21, 20, 42, 41, '오월', 329, 335, ['강하', '강릉', '시상']],
    ];
}