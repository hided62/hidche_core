export function resolveGatewayOpenState(
  serverDecision: boolean | undefined,
  openTime: string,
  wallNow: string,
): boolean {
  return serverDecision ?? openTime <= wallNow;
}
