import assert from 'assert';
import { resolveGatewayOpenState } from '../ts/gateway/resolveGatewayOpenState';

describe('resolveGatewayOpenState', () => {
  it('keeps the logical server decision even when projected dates disagree with wall time', () => {
    assert.strictEqual(resolveGatewayOpenState(false, '2000-01-01 00:00:00', '2026-08-04 00:00:00'), false);
    assert.strictEqual(resolveGatewayOpenState(true, '2042-01-01 00:00:00', '2026-08-04 00:00:00'), true);
  });

  it('falls back to legacy wall time only when isOpen is absent', () => {
    assert.strictEqual(resolveGatewayOpenState(undefined, '2026-08-03 00:00:00', '2026-08-04 00:00:00'), true);
    assert.strictEqual(resolveGatewayOpenState(undefined, '2026-08-05 00:00:00', '2026-08-04 00:00:00'), false);
  });
});
