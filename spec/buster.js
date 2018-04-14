var config = module.exports;

config['My tests'] = {
    environment: 'browser',
    autoRun: false,
    rootPath: '../',
    sources: [
        'src/js/INTER-Mediator.js',
        'src/js/INTER-Mediator-Page.js',
        'src/js/INTER-Mediator-Context.js',
        'src/js/INTER-Mediator-Lib.js',
        'src/js/INTER-Mediator-Format.js',
        'src/js/INTER-Mediator-Element.js',
        'src/lib/js_lib/js-expression-eval-parser.js',
        'src/js/INTER-Mediator-Calc.js',
        'src/js/INTER-Mediator-Parts.js',
        'src/js/INTER-Mediator-Navi.js',
        'src/js/INTER-Mediator-UI.js',
        'src/js/INTER-Mediator-Log.js',
        'src/lib/js_lib/tinySHA1.js',
        'src/lib/js_lib/sha256.js',
        'src/js/Adapter_DBServer.js',
        'src/js/INTER-Mediator-Queuing.js',
        'src/js/INTER-Mediator-Events.js',
        'src/js/INTER-Mediator-DoOnStart.js'
    ],
    tests: [
        'spec/INTER-Mediator-UnitTest/*-test.js',
        'spec/INTER-Mediator-UnitTest/*-test.js',
        'spec/run.js'
    ]
};