const typeMap = ["전력전", "통솔전", "일기토", "설전"];

export function formatTournamentType(type: null | undefined | 0 | 1 | 2 | 3): string {
  if (type === null || type === undefined) {
    return "?";
  }
  return typeMap[type];
}

export type TournamentStepType = {
  availableJoin: boolean;
  state: string;
  nextText: string;
};
const stepMap: TournamentStepType[] = [
  { availableJoin: false, state: "경기 없음", nextText:"" },
  { availableJoin: true, state: "참가 모집중", nextText:"개막시간" },
  { availableJoin: false, state: "예선 진행중", nextText:"다음경기" },
  { availableJoin: false, state: "본선 추첨중", nextText:"다음추첨" },
  { availableJoin: false, state: "본선 진행중", nextText:"다음경기" },
  { availableJoin: false, state: "16강 배정중", nextText:"16강배정" },
  { availableJoin: true, state: "베팅 진행중", nextText:"베팅마감" },
  { availableJoin: false, state: "16강 진행중", nextText:"다음경기" },
  { availableJoin: false, state: "8강 진행중", nextText:"다음경기" },
  { availableJoin: false, state: "4강 진행중", nextText:"다음경기" },
  { availableJoin: false, state: "결승 진행중", nextText:"다음경기" },
];

export function formatTournamentStep(step: null | undefined | 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10): TournamentStepType {
  if (step === null || step === undefined) {
    return stepMap[0];
  }

  return stepMap[step];
}
