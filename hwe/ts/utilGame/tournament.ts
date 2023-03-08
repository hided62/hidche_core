import { clamp } from 'lodash-es';
export function calcTournamentTerm(turnTerm: number): number{
  return clamp(turnTerm, 5, 120);
}