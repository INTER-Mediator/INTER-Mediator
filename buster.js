var config = module.exports;

config["My tests"] = {
    environment: "browser",
    sources: [
        "INTER-Mediator.js",
        "INTER-Mediator-Context.js",
        "INTER-Mediator-Lib.js",
        "lib/js_lib/*.js",
        "lib/bi2php/*.js",
        "*.js"
    ],
    tests: [
        "INTER-Mediator-UnitTest/*-test.js"
    ]
}