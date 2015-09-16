/**
 * Created by msyk on 15/09/13.
 */
/*

 The RSA Key for this test cases.

 -----BEGIN RSA PRIVATE KEY-----
 MIIEogIBAAKCAQEAxH7++yiHJUHEDU3wMw+FDfrlgOHNP+yiCFmYQPI0G7oj5uTy
 tPMv3YVrEtb2Y62452C6WZcLSwOBqWlLUGfH0NJx35aaZG2CsUheNJEH+WEIFyel
 mJmWDmwfZ6DnO+nsICylGWryDfgF7n4854mxa/SfI5bYAJD6x2D3o/NDwanlsbiU
 B/ICKQmhZXvqNRRWdIEALasdLsDQ15MCfBTG1vKZqB9hiCFnZQEvrUKfWLdp6Uqa
 j15QdEvTFopramsTkQHlOy/CnQDD7Qng8Qzqm7Ycq3Xz2R/nq5k/GeAnQdxKzW1j
 QhktWfYrFQtxhyKcPXa/bchNkzctp3a/QRN2WwIDAQABAoIBAFvXoAZ0ovZfDuvJ
 CgRTtLUcGDltUSoXyIRunCN/EawEDNPXHzpEkJLR0YI0x2U/xbUgGPnXB4hAU1KD
 zJgAafzI4EDJe9CE/xkt4hpfz4JYQBfSiCwTXXfQQb2GD46Jf7xqIaEHw6uTyfH3
 PzBZw3vaEqfn0X4yRYT7ZcRT58+UcAQJqQDU/6ZwNckewzhWzh/27LfstV+nJe5u
 GSmRdb2H3x9ISKb+EMysM0n+YrNKC9giObCRm7EbIOE5iJvZnA0SiBP/y90anhsS
 gHXaN4/cL5/U/ld9Nuk+MOH6R0qoVuGegDjdqHC+fCMUYbMvWKbnZiHU0/PnFfnu
 SKxkmgECgYEA4kQDLLEq9ebk1nS402AWx0XNlJHmVR/PFUbVTOT1xMWFhZA4XMnX
 KkMmeYIUMDJJcLbTBUYFeoygOM8TA5BSATxHGt9xrO2dl75HWVYP7Ncedc4iSj6P
 dM4EsnFHKCgL2LqEuaQnMIDTZKo0WnlLphChJ3MzXwVzK5lXAiKHnaECgYEA3lF6
 /o0mYjWTV0PDDYvUp6mtf2h5/V/wSDA7I4IJ9BpnsOIFCdShn8rDYN9qxJS0t3US
 8kNNXgFrq2zjCDMibr1xALnyXnPlc86c2+kfgv+7Biu2foJ3MI4cL9umn/y76ENE
 6VaO9bUs1HHKA0SFXNEr3ZFjm+kzx3qZZh1MensCgYBn99yFkrss1vXb3TJ4XjTZ
 SCfY1tnBz6X2HuAwPxz3V9OstcJQUKa/0q9BMhZYtyKr2jZIvA4Ua73LnMsd3hjw
 XGRH4th3H5BEg7iBQlx69bYXZ6q19t0wTOI3pHmP6CbZZYtLSjR/wxJftR3tXML4
 AbgrSnIWfYiYRhOG9ZrfQQKBgFkxTWwUywJ5xhwrlnS31eBSRcYo71BFDkyX9RIA
 2OdzNIiVlTnlcdZ+7bXOzLIDiyFTOf+yGrcNUNocvFUM1tKg9FY7Q867JqI4kVv1
 Amx3FtyZ6wSEaTc0vIBC2m2zYtwDKQGIdaCESHEPGeIHuo2LadLhwpnJjLmKKUL7
 nDRDAoGADIHzuzFTYYgG4heLd2yXGv5+MDX+NmVzTr1j5dVOfzpiBJBjCN8Q9x3v
 v9nNeZFIhPbhCTjCdY/NlcIHOZqUQulTu1DpDZ7zFO1Fs4aEDnBvp9i8yJquxhOQ
 dBazzmZ3S/t2b6NtqClmn/1BgjgnKYURBn888UzbX6lqCNG3/mI=
 -----END RSA PRIVATE KEY-----

 openssl rsa -in rsa.key -text

 shows modulus, publicExponent, and privateExponent.


 */
var assert = buster.referee.assert;

buster.testCase("RSA-JS Test:", {
    "Check the collect encription with RSA key.": function () {
        var modulus =
            '00c47efefb28872541c40d4df0330f850dfae580e1cd3feca208599840f2' +
            '341bba23e6e4f2b4f32fdd856b12d6f663adb8e760ba59970b4b0381a969' +
            '4b5067c7d0d271df969a646d82b1485e349107f961081727a59899960e6c' +
            '1f67a0e73be9ec202ca5196af20df805ee7e3ce789b16bf49f2396d80090' +
            'fac760f7a3f343c1a9e5b1b89407f2022909a1657bea3514567481002dab' +
            '1d2ec0d0d793027c14c6d6f299a81f6188216765012fad429f58b769e94a' +
            '9a8f5e50744bd3168a6b6a6b139101e53b2fc29d00c3ed09e0f10cea9bb6' +
            '1cab75f3d91fe7ab993f19e02741dc4acd6d6342192d59f62b150b718722' +
            '9c3d76bf6dc84d93372da776bf4113765b';
        var privateEx =
            '5bd7a00674a2f65f0eebc90a0453b4b51c18396d512a17c8846e9c237f11' +
            'ac040cd3d71f3a449092d1d18234c7653fc5b52018f9d7078840535283cc' +
            '980069fcc8e040c97bd084ff192de21a5fcf82584017d2882c135d77d041' +
            'bd860f8e897fbc6a21a107c3ab93c9f1f73f3059c37bda12a7e7d17e3245' +
            '84fb65c453e7cf94700409a900d4ffa67035c91ec33856ce1ff6ecb7ecb5' +
            '5fa725ee6e19299175bd87df1f4848a6fe10ccac3349fe62b34a0bd82239' +
            'b0919bb11b20e139889bd99c0d128813ffcbdd1a9e1b128075da378fdc2f' +
            '9fd4fe577d36e93e30e1fa474aa856e19e8038dda870be7c231461b32f58' +
            'a6e76621d4d3f3e715f9ee48ac649a01';

        var publickey = new biRSAKeyPair('010001', '0', modulus);
        var originalString="1234OhmyGOD#";
        var enc = publickey.biEncryptedString(originalString);
        //console.log(enc);
        var privatekey = new biRSAKeyPair('0', privateEx, modulus);
        var decrypted = privatekey.biDecryptedString(enc);
        assert.equals(originalString, decrypted);
    },
    "Check the collect decription from the PHP class encripted.": function () {
        var modulus =
            '00c47efefb28872541c40d4df0330f850dfae580e1cd3feca208599840f2' +
            '341bba23e6e4f2b4f32fdd856b12d6f663adb8e760ba59970b4b0381a969' +
            '4b5067c7d0d271df969a646d82b1485e349107f961081727a59899960e6c' +
            '1f67a0e73be9ec202ca5196af20df805ee7e3ce789b16bf49f2396d80090' +
            'fac760f7a3f343c1a9e5b1b89407f2022909a1657bea3514567481002dab' +
            '1d2ec0d0d793027c14c6d6f299a81f6188216765012fad429f58b769e94a' +
            '9a8f5e50744bd3168a6b6a6b139101e53b2fc29d00c3ed09e0f10cea9bb6' +
            '1cab75f3d91fe7ab993f19e02741dc4acd6d6342192d59f62b150b718722' +
            '9c3d76bf6dc84d93372da776bf4113765b';
        var privateEx =
            '5bd7a00674a2f65f0eebc90a0453b4b51c18396d512a17c8846e9c237f11' +
            'ac040cd3d71f3a449092d1d18234c7653fc5b52018f9d7078840535283cc' +
            '980069fcc8e040c97bd084ff192de21a5fcf82584017d2882c135d77d041' +
            'bd860f8e897fbc6a21a107c3ab93c9f1f73f3059c37bda12a7e7d17e3245' +
            '84fb65c453e7cf94700409a900d4ffa67035c91ec33856ce1ff6ecb7ecb5' +
            '5fa725ee6e19299175bd87df1f4848a6fe10ccac3349fe62b34a0bd82239' +
            'b0919bb11b20e139889bd99c0d128813ffcbdd1a9e1b128075da378fdc2f' +
            '9fd4fe577d36e93e30e1fa474aa856e19e8038dda870be7c231461b32f58' +
            'a6e76621d4d3f3e715f9ee48ac649a01';
        var enc =
            '1825cd9a83497583e91e7b27f845eedce2f9198b7017d97d2e02aaef90fe' +
            '9894356d94f4418d1acba99f626b977f20e7d740ce5996290666aaf0e7ab' +
            '164932b421e1b549afcf80450c32c09ba85883c59fe622b90a49b2582565' +
            'd299313c8253d48158f6e9f1e7341d7d5de8e301fb54128ab5236da7a174' +
            'eb26167732d8f8d177af91f368996242bf02a84bbec4d4a91b468d0f554b' +
            '796732d43ba8cb76d9ba0acdb95a74cc219ff3bf59456afc00dd6e59f312' +
            '2708839f378aebf7aa08d490273ba2b23e215f1920a46f2223ce178ebfb5' +
            '49b6abc338abce7a66f21037dfeda2fbd7c0d06ce4f4364aa73b5442279b' +
            '172354d33aab53fdd5d252c84f1f6611';
        var privatekey = new biRSAKeyPair('0', privateEx, modulus);
        var decrypted = privatekey.biDecryptedString(enc);
        var originalString = "happySAD200333#$#$#$#";
        assert.equals(originalString, decrypted);
    }
});


