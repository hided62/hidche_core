<?php

namespace sammo;

class CityConst extends CityConstBase{
    protected static $initCity = [
        //id,  도시, 규모, 인구, 농,  상, 치, 성, 수,(x100)지역, x,  y, 연결도시
        [1,  '낙양', '특', 6686, 78, 80, 67, 97, 99, '중원', 285, 176, ['하내', '홍농', '호로']], 
        [2,  '성도', '특', 5220, 82, 83, 67, 100, 98, '서촉', 30, 285, ['덕양', '강주']], 
        [3,  '건업', '특', 5109, 77, 82, 67, 92, 95, '오월', 507, 303, ['광릉', '합비', '오']], 
        [4,  '업',   '특', 4964, 83, 75, 67, 94, 98, '하북', 355, 135, ['하내', '거록', '남피', '제남', '진류']], 
        [5,  '장안', '특', 4738, 77, 82, 67, 96, 94, '서북', 162, 173, ['안정', '오장원', '한중', '홍농']], 
        [6,  '허창', '특', 4701, 81, 83, 67, 94, 100, '중원', 325, 218, ['호로', '진류', '초', '여남', '완']], 
        [7,  '양양', '특', 4670, 80, 84, 67, 92, 94, '초', 259, 295, ['신야', '강릉', '강하']], 
        [8,  '시상', '대', 4202, 65, 67, 53, 79, 77, '오월', 357, 357, ['적벽', '여강', '단양', '상동', '장사']], 
        [9,  '수춘', '대', 4114, 66, 64, 53, 79, 76, '중원', 385, 270, ['여남', '초', '하비', '합비']], 
        [10, '한중', '대', 4110, 64, 67, 53, 82, 82, '서촉', 130, 218, ['무도', '오장원', '장안', '상용', '자동']], 
        [11, '남피', '대', 4026, 66, 67, 53, 81, 84, '하북', 410, 93, ['계', '북평', '평원', '업', '거록']], 
        [12, '위례', '대', 3941, 67, 62, 53, 78, 82, '동이', 618, 140, ['평양', '북해', '웅진', '계림']], 
        [13, '북평', '대', 3890, 68, 63, 53, 82, 79, '하북', 442, 53, ['계', '요동', '남피']], 
        [14, '강릉', '대', 3880, 70, 64, 53, 76, 77, '초', 245, 330, ['이릉', '양양', '적벽', '장사', '무릉']], 
        [15, '완',   '대', 3779, 69, 67, 53, 81, 79, '중원', 275, 235, ['허창', '여남', '신야']], 
        [16, '장사', '대', 3768, 65, 66, 53, 80, 84, '초', 258, 373, ['강릉', '시상', '계양', '무릉']], 
        [17, '오',   '중', 3484, 51, 54, 40, 62, 61, '오월', 515, 340, ['건업', '단양', '회계', '탐라']], 
        [18, '하비', '중', 3422, 57, 55, 40, 66, 62, '중원', 460, 240, ['패', '북해', '광릉', '수춘']], 
        [19, '복양', '중', 3348, 53, 55, 40, 66, 64, '중원', 412, 170, ['제남', '진류', '패']], 
        [20, '웅진', '중', 3326, 51, 53, 40, 62, 64, '동이', 615, 205, ['위례', '계림', '탐라']], 
        [21, '강주', '중', 3301, 53, 53, 40, 67, 65, '서촉', 75, 305, ['성도', '덕양', '영안', '주제', '월수']], 
        [22, '무도', '중', 3222, 51, 56, 40, 64, 68, '서촉', 55, 191, ['저', '한중', '자동']], 
        [23, '국내', '중', 3186, 52, 53, 40, 66, 62, '동이', 596, 48, ['요동', '오환', '평양']], 
        [24, '진류', '중', 3166, 55, 53, 40, 64, 66, '중원', 370, 175, ['업', '복양', '패', '초', '허창', '호로']],
        [25, '계양', '중', 3164, 55, 53, 40, 65, 62, '초', 242, 408, ['영릉', '장사', '상동']], 
        [26, '계림', '중', 3129, 53, 49, 40, 65, 62, '동이', 660, 195, ['위례', '웅진', '왜']], 
        [27, '계',   '중', 3108, 50, 53, 40, 62, 65, '하북', 386, 55, ['진양', '북평', '남피']], 
        [28, '무위', '중', 3099, 51, 53, 40, 66, 64, '서북', 56, 76, ['강', '안정', '천수', '저']], 
        [29, '제남', '중', 3065, 51, 54, 40, 67, 62, '하북', 402, 132, ['업', '평원', '복양']], 
        [30, '남해', '중', 3042, 55, 51, 40, 64, 65, '오월', 270, 474, ['상동', '산월', '교지']], 
        [31, '덕양', '중', 3042, 54, 56, 40, 63, 62, '서촉', 73, 276, ['자동', '영안', '강주', '성도']], 
        [32, '하내', '중', 2989, 51, 54, 40, 65, 64, '하북', 295, 140, ['진양', '업', '낙양', '하동']], 
        [33, '상용', '중', 2950, 52, 51, 40, 62, 65, '서촉', 190, 220, ['한중', '신야']], 
        [34, '초',   '소', 2629, 40, 41, 27, 50, 46, '중원', 375, 225, ['허창', '진류', '패', '수춘', '여남']], 
        [35, '운남', '소', 2606, 41, 40, 27, 51, 49, '남중', 45, 405, ['월수', '건녕', '남만']], 
        [36, '대',   '소', 2605, 40, 41, 27, 46, 48, '오월', 450, 470, ['산월', '회계', '왜']], 
        [37, '하동', '소', 2566, 40, 40, 27, 50, 44, '서북', 240, 140, ['흉노', '진양', '하내', '홍농']], 
        [38, '무릉', '소', 2557, 39, 42, 27, 50, 46, '초', 195, 352, ['강릉', '장사', '영릉']], 
        [39, '교지', '소', 2556, 39, 39, 27, 46, 47, '남중', 136, 480, ['남만', '남해']], 
        [40, '단양', '소', 2546, 41, 43, 27, 46, 46, '오월', 440, 350, ['여강', '오', '건안', '시상']], 
        [41, '영안', '소', 2522, 41, 39, 27, 46, 47, '서촉', 116, 282, ['덕양', '이릉', '강주']], 
        [42, '북해', '소', 2517, 37, 42, 27, 50, 46, '하북', 470, 150, ['평원', '위례', '하비']], 
        [43, '합비', '진', 798, 13, 13, 13, 31, 33, '중원', 420, 294, ['수춘', '건업', '여강']], 
        [44, '이릉', '진', 774, 12, 13, 13, 31, 33, '초', 188, 275, ['영안', '강릉']], 
        [45, '건녕', '소', 2466, 39, 39, 27, 50, 45, '남중', 85, 390, ['주제', '장가', '운남']], 
        [46, '강하', '소', 2459, 37, 37, 27, 46, 48, '초', 320, 299, ['양양', '적벽', '여강']], 
        [47, '진양', '소', 2459, 37, 39, 27, 51, 47, '하북', 310, 75, ['흉노', '하동', '하내', '거록', '계']], 
        [48, '평원', '소', 2459, 41, 43, 27, 49, 50, '하북', 445, 110, ['남피', '제남', '북해']], 
        [49, '회계', '소', 2404, 43, 39, 27, 50, 51, '오월', 485, 390, ['오', '건안', '대']], 
        [50, '천수', '소', 2388, 39, 43, 27, 48, 46, '서북', 76, 140, ['무위', '안정', '오장원', '저']], 
        [51, '평양', '소', 2351, 37, 39, 27, 48, 46, '동이', 606, 97, ['국내', '위례']], 
        [52, '요동', '소', 2350, 42, 39, 27, 47, 50, '동이', 549, 26, ['북평', '오환', '국내']], 
        [53, '거록', '소', 2349, 41, 38, 27, 51, 46, '하북', 355, 95, ['진양', '남피', '업']], 
        [54, '여강', '소', 2324, 37, 39, 27, 48, 44, '오월', 392, 325, ['합비', '단양', '시상', '강하']], 
        [55, '패',   '소', 2302, 43, 39, 27, 46, 47, '중원', 425, 210, ['진류', '복양', '하비', '초']], 
        [56, '자동', '소', 2296, 38, 37, 27, 48, 46, '서촉', 62, 240, ['무도', '한중', '덕양']], 
        [57, '광릉', '소', 2294, 41, 37, 27, 48, 50, '오월', 478, 270, ['하비', '건업']], 
        [58, '장가', '소', 2282, 39, 41, 27, 46, 46, '남중', 136, 395, ['건녕', '영릉', '남만']], 
        [59, '영릉', '소', 2279, 41, 39, 27, 50, 50, '초', 197, 390, ['무릉', '계양', '장가']], 
        [60, '월수', '소', 2262, 40, 39, 27, 46, 50, '남중', 39, 349, ['강주', '주제', '운남']], 
        [61, '건안', '소', 2242, 38, 41, 27, 46, 50, '오월', 440, 420, ['단양', '회계', '산월']], 
        [62, '신야', '소', 2229, 40, 41, 27, 46, 44, '초', 245, 255, ['상용', '완', '양양']], 
        [63, '탐라', '수', 904, 15, 14, 13, 34, 33, '동이', 614, 259, ['웅진', '왜', '오']], 
        [64, '상동', '소', 2214, 39, 39, 27, 50, 46, '초', 285, 405, ['계양', '시상', '남해']], 
        [65, '안정', '소', 2211, 38, 39, 27, 46, 50, '서북', 135, 130, ['강', '무위', '천수', '장안']], 
        [66, '여남', '소', 2199, 42, 37, 27, 51, 51, '중원', 335, 255, ['완', '허창', '초', '수춘']], 
        [67, '홍농', '소', 2198, 38, 42, 27, 46, 50, '서북', 220, 170, ['하동', '낙양', '장안']], 
        [68, '주제', '소', 2197, 39, 41, 27, 49, 46, '남중', 93, 357, ['강주', '월수', '건녕']], 
        [69, '남만', '이', 1902, 27, 28, 13, 34, 36, '남중', 90, 454, ['운남', '장가', '교지']], 
        [70, '산월', '이', 1820, 27, 25, 13, 34, 30, '오월', 373, 447, ['건안', '대', '남해']], 
        [71, '오환', '이', 1722, 28, 25, 13, 34, 32, '동이', 628, 19, ['요동', '국내']], 
        [72, '강',   '이', 1676, 27, 28, 13, 34, 32, '서북', 154, 70, ['무위', '안정']], 
        [73, '왜',   '이', 1652, 26, 25, 13, 34, 33, '동이', 681, 292, ['계림', '탐라', '대']], 
        [74, '흉노', '이', 1651, 27, 27, 13, 32, 30, '서북', 227, 79, ['진양', '하동']], 
        [75, '저',   '이', 1566, 27, 28, 13, 34, 34, '서북', 24, 123, ['무위', '천수', '무도']], 
        [76, '호로', '관', 766, 11, 13, 13, 76, 77, '중원', 317, 182, ['낙양', '진류', '허창']], 
        [77, '오장원', '진', 804, 13, 12, 13, 33, 32, '서북', 104, 175, ['천수', '장안', '한중']], 
        [78, '적벽', '수', 894, 15, 14, 13, 34, 33, '오월', 335, 330, ['강하', '강릉', '시상']]
    ];
}