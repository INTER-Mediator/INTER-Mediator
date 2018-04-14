var config = module.exports;

config['My tests'] = {
    environment: 'browser',
    autoRun: false,
    rootPath: '../',
    sources: [
        'src/php/INTER-Mediator.js',
        'src/php/INTER-Mediator-Page.js',
        'src/php/INTER-Mediator-Context.js',
        'src/php/INTER-Mediator-Lib.js',
        'src/php/INTER-Mediator-Format.js',
        'src/php/INTER-Mediator-Element.js',
        'src/lib/js_lib/js-expression-eval-parser.js',
        'src/php/INTER-Mediator-Calc.js',
        'src/php/INTER-Mediator-Parts.js',
        'src/php/INTER-Mediator-Navi.js',
        'src/php/INTER-Mediator-UI.js',
        'src/php/INTER-Mediator-Log.js',
        'src/lib/js_lib/tinySHA1.js',
        'src/lib/js_lib/sha256.js',
        'src/lib/js_lib/jsencrypt.min.js',
        'src/php/Adapter_DBServer.js',
        'src/php/INTER-Mediator-Queuing.js',
        'src/php/INTER-Mediator-Events.js',
        'src/php/INTER-Mediator-DoOnStart.js'
    ],
    tests: [
        'spec/INTER-Mediator-UnitTest/INTER-Mediator-test.js',
        'spec/INTER-Mediator-UnitTest/INTER-Mediator-Page-test.js',
        'spec/INTER-Mediator-UnitTest/INTER-Mediator-Element-test.js',
        'spec/INTER-Mediator-UnitTest/INTER-Mediator-Context-test.js',
        'spec/INTER-Mediator-UnitTest/INTER-Mediator-Lib-test.js',
        'spec/INTER-Mediator-UnitTest/sha1-test.js',
        //'INTER-Mediator-UnitTest/RSA_JavaScript-test.js',
        'spec/INTER-Mediator-UnitTest/js-expression-eval-test.js',
        'spec/run.js'
    ]
};