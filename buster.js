var config = module.exports;

config["My tests"] = {
    environment: "browser",
    sources: [
        "develop-im/INTER-Mediator/*.js",
    ],
    tests: [
        "develop-im/INTER-Mediator-UnitTest/*-test.js"
    ]
}