/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// INTERMediatorOnPage.doBeforeConstruct = function () {
//     INTERMediatorOnPage.isShowProgress = false;
// };

INTERMediatorOnPage.doBeforeConstruct = function () {
  INTERMediatorLog.errorMessageByAlert = true
  INTERMediatorOnPage.progressStartDelay = 0
  INTERMediatorOnPage.includingParts["page_footer"] = "<small>INTER-Mediator ©2024</small>"
}

INTERMediatorOnPage.doAfterConstruct = function () {
  document.getElementById('wrapper').style.display = 'block'
}

// INTERMediatorOnPage.postRepeater_person = function (param) {
//   console.log('post repeater method called')
//   console.log(param)
// }
//
// INTERMediatorOnPage.postEnclosure_person = function (param) {
//   console.log('post enclosure method called')
//   console.log(param)
// }