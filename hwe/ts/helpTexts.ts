import { NPCChiefActions, NPCGeneralActions } from "./defs";

export const NPCPriorityBtnHelpMessage: {
  [v in NPCChiefActions | NPCGeneralActions]: string;
} &
  Record<string, string> = {
  불가침제의:
    "군주가 NPC이고, 타국에서 원조를 받았을 때,<br>세입(금/쌀) 대비 원조량에 따라 불가침제의를 합니다.",
  선전포고:
    "군주가 NPC이고, 전쟁중이 아닐 때,<br>주변국중 하나를 골라 선포합니다.<br><br>선포 시점은 다음을 참고합니다.<br>- 인구율<br>- 도시내정률<br>- NPC전투장권장 금 충족률<br>- NPC전투장권장 쌀 충족률<br><br>국력이 낮은 국가를 조금 더 선호합니다.",
  천도: "인구가 많은 곳을 찾아 천도를 시도합니다.<br>영토의 가운데를 선호합니다.<br><br>도시 인구가 충분하다면, 굳이 천도하지는 않습니다.",
  유저장긴급포상:
    "금/쌀이 부족한 유저전투장에게 긴급하게 포상합니다.<br>국고가 권장량보다 적어지더라도 시도합니다.",
  부대전방발령:
    "(작동하지 않음)<br>전투 부대를 접경으로 발령합니다.<br>수도->시작점->도착점 경로를 따릅니다.",
  유저장구출발령:
    "아군 영토에 있지 않은 유저장을 아군 영토로 발령합니다.<br>곧 집합하는 부대에 탑승한 경우는 제외합니다.",
  유저장후방발령:
    "유저전투장 중에<br>- 병력이 충분하지 않고,<br>- 도시의 인구가 제자리 징병할 수 있을 정도로 충분하지 않고,<br>- 부대에 탑승하지 않았다면,<br>인구가 충분한 후방도시로 발령합니다.",
  부대유저장후방발령:
    "접경에 위치한 부대에 탑승한 유저전투장 중에,<br>- 병력이 충분하지 않고,<br>- 첫 턴이 징병턴이며,<br>- 부대장 집합 턴 사이라면,<br>인구가 충분한 후방도시로 발령합니다.<br><br>부대장의 위치와 유저장의 위치가 다르다면 발령하지 않습니다.",
  유저장전방발령:
    "후방에 있는 유저장이<br>- 병력을 가지고 있으며,<br>- 곧 훈련/사기진작이 완료될 것 같으면,<br>전방으로 발령합니다.<br><br>도시 관직이 많이 임명된 도시를 선호합니다.",
  유저장포상:
    "금/쌀이 부족한 유저장에게 포상합니다.<br>유저전투장과 유저내정장은 각각 기준을 따릅니다.<br>국고 권장량을 가급적 지킵니다.",
  부대후방발령:
    "(작동하지 않음)<br>후방 부대가 위치한 도시의 인구가 충분하지 않을 경우,<br>인구가 충분한 도시로 발령합니다.",
  부대구출발령:
    "전투 부대, 후방 부대가 아닌 부대가 아군 영토에 있지 않을 때,<br>전방 도시 중 하나를 골라 발령합니다.",
  NPC긴급포상:
    "금/쌀이 부족한 NPC전투장에게 긴급하게 포상합니다.<br>국고가 권장량보다 '약간' 적어지더라도 시도합니다.",
  NPC구출발령: "아군 영토에 있지 않은 NPC장을 아군 영토로 발령합니다.",
  NPC후방발령:
    "NPC전투장 중에<br>- 병력이 충분하지 않고,<br>- 도시의 인구가 제자리 징병할 수 있을 정도로 충분하지 않고,<br>- 부대에 탑승하지 않았다면,<br>인구가 충분한 후방도시로 발령합니다.",
  NPC포상:
    "금/쌀이 부족한 NPC에게 포상합니다.<br>NPC전투장과 NPC내정장은 각각 기준을 따릅니다.<br>국고 권장량을 가급적 지킵니다.",
  NPC전방발령:
    "후방에 있는 유저장이<br>- 병력을 가지고 있으며,<br>- 곧 훈련/사기진작이 완료될 것 같으면,<br>전방으로 발령합니다.<br><br>도시 관직이 많이 임명된 도시를 선호합니다.",
  유저장내정발령:
    "내정중인 유저장이 위치한 도시의 내정률이 95% 이상이면<br>개발되지 않은 도시로 발령합니다.",
  NPC내정발령:
    "내정중인 NPC장이 위치한 도시의 내정률이 95% 이상이면<br>개발되지 않은 도시로 발령합니다.",
  NPC몰수:
    "국고가 부족하다면 NPC에게서 몰수합니다. 내정NPC장은 국고가 부족하지 않아도 몰수합니다.",

  NPC사망대비:
    "NPC의 사망까지 5턴 이내인 경우, 헌납합니다.<br>헌납할 금쌀이 없다면 물자조달을 수행합니다.",
  귀환: "아국 도시에 있지 않다면 귀환합니다.",
  금쌀구매:
    "전쟁 중에 금쌀의 비율이 크게 차이난다면 금쌀을 거래하여 비슷하게 맞춥니다.<br>금쌀 비율이 적절하는지 판단하는데 살상률을 포함합니다.<br>NPC는 상인이 없어도 금쌀을 구매할 수 있습니다.<br><br>또는 금쌀 한쪽이 지나치게 적은 경우에는 내정 중에도 금쌀을 거래합니다.",
  출병: "충분한 병력과 충분한 훈련/사기를 가지고 있는 경우 출병합니다.<br>접경이 여럿인 경우 무작위로 선택합니다.<br><br>타국과 전쟁중인 경우 공백지로는 출병하지 않습니다.",
  긴급내정:
    "전쟁중에 민심이 70 미만이거나,<br>인구가 제자리 징병이 가능하지 않을 정도로 적을 경우,<br>일정확률로 주민선정과 정착장려를 수행합니다.<br><br>통솔이 높을 수록 수행할 확률이 높습니다.",
  전투준비:
    "충분한 병력을 가지고 있지만 훈련과 사기가 부족한 경우 훈련과 사기진작을 수행합니다.",
  전방워프: "전투장이 충분한 병력을 가지고 있다면 전방으로 이동합니다.",
  NPC헌납:
    "국고가 부족한데 NPC전쟁장이 충분한 금쌀(권장량 대비 1.5배)을 가지고 있다면 일부를 헌납합니다. <br>NPC내정장은 국고가 넉넉하더라도 충분한 금쌀을 가지고 있다면 권장량만 유지하고 헌납합니다.",
  징병: "전쟁 중 병력을 소진하였다면 재 징병합니다.<br><br>기존에 사용한 병종군 중에서 사용가능한 병종을 랜덤하게 선택합니다.<br>고급 병종을 선택할 확률이 조금 더 높습니다.<br><br>NPC의 경우 도시의 인구가 충분하지 않다면 징병을 할 확률이 감소합니다.<br><br>유저장은 최대한 고급병종을 유지하며,<br>유저장 모병이 허용되는 경우 모병을 3회할 수 있다면 모병합니다.",
  후방워프:
    "전쟁 중 병력을 소진하였는데 도시의 인구가 충분하지 않다면,<br>인구가 많은 도시로 이동합니다.",
  전쟁내정:
    "전쟁 중 수행하는 내정입니다.<br>정착장려, 기술연구의 확률이 좀 더 높고,<br>치안강화, 농지개간, 상업투자의 확률이 낮습니다.<br><br>내정이 가능하다 하더라도 전시임을 고려해,<br>30% 확률로 다른 턴을 수행합니다.",
  소집해제:
    "전쟁 중이 아닌 데 병력이 남아있는 경우,<br>3/4 확률로 소집해제합니다.",
  일반내정:
    "도시에서 내정을 수행합니다. 낮은 내정일 수록 수행할 확률이 높습니다.<br>기술 연구는 1등급 이상 뒤쳐지지 않도록 노력합니다.",
  내정워프:
    "도시에서 더이상 내정을 수행할 수 없는 경우,<br>일정확률로 내정이 부족한 다른 도시로 이동합니다.",
};
