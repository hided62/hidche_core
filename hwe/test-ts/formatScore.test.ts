import chai, { assert } from "chai";
import _ from "lodash-es";
import { formatConnectScore } from "../ts/utilGame/formatConnectScore";
import { formatDexLevel } from "../ts/utilGame/formatDexLevel";
import { formatDefenceTrain } from "../ts/utilGame/formatDefenceTrain";
import { formatHonor } from "../ts/utilGame/formatHonor";

describe("formatConnectScore", () => {
  it("connectScoreEqual", () => {
    assert.equal(formatConnectScore(0), "안함");
    assert.equal(formatConnectScore(50), "무관심");
    assert.equal(formatConnectScore(100), "보통");
    assert.equal(formatConnectScore(200), "가끔");
    assert.equal(formatConnectScore(400), "자주");
    assert.equal(formatConnectScore(800), "열심");
    assert.equal(formatConnectScore(1600), "중독");
    assert.equal(formatConnectScore(3200), "폐인");
    assert.equal(formatConnectScore(6400), "경고");
    assert.equal(formatConnectScore(12800), "헐...");
  });

  it("connectScore+1", () => {
    assert.equal(formatConnectScore(1), "안함");
    assert.equal(formatConnectScore(51), "무관심");
    assert.equal(formatConnectScore(101), "보통");
    assert.equal(formatConnectScore(201), "가끔");
    assert.equal(formatConnectScore(401), "자주");
    assert.equal(formatConnectScore(801), "열심");
    assert.equal(formatConnectScore(1601), "중독");
    assert.equal(formatConnectScore(3201), "폐인");
    assert.equal(formatConnectScore(6401), "경고");
    assert.equal(formatConnectScore(12801), "헐...");
  });

  it("connectScoreF-1", () => {
    assert.equal(formatConnectScore(49), "안함");
    assert.equal(formatConnectScore(99), "무관심");
    assert.equal(formatConnectScore(199), "보통");
    assert.equal(formatConnectScore(399), "가끔");
    assert.equal(formatConnectScore(799), "자주");
    assert.equal(formatConnectScore(1599), "열심");
    assert.equal(formatConnectScore(3199), "중독");
    assert.equal(formatConnectScore(6399), "폐인");
    assert.equal(formatConnectScore(11799), "경고");
    assert.equal(formatConnectScore(20000), "헐...");
  });
});

describe("formatDexLevel", () => {
  it ("dexLevelEqual", () => {
    assert.deepEqual(formatDexLevel(0      ), {level:0, 'color':'navy',        name:'F-'});
    assert.deepEqual(formatDexLevel(350    ), {level:1, 'color':'navy',        name:'F'});
    assert.deepEqual(formatDexLevel(1375   ), {level:2, 'color':'navy',        name:'F+'});
    assert.deepEqual(formatDexLevel(3500   ), {level:3, 'color':'skyblue',     name:'E-'});
    assert.deepEqual(formatDexLevel(7125   ), {level:4, 'color':'skyblue',     name:'E'});
    assert.deepEqual(formatDexLevel(12650  ), {level:5, 'color':'skyblue',     name:'E+'});
    assert.deepEqual(formatDexLevel(20475  ), {level:6, 'color':'seagreen',    name:'D-'});
    assert.deepEqual(formatDexLevel(31000  ), {level:7, 'color':'seagreen',    name:'D'});
    assert.deepEqual(formatDexLevel(44625  ), {level:8, 'color':'seagreen',    name:'D+'});
    assert.deepEqual(formatDexLevel(61750  ), {level:9, 'color':'teal',        name:'C-'});
    assert.deepEqual(formatDexLevel(82775  ), {level:10, 'color':'teal',       name:'C'});
    assert.deepEqual(formatDexLevel(108100 ), {level:11, 'color':'teal',       name:'C+'});
    assert.deepEqual(formatDexLevel(138125 ), {level:12, 'color':'limegreen',  name:'B-'});
    assert.deepEqual(formatDexLevel(173250 ), {level:13, 'color':'limegreen',  name:'B'});
    assert.deepEqual(formatDexLevel(213875 ), {level:14, 'color':'limegreen',  name:'B+'});
    assert.deepEqual(formatDexLevel(260400 ), {level:15, 'color':'darkorange', name:'A-'});
    assert.deepEqual(formatDexLevel(313225 ), {level:16, 'color':'darkorange', name:'A'});
    assert.deepEqual(formatDexLevel(372750 ), {level:17, 'color':'darkorange', name:'A+'});
    assert.deepEqual(formatDexLevel(439375 ), {level:18, 'color':'tomato',     name:'S-'});
    assert.deepEqual(formatDexLevel(513500 ), {level:19, 'color':'tomato',     name:'S'});
    assert.deepEqual(formatDexLevel(595525 ), {level:20, 'color':'tomato',     name:'S+'});
    assert.deepEqual(formatDexLevel(685850 ), {level:21, 'color':'darkviolet', name:'Z-'});
    assert.deepEqual(formatDexLevel(784875 ), {level:22, 'color':'darkviolet', name:'Z'});
    assert.deepEqual(formatDexLevel(893000 ), {level:23, 'color':'darkviolet', name:'Z+'});
    assert.deepEqual(formatDexLevel(1010625), {level:24, 'color': 'gold',      name:'EX-'});
    assert.deepEqual(formatDexLevel(1138150), {level:25, 'color': 'gold',      name:'EX'});
    assert.deepEqual(formatDexLevel(1275975), {level:26, 'color': 'white',     name:'EX+'});
  });

  it ("dexLevel+1", () => {
    assert.deepEqual(formatDexLevel(1      ), {level:0, 'color':'navy',        name:'F-'});
    assert.deepEqual(formatDexLevel(351    ), {level:1, 'color':'navy',        name:'F'});
    assert.deepEqual(formatDexLevel(1376   ), {level:2, 'color':'navy',        name:'F+'});
    assert.deepEqual(formatDexLevel(3501   ), {level:3, 'color':'skyblue',     name:'E-'});
    assert.deepEqual(formatDexLevel(7126   ), {level:4, 'color':'skyblue',     name:'E'});
    assert.deepEqual(formatDexLevel(12651  ), {level:5, 'color':'skyblue',     name:'E+'});
    assert.deepEqual(formatDexLevel(20476  ), {level:6, 'color':'seagreen',    name:'D-'});
    assert.deepEqual(formatDexLevel(31001  ), {level:7, 'color':'seagreen',    name:'D'});
    assert.deepEqual(formatDexLevel(44626  ), {level:8, 'color':'seagreen',    name:'D+'});
    assert.deepEqual(formatDexLevel(61751  ), {level:9, 'color':'teal',        name:'C-'});
    assert.deepEqual(formatDexLevel(82776  ), {level:10, 'color':'teal',       name:'C'});
    assert.deepEqual(formatDexLevel(108101 ), {level:11, 'color':'teal',       name:'C+'});
    assert.deepEqual(formatDexLevel(138126 ), {level:12, 'color':'limegreen',  name:'B-'});
    assert.deepEqual(formatDexLevel(173251 ), {level:13, 'color':'limegreen',  name:'B'});
    assert.deepEqual(formatDexLevel(213876 ), {level:14, 'color':'limegreen',  name:'B+'});
    assert.deepEqual(formatDexLevel(260401 ), {level:15, 'color':'darkorange', name:'A-'});
    assert.deepEqual(formatDexLevel(313226 ), {level:16, 'color':'darkorange', name:'A'});
    assert.deepEqual(formatDexLevel(372751 ), {level:17, 'color':'darkorange', name:'A+'});
    assert.deepEqual(formatDexLevel(439376 ), {level:18, 'color':'tomato',     name:'S-'});
    assert.deepEqual(formatDexLevel(513501 ), {level:19, 'color':'tomato',     name:'S'});
    assert.deepEqual(formatDexLevel(595526 ), {level:20, 'color':'tomato',     name:'S+'});
    assert.deepEqual(formatDexLevel(685851 ), {level:21, 'color':'darkviolet', name:'Z-'});
    assert.deepEqual(formatDexLevel(784876 ), {level:22, 'color':'darkviolet', name:'Z'});
    assert.deepEqual(formatDexLevel(893001 ), {level:23, 'color':'darkviolet', name:'Z+'});
    assert.deepEqual(formatDexLevel(1010626), {level:24, 'color': 'gold',      name:'EX-'});
    assert.deepEqual(formatDexLevel(1138151), {level:25, 'color': 'gold',      name:'EX'});
    assert.deepEqual(formatDexLevel(1275976), {level:26, 'color': 'white',     name:'EX+'});
  });
});

describe('formatDefenceTrain', ()=>{
  it('defenceTrainEqual', ()=>{
    assert.equal(formatDefenceTrain(0  ), "△");
    assert.equal(formatDefenceTrain(60 ), "○");
    assert.equal(formatDefenceTrain(80 ), "◎");
    assert.equal(formatDefenceTrain(90 ), "☆");
    assert.equal(formatDefenceTrain(999), "×");
  });

  it('defenceTrain+1', ()=>{
    assert.equal(formatDefenceTrain(1  ), "△");
    assert.equal(formatDefenceTrain(61 ), "○");
    assert.equal(formatDefenceTrain(81 ), "◎");
    assert.equal(formatDefenceTrain(91 ), "☆");
    assert.equal(formatDefenceTrain(1000), "×");
  });
});

describe('formatHonor', ()=>{
  it('honorEqual', ()=>{
    assert.equal(formatHonor(0    ), '전무');
    assert.equal(formatHonor(640  ), '무명');
    assert.equal(formatHonor(2560 ), '신동');
    assert.equal(formatHonor(5760 ), '약간');
    assert.equal(formatHonor(10240), '평범');
    assert.equal(formatHonor(16000), '지역적');
    assert.equal(formatHonor(23040), '전국적');
    assert.equal(formatHonor(31360), '세계적');
    assert.equal(formatHonor(40960), '유명');
    assert.equal(formatHonor(45000), '명사');
    assert.equal(formatHonor(51840), '호걸');
    assert.equal(formatHonor(55000), '효웅');
    assert.equal(formatHonor(64000), '영웅');
    assert.equal(formatHonor(77440), '구세주');
  });

  it('honor+1', ()=>{
    assert.equal(formatHonor(1    ), '전무');
    assert.equal(formatHonor(641  ), '무명');
    assert.equal(formatHonor(2561 ), '신동');
    assert.equal(formatHonor(5761 ), '약간');
    assert.equal(formatHonor(10241), '평범');
    assert.equal(formatHonor(16001), '지역적');
    assert.equal(formatHonor(23041), '전국적');
    assert.equal(formatHonor(31361), '세계적');
    assert.equal(formatHonor(40961), '유명');
    assert.equal(formatHonor(45001), '명사');
    assert.equal(formatHonor(51841), '호걸');
    assert.equal(formatHonor(55001), '효웅');
    assert.equal(formatHonor(64001), '영웅');
    assert.equal(formatHonor(77441), '구세주');
  });
});