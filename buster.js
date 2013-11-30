var config = module.exports;

config["My tests"] = {
    environment: "browser",
    sources: [
        "*.js",
        "lib/js_lib/*.js"
    ],
    tests: [
        "INTER-Mediator-UnitTest/*-test.js"
    ]
}