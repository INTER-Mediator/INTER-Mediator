function startPasswordReset() {
  const button = document.getElementById('button')
  const sentmsg = document.getElementById('sentmsg')
  const ad1 = document.getElementById('ad1').value
  const ad2 = document.getElementById('ad2').value
  const ad1err = document.getElementById('ad1err')
  const ad2err = document.getElementById('ad2err')
  const completemsg = document.getElementById('completemsg')
  const errormsg = document.getElementById('errormsg')
  ad1err.innerHTML = ''
  ad2err.innerHTML = ''
  completemsg.innerHTML = ''
  errormsg.innerHTML = ''
  sentmsg.innerHTML = ''
  button.style.display = ''

  const regexMail = new RegExp('^.+@.+$');
  if (ad1 === '') {
    ad1err.innerHTML = '未入力です';
    return;
  }
  if (!regexMail.test(ad1)) {
    ad1err.innerHTML = 'メールアドレスの形式に合致しません';
    return;
  }
  if (ad2 === '') {
    ad2err.innerHTML = '未入力です';
    return;
  }
  if (!regexMail.test(ad2)) {
    ad1err.innerHTML = 'メールアドレスの形式に合致しません';
    return;
  }
  if (ad1 !== ad2) {
    ad2err.innerHTML = '2つのメールアドレスが一致しません';
    return;
  }

  IMLibQueue.setTask((completeTask) => {
    INTERMediator_DBAdapter.db_query_async({
      name: "authuser_request", records: 1, conditions: [{field: "email", operator: "=", value: ad1}],
    }, (result) => {
      if (result && result.dbresult && result.count === 1 && result.dbresult[0]['hash']) {
        completemsg.innerHTML = 'パスワードのリセットをご案内するメールが、指定されたメールアドレスに送信されました。'
        sentmsg.innerHTML = '送信できました'
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
