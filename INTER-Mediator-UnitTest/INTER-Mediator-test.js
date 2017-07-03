var assert = buster.referee.assert;

buster.testCase("INTERMediator class additionalConditions", {
    setUp: function () {
        INTERMediator.clearCondition("context1");
        INTERMediator.clearCondition("context2");
    },
    tearDown: function () {
        INTERMediator.clearCondition("context1");
        INTERMediator.clearCondition("context2");
    },

    "AdditionalCondition-Add_Clear": function () {
        INTERMediator.clearCondition("context1");
        INTERMediator.addCondition("context1", {field: "f1", operator: "=", value: 1});
        assert.equals(INTERMediator.additionalCondition["context1"].length, 1, "Having 1 condition in context.");
        INTERMediator.addCondition("context1", {field: "f2", operator: "=", value: 1});
        assert.equals(INTERMediator.additionalCondition["context1"].length, 2, "Having 2 conditions in context.");
        INTERMediator.clearCondition("context2");
        INTERMediator.addCondition("context2", {field: "f1", operator: "=", value: 1});
        assert.equals(INTERMediator.additionalCondition["context1"].length, 2, "Having 1 condition in context.");
        assert.equals(INTERMediator.additionalCondition["context2"].length, 1, "Having 1 condition in context.");
        INTERMediator.clearCondition("context1");
        assert.equals(INTERMediator.additionalCondition["context1"], undefined, "Clear definition for context.");
        assert.equals(INTERMediator.additionalCondition["context2"].length, 1, "Having 1 condition in context.");
    },

    "AdditionalCondition-Add_Clear_Label": function () {
        INTERMediator.clearCondition("context1");
        INTERMediator.addCondition("context1", {field: "f1", operator: "=", value: 1});
        INTERMediator.addCondition("context1", {field: "f2", operator: "=", value: 1});
        INTERMediator.addCondition("context1", {field: "f3", operator: "=", value: 1}, undefined, "label");
        assert.equals(INTERMediator.additionalCondition["context1"].length, 3, "Having 2 conditions in context.");
        INTERMediator.clearCondition("context1", "label");
        assert.equals(INTERMediator.additionalCondition["context1"].length, 2, "Clear definition for context.");

        INTERMediator.clearCondition("context2");
        INTERMediator.addCondition("context2", {field: "f1", operator: "=", value: 1});
        INTERMediator.addCondition("context2", {field: "f2", operator: "=", value: 1});
        INTERMediator.addCondition("context2", {field: "f3", operator: "=", value: 1}, true, "label");
        INTERMediator.addCondition("context2", {field: "f4", operator: "=", value: 1}, true, "label");
        INTERMediator.addCondition("context2", {field: "f5", operator: "=", value: 1});
        assert.equals(INTERMediator.additionalCondition["context2"].length, 5, "Having 2 conditions in context.");
        INTERMediator.clearCondition("context2", "label");
        assert.equals(INTERMediator.additionalCondition["context2"].length, 3, "Clear definition for context.");
    }
});
