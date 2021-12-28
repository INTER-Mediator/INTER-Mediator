function doSearch() {
  let sel = 0
  const contextName = "postalcode"
  INTERMediator.clearCondition(contextName)
  INTERMediator.addCondition(contextName, {field: "f3", operator: ">=", value: "140000"})
  INTERMediator.addCondition(contextName, {field: "f3", operator: "<=", value: "180000"})
  if (document.getElementById("opt1").checked) {
    sel = 1
  } else if (document.getElementById("opt2").checked) {
    sel = 2
    INTERMediator.addCondition(contextName, {field: "__operation__"})
  } else if (document.getElementById("opt3").checked) {
    sel = 3
    INTERMediator.addCondition(contextName, {field: "__operation__", operator: "ex"})
  }
  INTERMediator.addCondition(contextName, {field: "f8", operator: "like", value: "%田%"})
  INTERMediator.addCondition(contextName, {field: "f9", operator: "like", value: "%町%"})
  INTERMediator.constructMain()
}