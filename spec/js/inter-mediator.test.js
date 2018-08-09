const assert = require('power-assert');
import * as IM from '../../src/js/INTER-Mediator'

test('INTERMediator.separator', () => {
  assert(IM.INTERMediator.separator === '@');
});
