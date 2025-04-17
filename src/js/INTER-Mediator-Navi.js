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
//const INTERMediator_DBAdapter = require("./Adapter_DBServer");
//const IMLibLocalContext = require("./INTER-Mediator-LocalContext");
/**
 *
 * Usually you don't have to instantiate this class with the new operator.
 * @constructor
 */
/**
 * IMLibPageNavigation handles page navigation functionality
 * @type {Object}
 */
const IMLibPageNavigation = {
  /** @type {Array} Stores delete/insert navigation items */
  deleteInsertOnNavi: [],

  /** @type {Array} Backup of delete/insert navigation items */
  deleteInsertOnNaviBackup: [],

  /** @type {boolean} Flag to keep navigation array during operations */
  isKeepOnNaviArray: false,

  /** @type {Object|null} Stores previous detail mode state */
  previousModeDetail: null,

  /** @type {Array} Stores navigation steps */
  stepNavigation: [],

  /** @type {string|null} Current context name in step navigation */
  stepCurrentContextName: null,

  /** @type {string|null} Starting context name in step navigation */
  stepStartContextName: null,

  /** @type {string} Original display style of master node */
  masterNodeOriginalDisplay: 'block',

  /**
   * Create Navigation Bar to move previous/next page
   */

  /**
   * Sets up navigation controls including buttons and event handlers
   * Creates navigation bar for page movement and handles insert/delete/copy operations
   */
  navigationSetup: function () {
    'use strict'
    if (INTERMediator.partialConstructing) {
      IMLibPageNavigation.deleteInsertOnNavi = IMLibPageNavigation.deleteInsertOnNaviBackup
    }

    const allNavNodes = allNavigator()
    for (let ix = 0; ix < allNavNodes.length; ix++) {
      const navigation = allNavNodes[ix]
      if (!IMLibContextPool.getPagingContext()) {
        navigation.style.display = 'none'
        return
      }
      while (navigation.firstChild) {
        navigation.removeChild(navigation.lastChild)
      }
      // const insideNav = navigation.childNodes
      // for (let i = 0; i < insideNav.length; i += 1) {
      //   navigation.removeChild(insideNav[i])
      // }
      navigation.innerHTML = ''
      navigation.setAttribute('class', navigation.getAttribute('class') + ' IM_NAV_panel')
      const navLabel = INTERMediator.navigationLabel

      if (navLabel === null || navLabel[8] !== false) {
        const node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[8] === null || typeof navLabel[8] === 'undefined')
            ? INTERMediatorOnPage.getMessages()[2] : navLabel[8]))
        node.setAttribute('class', 'IM_NAV_update_button IM_NAV_button')
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, function () {
          IMLibQueue.setTask(async (complete) => {
            complete()
            INTERMediator.initialize()
            IMLibLocalContext.archive()
            await INTERMediator_DBAdapter.unregister()
            location.reload()
          }, false, true)
        })
      }

      let node
      const start = parseInt(INTERMediator.startFrom)
      const pageSize = parseInt(INTERMediator.pagedSize)
      const allCount = parseInt(INTERMediator.pagedAllCount)
      const disableClass = ' IM_NAV_disabled'
      if (navLabel === null || navLabel[4] !== false) {
        const dataSource = IMLibContextPool.getPagingContext().getContextDef()
        if (dataSource && dataSource.maxrecords && dataSource.maxrecords < parseInt(INTERMediator.pagedSize)) {
          INTERMediator.pagedSize = dataSource.maxrecords
        }
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          ((navLabel === null || navLabel[4] === null) ? INTERMediatorOnPage.getMessages()[1]
            : navLabel[4]) + (allCount === 0 ? 0 : start + 1)
          + ((Math.min(start + pageSize, allCount) - start > 1) ? (((navLabel === null || navLabel[5] === null) ? '-'
            : navLabel[5]) + Math.min(start + pageSize, allCount)) : '')
          + ((navLabel === null || navLabel[6] === null) ? ' / ' : navLabel[6])
          + (allCount) + ((navLabel === null || navLabel[7] === null) ? '' : navLabel[7])))
        node.setAttribute('class', 'IM_NAV_info')
      }

      if ((navLabel === null || navLabel[0] !== false) && INTERMediator.pagination === true) {
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[0] === null || typeof navLabel[0] === 'undefined') ? '<<' : navLabel[0]))
        node.setAttribute('class', 'IM_NAV_move_button IM_NAV_button' + (start === 0 ? disableClass : ''))
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, function () {
          IMLibQueue.setTask((complete) => {
            complete()
            IMLibPageNavigation.moveRecordFromNavi('navimoving', 0)
          })
        })

        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[1] === null || typeof navLabel[1] === 'undefined') ? '<' : navLabel[1]))
        node.setAttribute('class', 'IM_NAV_move_button IM_NAV_button' + (start === 0 ? disableClass : ''))
        const prevPageCount = (start - pageSize > 0) ? start - pageSize : 0
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, (function () {
          const pageCount = prevPageCount
          return function () {
            IMLibQueue.setTask((complete) => {
              complete()
              IMLibPageNavigation.moveRecordFromNavi('navimoving', pageCount)
            })
          }
        })())

        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[2] === null || typeof navLabel[2] === 'undefined') ? '>' : navLabel[2]))
        node.setAttribute('class', 'IM_NAV_move_button IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''))
        const nextPageCount = (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0)
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, (function () {
          const pageCount = nextPageCount
          return function () {
            IMLibQueue.setTask((complete) => {
              complete()
              IMLibPageNavigation.moveRecordFromNavi('navimoving', pageCount)
            })
          }
        })())

        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(
          (navLabel === null || navLabel[3] === null || typeof navLabel[3] === 'undefined') ? '>>' : navLabel[3]))
        node.setAttribute('class', 'IM_NAV_move_button IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''))
        const endPageCount = (allCount % pageSize === 0) ? allCount - (allCount % pageSize) - pageSize : allCount - (allCount % pageSize)
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        IMLibMouseEventDispatch.setExecute(node.id, (function () {
          const pageCount = endPageCount
          return function () {
            IMLibQueue.setTask((complete) => {
              complete()
              IMLibPageNavigation.moveRecordFromNavi('navimoving', (pageCount > 0) ? pageCount : 0)
            })
          }
        })())

        // Get from http://agilmente.com/blog/2013/08/04/inter-mediator_pagenation_1/
        node = document.createElement('SPAN')
        navigation.appendChild(node)
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[10]))
        const c_node = document.createElement('INPUT')
        c_node.setAttribute('class', 'IM_NAV_JUMP')
        c_node.setAttribute('type', 'number')
        c_node.setAttribute('min', 1)
        if (!c_node.id) {
          c_node.id = INTERMediator.nextIdValue()
        }
        c_node.setAttribute('value', String(Math.ceil(INTERMediator.startFrom / pageSize + 1)))
        node.appendChild(c_node)
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[11]))
        // ---------
        IMLibChangeEventDispatch.setExecute(c_node.id, (function () {
          const targetNode = c_node
          return function () {
            IMLibQueue.setTask((complete) => {
              complete()
              let moveTo = parseInt(targetNode.value)
              if (moveTo < 1) {
                moveTo = 1
              }
              const max_page = Math.ceil(allCount / pageSize)
              if (max_page < moveTo) {
                moveTo = max_page
              }
              INTERMediator.startFrom = (moveTo - 1) * pageSize
              INTERMediator.constructMain(true)
            }, false, true)
          }
        })())
      }

      let contextName
      let contextDef
      let buttonLabel
      if (navLabel === null || navLabel[9] !== false) {
        for (let i = 0; i < IMLibPageNavigation.deleteInsertOnNavi.length; i += 1) {
          switch (IMLibPageNavigation.deleteInsertOnNavi[i].kind) {
            case 'INSERT':
              node = document.createElement('SPAN')
              navigation.appendChild(node)
              contextName = IMLibPageNavigation.deleteInsertOnNavi[i].name
              contextDef = IMLibContextPool.getContextDef(contextName)
              buttonLabel = (contextDef && contextDef['button-names'] && contextDef['button-names'].insert)
                ? contextDef['button-names'].insert : INTERMediatorOnPage.getMessages()[3] + ': ' + contextName
              node.appendChild(document.createTextNode(buttonLabel))
              node.setAttribute('class', 'IM_NAV_insert_button IM_NAV_button')
              if (!node.id) {
                node.id = INTERMediator.nextIdValue()
              }
              IMLibMouseEventDispatch.setExecute(node.id, (function () {
                const obj = IMLibPageNavigation.deleteInsertOnNavi[i]
                const contextName = obj.name
                const keyValue = obj.key
                const confirming = obj.confirm
                return function () {
                  IMLibPageNavigation.insertRecordFromNavi(contextName, keyValue, confirming)
                }
              })())
              break
            case 'DELETE':
              node = document.createElement('SPAN')
              navigation.appendChild(node)
              contextName = IMLibPageNavigation.deleteInsertOnNavi[i].name
              contextDef = IMLibContextPool.getContextDef(contextName)
              buttonLabel = (contextDef && contextDef['button-names'] && contextDef['button-names'].delete)
                ? contextDef['button-names'].delete : INTERMediatorOnPage.getMessages()[4] + ': ' + contextName
              node.appendChild(document.createTextNode(buttonLabel))
              node.setAttribute('class', 'IM_NAV_delete_button IM_NAV_button')
              INTERMediatorLib.addEvent(node, 'click', (function () {
                const obj = IMLibPageNavigation.deleteInsertOnNavi[i]
                const contextName = obj.name
                const keyName = obj.key
                const keyValue = obj.value
                const confirming = obj.confirm
                return function () {
                  IMLibPageNavigation.deleteRecordFromNavi(contextName, keyName, keyValue, confirming)
                }
              })())
              break
            case 'COPY':
              node = document.createElement('SPAN')
              navigation.appendChild(node)
              contextName = IMLibPageNavigation.deleteInsertOnNavi[i].name
              contextDef = IMLibContextPool.getContextDef(contextName)
              buttonLabel = (contextDef && contextDef['button-names'] && contextDef['button-names'].copy)
                ? contextDef['button-names'].copy : INTERMediatorOnPage.getMessages()[15] + ': ' + contextName
              node.appendChild(document.createTextNode(buttonLabel))
              node.setAttribute('class', 'IM_NAV_copy_button IM_NAV_button')
              if (!node.id) {
                node.id = INTERMediator.nextIdValue()
              }
              IMLibMouseEventDispatch.setExecute(node.id, (function () {
                const obj = IMLibPageNavigation.deleteInsertOnNavi[i]
                const contextDef = obj.contextDef
                const record = obj.keyValue
                return function () {
                  IMLibPageNavigation.copyRecordFromNavi(contextDef, record)
                }
              })())
              break
          }
        }
      }
      if (navLabel === null || navLabel[10] !== false) {
        if (INTERMediatorOnPage.getOptionsTransaction() === 'none') {
          node = document.createElement('SPAN')
          navigation.appendChild(node)
          node.appendChild(document.createTextNode(
            (navLabel === null || navLabel[10] === null || typeof navLabel[10] === 'undefined')
              ? INTERMediatorOnPage.getMessages()[7] : navLabel[10]))
          node.setAttribute('class', 'IM_NAV_save_button IM_NAV_button')
          INTERMediatorLib.addEvent(node, 'click', IMLibPageNavigation.saveRecordFromNavi)
        }
      }
      if (navLabel === null || navLabel[11] !== false) {
        if (INTERMediatorOnPage.requireAuthentication) {
          node = document.createElement('SPAN')
          navigation.appendChild(node)
          node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[8] + INTERMediatorOnPage.authUser()))
          node.setAttribute('class', 'IM_NAV_info')

          node = document.createElement('SPAN')
          navigation.appendChild(node)
          node.appendChild(document.createTextNode(
            (navLabel === null || navLabel[11] === null || typeof navLabel[11] === 'undefined')
              ? INTERMediatorOnPage.getMessages()[9] : navLabel[11]))
          node.setAttribute('class', 'IM_NAV_logout_button IM_NAV_button')
          if (!node.id) {
            node.id = INTERMediator.nextIdValue()
          }
          IMLibMouseEventDispatch.setExecute(node.id, function () {
            IMLibQueue.setTask((complete) => {
              complete()
              INTERMediatorOnPage.logout()
            }, false, true)
          })
        }
      }
      if (!INTERMediator.partialConstructing) {
        IMLibPageNavigation.deleteInsertOnNaviBackup = IMLibPageNavigation.deleteInsertOnNavi
      }
    }

    function allNavigator() {
      const nodes = []
      const naviIdElement = document.getElementById('IM_NAVIGATOR')
      if (naviIdElement) {
        //naviIdElement.removeAttribute('id')
        nodes.push(naviIdElement)
      }
      const naviClassElements = document.getElementsByClassName('IM_NAVIGATOR')
      if (naviClassElements) {
        for (let ix = 0; ix < naviClassElements.length; ix++) {
          nodes.push(naviClassElements[ix])
        }
      }
      return nodes
    }
  },

  /**
   * Moves to specified page in navigation
   * @param {string} targetName - Target context name
   * @param {number} page - Page number to move to
   * @returns {Promise<void>}
   */
  moveRecordFromNavi: async function (targetName, page) {
    'use strict'
    await INTERMediator_DBAdapter.unregister()
    INTERMediator.startFrom = page
    INTERMediator.constructMain(true)
  },

  /**
   * Inserts a new record from navigation
   * @param {string} targetName - Context name to insert into
   * @param {string} keyField - Key field name
   * @param {boolean} isConfirm - Whether to show confirmation dialog
   */
  insertRecordFromNavi: function (targetName, keyField, isConfirm) {
    'use strict'
    const contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName)
    if (contextDef === null) {
      window.alert('no targetname :' + targetName)
      return
    }
    if (isConfirm) {
      let confirmMessage = INTERMediatorOnPage.getMessages()[1026]
      if (contextDef['confirm-messages'] && contextDef['confirm-messages']['insert']) {
        confirmMessage = contextDef['confirm-messages']['insert']
      }
      if (!window.confirm(confirmMessage)) {
        return
      }
    }
    INTERMediatorOnPage.showProgress()

    IMLibQueue.setTask((function () {
      const contextDefCapt = contextDef
      const targetNameCapt = targetName
      const keyFieldCapt = keyField
      const isConfirmCapt = isConfirm
      return function (completeTask) {
        try {
          // await INTERMediatorOnPage.retrieveAuthInfo()
          INTERMediator_DBAdapter.db_createRecord_async(
            {name: targetNameCapt, dataset: []},
            async function (response) {
              const newId = response.newRecordKeyValue
              INTERMediatorOnPage.newRecordId = newId
              if (newId > -1) {
                const restore = INTERMediator.additionalCondition
                if (contextDefCapt.records <= 1) {
                  INTERMediator.startFrom = 0
                  INTERMediator.pagedAllCount = 1
                  const conditions = INTERMediator.additionalCondition
                  conditions[targetNameCapt] = {field: keyFieldCapt, value: newId}
                  INTERMediator.additionalCondition = conditions
                  IMLibLocalContext.archive()
                } else {
                  INTERMediator.pagedAllCount++
                }
                completeTask()
                await INTERMediator_DBAdapter.unregister()
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
            }, completeTask)
        } catch (ex) {
          completeTask()
          if (ex.message === '_im_auth_required_') {
            if (INTERMediatorOnPage.requireAuthentication) {
              if (!INTERMediatorOnPage.isComplementAuthData()) {
                INTERMediatorOnPage.clearCredentials()
                INTERMediatorOnPage.authenticating(function () {
                  IMLibPageNavigation.insertRecordFromNavi(targetNameCapt, keyFieldCapt, isConfirmCapt)
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

  /**
   * Deletes a record from navigation
   * @param {string} targetName - Context name to delete from
   * @param {string} keyField - Key field name
   * @param {string} keyValue - Value of key field
   * @param {boolean} isConfirm - Whether to show confirmation dialog
   */
  deleteRecordFromNavi: function (targetName, keyField, keyValue, isConfirm) {
    'use strict'
    const contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName)
    if (isConfirm) {
      let confirmMessage = INTERMediatorOnPage.getMessages()[1025]
      if (contextDef['confirm-messages'] && contextDef['confirm-messages']['delete']) {
        confirmMessage = contextDef['confirm-messages']['delete']
      }
      if (!window.confirm(confirmMessage)) {
        return
      }
    }
    IMLibQueue.setTask((function () {
      const deleteArgs = {
        name: targetName, conditions: [{field: keyField, operator: '=', value: keyValue}]
      }
      return function (completeTask) {
        INTERMediatorOnPage.showProgress()
        try {
          // await INTERMediatorOnPage.retrieveAuthInfo()
          INTERMediator_DBAdapter.db_delete_async(deleteArgs, async () => {
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
          }, () => {
            completeTask()
          })
        } catch (ex) {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-6')
          completeTask()
        }
      }
    })())
  },

  /**
   * Copies a record from navigation
   * @param {Object} contextDef - Context definition object
   * @param {string} keyValue - Value of key field
   */
  copyRecordFromNavi: function (contextDef, keyValue) {
    'use strict'
    if (contextDef['repeat-control'].match(/confirm-copy/)) {
      let confirmMessage = INTERMediatorOnPage.getMessages()[1041]
      if (contextDef['confirm-messages'] && contextDef['confirm-messages']['copy']) {
        confirmMessage = contextDef['confirm-messages']['copy']
      }
      if (!window.confirm(confirmMessage)) {
        return
      }
    }
    IMLibQueue.setTask((function () {
      const contextDefCapt = contextDef
      const keyValueCapt = keyValue
      return function (completeTask) {
        INTERMediatorOnPage.showProgress()
        try {
          if (contextDefCapt.relation) {
            for (let index in contextDefCapt.relation) {
              if (contextDefCapt.relation[index].portal === true) {
                contextDefCapt.portal = true
              }
            }
          }
          const assocDef = []
          if (contextDefCapt['repeat-control'].match(/copy-/)) {
            let pStart = contextDefCapt['repeat-control'].indexOf('copy-')
            let copyTerm = contextDefCapt['repeat-control'].substr(pStart + 5)
            if ((pStart = copyTerm.search(/\s/)) > -1) {
              copyTerm = copyTerm.substr(0, pStart)
            }
            const assocContexts = copyTerm.split(',')
            for (let i = 0; i < assocContexts.length; i += 1) {
              const def = IMLibContextPool.getContextDef(assocContexts[i])
              if (def.relation[0]['foreign-key']) {
                assocDef.push({
                  name: def.name, field: def.relation[0]['foreign-key'], value: keyValueCapt
                })
              }
            }
          }
          // await INTERMediatorOnPage.retrieveAuthInfo()
          INTERMediator_DBAdapter.db_copy_async({
            name: contextDefCapt.name,
            conditions: [{field: contextDefCapt.key, operator: '=', value: keyValueCapt}],
            associated: assocDef.length > 0 ? assocDef : null
          }, (function () {
            const contextDefCapt2 = contextDefCapt
            return async function (result) {
              const newId = result.newRecordKeyValue
              INTERMediatorOnPage.newRecordId = newId
              completeTask()
              if (newId > -1) {
                const restore = INTERMediator.additionalCondition
                INTERMediator.startFrom = 0
                if (contextDefCapt2.records <= 1) {
                  const conditions = INTERMediator.additionalCondition
                  conditions[contextDefCapt2.name] = {field: contextDefCapt2.key, value: newId}
                  INTERMediator.additionalCondition = conditions
                  IMLibLocalContext.archive()
                }
                await INTERMediator_DBAdapter.unregister()
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
          })(), completeTask)
        } catch (ex) {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-43')
          completeTask()
        }
      }
    })())
  },

  saveRecordFromNavi: async function (dontUpdate) {
    'use strict'
    INTERMediatorOnPage.showProgress()
    // await INTERMediatorOnPage.retrieveAuthInfo()
    for (let i = 0; i < IMLibContextPool.poolingContexts.length; i += 1) {
      const context = IMLibContextPool.poolingContexts[i]
      const updateData = context.getModified()
      for (const keying in updateData) {
        if (updateData.hasOwnProperty(keying)) {
          const fieldArray = []
          const valueArray = []
          for (const field in updateData[keying]) {
            if (updateData[keying].hasOwnProperty(field)) {
              fieldArray.push(field)
              valueArray.push({field: field, value: updateData[keying][field]})
            }
          }
          const keyingComp = keying.split('=')
          const keyingField = keyingComp[0]
          keyingComp.shift()
          const keyingValue = keyingComp.join('=')
          let currentVal = null
          if (!INTERMediator.ignoreOptimisticLocking) {
            const checkQueryParameter = {
              name: context.contextName,
              records: 1,
              paging: false,
              fields: fieldArray,
              parentkeyvalue: null,
              conditions: [{field: keyingField, operator: '=', value: keyingValue}],
              useoffset: false,
              primaryKeyOnly: true
            }
            try {
              await INTERMediator_DBAdapter.db_query_async(checkQueryParameter, function (result) {
                currentVal = result
              }, null)
            } catch (ex) {
              if (ex.message === '_im_auth_required_') {
                if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                  INTERMediatorOnPage.clearCredentials()
                  INTERMediatorOnPage.authenticating((function () {
                    const qParam = checkQueryParameter
                    return async function () {
                      await INTERMediator_DBAdapter.db_query_async(qParam, null, null)
                    }
                  })())
                  return
                }
              } else {
                INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-28')
              }
            }

            if (currentVal.dbresult === null || currentVal.dbresult[0] === null) {
              window.alert(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1003], [fieldArray.join(',')]))
              return
            }
            if (currentVal.count > 1) {
              if (!window.confirm(INTERMediatorOnPage.getMessages()[1024])) {
                return
              }
            }

            let difference = false
            for (const field in updateData[keying]) {
              if (updateData[keying].hasOwnProperty(field)) {
                const initialValue = context.getValue(keying, field)
                if (initialValue !== currentVal.dbresult[0][field]) {
                  difference += INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1035], [field, currentVal.dbresult[0][field], updateData[keying][field]])
                }
              }
            }
            if (difference !== false) {
              if (!window.confirm(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1034], [difference]))) {
                return
              }
              // await INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
            }
          }

          try {
            INTERMediator_DBAdapter.db_update({
              name: context.contextName,
              conditions: [{field: keyingField, operator: '=', value: keyingValue}],
              dataset: valueArray
            })
          } catch (ex) {
            if (ex.message === '_im_auth_required_') {
              if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                INTERMediatorOnPage.clearCredentials()
                INTERMediatorOnPage.authenticating(function () {
                  IMLibPageNavigation.saveRecordFromNavi(dontUpdate)
                })
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
    if (dontUpdate !== true) {
      await INTERMediator.constructMain(true)
    }
    INTERMediatorOnPage.hideProgress()
    INTERMediatorLog.flushMessage()
  },

  /* --------------------------------------------------------------------
     Handling Copy buttons
     */
  setupCopyButton: function (encNodeTag, repNodeTag, repeaters, currentContext, currentRecord) {
    'use strict'
    const currentContextDef = currentContext.getContextDef()
    if (!currentContextDef['repeat-control'] || !currentContextDef['repeat-control'].match(/copy/i)) {
      return
    }
    if (currentContextDef.relation || typeof (currentContextDef.records) === 'undefined' || !currentContextDef.paging || (currentContextDef.records > 1 && parseInt(INTERMediator.pagedSize) !== 1)) {
      const buttonNode = document.createElement('BUTTON')
      buttonNode.setAttribute('class', 'IM_Button_Copy' + (INTERMediatorOnPage.buttonClassCopy ? (' ' + INTERMediatorOnPage.buttonClassCopy) : ''))
      let buttonName = INTERMediatorOnPage.getMessages()[14]
      if (currentContextDef['button-names'] && currentContextDef['button-names'].copy) {
        buttonName = currentContextDef['button-names'].copy
      }
      buttonNode.appendChild(document.createTextNode(buttonName))
      const thisId = INTERMediator.nextIdValue('CopyButton') // 'IM_Button_' + INTERMediator.buttonIdNum
      buttonNode.setAttribute('id', thisId)
      INTERMediator.buttonIdNum++
      IMLibMouseEventDispatch.setExecute(thisId, (function () {
        const currentContextCapt = currentContext
        const currentRecordCapt = currentRecord[currentContextDef.key]
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
     Handling Delete buttons
   */
  setupDeleteButton: function (encNodeTag, repeaters, currentContext, keyField, keyValue) {
    'use strict'
    const currentContextDef = currentContext.contextDefinition
    if (!currentContextDef['repeat-control'] || !currentContextDef['repeat-control'].match(/delete/i)) {
      return
    }
    if (currentContextDef.relation || typeof (currentContextDef.records) === 'undefined' || !currentContextDef.paging || (currentContextDef.records > 1 && parseInt(INTERMediator.pagedSize) !== 1)) {
      const buttonNode = document.createElement('BUTTON')
      buttonNode.setAttribute('class', 'IM_Button_Delete' + (INTERMediatorOnPage.buttonClassDelete ? (' ' + INTERMediatorOnPage.buttonClassDelete) : ''))
      let buttonName = INTERMediatorOnPage.getMessages()[6]
      if (currentContextDef['button-names'] && currentContextDef['button-names'].delete) {
        buttonName = currentContextDef['button-names'].delete
      }
      buttonNode.appendChild(document.createTextNode(buttonName))
      const thisId = INTERMediator.nextIdValue('DeleteButton') // 'IM_Button_' + INTERMediator.buttonIdNum
      buttonNode.setAttribute('id', thisId)
      INTERMediator.buttonIdNum++
      IMLibMouseEventDispatch.setExecute(thisId, (function () {
        const currentContextCapt = currentContext
        const keyFieldCapt = keyField
        const keyValueCapt = keyValue
        const confirmingCapt = currentContextDef['repeat-control'].match(/(confirm-delete|delete-confirm)/i)
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
        confirm: currentContextDef['repeat-control'].match(/(confirm-delete|delete-confirm)/i)
      })
    }
  },

  includeButtonInContext: function (encNodeTag, repeaters, buttonNode) {
    let repeaterCtl, repeaterIx

    const ignoreTerms = ['header', 'separator', 'footerheader', 'separator', 'footer']
    switch (encNodeTag) {
      case 'TBODY':
        repeaterIx = repeaters.length - 1
        while (repeaterIx >= 0) {
          repeaterCtl = repeaters[repeaterIx].getAttribute('data-im-control')
          if (!repeaterCtl || (repeaterCtl && ignoreTerms.indexOf(repeaterCtl.toLowerCase()) < 0)) {
            const tdNodes = repeaters[repeaterIx].getElementsByTagName('TD')
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
          repeaterCtl = repeaters[repeaterIx].getAttribute('data-im-control')
          const repeaterIMCtrl = repeaterCtl
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
  }, /* --------------------------------------------------------------------

   */
  /**
   * Sets up insert button
   * @param {Object} currentContext - Current context object
   * @param {string} keyValue - Value of key field
   * @param {HTMLElement} node - DOM node to attach button to
   * @param {Array} relationValue - Related values array
   */
  setupInsertButton: function (currentContext, keyValue, node, relationValue) {
    'use strict'
    const encNodeTag = node.tagName
    const currentContextDef = currentContext.getContextDef()
    if (currentContextDef['repeat-control'] && currentContextDef['repeat-control'].match(/insert/i)) {
      if (relationValue.length > 0 || !currentContextDef.paging || currentContextDef.paging === false) {
        const buttonNode = document.createElement('BUTTON')
        buttonNode.setAttribute('class', 'IM_Button_Insert' + (INTERMediatorOnPage.buttonClassInsert ? (' ' + INTERMediatorOnPage.buttonClassInsert) : ''))
        let buttonName = INTERMediatorOnPage.getMessages()[5]
        if (currentContextDef['button-names'] && currentContextDef['button-names'].insert) {
          buttonName = currentContextDef['button-names'].insert
        }
        buttonNode.appendChild(document.createTextNode(buttonName))
        const thisId = INTERMediator.nextIdValue('InsertButton') // 'IM_Button_' + INTERMediator.buttonIdNum
        buttonNode.setAttribute('id', thisId)
        INTERMediator.buttonIdNum++
        let existingButtons
        switch (encNodeTag) {
          case 'TBODY':
            let setTop = false
            let targetNodeTag = 'TFOOT'
            if (currentContextDef['repeat-control'].match(/top/i)) {
              targetNodeTag = 'THEAD'
              setTop = true
            }
            const enclosedNode = node.parentNode
            const firstLevelNodes = enclosedNode.childNodes
            let footNode = null
            for (let i = 0; i < firstLevelNodes.length; i += 1) {
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
              const trNode = document.createElement('TR')
              trNode.setAttribute('class', 'IM_Insert_TR')
              const tdNode = document.createElement('TD')
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
            const liNode = document.createElement('LI')
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
            const divNode = document.createElement('DIV')
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
          const context = currentContext
          const keyValueCapt = keyValue
          const relationValueCapt = relationValue
          const nodeId = node.getAttribute('id')
          const confirming = currentContextDef['repeat-control'].match(/(confirm-insert|insert-confirm)/i)
          return function () {
            IMLibUI.insertButton(context, keyValueCapt, relationValueCapt, nodeId, confirming)
          }
        })())
      } else {
        let keyField
        if (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) || INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
          keyField = currentContextDef.key ? currentContextDef.key : INTERMediatorOnPage.defaultKeyName
        } else {
          keyField = currentContextDef.key ? currentContextDef.key : 'id'
        }
        IMLibPageNavigation.deleteInsertOnNavi.push({
          kind: 'INSERT',
          name: currentContextDef.name,
          key: keyField,
          confirm: currentContextDef['repeat-control'].match(/(confirm-insert|insert-confirm)/i)
        })
      }
    }
  }, /* --------------------------------------------------------------------
     Handling Detail buttons
     */
  /**
   * Sets up navigation button
   * @param {string} encNodeTag - Enclosing node tag name
   * @param {Array} repeaters - Array of repeater elements
   * @param {Object} currentContextDef - Current context definition
   * @param {string} keyField - Key field name
   * @param {string} keyValue - Value of key field
   * @param {Object} contextObj - Context object
   */
  setupNavigationButton: function (encNodeTag, repeaters, currentContextDef, keyField, keyValue, contextObj) {
    'use strict'
    if (!currentContextDef['navi-control'] || (!currentContextDef['navi-control'].match(/master/i) && !currentContextDef['navi-control'].match(/step/i)) || encNodeTag === 'SELECT') {
      return
    }

    const isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet
    const isHide = currentContextDef['navi-control'].match(/hide/i)
    const isHidePageNavi = isHide && !!currentContextDef.paging
    const isMasterDetail = currentContextDef['navi-control'].match(/master/i)
    const isStep = currentContextDef['navi-control'].match(/step/i)
    const isNoNavi = currentContextDef['navi-control'].match(/nonavi/i)
    const isFullNavi = currentContextDef['navi-control'].match(/fullnavi/i)

    // if (isMasterDetail && INTERMediator.detailNodeOriginalDisplay) {
    //   const detailContext = IMLibContextPool.getDetailContext()
    //   if (detailContext) {
    //     let showingNode = detailContext.enclosureNode
    //     if (showingNode.tagName === 'TBODY') {
    //       showingNode = showingNode.parentNode
    //     }
    //     INTERMediator.detailNodeOriginalDisplay = showingNode.style.display
    //   }
    // }

    const buttonNode = document.createElement('BUTTON')
    buttonNode.setAttribute('class', 'IM_Button_Master' + (INTERMediatorOnPage.buttonClassMaster ? (' ' + INTERMediatorOnPage.buttonClassMaster) : ''))
    let buttonName = INTERMediatorOnPage.getMessages()[12]
    if (currentContextDef['button-names'] && currentContextDef['button-names']['navi-detail']) {
      buttonName = currentContextDef['button-names']['navi-detail']
    }
    buttonNode.appendChild(document.createTextNode(buttonName))
    const thisId = INTERMediator.nextIdValue('MasterButton') // 'IM_Button_' + INTERMediator.buttonIdNum
    buttonNode.setAttribute('id', thisId)
    INTERMediator.buttonIdNum++
    let moveToDetailFunc = null
    if (isMasterDetail) {
      const masterContext = IMLibContextPool.getMasterContext()
      masterContext.setValue(keyField + '=' + keyValue, '_im_button_master_id', thisId, thisId)
      moveToDetailFunc = IMLibPageNavigation.moveToDetail(keyField, keyValue, isHide, isHidePageNavi)
    }
    if (isStep) {
      moveToDetailFunc = IMLibPageNavigation.moveToNextStep(contextObj, keyField, keyValue)
    }
    if ((isTouchRepeater && !isNoNavi) || isFullNavi) {
      for (let i = 0; i < repeaters.length; i += 1) {
        const originalColor = repeaters[i].style.backgroundColor
        repeaters[i].style.cursor = 'pointer'
        const css = '#' + repeaters[i].id + ':hover{background-color:' + IMLibUI.mobileSelectionColor + '}'
        const style = document.createElement('style')
        style.appendChild(document.createTextNode(css))
        document.getElementsByTagName('head')[0].appendChild(style)
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id, 'event': 'touchstart', 'todo': (function () {
            const targetNode = repeaters[i]
            return function (ev) {
              IMLibEventResponder.touchEventCancel = false
              targetNode.style.backgroundColor = IMLibUI.mobileSelectionColor
            }
          })()
        })
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id, 'event': 'click', 'todo': (function () {
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
          'id': repeaters[i].id, 'event': 'touchmove', 'todo': (function () {
            return function () {
              IMLibEventResponder.touchEventCancel = true
            }
          })()
        })
        INTERMediator.eventListenerPostAdding.push({
          'id': repeaters[i].id, 'event': 'touchcancel', 'todo': (function () {
            return function () {
              IMLibEventResponder.touchEventCancel = true
            }
          })()
        })
      }
    } else {
      if (!isNoNavi) {
        IMLibMouseEventDispatch.setExecute(thisId, moveToDetailFunc)
        let firstInNode = null
        switch (encNodeTag) {
          case 'TBODY':
            const tdNodes = repeaters[repeaters.length - 1].getElementsByTagName('TD')
            const tdNode = tdNodes[0]
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
    const lastSelection = IMLibPageNavigation.stepNavigation[IMLibPageNavigation.stepNavigation.length - 1]
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
    IMLibPageNavigation.stepNavigation = []
    IMLibPageNavigation.stepCurrentContextName = null
    IMLibPageNavigation.stepStartContextName = null
    IMLibPageNavigation.setupStepReturnButton('none')
    let isDetected = false
    if (INTERMediatorOnPage.getDataSources) { // Avoid processing on unit test
      const dataSrcs = INTERMediatorOnPage.getDataSources()
      for (let key of Object.keys(dataSrcs)) {
        const cDef = dataSrcs[key]
        if (cDef['navi-control']) {
          const judgeHide = includeHide || (!includeHide && !cDef['navi-control'].match(/hide/i))
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
            INTERMediator_DBAdapter.db_createRecord_async({
              name: IMLibPageNavigation.stepCurrentContextName,
              dataset: []
            }, function (result) {
              INTERMediator.constructMain(context)
              completeTask()
            }, function () {
              INTERMediatorLog.setErrorMessage('Insert Error', 'EXCEPTION-4')
              completeTask()
            })
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
    let lastSelection = -1
    let isAfterCurrent = false
    let control = null
    let hasNextContext = false
    let hasBeforeMoveNext = false
    let contextDef = contextObj.getContextDef()
    IMLibPageNavigation.stepNavigation.push({context: contextObj, key: keying})
    if (INTERMediatorOnPage[contextDef['before-move-nextstep']]) {
      control = INTERMediatorOnPage[contextDef['before-move-nextstep']]()
      hasBeforeMoveNext = true
    } else {
      const lastRecord = IMLibPageNavigation.getStepLastSelectedRecord()
      lastSelection = lastRecord[contextDef['key']]
    }
    if (control === false) {
      IMLibPageNavigation.stepNavigation.pop()
      return
    } else if (control) {
      IMLibPageNavigation.stepCurrentContextName = control
    } else {
      const dataSrcs = INTERMediatorOnPage.getDataSources()
      for (let key of Object.keys(dataSrcs)) {
        const cDef = dataSrcs[key]
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
    const nextContext = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName)
    contextDef = nextContext.getContextDef()
    if (nextContext.enclosureNode.tagName === 'TBODY') {
      nextContext.enclosureNode.parentNode.style.display = ''
    } else {
      nextContext.enclosureNode.style.display = ''
    }
    if (!hasBeforeMoveNext) {
      INTERMediator.clearCondition(IMLibPageNavigation.stepCurrentContextName)
      INTERMediator.addCondition(IMLibPageNavigation.stepCurrentContextName, {
        field: contextDef['key'],
        operator: '=',
        value: lastSelection
      })
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
    const currentContext = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName)
    const prevInfo = IMLibPageNavigation.stepNavigation.pop()
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
    const contextDef = prevInfo.context.getContextDef()
    if (contextDef['navi-title']) {
      IMLibLocalContext.setValue('navi_title', contextDef['navi-title'], !0)
    }
    if (INTERMediatorOnPage[contextDef['just-move-thisstep']]) {
      INTERMediatorOnPage[contextDef['just-move-thisstep']]()
    }
  },

  /* --------------------------------------------------------------------
   */
  /**
   * Moves to detail view
   * @param {string} keying - Key string in format "field=value"
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

  /**
   * Creates function to move to detail view
   * @param {string} keyField - Key field name
   * @param {string} keyValue - Value of key field
   * @param {boolean} isHide - Whether to hide master view
   * @param {boolean} isHidePageNavi - Whether to hide page navigation
   * @returns {Function} Function that moves to detail view
   */
  moveToDetail: function (keyField, keyValue, isHide, isHidePageNavi) {
    'use strict'
    const f = keyField
    const v = keyValue
    const mh = isHide
    const pnh = isHidePageNavi
    return function () {
      return IMLibPageNavigation.moveToDetailImpl(f, v, mh, pnh)
    }
  },

  moveToDetailImpl: async function (keyField, keyValue, isHide, isHidePageNavi) {
    'use strict'
    IMLibPageNavigation.previousModeDetail = {
      keyField: keyField, keyValue: keyValue, isHide: isHide, isHidePageNavi: isHidePageNavi
    }

    let masterContext = IMLibContextPool.getMasterContext()
    let detailContext = IMLibContextPool.getDetailContext()
    if (detailContext) {
      if (INTERMediatorOnPage.naviBeforeMoveToDetail) {
        INTERMediatorOnPage.naviBeforeMoveToDetail(masterContext, detailContext)
      }
      const contextName = detailContext.getContextDef().name
      INTERMediator.clearCondition(contextName, '_imlabel_crosstable')
      INTERMediator.addCondition(contextName, {
        field: keyField,
        operator: '=',
        value: keyValue
      }, undefined, '_imlabel_crosstable')
      IMLibPageNavigation.isKeepOnNaviArray = true
      await INTERMediator.constructMain(detailContext)
      IMLibPageNavigation.isKeepOnNaviArray = false
      INTERMediator.clearCondition(contextName)
      if (isHide) {
        INTERMediatorOnPage.masterScrollPosition = {x: window.scrollX, y: window.scrollY}
        INTERMediator.prepareToScrollBack(contextName, keyValue)
        window.scrollTo(0, 0)
        let masterContainer = masterContext.visiblyEnclosureNode()
        if (masterContainer.style.display !== 'none') {
          IMLibPageNavigation.masterNodeOriginalDisplay = masterContainer.style.display
          masterContainer.style.display = 'none'
        }
      }
      detailContext.visiblyEnclosureNode().style.display = "" /// INTERMediator.detailNodeOriginalDisplay

      if (isHidePageNavi) {
        document.getElementById('IM_NAVIGATOR').style.display = 'none'
      }
      if (IMLibUI.mobileNaviBackButtonId) {
        document.getElementById(IMLibUI.mobileNaviBackButtonId).style.display = 'inline-block'
      }
      if (INTERMediatorOnPage.naviAfterMoveToDetail) {
        INTERMediatorOnPage.naviAfterMoveToDetail(IMLibContextPool.getMasterContext(), IMLibContextPool.getDetailContext())
      }
    }
  },

  /**
   * Sets up detail area for first record
   * @param {Object} currentContextDef - Current context definition
   * @param {Object} masterContext - Master context object
   */
  setupDetailAreaToFirstRecord: function (currentContextDef, masterContext) {
    'use strict'
    if (currentContextDef['navi-control'] && currentContextDef['navi-control'].match(/master/i)) {
      const contextDefs = INTERMediatorOnPage.getDataSources()
      for (let i in contextDefs) {
        if (contextDefs.hasOwnProperty(i) && contextDefs[i] && contextDefs[i].name && contextDefs[i]['navi-control'] && contextDefs[i]['navi-control'].match(/detail/i)) {
          if (Object.keys(masterContext.store).length > 0) {
            const comp = Object.keys(masterContext.store)[0].split('=')
            if (comp.length > 1) {
              INTERMediator.clearCondition(contextDefs[i].name, '_imlabel_crosstable')
              INTERMediator.addCondition(contextDefs[i].name, {
                field: comp[0],
                operator: '=',
                value: comp[1]
              }, undefined, '_imlabel_crosstable')
            }
          }
        }
      }
    }
  },
  moveDetailOnceAgain: function () {
    'use strict'
    const p = IMLibPageNavigation.previousModeDetail
    IMLibPageNavigation.moveToDetailImpl(p.keyField, p.keyValue, p.isHide, p.isHidePageNavi)
  }, /* --------------------------------------------------------------------

     */
  /**
   * Sets up back navigation button
   * @param {Object} currentContext - Current context object
   * @param {HTMLElement} node - DOM node to attach button to
   */
  setupBackNaviButton: function (currentContext, node) {
    'use strict'
    // let divNode // Used in a private function
    const currentContextDef = currentContext.getContextDef()
    if (!currentContextDef['navi-control'] || !currentContextDef['navi-control'].match(/detail/i)) {
      return
    }

    const masterContext = IMLibContextPool.getMasterContext()
    const isHidePageNavi = !!masterContext.getContextDef().paging
    if (masterContext.getContextDef().paging && currentContextDef.paging) {
      INTERMediatorLog.setErrorMessage('The datail context definition has the "paging" key. ' + 'This is not required and causes bad effect to the pagenation.', 'Detected Error')
    }

    const naviControlValue = masterContext.getContextDef()['navi-control']
    if (!naviControlValue || (!naviControlValue.match(/hide/i))) {
      return
    }
    const isUpdateMaster = currentContextDef['navi-control'].match(/update/i)
    const isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet
    const isTop = !(currentContextDef['navi-control'].match(/bottom/i))
    currentContext.visiblyEnclosureNode().style.display = 'none'
    if (isTouchRepeater) {
      let nodes = document.getElementsByClassName('IM_Button_BackNavi')
      if (!nodes || nodes.length === 0) {
        const aNode = createBackButton('DIV', currentContextDef)
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
          if (node.tagName === 'TBODY') {
            tbodyTargetNode(node, isTop, aNode)
          } else {
            genericTargetNode(node, isTop, aNode)
          }
        }
        if (!aNode.id) {
          aNode.id = INTERMediator.nextIdValue()
        }
        INTERMediator.eventListenerPostAdding.push({
          'id': aNode.id,
          'event': 'touchstart',
          'todo': moveToMaster(masterContext, currentContext, isHidePageNavi, isUpdateMaster)
        })
      }
    } else {
      const buttonNode = createBackButton('BUTTON', currentContextDef)
      if (node.tagName === 'TBODY') {
        tbodyTargetNode(node, isTop, buttonNode)
      } else {
        genericTargetNode(node, isTop, buttonNode)
      }
      INTERMediatorLib.addEvent(buttonNode, 'click', moveToMaster(masterContext, currentContext, isHidePageNavi, isUpdateMaster))
    }

    function createBackButton(tagName, currentContextDef) {
      const buttonNode = document.createElement(tagName)
      buttonNode.setAttribute('class', 'IM_Button_BackNavi' + (INTERMediatorOnPage.buttonClassBackNavi ? (' ' + INTERMediatorOnPage.buttonClassBackNavi) : ''))
      let buttonName = INTERMediatorOnPage.getMessages()[13]
      if (currentContextDef['button-names'] && currentContextDef['button-names']['navi-back']) {
        buttonName = currentContextDef['button-names']['navi-back']
      }
      buttonNode.appendChild(document.createTextNode(buttonName))
      setIdForIMButtons(buttonNode)
      return buttonNode
    }

    function setIdForIMButtons(node) {
      const thisId = INTERMediator.nextIdValue('BackButton') // 'IM_Button_' + INTERMediator.buttonIdNum
      node.setAttribute('id', thisId)
      INTERMediator.buttonIdNum++
    }

    function tbodyTargetNode(node, isTop, buttonNode) {
      const targetNodeTag = isTop ? 'THEAD' : 'TFOOT'
      const enclosedNode = node.parentNode
      const firstLevelNodes = enclosedNode.childNodes
      let targetNode = null
      for (let i = 0; i < firstLevelNodes.length; i += 1) {
        if (firstLevelNodes[i].tagName === targetNodeTag) {
          targetNode = firstLevelNodes[i]
          break
        }
      }
      if (targetNode === null) {
        targetNode = document.createElement(targetNodeTag)
        let sibiling = null
        if (isTop) {
          sibiling = enclosedNode.getElementsByTagName('TBODY')[0]
        }
        INTERMediator.appendingNodesAtLast.push({
          targetNode: targetNode, parentNode: enclosedNode, siblingNode: sibiling
        })
      }
      const existingButtons = INTERMediatorLib.getElementsByClassName(targetNode, 'IM_Button_BackNavi')
      if (existingButtons.length === 0) {
        const trNode = document.createElement('TR')
        trNode.setAttribute('class', 'IM_NaviBack_TR')
        const tdNode = document.createElement('TD')
        tdNode.setAttribute('colspan', 100)
        tdNode.setAttribute('class', 'IM_NaviBack_TD')
        INTERMediator.setIdValue(trNode)
        if (isTop) {
          targetNode.insertBefore(trNode, targetNode.getElementsByTagName('TR')[0])
        } else {
          targetNode.appendChild(trNode)
        }
        trNode.appendChild(tdNode)
        tdNode.appendChild(buttonNode)
      }
    }

    function genericTargetNode(node, isTop, buttonNode) {
      const naviEncTag = (node.tagName === 'OL' || node.tagName === 'UL') ? "LI" : ((node.tagName === 'SPAN') ? "SPAN" : "DIV")
      const newNode = document.createElement(naviEncTag)
      const existingButtons = INTERMediatorLib.getElementsByClassName(node, 'IM_Button_BackNavi')
      if (existingButtons.length === 0) {
        newNode.appendChild(buttonNode)
        if (isTop) {
          node.insertBefore(newNode, node.firstChild)
        } else {
          node.appendChild(newNode)
        }
      }
    }

    function moveToMaster(a, b, c, d) {
      const masterContextCL = a
      const detailContextCL = b
      const pageNaviShow = c
      const masterUpdate = d
      return async function () {
        if (INTERMediatorOnPage.naviBeforeMoveToMaster) {
          INTERMediatorOnPage.naviBeforeMoveToMaster(masterContextCL, detailContextCL)
        }
        detailContextCL.visiblyEnclosureNode().style.display = 'none'
        masterContextCL.visiblyEnclosureNode().style.display = IMLibPageNavigation.masterNodeOriginalDisplay
        if (pageNaviShow) {
          document.getElementById('IM_NAVIGATOR').style.display = 'block'
        }
        IMLibQueue.setTask(async (complete) => {
          complete()
          if (masterUpdate) {
            await INTERMediator.constructMain(masterContextCL)
          }
          if (IMLibUI.mobileNaviBackButtonId) {
            document.getElementById(IMLibUI.mobileNaviBackButtonId).style.display = 'none'
          }
          if (INTERMediatorOnPage.naviAfterMoveToMaster) {
            INTERMediatorOnPage.naviAfterMoveToMaster(IMLibContextPool.getMasterContext(), IMLibContextPool.getDetailContext())
          }
          if (INTERMediatorOnPage.masterScrollPosition) {
            window.scrollTo(INTERMediatorOnPage.masterScrollPosition.x, INTERMediatorOnPage.masterScrollPosition.y)
            // const contextName = IMLibLocalContext.getValue("_im_sb_contextName")
            // const targetId = IMLibLocalContext.getValue("_im_sb_cid")
            // if (targetId && contextName) {
            //   const context = IMLibContextPool.contextFromName(contextName)
            //   const contextDef = context.getContextDef()
            //   const binding = context.binding[`${contextDef.key}=${targetId}`]
            //   if (binding) {
            //     const target = document.getElementById(binding._im_repeater[0].id)
            //     target.animate({
            //       backgroundColor: [target.style.backgroundColor, "#ffffff", "#7e7e7e", target.style.backgroundColor],
            //     }, 1000);
            //   }
            // }
          }
          // INTERMediator.scrollBack(0, true)
        })
      }
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibPageNavigation
