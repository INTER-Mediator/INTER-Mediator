/*
 The MIT License

 Copyright (c)2009 Андрій Овчаренко (Andrey Ovcharenko)

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */
// bi2php v0.1.113.alfa from http://code.google.com/p/bi2php/
// Base on dave@ohdave.com


// BigInt, a suite of routines for performing multiple-precision arithmetic in
// JavaScript.
//
// Copyright 1998-2005 David Shapiro.
//
// You may use, re-use, abuse,
// copy, and modify this code to your liking, but please keep this header.
// Thanks!
//
// Dave Shapiro
// dave@ohdave.com

// IMPORTANT THING: Be sure to set maxDigits according to your precision
// needs. Use the setMaxDigits() function to do this. See comments below.
//
// Tweaked by Ian Bunning
// Alterations:
// Fix bug in function biFromHex(s) to allow
// parsing of strings of length != 0 (mod 4)

// Changes made by Dave Shapiro as of 12/30/2004:
//
// The BigInt() constructor doesn't take a string anymore. If you want to
// create a BigInt from a string, use biFromDecimal() for base-10
// representations, biFromHex() for base-16 representations, or
// biFromString() for base-2-to-36 representations.
//
// biFromArray() has been removed. Use biCopy() instead, passing a BigInt
// instead of an array.
//
// The BigInt() constructor now only constructs a zeroed-out array.
// Alternatively, if you pass <true>, it won't construct any array. See the
// biCopy() method for an example of this.
//
// Be sure to set maxDigits depending on your precision needs. The default
// zeroed-out array ZERO_ARRAY is constructed inside the setMaxDigits()
// function. So use this function to set the variable. DON'T JUST SET THE
// VALUE. USE THE FUNCTION.
//
// ZERO_ARRAY exists to hopefully speed up construction of BigInts(). By
// precalculating the zero array, we can just use slice(0) to make copies of
// it. Presumably this calls faster native code, as opposed to setting the
// elements one at a time. I have not done any timing tests to verify this
// claim.

//var biRadixBase = 2;
var biRadixBits = 16;
biRadixBits = biRadixBits - biRadixBits % 4;
var bitsPerDigit = biRadixBits;
var biRadix = 1 << biRadixBits; // = 2^16 = 65536
var biHalfRadix = biRadix >>> 1;
var biRadixSquared = biRadix * biRadix;
var maxDigitVal = biRadix - 1;
var maxInteger = 4294967295;
var biHexPerDigit = biRadixBits / 4;
var bigZero = biFromNumber(0);
var bigOne = biFromNumber(1);

// The maximum number of digits in base 10 you can convert to an
// integer without JavaScript throwing up on you.
//var dpl10 = 9;
// lr10 = 10 ^ dpl10
//var lr10 = biFromNumber(1000000000);

function BigInt(i) {
    this.isNeg = false;
    if (i == -1)
        return;
    if (i) {
        this.digits = new Array(i);
        while (i)
            this.digits[--i] = 0;
    } else
        this.digits = [0];
}

BigInt.prototype.isZero = function () {
    return this.digits[0] == 0 && biNormalize(this).digits.length == 1;
};
BigInt.prototype.isOne = function () {
    return this.digits[0] == 1 && !this.isNeg && biNormalize(this).digits.length == 1;
};
BigInt.prototype.isEqual = function (bis) {
    var i;
    if (this.isNeg != bis.isNeg)
        return false;
    if (this.digits.length != bis.digits.length)
        return false;
    for (i = this.digits.length - 1; i > -1; i--)
        if (this.digits[i] != bis.digits[i])
            return false;
    return true;
};
BigInt.prototype.blankZero = function () {
    this.digits.length = 1;
    this.digits[0] = 0;
};
BigInt.prototype.blankOne = function () {
    this.digits.length = 1;
    this.digits[0] = 1;
};
BigInt.prototype.blankEmpty = function () {
    this.digits.length = 0;
};

function biCopy(bi) {
    var result = new BigInt(-1);
    result.digits = bi.digits.slice(0);
    result.isNeg = bi.isNeg;
    return result;
}

function biAbs(bi) {
    var result = new BigInt(-1);
    result.digits = bi.digits.slice(0);
    result.isNeg = false;
    return result;
}

function biMinus(bi) {
    var result = new BigInt(-1);
    result.digits = bi.digits.slice(0);
    result.isNeg = !bi.isNeg;
    return result;
}

function biFromNumber(i) {
    var result, j;
    if (Math.abs(i) > maxInteger)
        return (biFromFloat(i));
    result = new BigInt();
    if (result.isNeg = i < 0)
        i = -i;
    j = 0;
    while (i > 0) {
        result.digits[j++] = i & maxDigitVal;
        i >>>= biRadixBits;
    }
    return result;
}

function biFromFloat(i) {
    var result, j, c;
    result = new BigInt();
    if (result.isNeg = i < 0)
        i = -i;
    j = 0;
    while (i > 0) {
        c = Math.floor(i / biRadix);
        result.digits[j++] = i - c * biRadix;
        i = c;
    }
    return result;
}

function biFromString(s, radix) {
    var isNeg, first, result, place, i, c, digit, biDigit;
    if (radix == 16)
        return biFromHex(s);
    isNeg = s.charAt(0) == '-';
    first = (isNeg ? 1 : 0) - 1;
    result = new BigInt();
    place = biCopy(bigOne);
    for (i = s.length - 1; i > first; i--) {
        c = s.charCodeAt(i);
        digit = charToHex(c);
        biDigit = biMultiplyDigit(place, digit);
        result = biAdd(result, biDigit);
        place = biMultiplyDigit(place, radix);
    }
    result.isNeg = isNeg;
    return biNormalize(result);
}

function biFromDecimal(s) {
    return biFromString(s, 10);
}

function biFromHex(s) {
    var result, sl, i;
    result = new BigInt();
    if (s.charAt(0) == '-') {
        result.isNeg = true;
        s = substr(s, 1);
    } else {
        result.isNeg = false;
    }
    sl = s.length;
    for (i = sl, j = 0; i > 0; i -= biHexPerDigit, j++)
        result.digits[j] = hexToDigit(s.substr(Math.max(i - biHexPerDigit, 0), Math.min(i, biHexPerDigit)));
    return biNormalize(result);
}

function reverseStr(s) {
    var result = "", i;
    for (i = s.length - 1; i > -1; i--)
        result += s.charAt(i);
    return result;
}

var hexatrigesimalToChar = [
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
    'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
    'u', 'v', 'w', 'x', 'y', 'z'];

function biToString(x, radix) {
    // 2 <= radix <= 36
    var b, qr, result;
    if (radix == 16)
        return biToHex(x);
    b = biFromNumber(radix);
    qr = biDivideModulo(biAbs(x), b);
    result = hexatrigesimalToChar[qr[1].digits[0]];
    while (!qr[0].isZero()) {
        qr = biDivideModulo(qr[0], b);
        result += hexatrigesimalToChar[qr[1].digits[0]];
    }
    return (x.isNeg ? "-" : "") + reverseStr(result);
}

function biToDecimal(x) {
    return biToString(x, 10);
}

var hexToChar = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    'a', 'b', 'c', 'd', 'e', 'f'];

function digitToHex(n) {
    var mask, result, i;
    mask = 0xf;
    result = "";
    for (i = 0; i < biHexPerDigit; i++) {
        result += hexToChar[n & mask];
        n >>>= 4;
    }
    return reverseStr(result);
}

function digitToHexTrunk(n) {
    var mask, result, i;
    if (n == 0)
        return "0";
    mask = 0xf;
    result = "";
    for (i = 0; i < biHexPerDigit && n > 0; i++) {
        result += hexToChar[n & mask];
        n >>>= 4;
    }
    return reverseStr(result);
}

function biToHex(x) {
    var i, result;
    result = x.isNeg ? "-" : "";
    i = biHighIndex(x);
    result += digitToHexTrunk(x.digits[i--]);
    while (i > -1)
        result += digitToHex(x.digits[i--]);
    return result;
}

function biToNumber(x) {
    var result = 0, faktor = 1, k;
    k = biHighIndex(x) + 1;
    for (var i = 0; i < k; i++) {
        result += x.digits[i] * faktor;
        faktor *= biRadix;
    }
    return x.isNeg ? -result : result;
}

function charToHex(c) {
    var ZERO = 48;
    var NINE = ZERO + 9;
    var littleA = 97;
    var littleZ = littleA + 25;
    var bigA = 65;
    var bigZ = 65 + 25;
    var result;
    if (c >= ZERO && c <= NINE)
        result = c - ZERO;
    else if (c >= bigA && c <= bigZ)
        result = 10 + c - bigA;
    else if (c >= littleA && c <= littleZ)
        result = 10 + c - littleA;
    else
        result = 0;
    return result;
}

function hexToDigit(s) {
    var result = 0, sl, i;
    sl = Math.min(s.length, biHexPerDigit);
    for (i = 0; i < sl; i++) {
        result <<= 4;
        result |= charToHex(s.charCodeAt(i))
    }
    return result;
}

function biDump(b) {
    return (b.isNeg ? "minus " : "plus ") + b.digits.join(" ");
}

function biNormalize(x) {
    var k, i;
    k = x.digits.length;
    if (x.digits[k - 1] != 0 && !isNaN(x.digits[k - 1]))
        return x;
    for (i = k - 1; i > 0; i--)
        if (x.digits[i] == 0 || isNaN(x.digits[i])) // todo
            x.digits.pop();
        else
            return x;
    if (x.digits.length == 1 && x.digits[0] == 0)
        x.isNeg = false;
    if (isNaN(x.digits[0]))
        throw new Error("Undefined BigInt: " + biDump(x));
    return x;
}

function biHighIndex(num) {
    biNormalize(num);
    return num.digits.length - 1;
}

function biNumBits(num) {
    var n, d, m, result;
    n = biHighIndex(num);
    d = num.digits[n];
    m = (n + 1) * bitsPerDigit;
    for (result = m; result > m - bitsPerDigit; result--) {
        if ((d & biHalfRadix) != 0)
            break;
        d <<= 1;
    }
    return result;
}

function biCompareAbs(numx, numy) {
    var nx = biHighIndex(numx);
    var ny = biHighIndex(numy);
    if (nx != ny)
        return 1 - 2 * ((nx < ny) ? 1 : 0);
    for (var i = numx.digits.length - 1; i > -1; i--)
        if (numx.digits[i] != numy.digits[i])
            return 1 - 2 * ((numx.digits[i] < numy.digits[i]) ? 1 : 0);
    return 0;
}

function biCompare(numx, numy) {
    if (numx.isNeg != numy.isNeg)
        return 1 - 2 * (numx.isNeg ? 1 : 0);
    return numx.isNeg ? -biCompareAbs(numx, numy) : biCompareAbs(numx, numy);
}

function biAddNatural(numx, numy) {
    var nx, ny, i, c, result, source, k;
    nx = biHighIndex(numx) + 1;
    ny = biHighIndex(numy) + 1;
    i = 0;
    c = 0;
    if (nx > ny) {
        result = biAbs(numx);
        source = numy;
        k = ny;
    } else {
        result = biAbs(numy);
        source = numx;
        k = nx;
    }
    while (i < k) {
        result.digits[i] += source.digits[i] + c;
        if (result.digits[i] < biRadix) {
            c = 0;
        } else {
            result.digits[i] &= maxDigitVal;
            c = 1;
        }
        i++;
    }
    while (c > 0) {
        result.digits[i] = (result.digits[i] || 0) + c;
        if (result.digits[i] < biRadix) {
            c = 0;
        } else {
            result.digits[i] &= maxDigitVal;
            c = 1;
        }
        i++;
    }
    return result;
}

function biSubtractNatural(numx, numy) {
// require numx >= numy
    var nx, ny, result, resultdigits, xdigits, ydigits, c, i;
    nx = biHighIndex(numx) + 1;
    ny = biHighIndex(numy) + 1;
    result = biAbs(numx);
    resultdigits = result.digits;
    xdigits = numx.digits;
    ydigits = numy.digits;
    c = 0;
    for (i = 0; i < ny; i++) {
        if (xdigits[i] >= ydigits[i] - c) {
            resultdigits[i] = xdigits[i] - ydigits[i] + c;
            c = 0;
        } else {
            resultdigits[i] = biRadix + xdigits[i] - ydigits[i] + c;
            c = -1;
        }
    }
    while (c < 0 && i < nx) {
        if (xdigits[i] >= -c) {
            resultdigits[i] = xdigits[i] + c;
            c = 0;
        } else {
            resultdigits[i] = biRadix + xdigits[i] + c;
            c = -1;
        }
        i++;
    }
    return biNormalize(result);
}

function biAdd(numx, numy) {
    var result, x_y;
    if (!numx.isNeg && !numy.isNeg)
        return biAddNatural(numx, numy);
    if (numx.isNeg && numy.isNeg) {
        result = biAddNatural(numx, numy);
        result.isNeg = true;
        return result;
    }
    x_y = biCompareAbs(numx, numy);
    if (x_y == 0)
        return biFromNumber(0);
    if (x_y > 0) {
        result = biSubtractNatural(numx, numy);
        result.isNeg = numx.isNeg;
    }
    if (x_y < 0) {
        result = biSubtractNatural(numy, numx);
        result.isNeg = numy.isNeg;
    }
    return result;
}

function biSubtract(numx, numy) {
    var result, x_y;
    if (!numx.isNeg && numy.isNeg)
        return biAddNatural(numx, numy);
    if (numx.isNeg && !numy.isNeg) {
        result = biAddNatural(numx, numy);
        result.isNeg = true;
        return result;
    }
    x_y = biCompareAbs(numx, numy);
    if (x_y == 0)
        return biCopy(bigZero);
    if (x_y > 0) {
        result = biSubtractNatural(numx, numy);
        result.isNeg = numx.isNeg;
    }
    if (x_y < 0) {
        result = biSubtractNatural(numy, numx);
        result.isNeg = !numx.isNeg;
    }
    return result;
}

function biMultiply(numx, numy) {
    var c, n, t, result, resultdigits, xdigits, ydigits, i, j, uv, k;
    n = biHighIndex(numx) + 1;
    t = biHighIndex(numy) + 1;
    if (n == 1 && numx.digits[0] == 0 || t == 1 && numy.digits[0] == 0)
        return new BigInt();
    result = new BigInt(n + t);
    resultdigits = result.digits;
    xdigits = numx.digits;
    ydigits = numy.digits;
    for (i = 0; i < t; i++) {
        c = 0;
        k = i;
        for (j = 0; j < n; j++, k++) {
            uv = resultdigits[k] + xdigits[j] * ydigits[i] + c;
            resultdigits[k] = uv & maxDigitVal;
            c = uv >>> biRadixBits;
        }
        resultdigits[i + n] = c;
    }
    result.isNeg = numx.isNeg != numy.isNeg;
    return biNormalize(result);
}

function biMultiplyDigit(numx, numy) {
    var n, result, c, j, uv;
    n = biHighIndex(numx) + 1;
    result = new BigInt(n);
    c = 0;
    for (j = 0; j < n; j++) {
        uv = result.digits[j] + numx.digits[j] * numy + c;
        result.digits[j] = uv & maxDigitVal;
        c = uv >>> biRadixBits;
    }
    result.digits[n] = c;
    return result;
}

function arrayCopy(src, srcStart, dest, destStart, count) {
    var i, m;
    if (srcStart >= src.length) {
        if (dest.length == 0)
            dest[0] = 0;
        return;
    }
    for (i = 0; i < destStart; i++)
        // if !dest[i] ???//todo
        dest[i] = 0;
    m = Math.min(srcStart + count, src.length);
    for (i = srcStart, j = destStart; i < m; i++, j++)
        dest[j] = src[i];
}

function biShiftLeft(x, n) {
    var digitCount, result, bits, rightBits, i;
    digitCount = Math.floor(n / bitsPerDigit);
    result = new BigInt();
    arrayCopy(x.digits, 0, result.digits, digitCount, x.digits.length);
    bits = n % bitsPerDigit;
    rightBits = bitsPerDigit - bits;
    result.digits[result.digits.length] = result.digits[result.digits.length] >>> rightBits;
    for (i = result.digits.length - 1; i > 0; i--)
        result.digits[i] = ((result.digits[i] << bits) & maxDigitVal) | (result.digits[i - 1] >>> rightBits);
    result.digits[0] = (result.digits[0] << bits) & maxDigitVal;
    result.isNeg = x.isNeg;
    return biNormalize(result);
}

function biShiftRight(x, n) {
    var digitCount, result, bits, leftBits, i;
    digitCount = Math.floor(n / bitsPerDigit);
    result = new BigInt();
    arrayCopy(x.digits, digitCount, result.digits, 0, x.digits.length - digitCount);
    bits = n % bitsPerDigit;
    leftBits = bitsPerDigit - bits;
    for (i = 0; i < result.digits.length - 1; i++)
        result.digits[i] = (result.digits[i] >>> bits) | ((result.digits[i + 1] << leftBits) & maxDigitVal);
    result.digits[result.digits.length - 1] >>>= bits;
    result.isNeg = x.isNeg;
    return biNormalize(result);
}

function biMultiplyByRadixPower(x, n) {
    var result = new BigInt();
    arrayCopy(x.digits, 0, result.digits, n, x.digits.length);
    return result;
}

function biDivideByRadixPower(x, n) {
    var result = new BigInt();
    arrayCopy(x.digits, n, result.digits, 0, x.digits.length - n);
    return result;
}

function biModuloByRadixPower(x, n) {
    var result = new BigInt();
    arrayCopy(x.digits, 0, result.digits, 0, n);
    return result;
}

function biMultiplyModByRadixPower(x, y, p) {
    var c, n, t, uv, k, result, resultdigits, xdigits, ydigits, i, j;
    n = biHighIndex(x) + 1;
    t = biHighIndex(y) + 1;
    if (n == 1 && x.digits[0] == 0 || t == 1 && y.digits[0] == 0)
        return new BigInt();
    result = new BigInt(p);
    resultdigits = result.digits;
    xdigits = x.digits;
    ydigits = y.digits;
    for (i = 0; i < t && i < p; i++) {
        c = 0;
        k = i;
        for (j = 0; j < n && k < p; j++, k++) {
            uv = resultdigits[k] + xdigits[j] * ydigits[i] + c;
            resultdigits[k] = uv & maxDigitVal;
            c = uv >>> biRadixBits;
        }
        resultdigits[i + n] = c;
    }
    result = biModuloByRadixPower(result, p);
    result.isNeg = x.isNeg != y.isNeg;
    return biNormalize(result);
}

function biDivideModuloNatural(x, y) {
    var nx, ny, q, r, nr, jm, qm, flag;
    nx = biHighIndex(x);
    ny = biHighIndex(y);
    q = new BigInt(-1);
    q.digits = [];
    r = new BigInt();
    //r.digits = [0]
    for (var i = nx; i > -1; i--) {
        r.digits.unshift(x.digits[i]);
        flag = biCompareAbs(y, r);
        if (flag > 0) {
            q.digits.unshift(0);
            continue;
        }
        if (flag == 0) {
            q.digits.unshift(1);
            r.blankZero();
            continue;
        }
        nr = biHighIndex(r);
        if (nr == ny)
            jm = Math.floor((r.digits[nr] * biRadix + (r.digits[nr - 1] || 0)) /
                (y.digits[ny] * biRadix + (y.digits[ny - 1] || 0) + 1));
        else
            jm = Math.floor((r.digits[nr] * biRadixSquared + (r.digits[nr - 1] || 0) * biRadix + (r.digits[nr - 2] || 0)) /
                (y.digits[ny] * biRadix + (y.digits[ny - 1] || 0) + 1));
        jm = Math.max(0, Math.min(jm, maxDigitVal));
        qm = biMultiplyDigit(y, jm);
        r = biSubtract(r, qm);
        if (r.isNeg)
            while (r.isNeg) {
                r = biAdd(r, y);
                jm--
            }
        else
            while (biCompare(r, y) >= 0) {
                r = biSubtract(r, y);
                jm++;
            }
        q.digits.unshift(jm);
    }
    return [biNormalize(q), biNormalize(r)];
}

function biDivideModulo(x, y) {
    var q, r, origXIsNeg, origYIsNeg, result;
    if (biCompareAbs(x, y) < 0) {
        // |x| < |y|
        if ((x.isNeg && y.isNeg) || (!x.isNeg && !y.isNeg)) {
            q = biFromNumber(0);
            r = biCopy(x);
        } else {
            q = biFromNumber(-1);
            r = biAdd(y, x);
        }
        return [q, r];
    }
    origXIsNeg = x.isNeg;
    origYIsNeg = y.isNeg;
    result = biDivideModuloNatural(biAbs(x), biAbs(y));
    q = result[0];
    r = result[1];
    if (!origXIsNeg && !origYIsNeg) {
        return [q, r];
    } else if (origXIsNeg && origYIsNeg) {
        r.isNeg = true;
        return [q, r];
    } else {
        q.isNeg = true;
        q = biSubtract(q, bigOne);
        r.isNeg = origXIsNeg;
        r = biAdd(r, y);
    }
    if (r.digits[0] == 0 && biHighIndex(r) == 0)
        r.isNeg = false;
    return [q, r];
}

function biDivide(x, y) {
    return biDivideModulo(x, y)[0];
}

function biModulo(x, y) {
    return biDivideModulo(x, y)[1];
}

function biMultiplyMod(x, y, m) {
    return biModulo(biMultiply(x, y), m);
}

function biPow(x, y) {
    var result = biCopy(bigOne);
    var a = x;
    while (true) {
        if ((y & 1) != 0)
            result = biMultiply(result, a);
        y >>>= 1;
        if (y == 0)
            break;
        a = biMultiply(a, a);
    }
    return result;
}

function biPowMod(x, y, m) {
    var result = biCopy(bigOne);
    var a = x;
    var k = y;
    while (true) {
        if ((k.digits[0] & 1) != 0)
            result = biMultiplyMod(result, a, m);
        k = biShiftRight(k, 1);
        if (k.digits[0] == 0 && biHighIndex(k) == 0)
            break;
        a = biMultiplyMod(a, a, m);
    }
    return result;
}

function biRandom(n) {
    var result = new BigInt();
    while (n--)
        result.digits[n] = Math.floor(Math.random() * maxDigitVal);
    return result;
}
