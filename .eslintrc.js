module.exports = {
    "env": {
        "browser": true
    },
    "extends": "eslint:recommended",
    "rules": {
        "indent": [
            "error",
            4
        ],
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "single"
        ],
        "semi": [
            "error",
            "always"
        ]
    },
    "globals": {
        "INTERMediator": false,
        "INTERMediatorLib": false,
        "INTERMediator_DBAdapter": false,
        "INTERMediatorOnPage": false,
        "IMLib": false,
        "IMLibNodeGraph": false,
        "IMLibElement": false,
        "IMLibContextPool": false,
        "IMLibLocalContext": false,
        "IMLibContext": false,
        "IMLibCalc": false,
        "IMLibEventDispatch": false,
        "IMLibMouseEventDispatch": false,
        "IMLibKeyEventDispatch": false,
        "IMLibChangeEventDispatch": false,
        "IMLibEventResponder": false,
        "IMLibPageNavigation": false,
        "IMParts_Catalog": false,
        "IMLibQueue": false,
        "IMLibUI": false,
        "Parser": false,
        "Pusher": false,
        "SHA1": false,
        "jsSHA": false,
        "Base64": false,
        "console": false,
        "Exception": false
    }
};
