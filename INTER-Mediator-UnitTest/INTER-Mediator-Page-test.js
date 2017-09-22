var assert = buster.referee.assert;
INTERMediatorOnPage.getIMRootPath = function() {
    return "/INTER-Mediator";
};
INTERMediatorOnPage.getEntryPath = function() {
    return "/INTER-Mediator";
};
INTERMediatorOnPage.getTheme = function() {
    return "default";
};

buster.testCase("INTERMediatorOnPage.getMessages() Test", {
    "should return null": function () {
        assert.equals(INTERMediatorOnPage.getMessages(), null);
    },

    "Password Policy assigned": function () {
        var policy = "", message;
        var authFunc = (new INTERMediatorOnPage.authenticating());

        // No policy returns no error.
        message = authFunc.checkPasswordPolicy("", "username", policy, true);
        assert.equals(message.length, 0);

        // Full policy applied
        policy = "useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName";
        message = authFunc.checkPasswordPolicy("1234567890", "username", policy, true);
        assert.equals(message.length, 4);
        message = authFunc.checkPasswordPolicy("1234567890a", "username", policy, true);
        assert.equals(message.length, 2);
        message = authFunc.checkPasswordPolicy("1234567890aS", "username", policy, true);
        assert.equals(message.length, 1);
        message = authFunc.checkPasswordPolicy("1234567890aS#", "username", policy, true);
        assert.equals(message.length, 0);
        message = authFunc.checkPasswordPolicy("0aS#", "username", policy, true);
        assert.equals(message.length, 1);
        message = authFunc.checkPasswordPolicy("aaaaaaaS#", "username", policy, true);
        assert.equals(message.length, 2);
        message = authFunc.checkPasswordPolicy("aaaaaaa0S#", "username", policy, true);
        assert.equals(message.length, 0);

        // Check length
        policy = "length(4)";
        message = authFunc.checkPasswordPolicy("1234", "username", policy, true);
        assert.equals(message.length, 0);
        message = authFunc.checkPasswordPolicy("123", "username", policy, true);
        assert.equals(message.length, 1);

        // Check notUserName
        policy = "notUserName";
        message = authFunc.checkPasswordPolicy("username", "username", policy, true);
        assert.equals(message.length, 1);
    }
});
