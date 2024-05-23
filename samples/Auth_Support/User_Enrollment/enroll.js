INTERMediatorOnPage.processingAfterPostOnlyContext = function (targetNode, idValue) {
  if (idValue) {
    const button = document.getElementById('button')
    button.parentNode.innerHTML = '確認メールを送信しました。そちらをご確認ください。'
  }
}