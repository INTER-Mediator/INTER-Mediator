var config = module.exports;

config["My tests"] = {
    environment: "browser",
    sources: [
        "*.js",
        "lib/js_lib/*.js",
        "lib/bi2php/*.js"
    ],
    tests: [
        "INTER-Mediator-UnitTest/*-test.js"
    ]
}