

function getCityPosition(){
    return _tK_cityPosition_;
}

function formatCityInfo(city) {
            

    var regionMap = _tK_regionMap_;

    var levelMap = _tK_levelMap_;
    
    var region_str = regionMap[city.region];
    var level_str = levelMap[city.level];
    
    city.text = region_str + level_str + city.name;
    city.region_str = region_str;
    city.level_str = level_str;

    return city;
}