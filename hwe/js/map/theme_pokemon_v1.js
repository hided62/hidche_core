function getCityPosition(){
    return {"1":["태초마을",169,321],"2":["상록시티",169,231],"3":["회색시티",174,104],"4":["달맞이산",297,94],"5":["블루시티",452,92],"6":["갈색시티",451,278],"7":["보라타운",627,180],"8":["무지개시티",343,180],"9":["연분홍시티",357,377],"10":["노랑시티",451,180],"11":["홍련마을",169,454],"12":["챔피언로드(입구)",90,231],"13":["챔피언로드(동굴)",90,180],"14":["석영고원",90,29],"15":["사이클링로드(입구)",252,180],"16":["사이클링로드(출구)",252,377],"17":["관동15로",451,377],"18":["무인발전소",627,94],"19":["IC",627,278],"20":["관동13로",627,344],"21":["블루시티동굴",451,32],"22":["이수재의집",572,32],"23":["쌍둥이섬",263,454]};
}

function formatCityInfo(city) {
            

    var regionMap = {"관동 지방":1,"1":"관동 지방"};

    var levelMap = {"수":1,"1":"수","진":2,"2":"진","관":3,"3":"관","이":4,"4":"이","소":5,"5":"소","중":6,"6":"중","대":7,"7":"대","특":8,"8":"특"};
    
    var region_str = regionMap[city.region];
    var level_str = levelMap[city.level];
    
    city.text = '【' + region_str + '|' + level_str + '】' + city.name;
    city.region_str = region_str;
    city.level_str = level_str;

    return city;
}