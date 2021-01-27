/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// JSHint support
/* global IMLibContextPool, INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibUI,
 IMLibEventResponder, INTERMediatorLog */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLibPageNavigation class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
const IMLibPageNavigation = {
  deleteInsertOnNavi: [],
  previousModeDetail: null,
  stepNavigation: [],
  stepCurrentContextName: null,
  stepStartContextName: null,

  /**
   * Create Navigation Bar to move previous/next page
   */

  navigationSetup: function () {
    'use strict'
    let navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass, c_node,
      prevPageCount, nextPageCount, endPageCount, contextName, contextDef, buttonLabel, dataSource, allNavNodes, ix

    allNavNodes = allNavigator()
    for (ix = 0; ix < allNavNodes.length; ix++) {
      navigation = allNavNodes[ix]
      if (!IMLibContextPool.getPagingContext()) {
        navigation.style.display = 'none'
        return
      }
      insideNav = navigation.childNodes
      for (i = 0; i < insideNav.length; i += 1) {
        navigation.removeChild(insideNav[i])
      }
      navigation.innerHTML = ''
      navigation.setAttribute('class', navigation.getAttribute('class') + ' IM_NAV_panel')
      navLabel = INTERMediator.navigationLabel

      if (navLabel === null || navLabel[8] !== false) {
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          ((navLabel === null || navLabel[8] === null) ? INTERMediatorOnPage.getMessages()[2] : navLabel[8])))
        node.setAttribute('class', 'IM_NAV_button')
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, function () {
          INTERMediator.initialize()
          IMLibLocalContext.archive()
          location.reload()
        })
      }

      if (navLabel === null || navLabel[4] !== false) {
        start = parseInt(INTERMediator.startFrom)

        dataSource = IMLibContextPool.getPagingContext().getContextDef()
        if (dataSource && dataSource.maxrecords &&
          dataSource.maxrecords < parseInt(INTERMediator.pagedSize)) {
          INTERMediator.pagedSize = dataSource.maxrecords
        }
        pageSize = parseInt(INTERMediator.pagedSize)
        allCount = parseInt(INTERMediator.pagedAllCount)
        disableClass = ' IM_NAV_disabled'
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          ((navLabel === null || navLabel[4] === null) ? INTERMediatorOnPage.getMessages()[1] : navLabel[4]) +
          (allCount === 0 ? 0 : start + 1) +
          ((Math.min(start + pageSize, allCount) - start > 1) ? (((navLabel === null || navLabel[5] === null) ? '-' : navLabel[5]) +
            Math.min(start + pageSize, allCount)) : '') +
          ((navLabel === null || navLabel[6] === null) ? ' / ' : navLabel[6]) + (allCount) +
          ((navLabel === null || navLabel[7] === null) ? '' : navLabel[7])))
        node.setAttribute('class', 'IM_NAV_info')
      }

      if ((navLabel === null || navLabel[0] !== false) && INTERMediator.pagination === true) {
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[0] === null) ? '<<' : navLabel[0]))
        node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ''))
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, function () {
          IMLibPageNavigation.moveRecordFromNavi('navimoving', 0)
        })

        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[1] === null) ? '<' : navLabel[1]))
        node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ''))
        prevPageCount = (start - pageSize > 0) ? start - pageSize : 0
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, (function () {
          var pageCount = prevPageCount
          return function () {
            IMLibPageNavigation.moveRecordFromNavi('navimoving', pageCount)
          }
        })())

        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[2] === null) ? '>' : navLabel[2]))
        node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''))
        nextPageCount =
          (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0)
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, (function () {
          var pageCount = nextPageCount
          return function () {
            IMLibPageNavigation.moveRecordFromNavi('navimoving', pageCount)
          }
        })())

        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[3] === null) ? '>>' : navLabel[3]))
        node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''))
        if (allCount % pageSize === 0) {
          endPageCount = allCount - (allCount % pageSize) - pageSize
        } else {
          endPageCount = allCount - (allCount % pageSize)
        }
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, (function () {
          var pageCount = endPageCount
          return function () {
            IMLibPageNavigation.moveRecordFromNavi('navimoving', (pageCount > 0) ? pageCount : 0)
          }
        })())

        // Get from http://agilmente.com/blog/2013/08/04/inter-mediator_pagenation_1/
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[10]))
        c_node = document.createElement('INPUT')
        c_node.setAttribute('class', 'IM_NAV_JUMP')
        c_node.setAttribute('type', 'text')
        if (!c_node.id) {
          c_node.id = INTERMediator.nextIdValue()
        }
        c_node.setAttribute('value', String(Math.ceil(INTERMediator.startFrom / pageSize + 1)))
        node.appendChild(c_node)
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[11]))
        // ---------
        IMLibChangeEventDispatch.setExecute(c_node.id, (function () {
          var targetNode = c_node
          return function () {
            var moveTo, max_page
            moveTo = parseInt(targetNode.value)
            if (moveTo < 1) {
              moveTo = 1
            }
            max_page = Math.ceil(allCount / pageSize)
            if (max_page < moveTo) {
              moveTo = max_page
            }
            INTERMediator.startFrom = (moveTo - 1) * pageSize
            INTERMediator.constructMain(true)
          }
        })())
      }

      if (navLabel === null || navLabel[9] !== false) {
        for (i = 0; i < IMLibPageNavigation.deleteInsertOnNavi.length; i += 1) {
          switch (IMLibPageNavigation.deleteInsertOnNavi[i].kind) {
            case 'INSERT':
              node = document.createElement('SPAN')
              navigation.appendChild(node)
              contextName = IMLibPageNavigation.deleteInsertOnNavi[i].name
              contextDef = IMLibContextPool.getContextDef(contextName)
              if (contextDef && contextDef['button-names'] && contextDef['button-names'].insert) {
                buttonLabel = contextDef['button-names'].insert
              } else {
                buttonLabel = INTERMediatorOnPage.getMessages()[3] + ': ' + contextName
              }
              node.appendChild(document.createTextNode(buttonLabel))
              node.setAttribute('class', 'IM_NAV_button')
              if (!node.id) {
                node.id = INTERMediator.nextIdValue()
              }
              IMLibMouseEventDispatch.setExecute(node.id,
                (function () {
                  var obj = IMLibPageNavigation.deleteInsertOnNavi[i]
                  let contextName = obj.name
                  let keyValue = obj.key
                  let confirming = obj.confirm
                  return function () {
                    IMLibPageNavigation.insertRecordFromNavi(contextName, keyValue, confirming)
                  }
                })()
              )
              break
            case 'DELETE':
              node = document.createElement('SPAN')
              navigation.appendChild(node)
              contextName = IMLibPageNavigation.deleteInsertOnNavi[i].name
              contextDef = IMLibContextPool.getContextDef(contextName)
              if (contextDef && contextDef['button-names'] && contextDef['button-names'].delete) {
                buttonLabel = contextDef['button-names'].delete
              } else {
                buttonLabel = INTERMediatorOnPage.getMessages()[4] + ': ' + contextName
              }
              node.appendChild(document.createTextNode(buttonLabel))
              node.setAttribute('class', 'IM_NAV_button')
              INTERMediatorLib.addEvent(
                node,
                'click',
                (function () {
                  var obj = IMLibPageNavigation.deleteInsertOnNavi[i]
                  let contextName = obj.name
                  let keyName = obj.key
                  let keyValue = obj.value
                  let confirming = obj.confirm
                  return function () {
                    IMLibPageNavigation.deleteRecordFromNavi(contextName, keyName, keyValue, confirming)
                  }
                })()
              )
              break
            case 'COPY':
              node = document.createElement('SPAN')
              navigation.appendChild(node)
              contextName = IMLibPageNavigation.deleteInsertOnNavi[i].name
              contextDef = IMLibContextPool.getContextDef(contextName)
              if (contextDef && contextDef['button-names'] && contextDef['button-names'].copy) {
                buttonLabel = contextDef['button-names'].copy
              } else {
                buttonLabel = INTERMediatorOnPage.getMessages()[15] + ': ' + contextName
              }
              node.appendChild(document.createTextNode(buttonLabel))
              node.setAttribute('class', 'IM_NAV_button')
              if (!node.id) {
                node.id = INTERMediator.nextIdValue()
              }
              IMLibMouseEventDispatch.setExecute(node.id,
                (function () {
                  var obj = IMLibPageNavigation.deleteInsertOnNavi[i]
                  let contextDef = obj.contextDef
                  let record = obj.keyValue
                  return function () {
                    IMLibPageNavigation.copyRecordFromNavi(contextDef, record)
                  }
                })()
              )
              break
          }
        }
      }
      if (navLabel === null || navLabel[10] !== false) {
        if (INTERMediatorOnPage.getOptionsTransaction() === 'none') {
          node = document.createElement('SPAN')
          navigation.appendChild(node)
          node.appendChild(document.createTextNode(
            (navLabel === null || navLabel[10] === null) ? INTERMediatorOnPage.getMessages()[7] : navLabel[10]))
          node.setAttribute('class', 'IM_NAV_button')
          INTERMediatorLib.addEvent(node, 'click', IMLibPageNavigation.saveRecordFromNavi)
        }
      }
      if (navLabel === null || navLabel[11] !== false) {
        if (INTERMediatorOnPage.requireAuthentication) {
          node = document.createElement('SPAN')
          navigation.appendChild(node)
          node.appendChild(document.createTextNode(
            INTERMediatorOnPage.getMessages()[8] + INTERMediatorOnPage.authUser))
          node.setAttribute('class', 'IM_NAV_info')

          node = document.createElement('SPAN')
          navigation.appendChild(node)
          node.appendChild(document.createTextNode(
            (navLabel === null || navLabel[11] === null) ? INTERMediatorOnPage.getMessages()[9] : navLabel[11]))
          node.setAttribute('class', 'IM_NAV_button')
          if (!node.id) {
            node.id = INTERMediator.nextIdValue()
          }
          IMLibMouseEventDispatch.setExecute(node.id,
            function () {
              INTERMediatorOnPage.logout()
              location.reload()
            })
        }
      }
    }

    function allNavigator() {
      var naviIdElement, naviClassElements, nodes = [], ix
      naviIdElement = document.getElementById('IM_NAVIGATOR')
      if (naviIdElement) {
        //naviIdElement.removeAttribute('id')
        nodes.push(naviIdElement)
      }
      naviClassElements = document.getElementsByClassName('IM_NAVIGATOR')
      if (naviClassElements) {
        for (ix = 0; ix < naviClassElements.length; ix++) {
          nodes.push(naviClassElements[ix])
        }
      }
      return nodes
    }
  },

  moveRecordFromNavi: function (targetName, page) {
    'use strict'
    INTERMediator_DBAdapter.unregister()
    INTERMediator.startFrom = page
    INTERMediator.constructMain(true)
  },

  insertRecordFromNavi: function (targetName, keyField, isConfirm) {
    'use strict'
    var contextDef

    if (isConfirm) {
      if (!window.confirm(INTERMediatorOnPage.getMessages()[1026])) {
        return
      }
    }
    INTERMediatorOnPage.showProgress()
    contextDef = INTERMediatorLib.getNamedObject(
      INTERMediatorOnPage.getDataSources(), 'name', targetName)
    if (contextDef === null) {
      window.alert('no targetname :' + targetName)
      INTERMediatorOnPage.hideProgress()
      return
    }

    IMLibQueue.setTask((function () {
      var conditions, restore
      var contextDefCapt = contextDef
      var targetNameCapt = targetName
      var keyFieldCapt = keyField
      var isConfirmCapt = isConfirm
      return function (completeTask) {
        try {
          // await INTERMediatorOnPage.retrieveAuthInfo()
          INTERMediator_DBAdapter.db_createRecord_async(
            {name: targetNameCapt, dataset: []},
            async function (response) {
              var newId = response.newRecordKeyValue
              INTERMediatorOnPage.newRecordId = newId
              if (newId > -1) {
                restore = INTERMediator.additionalCondition
                if (contextDefCapt.records <= 1) {
                  INTERMediator.startFrom = 0
                  INTERMediator.pagedAllCount = 1
                  conditions = INTERMediator.additionalCondition
                  conditions[targetNameCapt] = {field: keyFieldCapt, value: newId}
                  INTERMediator.additionalCondition = conditions
                  IMLibLocalContext.archive()
                } else {
                  INTERMediator.pagedAllCount++
                }
                completeTask()
                INTERMediator_DBAdapter.unregister()
                await INTERMediator.constructMain(true)
                INTERMediator.additionalCondition = restore
                IMLibPageNavigation.navigationSetup()
              }
              IMLibCalc.recalculation()
              INTERMediatorOnPage.hideProgress()
              INTERMediatorLog.flushMessage()
              if (INTERMediatorOnPage.doAfterCreateRecord) {
                INTERMediatorOnPage.doAfterCreateRecord(INTERMediatorOnPage.newRecordId, contextDefCapt.name)
              }
            },
            completeTask
          )
        } catch (ex) {
          completeTask()
          if (ex.message === '_im_auth_required_') {
            if (INTERMediatorOnPage.requireAuthentication) {
              if (!INTERMediatorOnPage.isComplementAuthData()) {
                INTERMediatorOnPage.clearCredentials()
                INTERMediatorOnPage.authenticating(function () {
                  IMLibPageNavigation.insertRecordFromNavi(
                    targetNameCapt, keyFieldCapt, isConfirmCapt)
                })
                INTERMediatorLog.flushMessage()
              }
            }
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-5')
          }
        }
      }
    })())
  },

  deleteRecordFromNavi: function (targetName, keyField, keyValue, isConfirm) {
    'use strict'
    if (isConfirm) {
      if (!window.confirm(INTERMediatorOnPage.getMessages()[1025])) {
        return
      }
    }
    IMLibQueue.setTask((function () {
      var deleteArgs = {
        name: targetName,
        conditions: [{field: keyField, operator: '=', value: keyValue}]
      }
      return function (completeTask) {
        INTERMediatorOnPage.showProgress()
        try {
          // await INTERMediatorOnPage.retrieveAuthInfo()
          INTERMediator_DBAdapter.db_delete_async(
            deleteArgs,
            async () => {
              INTERMediator.pagedAllCount--
              INTERMediator.totalRecordCount--
              if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 1) {
                INTERMediator.startFrom--
                if (INTERMediator.startFrom < 0) {
                  INTERMediator.startFrom = 0
                }
              }
              completeTask()
              await INTERMediator.constructMain(true)
              INTERMediatorOnPage.hideProgress()
              INTERMediatorLog.flushMessage()
            },
            () => {
              completeTask()
            }
          )
        } catch (ex) {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-6')
          completeTask()
        }
      }
    })())
  },

  copyRecordFromNavi: function (contextDef, keyValue) {
    'use strict'
    if (contextDef['repeat-control'].match(/confirm-copy/)) {
      if (!window.confirm(INTERMediatorOnPage.getMessages()[1041])) {
        return
      }
    }
    IMLibQueue.setTask((function () {
      var contextDefCapt = contextDef
      var keyValueCapt = keyValue
      return function (completeTask) {
        var assocDef, i, def, assocContexts, pStart, copyTerm, index
        INTERMediatorOnPage.showProgress()
        try {
          if (contextDefCapt.relation) {
            for (index in contextDefCapt.relation) {
              if (contextDefCapt.relation[index].portal === true) {
                contextDefCapt.portal = true
              }
            }
          }
          assocDef = []
          if (contextDefCapt['repeat-control'].match(/copy-/)) {
            pStart = contextDefCapt['repeat-control'].indexOf('copy-')
            copyTerm = contextDefCapt['repeat-control'].substr(pStart + 5)
            if ((pStart = copyTerm.search(/\s/)) > -1) {
              copyTerm = copyTerm.substr(0, pStart)
            }
            assocContexts = copyTerm.split(',')
            for (i = 0; i < assocContexts.length; i += 1) {
              def = IMLibContextPool.getContextDef(assocContexts[i])
              if (def.relation[0]['foreign-key']) {
                assocDef.push({
                  name: def.name,
                  field: def.relation[0]['foreign-key'],
                  value: keyValueCapt
                })
              }
            }
          }
          // await INTERMediatorOnPage.retrieveAuthInfo()
          INTERMediator_DBAdapter.db_copy_async(
            {
              name: contextDefCapt.name,
              conditions: [{field: contextDefCapt.key, operator: '=', value: keyValueCapt}],
              associated: assocDef.length > 0 ? assocDef : null
            },
            (function () {
              var contextDefCapt2 = contextDefCapt
              return async function (result) {
                var restore, conditions
                var newId = result.newRecordKeyValue
                INTERMediatorOnPage.newRecordId = newId
                completeTask()
                if (newId > -1) {
                  restore = INTERMediator.additionalCondition
                  INTERMediator.startFrom = 0
                  if (contextDefCapt2.records <= 1) {
                    conditions = INTERMediator.additionalCondition
                    conditions[contextDefCapt2.name] = {field: contextDefCapt2.key, value: newId}
                    INTERMediator.additionalCondition = conditions
                    IMLibLocalContext.archive()
                  }
                  INTERMediator_DBAdapter.unregister()
                  await INTERMediator.constructMain(true)
                  INTERMediator.additionalCondition = restore
                }
                IMLibCalc.recalculation()
                INTERMediatorOnPage.hideProgress()
                if (INTERMediatorOnPage.doAfterCreateRecord) {
                  INTERMediatorOnPage.doAfterCreateRecord(INTERMediatorOnPage.newRecordId, contextDefCapt2.name)
                }
                INTERMediatorLog.flushMessage()
              }
            })(),
            completeTask
          )
        } catch (ex) {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-43')
          completeTask()
        }
      }
    })())
  },

  saveRecordFromNavi: async function (dontUpdate) {
    'use strict'
    var keying, field, keyingComp, keyingField, keyingValue, checkQueryParameter, i, initialValue,
      currentVal, fieldArray, valueArray, difference
    let needUpdate = true
    let context, updateData, response

    INTERMediatorOnPage.showProgress()
    // await INTERMediatorOnPage.retrieveAuthInfo()
    for (let i = 0; i < IMLibContextPool.poolingContexts.length; i += 1) {
      context = IMLibContextPool.poolingContexts[i]
      updateData = context.getModified()
      for (keying in updateData) {
        if (updateData.hasOwnProperty(keying)) {
          fieldArray = []
          valueArray = []
          for (field in updateData[keying]) {
            if (updateData[keying].hasOwnProperty(field)) {
              fieldArray.push(field)
              valueArray.push({field: field, value: updateData[keying][field]})
            }
          }
          keyingComp = keying.split('=')
          keyingField = keyingComp[0]
          keyingComp.shift()
          keyingValue = keyingComp.join('=')
          if (!INTERMediator.ignoreOptimisticLocking) {
            checkQueryParameter = {
              name: context.contextName,
              records: 1,
              paging: false,
              fields: fieldArray,
              parentkeyvalue: null,
              conditions: [
                {field: keyingField, operator: '=', value: keyingValue}
              ],
              useoffset: false,
              primaryKeyOnly: true
            }
            try {
              await INTERMediator_DBAdapter.db_query_async(
                checkQueryParameter,
                function (result) {
                  currentVal = result
                },
                null)
            } catch (ex) {
              if (ex.message === '_im_auth_required_') {
                if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                  INTERMediatorOnPage.clearCredentials()
                  INTERMediatorOnPage.authenticating(
                    (function () {
                      var qParam = checkQueryParameter
                      return async function () {
                        await INTERMediator_DBAdapter.db_query_async(qParam, null, null)
                      }
                    })()
                  )
                  return
                }
              } else {
                INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-28')
              }
            }

            if (currentVal.dbresult === null ||
              currentVal.dbresult[0] === null) {
              window.alert(INTERMediatorLib.getInsertedString(
                INTERMediatorOnPage.getMessages()[1003], [fieldArray.join(',')]))
              return
            }
            if (currentVal.count > 1) {
              response = window.confirm(INTERMediatorOnPage.getMessages()[1024])
              if (!response) {
                return
              }
            }

            difference = false
            for (field in updateData[keying]) {
              if (updateData[keying].hasOwnProperty(field)) {
                initialValue = context.getValue(keying, field)
                if (initialValue !== currentVal.dbresult[0][field]) {
                  difference += INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1035], [
                      field,
                      currentVal.dbresult[0][field],
                      updateData[keying][field]
                    ])
                }
              }
            }
            if (difference !== false) {
              if (!window.confirm(INTERMediatorLib.getInsertedString(
                INTERMediatorOnPage.getMessages()[1034], [difference]))) {
                return
              }
              // await INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
            }
          }

          try {
            INTERMediator_DBAdapter.db_update({
              name: context.contextName,
              conditions: [
                {field: keyingField, operator: '=', value: keyingValue}
              ],
              dataset: valueArray
            })
          } catch (ex) {
            if (ex.message === '_im_auth_required_') {
              if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                INTERMediatorOnPage.clearCredentials()
                INTERMediatorOnPage.authenticating(
                  function () {
                    IMLibPageNavigation.saveRecordFromNavi(dontUpdate)
                  }
                )
                return
              }
            } else {
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-29')
            }
          }
          context.clearModified()
        }
      }
    }
    if (needUpdate && (dontUpdate !== true)) {
      await INTERMediator.constructMain(true)
    }
    INTERMediatorOnPage.hideProgress()
    INTERMediatorLog.flushMessage()
  },

  setupCopyButton: function (encNodeTag, repNodeTag, repeaters, currentContext, currentRecord) {
    // Handling Copy buttons
    'use strict'
    var buttonNode, thisId, buttonName, currentContextDef

    currentContextDef = currentContext.getContextDef()
    if (!currentContextDef['repeat-control'] || !currentContextDef['repeat-control'].match(/copy/i)) {
      return
    }
    if (currentContextDef.relation ||
      currentContextDef.records === undefined ||
      !currentContextDef.paging ||
      (currentContextDef.records > 1 && parseInt(INTERMediator.pagedSize) !== 1)) {
      buttonNode = document.createElement('BUTTON')
      buttonNode.setAttribute('class', 'IM_Button_Copy')
      buttonName = INTERMediatorOnPage.getMessages()[14]
      if (currentContextDef['button-names'] && currentContextDef['button-names'].copy) {
        buttonName = currentContextDef['button-names'].copy
      }
      buttonNode.appendChild(document.createTextNode(buttonName))
      thisId = INTERMediator.nextIdValue('CopyButton') // 'IM_Button_' + INTERMediator.buttonIdNum
      buttonNode.setAttribute('id', thisId)
      INTERMediator.buttonIdNum++
      IMLibMouseEventDispatch.setExecute(thisId, (function () {
        var currentContextCapt = currentContext
        let currentRecordCapt = currentRecord[currentContextDef.key]
        return function () {
          IMLibUI.copyButton(currentContextCapt, currentRecordCapt)
        }
      })())
      IMLibPageNavigation.includeButtonInContext(encNodeTag, repeaters, buttonNode)
    } else {
      IMLibPageNavigation.deleteInsertOnNavi.push({
        kind: 'COPY',
        name: currentContextDef.name,
        contextDef: currentContextDef,
        keyValue: currentRecord[currentContextDef.key]
      })
    }
  },

  /* --------------------------------------------------------------------

   */
  setupDeleteButton: function (encNodeTag, repeaters, currentContext, keyField, keyValue) {
    // Handling Delete buttons
    'use strict'
    var buttonNode, thisId, buttonName, currentContextDef

    currentContextDef = currentContext.contextDefinition
    if (!currentContextDef['repeat-control'] ||
      !currentContextDef['repeat-control'].match(/delete/i)) {
      return
    }
    if (currentContextDef.relation ||
      currentContextDef.records === undefined ||
      !currentContextDef.paging ||
      (currentContextDef.records > 1 && parseInt(INTERMediator.pagedSize) !== 1)) {
      buttonNode = document.createElement('BUTTON')
      buttonNode.setAttribute('class', 'IM_Button_Delete')
      buttonName = INTERMediatorOnPage.getMessages()[6]
      if (currentContextDef['button-names'] && currentContextDef['button-names'].delete) {
        buttonName = currentContextDef['button-names'].delete
      }
      buttonNode.appendChild(document.createTextNode(buttonName))
      thisId = INTERMediator.nextIdValue('DeleteButton') // 'IM_Button_' + INTERMediator.buttonIdNum
      buttonNode.setAttribute('id', thisId)
      INTERMediator.buttonIdNum++
      IMLibMouseEventDispatch.setExecute(thisId, (function () {
        var currentContextCapt = currentContext
        let keyFieldCapt = keyField
        let keyValueCapt = keyValue
        let confirmingCapt = currentContextDef['repeat-control'].match(/confirm-delete/i)
        return function () {
          IMLibUI.deleteButton(currentContextCapt, keyFieldCapt, keyValueCapt, confirmingCapt)
        }
      })())
      IMLibPageNavigation.includeButtonInContext(encNodeTag, repeaters, buttonNode)
    } else {
      IMLibPageNavigation.deleteInsertOnNavi.push({
        kind: 'DELETE',
        name: currentContextDef.name,
        key: keyField,
        value: keyValue,
        confirm: currentContextDef['repeat-control'].match(/confirm-delete/i)
      })
    }
  },

  includeButtonInContext: function (encNodeTag, repeaters, buttonNode) {
    var tdNodes, repeaterCtl, repeaterIx, ignoreTerms, repeaterIMCtrl

    ignoreTerms = ['header', 'separator', 'footerheader', 'separator', 'footer']
    switch (encNodeTag) {
      case 'TBODY':
        repeaterIx = repeaters.length - 1
        while (repeaterIx >= 0) {
          repeaterCtl = repeaters[repeaterIx].getAttribute('data-im-control')
          if (!repeaterCtl || (repeaterCtl && ignoreTerms.indexOf(repeaterCtl.toLowerCase()) < 0)) {
            tdNodes = repeaters[repeaterIx].getElementsByTagName('TD')
            tdNodes[tdNodes.length - 1].appendChild(buttonNode)
            break
          }
          repeaterIx -= 1
        }
        break
      case 'SELECT':
        // OPTION tag can't contain any other tags.
        break
      default:
        repeaterIx = repeaters.length - 1
        while (repeaterIx >= 0) {
          repeaterIMCtrl = repeaterCtl = repeaters[repeaterIx].getAttribute('data-im-control')
          if (repeaterIMCtrl) {
            repeaterCtl = repeaterIMCtrl.toLowerCase()
            if (!repeaterCtl || (repeaterCtl && ignoreTerms.indexOf(repeaterCtl.toLowerCase()) < 0)) {
              if (repeaters[repeaterIx] && repeaters[repeaterIx].childNodes) {
                repeaters[repeaterIx].appendChild(buttonNode)
              } else {
                repeaters.push(buttonNode)
              }
              break
            }
          }
          repeaterIx -= 1
        }
        break
    }
  },
  /* --------------------------------------------------------------------

   */
  setupInsertButton: function (currentContext, keyValue, node, relationValue) {
    'use strict'
    var buttonNode, enclosedNode, footNode, trNode, tdNode, liNode, divNode, i,
      firstLevelNodes, targetNodeTag, existingButtons, keyField, thisId, encNodeTag,
      buttonName, setTop, currentContextDef

    encNodeTag = node.tagName
    currentContextDef = currentContext.getContextDef()
    if (currentContextDef['repeat-control'] && currentContextDef['repeat-control'].match(/insert/i)) {
      if (relationValue.length > 0 || !currentContextDef.paging || currentContextDef.paging === false) {
        buttonNode = document.createElement('BUTTON')
        buttonNode.setAttribute('class', 'IM_Button_Insert')
        buttonName = INTERMediatorOnPage.getMessages()[5]
        if (currentContextDef['button-names'] && currentContextDef['button-names'].insert) {
          buttonName = currentContextDef['button-names'].insert
        }
        buttonNode.appendChild(document.createTextNode(buttonName))
        thisId = INTERMediator.nextIdValue('InsertButton') // 'IM_Button_' + INTERMediator.buttonIdNum
        buttonNode.setAttribute('id', thisId)
        INTERMediator.buttonIdNum++
        switch (encNodeTag) {
          case 'TBODY':
            setTop = false
            targetNodeTag = 'TFOOT'
            if (currentContextDef['repeat-control'].match(/top/i)) {
              targetNodeTag = 'THEAD'
              setTop = true
            }
            enclosedNode = node.parentNode
            firstLevelNodes = enclosedNode.childNodes
            footNode = null
            for (i = 0; i < firstLevelNodes.length; i += 1) {
              if (firstLevelNodes[i].tagName === targetNodeTag) {
                footNode = firstLevelNodes[i]
                break
              }
            }
            if (footNode === null) {
              footNode = document.createElement(targetNodeTag)
              enclosedNode.appendChild(footNode)
            }
            existingButtons = INTERMediatorLib.getElementsByClassName(footNode, 'IM_Button_Insert')
            if (existingButtons.length === 0) {
              trNode = document.createElement('TR')
              trNode.setAttribute('class', 'IM_Insert_TR')
              tdNode = document.createElement('TD')
              tdNode.setAttribute('colspan', 100)
              tdNode.setAttribute('class', 'IM_Insert_TD')
              INTERMediator.setIdValue(trNode)
              if (setTop && footNode.childNodes) {
                footNode.insertBefore(trNode, footNode.childNodes[0])
              } else {
                footNode.appendChild(trNode)
              }
              trNode.appendChild(tdNode)
              tdNode.appendChild(buttonNode)
            }
            break
          case 'UL':
          case 'OL':
            liNode = document.createElement('LI')
            existingButtons = INTERMediatorLib.getElementsByClassName(liNode, 'IM_Button_Insert')
            if (existingButtons.length === 0) {
              liNode.appendChild(buttonNode)
              if (currentContextDef['repeat-control'].match(/top/i)) {
                node.insertBefore(liNode, node.firstChild)
              } else {
                node.appendChild(liNode)
              }
            }
            break
          case 'SELECT':
            // Select enclosure can't include Insert button.
            break
          default:
            divNode = document.createElement('DIV')
            existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_Insert')
            if (existingButtons.length === 0) {
              divNode.appendChild(buttonNode)
              if (currentContextDef['repeat-control'].match(/top/i)) {
                node.insertBefore(divNode, node.firstChild)
              } else {
                node.appendChild(divNode)
              }
            }
            break
        }
        IMLibMouseEventDispatch.setExecute(buttonNode.id, (function () {
          var context = currentContext
          let keyValueCapt = keyValue
          let relationValueCapt = relationValue
          let nodeId = node.getAttribute('id')
          let confirming = currentContextDef['repeat-control'].match(/confirm-insert/i)
          return function () {
            IMLibUI.insertButton(context, keyValueCapt, relationValueCapt, nodeId, confirming)
          }
        })())
      } else {
        if (INTERMediatorOnPage.dbClassName === 'FileMaker_FX' ||
          INTERMediatorOnPage.dbClassName === 'FileMaker_DataAPI') {
          keyField = currentContextDef.key ? currentContextDef.key : INTERMediatorOnPage.defaultKeyName
        } else {
          keyField = currentContextDef.key ? currentContextDef.key : 'id'
        }
        IMLibPageNavigation.deleteInsertOnNavi.push({
          kind: 'INSERT',
          name: currentContextDef.name,
          key: keyField,
          confirm: currentContextDef['repeat-control'].match(/confirm-insert/i)
        })
      }
    }
  },
  /* --------------------------------------------------------------------

     */
  setupNavigationButton: function (encNodeTag, repeaters, currentContextDef, keyField, keyValue, contextObj) {
    // Handling Detail buttons
    'use strict'
    var buttonNode, thisId, tdNodes, tdNode, firstInNode, isMasterDetail, isStep, isHide, masterContext,
      detailContext, showingNode, isHidePageNavi, buttonName, i, isTouchRepeater, moveToDetailFunc, isNoNavi, isFullNavi

    if (!currentContextDef['navi-control'] ||
      (!currentContextDef['navi-control'].match(/master/i) &&
        !currentContextDef['navi-control'].match(/step/i)) ||
      encNodeTag === 'SELECT') {
      return
    }

    isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet
    isHide = currentContextDef['navi-control'].match(/hide/i)
    isHidePageNavi = isHide && !!currentContextDef.paging
    isMasterDetail = currentContextDef['navi-control'].match(/master/i)
    isStep = currentContextDef['navi-control'].match(/step/i)
    isNoNavi = currentContextDef['navi-control'].match(/nonavi/i)
    isFullNavi = currentContextDef['navi-control'].match(/fullnavi/i)

    if (isMasterDetail && INTERMediator.detailNodeOriginalDisplay) {
      detailContext = IMLibContextPool.getDetailContext()
      if (detailContext) {
        showingNode = detailContext.enclosureNode
        if (showingNode.tagName === 'TBODY') {
          showingNode = showingNode.parentNode
        }
        INTERMediator.detailNodeOriginalDisplay = showingNode.style.display
      }
    }

    buttonNode = document.createElement('BUTTON')
    buttonNode.setAttribute('class', 'IM_Button_Master')
    buttonName = INTERMediatorOnPage.getMessages()[12]
    if (currentContextDef['button-names'] && currentContextDef['button-names']['navi-detail']) {
      buttonName = currentContextDef['button-names']['navi-detail']
    }
    buttonNode.appendChild(document.createTextNode(buttonName))
    thisId = INTERMediator.nextIdValue('MasterButton') // 'IM_Button_' + INTERMediator.buttonIdNum
    buttonNode.setAttribute('id', thisId)
    INTERMediator.buttonIdNum++
    if (isMasterDetail) {
      masterContext = IMLibContextPool.getMasterContext()
      masterContext.setValue(keyField + '=' + keyValue, '_im_button_master_id', thisId, thisId)
      moveToDetailFunc = IMLibPageNavigation.moveToDetail(keyField, keyValue, isHide, isHidePageNavi)
    }
    if (isStep) {
      moveToDetailFunc = IMLibPageNavigation.moveToNextStep(contextObj, keyField, keyValue)
    }
    if ((isTouchRepeater && !isNoNavi) || isFullNavi) {
      for (i = 0; i < repeaters.length; i += 1) {
        const originalColor = repeaters[i].style.backgroundColor
        repeaters[i].style.cursor = 'pointer'
        const css = '#' + repeaters[i].id + ':hover{background-color:' + IMLibUI.mobileSelectionColor + '}'
        const style = document.createElement('style')
        style.appendChild(document.createTextNode(css))
        document.getElementsByTagName('head')[0].appendChild(style)
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id,
          'event': 'touchstart',
          'todo': (function () {
            const targetNode = repeaters[i]
            return function (ev) {
              IMLibEventResponder.touchEventCancel = false
              targetNode.style.backgroundColor = IMLibUI.mobileSelectionColor
            }
          })()
        })
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id,
          'event': 'click',
          'todo': (function () {
            const targetNode = repeaters[i]
            const orgColor = originalColor
            return function (ev) {
              targetNode.style.backgroundColor = orgColor
              if (!IMLibEventResponder.touchEventCancel) {
                IMLibEventResponder.touchEventCancel = false
                moveToDetailFunc()
              }
              // ev.preventDefault() // Prevent to process at the next page.
            }
          })()
        })
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id,
          'event': 'touchmove',
          'todo': (function () {
            return function () {
              IMLibEventResponder.touchEventCancel = true
            }
          })()
        })
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id,
          'event': 'touchcancel',
          'todo': (function () {
            return function () {
              IMLibEventResponder.touchEventCancel = true
            }
          })()
        })
      }
    } else {
      if (!isNoNavi) {
        IMLibMouseEventDispatch.setExecute(thisId, moveToDetailFunc)
        switch (encNodeTag) {
          case 'TBODY':
            tdNodes = repeaters[repeaters.length - 1].getElementsByTagName('TD')
            tdNode = tdNodes[0]
            firstInNode = tdNode.childNodes[0]
            if (firstInNode) {
              tdNode.insertBefore(buttonNode, firstInNode)
            } else {
              tdNode.appendChild(buttonNode)
            }
            break
          case 'SELECT':
            break
          default:
            firstInNode = repeaters[repeaters.length - 1].childNodes[0]
            if (firstInNode) {
              repeaters[repeaters.length - 1].insertBefore(buttonNode, firstInNode)
            } else {
              repeaters[repeaters.length - 1].appendChild(buttonNode)
            }
            break
        }
      }
    }
  },
  getStepLastSelectedRecord: function () {
    'use strict'
    var lastSelection = IMLibPageNavigation.stepNavigation[IMLibPageNavigation.stepNavigation.length - 1]
    return lastSelection.context.store[lastSelection.key]
  },
  isNotExpandingContext: function (contextDef) {
    'use strict'
    if (contextDef['navi-control'] && contextDef['navi-control'].match(/step/i)) {
      return IMLibPageNavigation.stepCurrentContextName !== contextDef.name
    }
    return false
  },
  startStep: function () {
    'use strict'
    IMLibPageNavigation.initializeStepInfo(true)
    INTERMediator.constructMain(IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName))
  },
  initializeStepInfo: function (includeHide) {
    'use strict'
    var key, dataSrcs, cDef, judgeHide
    let isDetected = false
    IMLibPageNavigation.stepNavigation = []
    IMLibPageNavigation.stepCurrentContextName = null
    IMLibPageNavigation.stepStartContextName = null
    IMLibPageNavigation.setupStepReturnButton('none')
    if (INTERMediatorOnPage.getDataSources) { // Avoid processing on unit test
      dataSrcs = INTERMediatorOnPage.getDataSources()
      for (key of Object.keys(dataSrcs)) {
        cDef = dataSrcs[key]
        if (cDef['navi-control']) {
          judgeHide = includeHide || (!includeHide && !cDef['navi-control'].match(/hide/i))
          if (cDef['navi-control'] && cDef['navi-control'].match(/step/i)) {
            if (judgeHide && !isDetected) {
              IMLibPageNavigation.stepCurrentContextName = cDef.name
              IMLibPageNavigation.stepStartContextName = IMLibPageNavigation.stepCurrentContextName
              isDetected = true
              if (cDef['navi-title']) {
                IMLibLocalContext.setValue('navi_title', cDef['navi-title'], !0)
              }
              if (INTERMediatorOnPage[cDef['just-move-thisstep']]) {
                INTERMediatorOnPage[cDef['just-move-thisstep']]()
              }
            }
          }
        }
      }
    }
  },
  setupStepReturnButton: function (style) {
    'use strict'
    let nodes = document.getElementsByClassName('IM_Button_StepBack')
    for (let i = 0; i < nodes.length; i += 1) {
      nodes[i].style.display = style
      if (!INTERMediatorLib.isProcessed(nodes[i])) {
        INTERMediatorLib.addEvent(nodes[i], 'click', function () {
          IMLibQueue.setTask(function (complete) {
            IMLibPageNavigation.backToPreviousStep()
            complete()
          })
        })
        INTERMediatorLib.markProcessed(nodes[i])
      }
    }
    nodes = document.getElementsByClassName('IM_Button_StepInsert')
    for (let i = 0; i < nodes.length; i += 1) {
      if (!INTERMediatorLib.isProcessedInsert(nodes[i])) {
        INTERMediatorLib.addEvent(nodes[i], 'click', function () {
          INTERMediatorOnPage.showProgress()
          let context = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName)
          IMLibQueue.setTask(function (completeTask) {
            INTERMediator_DBAdapter.db_createRecord_async(
              {name: IMLibPageNavigation.stepCurrentContextName, dataset: []},
              function (result) {
                INTERMediator.constructMain(context)
                completeTask()
              },
              function () {
                INTERMediatorLog.setErrorMessage('Insert Error', 'EXCEPTION-4')
                completeTask()
              }
            )
          })
        })
        INTERMediatorLib.markProcessedInsert(nodes[i])
      }
    }
  },
  moveToNextStep: function (contextObj, keyField, keyValue) {
    'use strict'
    const context = contextObj
    const keying = keyField + '=' + keyValue
    return function () {
      IMLibQueue.setTask(function (complete) {
        IMLibPageNavigation.moveToNextStepImpl(context, keying)
        complete()
      })
    }
  },
  moveNextStep: function (keying) {
    'use strict'
    const context = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName)
    IMLibPageNavigation.moveToNextStepImpl(context, keying)
  },
  moveToNextStepImpl: async function (contextObj, keying) {
    'use strict'
    let key, cDef, dataSrcs, contextDef, lastRecord, lastSelection = -1
    let isAfterCurrent = false
    let control = null
    let hasNextContext = false
    let hasBeforeMoveNext = false
    let nextContext
    contextDef = contextObj.getContextDef()
    IMLibPageNavigation.stepNavigation.push({context: contextObj, key: keying})
    if (INTERMediatorOnPage[contextDef['before-move-nextstep']]) {
      control = INTERMediatorOnPage[contextDef['before-move-nextstep']]()
      hasBeforeMoveNext = true
    } else {
      lastRecord = IMLibPageNavigation.getStepLastSelectedRecord()
      lastSelection = lastRecord[contextDef['key']]
    }
    if (control === false) {
      IMLibPageNavigation.stepNavigation.pop()
      return
    } else if (control) {
      IMLibPageNavigation.stepCurrentContextName = control
    } else {
      dataSrcs = INTERMediatorOnPage.getDataSources()
      for (key of Object.keys(dataSrcs)) {
        cDef = dataSrcs[key]
        if (cDef.name === contextDef.name) {
          isAfterCurrent = true
        } else if (isAfterCurrent && cDef['navi-control'].match(/step/i)) {
          IMLibPageNavigation.stepCurrentContextName = cDef.name
          hasNextContext = true
          break
        }
      }
      if (!hasNextContext) {
        IMLibPageNavigation.stepNavigation.pop()
        return // Do nothing on the last step context
      }
    }
    if (INTERMediatorOnPage[contextDef['just-leave-thisstep']]) {
      INTERMediatorOnPage[contextDef['just-leave-thisstep']]()
    }
    if (contextObj.enclosureNode.tagName === 'TBODY') {
      contextObj.enclosureNode.parentNode.style.display = 'none'
    } else {
      contextObj.enclosureNode.style.display = 'none'
    }
    nextContext = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName)
    contextDef = nextContext.getContextDef()
    if (nextContext.enclosureNode.tagName === 'TBODY') {
      nextContext.enclosureNode.parentNode.style.display = ''
    } else {
      nextContext.enclosureNode.style.display = ''
    }
    if (!hasBeforeMoveNext) {
      INTERMediator.clearCondition(IMLibPageNavigation.stepCurrentContextName)
      INTERMediator.addCondition(IMLibPageNavigation.stepCurrentContextName,
        {field: contextDef['key'], operator: '=', value: lastSelection})
    }
    await INTERMediator.constructMain(nextContext)
    IMLibPageNavigation.setupStepReturnButton('')
    if (contextDef['navi-title']) {
      IMLibLocalContext.setValue('navi_title', contextDef['navi-title'], !0)
    }
    if (INTERMediatorOnPage[contextDef['just-move-thisstep']]) {
      INTERMediatorOnPage[contextDef['just-move-thisstep']]()
    }
  },
  backToPreviousStep: async function () {
    'use strict'
    var currentContext, prevInfo, contextDef
    currentContext = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName)
    prevInfo = IMLibPageNavigation.stepNavigation.pop()
    IMLibPageNavigation.stepCurrentContextName = prevInfo.context.contextName
    if (prevInfo.context.enclosureNode.tagName === 'TBODY') {
      prevInfo.context.enclosureNode.parentNode.style.display = ''
    } else {
      prevInfo.context.enclosureNode.style.display = ''
    }
    if (IMLibPageNavigation.stepStartContextName === IMLibPageNavigation.stepCurrentContextName) {
      IMLibPageNavigation.setupStepReturnButton('none')
    }
    await INTERMediator.constructMain(currentContext)
    await INTERMediator.constructMain(prevInfo.context)
    contextDef = prevInfo.context.getContextDef()
    if (contextDef['navi-title']) {
      IMLibLocalContext.setValue('navi_title', contextDef['navi-title'], !0)
    }
    if (INTERMediatorOnPage[contextDef['just-move-thisstep']]) {
      INTERMediatorOnPage[contextDef['just-move-thisstep']]()
    }
  },

  /* --------------------------------------------------------------------
   */
  moveDetail: (keying) => {
    const keyValue = keying.split('=')
    if (keyValue.length >= 2) {
      const field = keyValue[0]
      const value = keyValue.slice(1).join('=')
      const masterContext = IMLibContextPool.getMasterContext()
      const contextDef = masterContext.getContextDef()
      const isHide = contextDef['navi-control'].match(/hide/i)
      const isHidePageNavi = isHide && !!contextDef.paging
      IMLibPageNavigation.moveToDetail(field, value, isHide, isHidePageNavi)()
    }
  },
  moveToDetail: function (keyField, keyValue, isHide, isHidePageNavi) {
    'use strict'
    var f = keyField
    let v = keyValue
    let mh = isHide
    let pnh = isHidePageNavi

    return function () {
      return IMLibPageNavigation.moveToDetailImpl(f, v, mh, pnh)
    }
  },
  moveToDetailImpl: async function (keyField, keyValue, isHide, isHidePageNavi) {
    'use strict'
    var masterContext, detailContext, contextName, masterEnclosure, detailEnclosure, node, contextDef

    IMLibPageNavigation.previousModeDetail = {
      keyField: keyField,
      keyValue: keyValue,
      isHide: isHide,
      isHidePageNavi: isHidePageNavi
    }

    masterContext = IMLibContextPool.getMasterContext()
    detailContext = IMLibContextPool.getDetailContext()
    if (detailContext) {
      if (INTERMediatorOnPage.naviBeforeMoveToDetail) {
        INTERMediatorOnPage.naviBeforeMoveToDetail(masterContext, detailContext)
      }
      contextDef = detailContext.getContextDef()
      contextName = contextDef.name
      INTERMediator.clearCondition(contextName, '_imlabel_crosstable')
      INTERMediator.addCondition(contextName, {
        field: keyField,
        operator: '=',
        value: keyValue
      }, undefined, '_imlabel_crosstable')
      await INTERMediator.constructMain(detailContext)
      INTERMediator.clearCondition(contextName)
      if (isHide) {
        INTERMediatorOnPage.masterScrollPosition = {x: window.scrollX, y: window.scrollY}
        window.scrollTo(0, 0)
        masterEnclosure = masterContext.enclosureNode
        if (masterEnclosure.tagName === 'TBODY') {
          masterEnclosure = masterEnclosure.parentNode
        }
        INTERMediator.masterNodeOriginalDisplay = masterEnclosure.style.display
        masterEnclosure.style.display = 'none'

        detailEnclosure = detailContext.enclosureNode
        if (detailEnclosure.tagName === 'TBODY') {
          detailEnclosure = detailEnclosure.parentNode
        }
        detailEnclosure.style.display = INTERMediator.detailNodeOriginalDisplay
      }
      if (isHidePageNavi) {
        document.getElementById('IM_NAVIGATOR').style.display = 'none'
      }
      if (IMLibUI.mobileNaviBackButtonId) {
        node = document.getElementById(IMLibUI.mobileNaviBackButtonId)
        node.style.display = 'inline-block'
      }
      if (INTERMediatorOnPage.naviAfterMoveToDetail) {
        masterContext = IMLibContextPool.getMasterContext()
        detailContext = IMLibContextPool.getDetailContext()
        INTERMediatorOnPage.naviAfterMoveToDetail(masterContext, detailContext)
      }
    }
  },
  setupDetailAreaToFirstRecord: function (currentContextDef, masterContext) {
    'use strict'
    var i, comp
    if (currentContextDef['navi-control'] &&
      currentContextDef['navi-control'].match(/master/i)) {
      var contextDefs = INTERMediatorOnPage.getDataSources()
      for (i in contextDefs) {
        if (contextDefs.hasOwnProperty(i) &&
          contextDefs[i] &&
          contextDefs[i].name &&
          contextDefs[i]['navi-control'] &&
          contextDefs[i]['navi-control'].match(/detail/i)) {
          if (Object.keys(masterContext.store).length > 0) {
            comp = Object.keys(masterContext.store)[0].split('=')
            if (comp.length > 1) {
              INTERMediator.clearCondition(contextDefs[i].name, '_imlabel_crosstable')
              INTERMediator.addCondition(contextDefs[i].name,
                {field: comp[0], operator: '=', value: comp[1]},
                undefined, '_imlabel_crosstable'
              )
            }
          }
        }
      }
    }
  },
  moveDetailOnceAgain: function () {
    'use strict'
    var p = IMLibPageNavigation.previousModeDetail
    IMLibPageNavigation.moveToDetailImpl(
      p.encNodeTag, p.keyField, p.keyValue, p.isHide, p.isHidePageNavi)
  },
  /* --------------------------------------------------------------------

     */
  setupBackNaviButton: function (currentContext, node) {
    'use strict'
    var buttonNode, divNode, i, masterContext, naviControlValue, currentContextDef, showingNode,
      isHidePageNavi, isUpdateMaster, isTouchRepeater, aNode, nodes, isTop

    currentContextDef = currentContext.getContextDef()

    if (!currentContextDef['navi-control'] ||
      !currentContextDef['navi-control'].match(/detail/i)) {
      return
    }

    masterContext = IMLibContextPool.getMasterContext()
    isHidePageNavi = !!masterContext.getContextDef().paging
    if (masterContext.getContextDef().paging && currentContextDef.paging) {
      INTERMediatorLog.setErrorMessage(
        'The datail context definition has the "paging" key. ' +
        'This is not required and causes bad effect to the pagenation.',
        'Detected Error'
      )
    }

    naviControlValue = masterContext.getContextDef()['navi-control']
    if (!naviControlValue || (!naviControlValue.match(/hide/i))) {
      return
    }
    isUpdateMaster = currentContextDef['navi-control'].match(/update/i)
    isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet
    isTop = !(currentContextDef['navi-control'].match(/bottom/i))

    showingNode = currentContext.enclosureNode
    if (showingNode.tagName === 'TBODY') {
      showingNode = showingNode.parentNode
    }
    if (INTERMediator.detailNodeOriginalDisplay) {
      INTERMediator.detailNodeOriginalDisplay = showingNode.style.display
    }
    showingNode.style.display = 'none'

    if (isTouchRepeater) {
      nodes = document.getElementsByClassName('IM_Button_BackNavi')
      if (!nodes || nodes.length === 0) {
        aNode = createBackButton('DIV', currentContextDef)
        IMLibUI.mobileNaviBackButtonId = aNode.id
        aNode.style.display = 'none'
        nodes = INTERMediatorLib.getElementsByAttributeValue( // Check jQuery Mobile
          document.getElementsByTagName('BODY')[0], 'data-role', isTop ? 'header' : 'footer')
        if (nodes && nodes[0]) {
          if (nodes[0].firstChild) {
            nodes[0].insertBefore(aNode, nodes[0].firstChild)
          } else {
            nodes[0].appendChild(aNode)
          }
        } else { // If the page doesn't use JQuery Mobile
          switch (node.tagName) {
            case 'TBODY':
              tbodyTargetNode(node, isTop, aNode)
              break
            case 'UL':
            case 'OL':
              genericTargetNode(node, isTop, 'LI', aNode)
              break
            case 'SELECT':
              break
            default:
              genericTargetNode(node, isTop, 'DIV', aNode)
              break
          }
        }
        if (!aNode.id) {
          aNode.id = INTERMediator.nextIdValue()
        }
        INTERMediator.eventListenerPostAdding.push({
          'id': aNode.id,
          'event': 'touchstart',
          'todo': moveToMaster(
            masterContext, currentContext, isHidePageNavi, isUpdateMaster)
        })
      }
    } else {
      buttonNode = createBackButton('BUTTON', currentContextDef)
      switch (node.tagName) {
        case 'TBODY':
          tbodyTargetNode(node, isTop, buttonNode)
          break
        case 'UL':
        case 'OL':
          genericTargetNode(node, isTop, 'LI', buttonNode)
          break
        case 'SELECT':
          break
        default:
          genericTargetNode(node, isTop, 'DIV', buttonNode)
          break
      }
      INTERMediatorLib.addEvent(
        buttonNode,
        'click',
        moveToMaster(masterContext, currentContext, isHidePageNavi, isUpdateMaster)
      )
    }

    function createBackButton(tagName, currentContextDef) {
      var buttonNode, buttonName
      buttonNode = document.createElement(tagName)
      buttonNode.setAttribute('class', 'IM_Button_BackNavi')
      buttonName = INTERMediatorOnPage.getMessages()[13]
      if (currentContextDef['button-names'] && currentContextDef['button-names']['navi-back']) {
        buttonName = currentContextDef['button-names']['navi-back']
      }
      buttonNode.appendChild(document.createTextNode(buttonName))
      setIdForIMButtons(buttonNode)
      return buttonNode
    }

    function setIdForIMButtons(node) {
      var thisId
      thisId = INTERMediator.nextIdValue('BackButton') // 'IM_Button_' + INTERMediator.buttonIdNum
      node.setAttribute('id', thisId)
      INTERMediator.buttonIdNum++
    }

    function tbodyTargetNode(node, isTop, buttonNode) {
      var targetNodeTag, enclosedNode, firstLevelNodes, targetNode, existingButtons, trNode, tdNode

      targetNodeTag = isTop ? 'THEAD' : 'TFOOT'
      enclosedNode = node.parentNode
      firstLevelNodes = enclosedNode.childNodes
      targetNode = null
      for (i = 0; i < firstLevelNodes.length; i += 1) {
        if (firstLevelNodes[i].tagName === targetNodeTag) {
          targetNode = firstLevelNodes[i]
          break
        }
      }
      if (targetNode === null) {
        targetNode = document.createElement(targetNodeTag)
        INTERMediator.appendingNodesAtLast.push({
          targetNode: targetNode,
          parentNode: enclosedNode,
          siblingNode: (targetNodeTag === 'THEAD') ? enclosedNode.firstChild : null
        })
      }
      existingButtons = INTERMediatorLib.getElementsByClassName(targetNode, 'IM_Button_BackNavi')
      if (existingButtons.length === 0) {
        trNode = document.createElement('TR')
        trNode.setAttribute('class', 'IM_NaviBack_TR')
        tdNode = document.createElement('TD')
        tdNode.setAttribute('colspan', 100)
        tdNode.setAttribute('class', 'IM_NaviBack_TD')
        INTERMediator.setIdValue(trNode)
        targetNode.appendChild(trNode)
        trNode.appendChild(tdNode)
        tdNode.appendChild(buttonNode)
      }
    }

    function genericTargetNode(node, isTop, naviEncTag, buttonNode) {
      var newNode, existingButtons
      newNode = document.createElement(naviEncTag)
      existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_BackNavi')
      if (existingButtons.length === 0) {
        newNode.appendChild(buttonNode)
        if (!isTop) {
          node.appendChild(newNode)
        } else {
          node.insertBefore(newNode, node.firstChild)
        }
      }
    }

    function moveToMaster(a, b, c, d) {
      var masterContextCL = a
      let detailContextCL = b
      let pageNaviShow = c
      let masterUpdate = d
      let node
      return async function () {
        var showingNode
        if (INTERMediatorOnPage.naviBeforeMoveToMaster) {
          INTERMediatorOnPage.naviBeforeMoveToMaster(masterContextCL, detailContextCL)
        }
        showingNode = detailContextCL.enclosureNode
        if (showingNode.tagName === 'TBODY') {
          showingNode = showingNode.parentNode
        }
        showingNode.style.display = 'none'

        showingNode = masterContextCL.enclosureNode
        if (showingNode.tagName === 'TBODY') {
          showingNode = showingNode.parentNode
        }
        showingNode.style.display = INTERMediator.masterNodeOriginalDisplay

        if (pageNaviShow) {
          document.getElementById('IM_NAVIGATOR').style.display = 'block'
        }
        if (masterUpdate) {
          await INTERMediator.constructMain(masterContextCL)
        }
        if (IMLibUI.mobileNaviBackButtonId) {
          node = document.getElementById(IMLibUI.mobileNaviBackButtonId)
          node.style.display = 'none'
        }
        if (INTERMediatorOnPage.naviAfterMoveToMaster) {
          masterContextCL = IMLibContextPool.getMasterContext()
          detailContextCL = IMLibContextPool.getDetailContext()
          INTERMediatorOnPage.naviAfterMoveToMaster(masterContextCL, detailContextCL)
        }
        if (INTERMediatorOnPage.masterScrollPosition) {
          window.scrollTo(
            INTERMediatorOnPage.masterScrollPosition.x,
            INTERMediatorOnPage.masterScrollPosition.y)
        }
      }
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibPageNavigation
