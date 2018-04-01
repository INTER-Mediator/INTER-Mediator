<?php
header('Content-Type: text/javascript');
require('biRSA.php');
$keyEncrypt = new biRSAKeyPair(
    "a48a65eb6cc757fb487cd6628a83f0a3748032669171e6aabebddde200eb6d2cb9669336d70e422cfe22c17b6ad2efae71c5df9609ede03babf772f519df07b842e88a257e98e006bb706b9d03ab79f2dc6f38928a1461d4267943b1ba4818603001dbfbf97ddf58a4d04b0c63da9a34a9ddc8888a1b833964cf206bb30573e96820cdde618181b6c2b049ffde63af1773714e9b85cc70000141cd179909c2dbf6a358ef54c09ce00174c67e0a3b6b5576cabdf4fd0e6802fa7a0209c556820a0b1a2a6ef52b9a8531ab92ecc232d596953f3de373e1becab2b36b6554c70206f8dba389e8717850079ef54025524887542b2989724adc03f07fa96b285b679",
    "0",
    "222d99fdf0616080ebee15d1ff1469bc79a478ac9a13e9af8bb48b4b208b2deaf40c69f4c201f7a7f294d43b3591ec73f826809b62fbd5d15cabeef7baeed786119ca5cecd956d4795d8da7a12da9719349dbd4ea6d90cdae0ac5b280eb425c5ff46f41de567defbd5240471c8c6920d37e2fdd1bf5fd45d5c26364775055002cea3f3b9624f6d470035f18c451f37c454beda5f1b3cf19a787db43845f5a49faaa7f8f3ccde953a152e2588aff267b680b467f028064d9f370e220b153c1ee0ff71f7e5cc017033509b50c3959e49692db0d2741d586f01963572b1c7d00346e8736821728f342452447aa586cca14ffb175a1fadbb2745c714fd3cd15af66d"
);
$keyDecrypt = new biRSAKeyPair(
    "0",
    "1001c99eb5adf6c25948cd453b27518246187a79da122f693fe5275fec00ffe42c1a40bc74586d5b2bb806e399c2cdad2a41deba8a65c2c45c976fe75415cd393d46daa5e41f8099b8ea71eab918ed96fa1bd95f67f94b70d70b6f91cc35aec5e73c469ddbafaf2296fb16935747c6c1d233ce010313868240e1857b2882bc0777889a7ab46ccb225df61347872c0e7f9e71af619f421a05c5e8f3e496e9b33fab51f279b7de5c1182f4f2d20988baddf3562215db04afd998299279396ac11c7bb6d0daae021a47f608e93ebf1dc68f8c8841034beb77cfa1ed9cbda8d9123e006e273d3a61c29441a3dbe78f6ef13c55f5ebb5034fe4fb2eb18d1638ab89c9",
    "222d99fdf0616080ebee15d1ff1469bc79a478ac9a13e9af8bb48b4b208b2deaf40c69f4c201f7a7f294d43b3591ec73f826809b62fbd5d15cabeef7baeed786119ca5cecd956d4795d8da7a12da9719349dbd4ea6d90cdae0ac5b280eb425c5ff46f41de567defbd5240471c8c6920d37e2fdd1bf5fd45d5c26364775055002cea3f3b9624f6d470035f18c451f37c454beda5f1b3cf19a787db43845f5a49faaa7f8f3ccde953a152e2588aff267b680b467f028064d9f370e220b153c1ee0ff71f7e5cc017033509b50c3959e49692db0d2741d586f01963572b1c7d00346e8736821728f342452447aa586cca14ffb175a1fadbb2745c714fd3cd15af66d"
);

$keyEncrypt = new biRSAKeyPair(
    "142ab99af88b540da02041f562804665",
    "0",
    "30f1b353d5a09313825ca5ef7c87033f"
);
$keyDecrypt = new biRSAKeyPair(
    "0",
    "266f58f4654cc23e392c2eb99ec90635",
    "30f1b353d5a09313825ca5ef7c87033f"
);

if ($_POST['step'] == 2) {
    $decrypted = str_replace(array("\\", '"', '<', "\n", "\r"), array('\\\\', '\\"', "\<", "\\n", "\\r"), $keyDecrypt->biDecryptedString($_POST['encrypted'], FALSE));
    echo <<<EOT
document.getElementById("serverDecryptedText").value = "$decrypted";
EOT;
}

if ($_POST['step'] == 3) {
    $encrypted = str_replace(array('"', '<', "\n", "\r"), array('\\"', "\<", "\\n", "\\r"), $keyEncrypt->biEncryptedString($_POST['decrypted'], FALSE));
    echo <<<EOT
document.getElementById("serverEncryptedText").value = "$encrypted";
EOT;
}