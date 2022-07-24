import type { GameConstStore } from "@/GameConstStore";

interface WithCityID {
  city: number;
}

export function formatCityName(target: number | WithCityID, gameConst: GameConstStore): string {
  const cityID = typeof target === "number" ? target : target.city;
  const city = gameConst.cityConst[cityID];
  if (city === undefined) {
    throw `City ${cityID} not found`;
  }
  return city.name;
}
