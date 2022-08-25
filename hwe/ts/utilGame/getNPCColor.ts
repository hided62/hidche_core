export function getNPCColor(
  npcType: number
): "skyblue" | "cyan" | "deepskyblue" | "darkcyan" | "mediumaquamarine" | undefined {
  if (npcType == 6) {
    return "mediumaquamarine";
  }
  if (npcType == 5) {
    return "darkcyan";
  }
  if (npcType == 4) {
    return "deepskyblue";
  }
  if (npcType >= 2) {
    return "cyan";
  }
  if (npcType == 1) {
    return "skyblue";
  }
  return undefined;
}
