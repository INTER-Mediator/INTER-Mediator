/*
 Testing with jest on local.

 cd spec
 env PATH="../vendor/bin:$PATH" ../node_modules/.bin/jest
 */
module.exports = {
  'verbose': true,
  'testEnvironmentOptions': {url: 'http://localhost/'},
  'setupFiles': ['./test-setup.js']
}
