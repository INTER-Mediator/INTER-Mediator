INTERMediatorOnPage.doAfterConstruct = function () {
  document.getElementById('wrapper').style.display = 'block'
}

function sendMail(num, pid) {
  IMLibQueue.setTask((complete) => {
    INTERMediator_DBAdapter.db_query_async(
      {
        name: `mail_send_${num}`,
        records: 1,
        useoffset: false,
        conditions: [{field: "id", operator: "=", value: pid}]
      },
      (result) => {
        INTERMediatorLog.flushMessage()
        complete()
      },
      () => {
        INTERMediatorLog.flushMessage()
        complete()
      }
    )
  })
}
