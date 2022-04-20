
import { clamp } from "lodash";

export const TECH_LEVEL_YEAR_GAP = 5;
export const TECH_LEVEL_STEP = 1000;

export function isTechLimited(startYear: number, year: number, tech: number, maxTechLevel: number): boolean {
  const relMaxTech = getMaxRelativeTechLevel(startYear, year, maxTechLevel);
  const techLevel = convTechLevel(tech, maxTechLevel);

  return techLevel >= relMaxTech;
}

export function convTechLevel(tech: number, maxTechLevel: number): number{
  return clamp(Math.floor(tech / TECH_LEVEL_STEP), 0, maxTechLevel);
}


export function getMaxRelativeTechLevel(startYear: number, year: number, maxTechLevel: number): number {
  const relYear = year - startYear;
  return clamp(Math.floor(relYear / TECH_LEVEL_YEAR_GAP) + 1, 1, maxTechLevel);
}