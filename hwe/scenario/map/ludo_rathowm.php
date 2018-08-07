<?php

namespace sammo;

class CityConst extends CityConstBase{
    public static $regionMap = [
        '호넷 마인령'=>1,1=>'호넷 마인령',
        '케이브리스 마인령'=>2,2=>'케이브리스 마인령',
        '카라의 숲'=>3,3=>'카라의 숲',
        '샹그릴라'=>4,4=>'샹그릴라',
        '제스 북부'=>5,5=>'제스 북부',
        '제스 남부'=>6,6=>'제스 남부',
        '헬만 서부'=>7,7=>'헬만 서부',
        '헬만 동부'=>8,8=>'헬만 동부',
        '리자스 남부'=>9,9=>'리자스 남부',
        '리자스 북부'=>10,10=>'리자스 북부',
        'JAPAN'=>11,11=>'JAPAN',
        '자유도시 동부'=>12,12=>'자유도시 동부',
        '자유도시 서부'=>13,13=>'자유도시 서부',
        'AL교'=>14,14=>'AL교',
    ];

    protected static $initCity = [
        //id,  도시,            규모, 인구, 농,  상, 치, 성, 수,(x100)지역,             x,   y, 연결도시
        [ 1, '구 마왕성',       '특', 8000,120,120,100,120,120, '호넷 마인령',         45, 40, ['악의 탑','한탄의 협곡','유황의 숲']],
        [ 2, '악의 탑',         '이', 3500, 40, 40, 20, 40, 40, '호넷 마인령',       100,  60, ['반리요새 A','구 마왕성']],
        [ 3, '실키의 성',       '중', 5500, 80, 80, 60, 80, 80, '호넷 마인령',        45, 270, ['유황의 숲','카스케도바우','죽음의 대지']],
        [ 4, '카스케도바우',    '중', 5500, 80, 80, 60, 80, 80, '호넷 마인령',       120, 276, ['실키의 성','케셀링크의 성']],
        [ 5, '붉은 집',         '수', 2500, 20, 20, 20, 40, 40, '케이브리스 마인령',  70, 335, ['케이브리스의 성','케셀링크의 성']],
        [ 6, '케셀링크의 성',   '대', 7000,100,100, 80,100,100, '케이브리스 마인령', 135, 338, ['붉은 집','메이드의 묘지','카스케도바우','뼈의 숲']],
        [ 7, '케이브리스의 성', '특', 8000,120,120,100,120,120, '케이브리스 마인령',  37, 384, ['붉은 집','푸른 집','죽음의 대지']],
        [ 8, '푸른 집',         '수', 2500, 20, 20, 20, 40, 40, '케이브리스 마인령',  59, 446, ['케이브리스의 성','메이드의 묘지','카미라의 성']],
        [ 9, '한탄의 협곡',     '소', 4500, 60, 60, 40, 60, 60, '호넷 마인령',        97, 152, ['구 마왕성','반리요새 B']],
        [10, '뼈의 숲',         '소', 4500, 60, 60, 40, 60, 60, '케이브리스 마인령', 160, 386, ['케셀링크의 성','마지노 A']],
        [11, '유황의 숲',       '소', 4500, 60, 60, 40, 60, 60, '호넷 마인령',        72, 209, ['구 마왕성','실키의 성']],
        [12, '카미라의 성',     '대', 7000,100,100, 80,100,100, '케이브리스 마인령', 130, 460, ['푸른 집','갈라놓은 숲']],
        [13, '메이드의 묘지',   '이', 3500, 40, 40, 20, 40, 40, '케이브리스 마인령', 102, 380, ['케셀링크의 성','푸른 집']],
        [14, '갈라놓은 숲',     '소', 4500, 60, 60, 40, 60, 60, '케이브리스 마인령', 186, 472, ['카미라의 성','마지노 B']],
        [15, '죽음의 대지',     '이', 3500, 40, 40, 20, 40, 40, '케이브리스 마인령',  10, 320, ['케이브리스의 성','실키의 성']],
        [16, '녹의 마을',       '소', 4500, 60, 60, 40, 60, 60, '카라의 숲',         202, 290, ['마지노 A','파리티라온']],
        [17, '펜실 카우',       '이', 3500, 40, 40, 20, 40, 40, '카라의 숲',         297, 267, ['파리티라온','라보리']],
        [18, '샹그릴라',        '대', 7000,100,100, 80,100,100, '샹그릴라',          374, 272, ['스도리 13','보르고 Z','사바사바','릿치']],
        [19, '파리티라온',      '중', 5500, 80, 80, 60, 80, 80, '제스 북부',         259, 332, ['녹의 마을','마지노 A','왕자의 탑','펜실 카우']],
        [20, '왕자의 탑',       '소', 4500, 60, 60, 40, 60, 60, '제스 북부',         259, 367, ['파리티라온','도약의 탑','일요일의 탑','라그나로크 아크']],
        [21, '도약의 탑',       '소', 4500, 60, 60, 40, 60, 60, '제스 남부',         265, 406, ['왕자의 탑','라그나로크 아크','탄창의 탑','마크']],
        [22, '일요일의 탑',     '소', 4500, 60, 60, 40, 60, 60, '제스 북부',         324, 358, ['왕자의 탑','라그나로크 아크','탄창의 탑','나가르모르']],
        [23, '탄창의 탑',       '소', 4500, 60, 60, 40, 60, 60, '제스 남부',         331, 409, ['도약의 탑','라그나로크 아크','일요일의 탑','올드제스']],
        [24, '라그나로크 아크', '특', 8000,120,120,100,120,120, '제스 북부',         295, 385, ['왕자의 탑','도약의 탑','도약의 탑','탄창의 탑','일요일의 탑']],
        [25, '알텐누',          '중', 5500, 80, 80, 60, 80, 80, '제스 남부',         293, 482, ['마크','마지노 B']],
        [26, '마크',            '중', 5500, 80, 80, 60, 80, 80, '제스 남부',         288, 436, ['알텐누','호박성','도약의 탑']],
        [27, '호박성',          '이', 3500, 40, 40, 20, 40, 40, '제스 남부',         328, 458, ['마크','올드제스']],
        [28, '올드제스',        '특', 8000,120,120,100,120,120, '제스 남부',         367, 444, ['탄창의 탑','호박성','테이브','카이즈']],
        [29, '테이브',          '소', 4500, 60, 60, 40, 60, 60, '제스 남부',         385, 406, ['이탈리아','올드제스']],
        [30, '이탈리아',        '대', 7000,100,100, 80,100,100, '제스 북부',         375, 361, ['테이브','사바사바','아담의탑']],
        [31, '나가르모르',      '중', 5500, 80, 80, 60, 80, 80, '제스 북부',         322, 322, ['일요일의 탑','사바사바']],
        [32, '사바사바',        '대', 7000,100,100, 80,100,100, '제스 북부',         375, 324, ['나가르모르','이탈리아','샹그릴라']],
        [33, '아담의탑',        '진', 2500, 20, 20, 20, 40, 40, '제스 북부',         420, 340, ['이탈리아','파라파라성채']],
        [34, '마지노 A',        '관', 2500, 20, 20, 20, 90, 90, '제스 북부',         215, 365, ['뼈의 숲','녹의 마을','파리티라온']],
        [35, '마지노 B',        '관', 2500, 20, 20, 20, 90, 90, '제스 남부',         243, 477, ['갈라놓은 숲','알텐누']],
        [36, '이코마',          '중', 5500, 80, 80, 60, 80, 80, '헬만 서부',         211,  60, ['반리요새 A','거대전함','워크랜드','로렌그라드']],
        [37, '워크랜드',        '소', 4500, 60, 60, 40, 60, 60, '헬만 서부',         177,  90, ['이코마','로렌그라드']],
        [38, '로렌그라드',      '대', 7000,100,100, 80,100,100, '헬만 서부',         200, 126, ['파르나스','랑그바우','이코마','반리요새 B','워크랜드']],
        [39, '파르나스',        '중', 5500, 80, 80, 60, 80, 80, '헬만 서부',         204, 171, ['로렌그라드','세미타포']],
        [40, '라보리',          '중', 5500, 80, 80, 60, 80, 80, '헬만 서부',         225, 219, ['펜실 카우','스도리 17']],
        [41, '랑그바우',        '특', 8000,120,120,100,120,120, '헬만 서부',         263, 135, ['로렌그라드','마이크로그라드']],
        [42, '거대전함',        '이', 3500, 40, 40, 20, 40, 40, '헬만 서부',         273,  21, ['이코마','파스','호라가의  탑','시베리아']],
        [43, '세미타포',        '소', 4500, 60, 60, 40, 60, 60, '헬만 서부',         268, 174, ['파르나스','스도리 17']],
        [44, '스도리 17',       '진', 2500, 20, 20, 20, 40, 40, '헬만 서부',         296, 214, ['라보리','세미타포','스도리 13']],
        [45, '스도리 13',       '진', 2500, 20, 20, 20, 40, 40, '헬만 서부',         359, 217, ['샹그릴라','폰','스도리 17','코사크','보르고 Z']],
        [46, '마이크로그라드',  '소', 4500, 60, 60, 40, 60, 60, '헬만 서부',         316, 166, ['폰','랑그바우','스도리 10']],
        [47, '폰',              '중', 5500, 80, 80, 60, 80, 80, '헬만 서부',         377, 187, ['마이크로그라드','스도리 13','코사크']],
        [48, '파스',            '중', 5500, 80, 80, 60, 80, 80, '헬만 서부',         286,  90, ['거대전함','큐로브','스도리 10']],
        [49, '큐로브',          '소', 4500, 60, 60, 40, 60, 60, '헬만 서부',         341,  96, ['파스','소로토이']],
        [50, '스도리 10',       '진', 2500, 20, 20, 20, 40, 40, '헬만 서부',         337, 137, ['파스','로제스그라드','마이크로그라드']],
        [51, '소로토이',        '소', 4500, 60, 60, 40, 60, 60, '헬만 동부',         382, 119, ['큐로브','바바로프스']],
        [52, '로제스그라드',    '특', 8000,120,120,100,120,120, '헬만 동부',         401, 148, ['스도리 10','바바로프스','아크그라드']],
        [53, '코사크',          '중', 5500, 80, 80, 60, 80, 80, '헬만 동부',         419, 204, ['스도리 13','보르고 Z','폰']],
        [54, '아크그라드',      '소', 4500, 60, 60, 40, 60, 60, '헬만 동부',         452, 179, ['로그 B','로제스그라드','야크트크']],
        [55, '야크트크',        '소', 4500, 60, 60, 40, 60, 60, '헬만 동부',         437, 129, ['아크그라드','바바로프스','고라크']],
        [56, '시베리아',        '대', 7000,100,100, 80,100,100, '헬만 동부',         394,  55, ['바바로프스','호라가의  탑','거대전함']],
        [57, '바바로프스',      '중', 5500, 80, 80, 60, 80, 80, '헬만 동부',         418,  89, ['시베리아','야크트크','소로토이','로제스그라드']],
        [58, '호라가의  탑',    '관', 2500, 20, 20, 20, 90, 90, '헬만 동부',         561,  20, ['시베리아','거대전함','떨어진 궁도']],
        [59, '고라크',          '중', 5500, 80, 80, 60, 80, 80, '헬만 동부',         478, 146, ['블라디보스토크','로그 A','야크트크']],
        [60, '블라디보스토크',  '이', 3500, 40, 40, 20, 40, 40, '헬만 동부',         500, 109, ['고라크','로그 A']],
        [61, '보르고 Z',        '관', 2500, 20, 20, 20, 90, 90, '헬만 동부',         436, 245, ['코사크','스도리 13','로그 B', '샹그릴라']],
        [62, '로그 A',          '관', 2500, 20, 20, 20, 90, 90, '헬만 동부',         506, 183, ['고라크','블라디보스토크','로그 B','스케일']],
        [63, '로그 B',          '관', 2500, 20, 20, 20, 90, 90, '헬만 동부',         475, 216, ['아크그라드','로그 A','보르고 Z','마우네스']],
        [64, '반리요새 A',      '진', 2500, 20, 20, 20, 40, 40, '헬만 서부',         149,  18, ['악의 탑','이코마']],
        [65, '반리요새 B',      '진', 2500, 20, 20, 20, 40, 40, '헬만 서부',         151, 153, ['로렌그라드','한탄의 협곡']],
        [66, '파라파라성채',    '진', 2500, 20, 20, 20, 40, 40, '리자스 남부',       449, 135, ['아담의탑','릿치']],
        [67, '릿치',            '중', 5500, 80, 80, 60, 80, 80, '리자스 남부',       476, 288, ['푸아','마우네스','파라파라성채','샹그릴라']],
        [68, '푸아',            '소', 4500, 60, 60, 40, 60, 60, '리자스 남부',       528, 289, ['릿치','록아스']],
        [69, '마우네스',        '소', 4500, 60, 60, 40, 60, 60, '리자스 남부',       502, 250, ['릿치','로그 B','노스']],
        [70, '스케일',          '대', 7000,100,100, 80,100,100, '리자스 북부',       542, 198, ['로그 A','아란','노스']],
        [71, '아란',            '중', 5500, 80, 80, 60, 80, 80, '리자스 북부',       596, 154, ['떨어진 궁도','포나라','스케일','브리테리아']],
        [72, '떨어진 궁도',     '이', 3500, 40, 40, 20, 40, 40, '리자스 북부',       598, 133, ['호라가의  탑','아란']],
        [73, '포나라',          '소', 4500, 60, 60, 40, 60, 60, '리자스 북부',       640, 168, ['아란','브리테리아','웨스']],
        [74, '브리테리아',      '대', 7000,100,100, 80,100,100, '리자스 북부',       587, 205, ['아란','웨스','포나라']],
        [75, '파란치',          '소', 4500, 60, 60, 40, 60, 60, '리자스 북부',       667, 229, ['웨스','이스']],
        [76, '노스',            '중', 5500, 80, 80, 60, 80, 80, '리자스 남부',       563, 255, ['스케일','마우네스','오크','리자스성']],
        [77, '오크',            '중', 5500, 80, 80, 60, 80, 80, '리자스 남부',       577, 295, ['노스','사우스','지오']],
        [78, '리자스성',        '특', 8000,120,120,100,120,120, '리자스 남부',       611, 245, ['노스','사우스','웨스']],
        [79, '사우스',          '소', 4500, 60, 60, 40, 60, 60, '리자스 남부',       618, 285, ['리자스성','오크','이스']],
        [80, '이스',            '중', 5500, 80, 80, 60, 80, 80, '리자스 남부',       656, 269, ['사우스','파란치','오크스']],
        [81, '오크스',          '중', 5500, 80, 80, 60, 80, 80, '리자스 남부',       650, 311, ['이스','한나','오슈']],
        [82, '웨스',            '중', 5500, 80, 80, 60, 80, 80, '리자스 북부',       632, 212, ['리자스성','포나라','파란치','브리테리아']],
        [83, '오슈',            '이', 3500, 40, 40, 20, 40, 40, 'JAPAN',             688, 308, ['오크스','오와리']],
        [84, '오와리',          '특', 8000,120,120,100,120,120, 'JAPAN',             679, 394, ['오슈','모로코']],
        [85, '모로코',          '수', 2500, 20, 20, 20, 40, 40, 'JAPAN',             627, 450, ['오와리','포르투갈']],
        [86, '지오',            '중', 5500, 80, 80, 60, 80, 80, '자유도시 동부',     578, 333, ['오크','렛드','M랜드']],
        [87, '한나',            '소', 4500, 60, 60, 40, 60, 60, '자유도시 동부',     632, 342, ['오크스','M랜드']],
        [88, 'M랜드',           '대', 7000,100,100, 80,100,100, '자유도시 동부',     616, 379, ['지오','한나','렛드','포르투갈']],
        [89, '포르투갈',        '중', 5500, 80, 80, 60, 80, 80, '자유도시 동부',     599, 422, ['M랜드','투신도시','모로코']],
        [90, '투신도시',        '이', 3500, 40, 40, 20, 40, 40, '자유도시 동부',     553, 415, ['포르투갈','커스텀','파란쵸 왕국']],
        [91, '화성',            '소', 4500, 60, 60, 40, 60, 60, '자유도시 서부',     503, 482, ['커스텀']],
        [92, '커스텀',          '특', 8000,120,120,100,120,120, '자유도시 서부',     506, 416, ['투신도시','지브테리아','라지르','파란쵸 왕국','화성']],
        [93, '라지르',          '소', 4500, 60, 60, 40, 60, 60, '자유도시 서부',     530, 358, ['렛드','커스텀','아이스']],
        [94, '록아스',          '중', 5500, 80, 80, 60, 80, 80, '자유도시 서부',     489, 329, ['아이스','푸아']],
        [95, '지브테리아',      '대', 7000,100,100, 80,100,100, '자유도시 서부',     452, 370, ['아이스','카이즈','커스텀']],
        [96, '파란쵸 왕국',     '이', 3500, 40, 40, 20, 40, 40, '자유도시 동부',     537, 458, ['커스텀','투신도시']],
        [97, '아이스',          '소', 4500, 60, 60, 40, 60, 60, '자유도시 서부',     489, 375, ['록아스','지브테리아','라지르']],
        [98, '렛드',            '중', 5500, 80, 80, 60, 80, 80, '자유도시 서부',     566, 372, ['라지르','M랜드','지오']],
        [99, '카이즈',          '이', 3500, 40, 40, 20, 40, 40, 'AL교',              439, 425, ['올드제스','지브테리아']],
    ];
}