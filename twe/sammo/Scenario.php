<?php
namespace sammo;

class Scenario{
    const SCENARIO_PATH = __dir__.'/../scenario';

    private $scenarioIdx;
    private $scenarioPath;

    private $data;

    public function __construct(int $scenarioIdx){
        $scenarioPath = SCENARIO_PATH."/scenario_{$scenarioIdx}.json";

        $this->scenarioIdx = $scenarioIdx;
        $this->scenarioPath = $scenarioPath;

        $this->data = Json::decode(file_get_contents($scenarioPath));
    }

    public function getScenarioIdx(){
        return $this->scenarioIdx;
    }

    /**
     * @return \sammo\Scenario[]
     */
    public static function getAllScenarios(){
        $result = [];

        foreach(glob(SCENARIO_PATH.'/scenario_*.json') as $scenarioPath){
            $scenarioName = pathinfo(basename($scenarioName), PATHINFO_FILENAME);
            $scenarioIdx = Util::array_last(explode('_', $scenarioName));
            $scenarioIdx = Util::toInt($scenarioIdx);

            if($scenarioIdx === null){
                continue;
            }

            $result[$scenarioIdx] = new Scenario($scenarioIdx);
        }
        return $result;
    }
}