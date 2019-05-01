// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement, IMLibQueue */

const IMLibQueue = require('../../node_modules/inter-mediator-queue/index.js')
var counter1 = 0, counter2 = 0, counter3 = 0, counter4 = 0, counter5 = 0, counter6 = 0

test('IMLibQueue Test: should execute immidiately', function () {
  'use strict'
  counter1 = 1
  IMLibQueue.setTask(function (finishProc) {
    console.log('===check point 1-1 ===')
    counter1++
    expect(counter1).toBe(2)
    finishProc()
  }, true)
  IMLibQueue.setTask(function (finishProc) {
    console.log('===check point 1-2 ===')
    counter1++
    expect(counter1).toBe(3)
    finishProc()
  }, true)
  console.log('===check point 1-3 ===')
  expect(counter1).toBe(3)
})
test('IMLibQueue Test: execute lately', function () {
    'use strict'
    counter2 = 1
    IMLibQueue.setTask(function (finishProc) {
      console.log('===check point 2-1 ===')
      counter2++
      expect(counter2).toBe(2)
      finishProc()
    })
    IMLibQueue.setTask(function (finishProc) {
      console.log('===check point 2-2 ===')
      counter2++
      expect(counter2).toBe(3)
      finishProc()
    })
    console.log('===check point 2-3 ===')
    expect(counter2).toBe(1)
  }
)
test('IMLibQueue Test: execute sequential tasks', function () {
    'use strict'
    counter5 = 1
    IMLibQueue.setTask(function (finishProc) {
      console.log('===check point 5-1 ===')
      counter5++
      expect(counter5).toBe(2)
      finishProc()
    })
    IMLibQueue.setSequentialTasks([function (finishProc) {
      console.log('===check point 5-2 ===')
      counter5++
      expect(counter5).toBe(3)
      finishProc()
    }, function (finishProc) {
      console.log('===check point 5-3 ===')
      counter5++
      expect(counter5).toBe(4)
      finishProc()
    }, function (finishProc) {
      console.log('===check point 5-4 ===')
      counter5++
      expect(counter5).toBe(5)
      finishProc()
    }, function (finishProc) {
      console.log('===check point 5-5 ===')
      counter5++
      expect(counter5).toBe(6)
      finishProc()
    }])
    IMLibQueue.setTask(function (finishProc) {
      console.log('===check point 5-6 ===')
      counter5++
      expect(counter5).toBe(7)
      finishProc()
    })
    console.log('===check point 5-7 ===')
    expect(counter5).toBe(1)
  }
)
test('IMLibQueue Test: execute sequential tasks reverse order', function () {
    'use strict'
    counter6 = 1
    IMLibQueue.setPriorTask(function (finishProc) {
      console.log('===check point 6-1 ===')
      counter6++
      expect(counter6).toBe(7)
      finishProc()
    })
    IMLibQueue.setSequentialPriorTasks([function (finishProc) {
      console.log('===check point 6-2 ===')
      counter6++
      expect(counter6).toBe(3)
      finishProc()
    }, function (finishProc) {
      console.log('===check point 6-3 ===')
      counter6++
      expect(counter6).toBe(4)
      finishProc()
    }, function (finishProc) {
      console.log('===check point 6-4 ===')
      counter6++
      expect(counter6).toBe(5)
      finishProc()
    }, function (finishProc) {
      console.log('===check point 6-5 ===')
      counter6++
      expect(counter6).toBe(6)
      finishProc()
    }])
    IMLibQueue.setPriorTask(function (finishProc) {
      console.log('===check point 6-6 ===')
      counter6++
      expect(counter6).toBe(2)
      finishProc()
    })
    console.log('===check point 6-7 ===')
    expect(counter6).toBe(1)
  }
)
test('IMLibQueue Test: execute lately with reverse order', function () {
    'use strict'
    counter4 = 1
    IMLibQueue.setPriorTask(function (finishProc) {
      console.log('===check point 4-1 ===')
      counter4++
      expect(counter4).toBe(3)
      finishProc()
    })
    IMLibQueue.setPriorTask(function (finishProc) {
      console.log('===check point 4-2 ===')
      counter4++
      expect(counter4).toBe(2)
      finishProc()
    })
    console.log('===check point 4-3 ===')
    expect(counter4).toBe(1)
  }
)
test('IMLibQueue Test: data relaying', function () {
    'use strict'
    var label = IMLibQueue.getNewLabel()
    counter3 = 1
    IMLibQueue.setTask(function (finishProc) {
      var l = label
      console.log('===check point 3-1 ===')
      IMLibQueue.setDataStore(l, 'key1', 100)
      IMLibQueue.setDataStore(l, 'key2', 200)
      IMLibQueue.setDataStore(l, 'key3', 300)
      finishProc()
    })
    IMLibQueue.setTask(function (finishProc) {
      var l = label
      console.log('===check point 3-2 ===')
      expect(IMLibQueue.getDataStore(l, 'key3'), 300)
      finishProc()
    })
    IMLibQueue.setTask(function (finishProc) {
      var l = label
      console.log('===check point 3-3 ===')
      expect(IMLibQueue.getDataStore(l, 'key2'), 200)
      IMLibQueue.setDataStore(l, 'key4', 400)
      counter3++
      expect(counter3).toBe(2)
      finishProc()
    })
    IMLibQueue.setTask(function (finishProc) {
      var l = label
      console.log('===check point 3-4 ===')
      expect(IMLibQueue.getDataStore(l, 'key4')).toBe(400)
      expect(IMLibQueue.getDataStore(l, 'key1')).toBe(100)
      finishProc()
    })
    console.log('===check point 3-5 ===')
    expect(counter3).toBe(1)
  }
)
test('IMLibQueue Test: should execute immidiately once more', function () {
  'use strict'
  var label = IMLibQueue.getNewLabel()
  IMLibQueue.setDataStore(label, 'counter', 100)
  IMLibQueue.setTask(function (finishProc) {
    var l = label
    var c = IMLibQueue.getDataStore(l, 'counter')
    console.log('===check point 11-1 ===')
    expect(c).toBe( 100)
    IMLibQueue.setDataStore(l, 'counter', c + 1)
    finishProc()
  }, true)
  IMLibQueue.setTask(function (finishProc) {
    var l = label
    var c = IMLibQueue.getDataStore(l, 'counter')
    console.log('===check point 11-2 ===')
    expect(c).toBe( 101)
    IMLibQueue.setDataStore(l, 'counter', c + 1)
    expect(IMLibQueue.getDataStore(l, 'counter')).toBe( 102)
    finishProc()
  }, true)
  console.log('===check point 11-3 ===')
  var c = IMLibQueue.getDataStore(label, 'counter')
  IMLibQueue.setDataStore(label, 'counter', c + 1)
  expect(IMLibQueue.getDataStore(label, 'counter')).toBe( 103)
})
