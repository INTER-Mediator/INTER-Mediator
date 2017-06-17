var config = module.exports;

config['My tests'] = {
    environment: 'browser',
    rootPath: '../',
    sources: [
        'INTER-Mediator.js',
        'INTER-Mediator-Page.js',
        'INTER-Mediator-Element.js',
        'INTER-Mediator-Context.js',
        'INTER-Mediator-Lib.js',
        'INTER-Mediator-Calc.js',
        'INTER-Mediator-Parts.js',
        'INTER-Mediator-Navi.js',
        'INTER-Mediator-UI.js',
        'lib/js_lib/tinySHA1.js',
        'lib/js_lib/sha256.js',
        'lib/bi2php/biBigInt.js',
        'lib/bi2php/biMontgomery.js',
        'lib/bi2php/biRSA.js',
        'Adapter_DBServer.js',
        'INTER-Mediator-Events.js',
        'lib/js_lib/js-expression-eval-parser.js',
        'INTER-Mediator-DoOnStart.js'
    ],
    tests: [
        'INTER-Mediator-UnitTest/INTER-Mediator-test.js',
        'INTER-Mediator-UnitTest/INTER-Mediator-Page-test.js',
        'INTER-Mediator-UnitTest/INTER-Mediator-Element-test.js',
        'INTER-Mediator-UnitTest/INTER-Mediator-Context-test.js',
        'INTER-Mediator-UnitTest/INTER-Mediator-Lib-test.js',
        'INTER-Mediator-UnitTest/sha1-test.js',
        'INTER-Mediator-UnitTest/RSA_JavaScript-test.js',
        'INTER-Mediator-UnitTest/js-expression-eval-test.js'
    ]
};