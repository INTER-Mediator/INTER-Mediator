/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

INTERMediatorOnPage.syncBeforeInsert = (info) => {
  console.log(info)
  return true
}

INTERMediatorOnPage.syncBeforeUpdate = (info) => {
  console.log(info)
  return true
}

INTERMediatorOnPage.syncBeforeDelete = (info) => {
  if (info.entity == 'pearson') {
    alert('The current record deleted on another client. The contents of this page is going to delete soon.')
  }
  return true
}
