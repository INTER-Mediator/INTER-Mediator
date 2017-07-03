var assert = buster.referee.assert;

buster.testCase("INTER-Mediator Element Test", {
    "IMLibElement.setValueToIMNode() should return false without TypeError (curVal.replace is not a function)": function () {
        var tempElement = document.createElement("textarea");
        assert.equals(IMLibElement.setValueToIMNode(tempElement, "textNode", null, true), false);
        assert.equals(IMLibElement.setValueToIMNode(tempElement, "textNode", false, true), false);
    }, /*,
    "IMLibElement.checkOptimisticLock() should return false in case of handling of the local context without TypeError (contextInfo is null)": function () {
        var inputElement = document.createElement("input");
        inputElement.setAttribute("data-im", "_@localcontext");
        INTERMediatorOnPage.getOptionsAliases=function(){return {'kindid':'cor_way_kindname@kind_id@value'};};
        assert.equals(IMLibElement.checkOptimisticLock(inputElement, null), false);
    }*/
    "IMLibElement.setValueToIMNode() has to set the value to textarea": function () {
        var value;
        var tempElement = document.createElement("textarea");
        value = 'abc';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = '123';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = null;
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [999,888,777];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "999");
        value = "qwe\n122";
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
    },
    "IMLibElement.setValueToIMNode() has to set the value to text field": function () {
        var value;
        var tempElement = document.createElement("INPUT");
        tempElement.type = "text";
        value = 'abc';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = '123';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = null;
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [999,888,777];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "999");
        value = "qwe122";
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
    },
    "IMLibElement.setValueToIMNode() has to set the value to checkbox": function () {
        var value;
        var tempElement = document.createElement("INPUT");
        tempElement.type = "checkbox";
        tempElement.value = "1";
        IMLibElement.setValueToIMNode(tempElement, "", 1, true);
        assert.equals(tempElement.checked, true);
        IMLibElement.setValueToIMNode(tempElement, "", "1", true);
        assert.equals(tempElement.checked, true);
        IMLibElement.setValueToIMNode(tempElement, "", 0, true);
        assert.equals(tempElement.checked, false);
        IMLibElement.setValueToIMNode(tempElement, "", -1, true);
        assert.equals(tempElement.checked, false);
        tempElement.value = "anytext";
        IMLibElement.setValueToIMNode(tempElement, "", "anytext", true);
        assert.equals(tempElement.checked, true);
        IMLibElement.setValueToIMNode(tempElement, "", "1", true);
        assert.equals(tempElement.checked, false);
        IMLibElement.setValueToIMNode(tempElement, "", 0, true);
        assert.equals(tempElement.checked, false);
        IMLibElement.setValueToIMNode(tempElement, "", -1, true);
        assert.equals(tempElement.checked, false);
    }
});
