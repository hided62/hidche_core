
import { clamp } from "lodash-es";

export const TECH_LEVEL_STEP = 1000;

export function isTechLimited(startYear: number, year: number, tech: number, maxTechLevel: number, initialAllowedTechLevel: number, techLevelIncYear: number): boolean {
  const relMaxTech = getMaxRelativeTechLevel(startYear, year, maxTechLevel, initialAllowedTechLevel, techLevelIncYear);
  const techLevel = convTechLevel(tech, maxTechLevel);

  return techLevel >= relMaxTech;
}

export function convTechLevel(tech: number, maxTechLevel: number): number{
  return clamp(Math.floor(tech / TECH_LEVEL_STEP), 0, maxTechLevel);
}


export function getMaxRelativeTechLevel(startYear: number, year: number, maxTechLevel: number, initialAllowedTechLevel: number, techLevelIncYear: number): number {
  const relYear = year - startYear;
  return clamp(Math.floor(relYear / techLevelIncYear) + initialAllowedTechLevel, 1, maxTechLevel);
}