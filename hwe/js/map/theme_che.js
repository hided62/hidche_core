

function getCityPosition(){
    return {"1":["업",345,130],"2":["허창",330,215],"3":["낙양",275,180],"4":["장안",145,165],"5":["성도",25,290],"6":["양양",255,290],"7":["건업",505,305],"8":["북평",465,65],"9":["남피",395,95],"10":["완",270,235],"11":["수춘",395,270],"12":["서주",440,250],"13":["강릉",245,335],"14":["장사",255,375],"15":["시상",360,360],"16":["위례",620,145],"17":["계",365,35],"18":["복양",410,170],"19":["진류",365,185],"20":["여남",330,260],"21":["하비",480,235],"22":["서량",25,50],"23":["하내",230,150],"24":["한중",135,205],"25":["상용",185,225],"26":["덕양",85,275],"27":["강주",70,310],"28":["건녕",80,400],"29":["남해",245,480],"30":["계양",230,400],"31":["오",510,345],"32":["평양",590,100],"33":["사비",605,205],"34":["계림",655,200],"35":["진양",295,60],"36":["평원",440,115],"37":["북해",470,155],"38":["초",365,230],"39":["패",430,220],"40":["천수",70,105],"41":["안정",95,145],"42":["홍농",210,175],"43":["하변",45,190],"44":["자동",75,245],"45":["영안",115,295],"46":["귀양",90,360],"47":["주시",30,345],"48":["운남",30,415],"49":["남영",135,405],"50":["교지",130,480],"51":["신야",250,260],"52":["강하",315,295],"53":["무릉",195,355],"54":["영릉",190,395],"55":["상동",210,435],"56":["여강",380,315],"57":["회계",480,395],"58":["고창",350,405],"59":["대",450,480],"60":["안평",530,80],"61":["졸본",570,65],"62":["이도",680,260],"63":["강",95,35],"64":["저",25,120],"65":["흉노",180,95],"66":["남만",80,455],"67":["산월",425,430],"68":["오환",610,20],"69":["왜",680,320],"70":["호관",285,140],"71":["호로",285,205],"72":["사곡",240,175],"73":["함곡",180,175],"74":["사수",310,185],"75":["양평",90,220],"76":["가맹",45,225],"77":["역경",410,65],"78":["계교",405,135],"79":["동황",515,145],"80":["관도",340,165],"81":["정도",400,210],"82":["합비",435,285],"83":["광릉",490,275],"84":["적도",130,75],"85":["가정",40,160],"86":["기산",110,180],"87":["면죽",35,255],"88":["이릉",215,295],"89":["장판",280,315],"90":["백랑",530,30],"91":["적벽",330,325],"92":["파양",430,350],"93":["탐라",605,260],"94":["유구",625,435]};
}

function formatCityInfo(city) {
            

    var regionMap = {"하북":1,"1":"하북","중원":2,"2":"중원","서북":3,"3":"서북","서촉":4,"4":"서촉","남중":5,"5":"남중","초":6,"6":"초","오월":7,"7":"오월","동이":8,"8":"동이"};

    var levelMap = {"수":1,"1":"수","진":2,"2":"진","관":3,"3":"관","이":4,"4":"이","소":5,"5":"소","중":6,"6":"중","대":7,"7":"대","특":8,"8":"특"};
    
    var region_str = regionMap[city.region];
    var level_str = levelMap[city.level];
    
    city.text = '【' + region_str + '|' + level_str + '】' + city.name;
    city.region_str = region_str;
    city.level_str = level_str;

    return city;
}