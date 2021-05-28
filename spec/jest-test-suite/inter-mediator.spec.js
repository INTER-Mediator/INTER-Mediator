/**
 * @jest-environment jsdom
 */

const INTERMediator = require('../../src/js/INTER-Mediator')

test('INTERMediator.separator', () => {
  expect(INTERMediator.separator).toBe('@');
});
