var assert = buster.referee.assert;

buster.testCase("INTER-Mediator Element Test", {
    "IMLibElement.setValueToIMNode() should return false without TypeError (curVal.replace is not a function)": function () {
        var tempElement = document.createElement("textarea");
        assert.equals(IMLibElement.setValueToIMNode(tempElement, "textNode", null, true), false);
        assert.equals(IMLibElement.setValueToIMNode(tempElement, "textNode", false, true), false);
    } /*,
    "IMLibElement.checkOptimisticLock() should return false in case of handling of the local context without TypeError (contextInfo is null)": function () {
        var inputElement = document.createElement("input");
        inputElement.setAttribute("data-im", "_@localcontext");
        INTERMediatorOnPage.getOptionsAliases=function(){return {'kindid':'cor_way_kindname@kind_id@value'};};
        assert.equals(IMLibElement.checkOptimisticLock(inputElement, null), false);
    }*/
});
