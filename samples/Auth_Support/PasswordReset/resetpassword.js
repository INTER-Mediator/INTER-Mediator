let resetCode = null

INTERMediatorOnPage.doBeforeConstruct = () => {
  const params = INTERMediatorOnPage.getURLParametersAsArray()
  resetCode = params['c'] ?? null
}

let returnValue = true;

function finishPasswordReset() {
  const button = document.getElementById('button')
  const sentmsg = document.getElementById('sentmsg')
  const mail = document.getElementById('mail').value
  const pass1 = document.getElementById('pass1').value
  const pass2 = document.getElementById('pass2').value
  const mailerr = document.getElementById('mailerr')
  const pass1err = document.getElementById('pass1err')
  const pass2err = document.getElementById('pass2err')
  const errormsg = document.getElementById('errormsg')
  mailerr.innerHTML = ''
  pass1err.innerHTML = ''
  pass2err.innerHTML = ''
  errormsg.innerHTML = ''
  sentmsg.innerHTML = ''
  button.style.display = ''

  if (mail === '') {
    mailerr.innerHTML = '未入力です'
    return
  }
  if (pass1 === '') {
    pass1err.innerHTML = '未入力です'
    return
  }
  if (pass2 === '') {
    pass2err.innerHTML = '未入力です'
    return
  }
  if (pass1 !== pass2) {
    pass2err.innerHTML = '2つのパスワードが一致しません'
    return
  }
  if (!resetCode) {
    errormsg.innerHTML = 'リセットのコードが発行されていません。'
    return
  }

  IMLibQueue.setTask((completeTask) => {
    const hashedPW = INTERMediatorLib.generatePasswordHash(pass2)
    INTERMediator_DBAdapter.db_query_async({
      name: "authuser_finish",
      records: 1,
      conditions: [
        {field: "email", operator: "=", value: mail},
        {field: "resetcode", operator: "=", value: resetCode},
        {field: "hashedpw", operator: "=", value: hashedPW}
      ],
    }, (result) => {
      if (result && result.dbresult && result.count === 1) {
        sentmsg.innerHTML = 'パスワードがリセットされました'
        button.style.display = 'none'
      } else {
        errormsg.innerHTML = 'エラーが発生しました。'
      }
      completeTask()
    }, () => {
      errormsg.innerHTML = 'エラーが発生しました。通信エラーが可能性として考えられます。'
      completeTask()
    })
  })
}
