<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

/* DB_FileMaker_FX awares below:
 */
$dbServer = '127.0.0.1';
$dbPort = '80';
$dbUser = 'web';
$dbPassword = 'password';
$dbDataType = 'FMPro7';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';

/* DB_PDO awares below:
 */
$dbDSN = 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;';
$dbOption = array();
$dbUser = 'web';
$dbPassword = 'password';

/* Browser Compatibility Check:
 */
$browserCompatibility = array(
    'msie' => '7+',
    'FireFox' => '2+',
    'Safari' => '4+',
//    'Safari'=>array('Mac'=>'4+','Win'=>'4+'), // Sample for dividing with OS
    'Chrome' => '1+',
    'Opera' => '1+'
);

/* This statement set debug to false forcely. */
//$prohibitDebugMode = true;

/*
Command to generate the following RSA key:
$ openssl genrsa 2048


openssl_pkey_get_public( $generatedKey )
openssl_pkey_get_private( $generatedKey )
openssl_private_decrypt( $data, $decripted, $key )
*/
$generatedKey = <<<EOL
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA0DSFDXQSk1N9dWU/+OJj2cvZzttFZSicajKkaK+m6wZd2+cj
j+dfTXPiDFrjFQuchjDKYUfWpEeHQDNwoLIDtWjNfT4w7P6SInqCUd9AYJ4aYZnF
a0kIkxkPwkx3mFxWvx6Qq6VNcY6LroGHNXs2wr0dIxm9XfIXQduq3QT1COaZS4Nn
tUsYDbXB7f3tRHCnt3oaPti56dUA7KBU5fVWFQwWqjG/j4B5tMkqjO58W6fzW9XC
mN7VlSthwXN4R4MEFzQ8onUXy3z4nXuPDm1DdFm1c60aBOylS527KKDgLy30Of3E
fj7h9LN6vOZDD7PCHI1UNBqW9ZK8BCap+YWTLwIDAQABAoIBACHDoqAf5rNFot7a
4Jj3/cFgMZ4+KO7Suyrts4PWmHccvTPgNAAuQWJKHKpsQs8y5ttMJkXIZKKXhvN+
ZBFrTPaqXEinQT/tuL0mqOOmFMaWXSjeywku+tkAA3I6/FoU/2xXBJcRY5G60CQo
lUizBppmGMeMcQ0/KU5g1UCqgSJEGCpJuxJzFxzLYtLzPKincYbeGsxQEkW+LMgU
pdxeit15LkGsyihUgZzlK4imObHtEehVGLs+ga+7X5l3u09EJUDZnPA1SX3yyPeT
UwPTY7eDiLMSnj/Dd+Edmm6sztsgqN5uP9b30dOOvc436B34Fvqv/rjBFDUmGSRE
fwMszkECgYEA9YsZ6Kor3aofj7oqowg3Z5mhG8Z0vsPBY0J0eb0qZZYyvNmbengY
IFcQC2jW4nFmBYATF71K+9lQ7xMAoZMQpPcDkqlLmQtPlbfk4fivVsuB0varbhEB
OBkFB4cVr04cdTyNfQdf49VHkrRPo2wYsk32qHaarUPvzIAoO+iv7gcCgYEA2RJc
EioYKC6I0BAHtqF798yJqJCZ+IDsV0aDAWKwXY//gvvGQX/h5SKYEEJmW8QiCqIS
Oe2c+zWc/kVxqe01qLZtCCi0fHTr/cUD+FqriGLpakcp6fHCMpps36frytjLQe0t
QwDBTEbpOVRHFEXaVyKIkeaMm5jqXyCeSmyo55kCgYEAsmwfqYduVKZ4RLJcpRcl
W2yxO0OcNbp9Xik+BfBFTIg12jCt3/JAZc+d4wyNiQIpxjlopklwoU0qmG7QeJgq
8hRkTkAipNUpQXvdH67bilBax/diXpqQrjjQBMZOJZK27yHPFlkzfpTOa3YGoJO9
5mLsp0F36AuDxrAhjFbq8CUCgYB8NfpDVpz+GSqmTBXt1le2gu7eauf//929TxbV
qyAaWbSuwd0/S6r6T6JN44Doz8Fe7kCZrzLduF7+TMRupNLImKKpQYmNkeYcDyln
apKu91JzQwdj27fw9taH4HGXYBhmwA3fQkZZnFYGPQhzPEllBNi7C/63ZnfuOR76
nVTFOQKBgQCrqWGPu/Y9GIzUwrd2S7YaiGPD7AN7FVVm7PTMA245Fk1FVCPVHXnq
3vI+QnB3FVQj3k0X2IIhQEDYH7s2eMI9UfMV81HmHS0MevJFbN7Zp5seDEfaXp/V
EukFGYaLhvBbQJ4Igk914w48+xAY58pQJv5Bf0ABOeyG1diV0VPy8A==
-----END RSA PRIVATE KEY-----
EOL;

?>