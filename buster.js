var config = module.exports;

config["My tests"] = {
    environment: "browser",
    sources: [
        "INTER-Mediator/*.js",
        "INTER-Mediator/lib/js_lib/*.js"
    ],
    tests: [
        "INTER-Mediator-UnitTest/*-test.js"
    ]
}