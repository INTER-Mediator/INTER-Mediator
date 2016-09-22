var config = module.exports;

config["My tests"] = {
    environment: "browser",
    rootPath: "../",
    sources: [
        "*.js",
        "lib/js_lib/*.js",
        "lib/bi2php/*.js"
    ],
    tests: [
        "INTER-Mediator-UnitTest/INTER-Mediator-Context-test.js",
        "INTER-Mediator-UnitTest/INTER-Mediator-Element-test.js",
        "INTER-Mediator-UnitTest/INTER-Mediator-Lib-test.js",
        "INTER-Mediator-UnitTest/INTER-Mediator-Page-test.js",
        "INTER-Mediator-UnitTest/RSA_JavaScript-test.js",
        "INTER-Mediator-UnitTest/js-expression-eval-test.js",
        "INTER-Mediator-UnitTest/sha1-test.js"
    ]
};