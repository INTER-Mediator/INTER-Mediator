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

function biModularInverse(e, m) {
    var result;
    e = biModulo(e, m);
     result = biExtendedEuclid(m, e);
    if (!result[2].isOne())
        return null;
    return biModulo(result[1], m);
}

function biExtendedEuclid(a, b) {
    var result;
    if (biCompare(a, b) >= 0)
        return biExtendedEuclidNatural(a, b);
     result = biExtendedEuclidNatural(b, a);
    return [ result[1], result[0], result[2] ];
}

function biExtendedEuclidNatural(a, b) {
// calculates a * x + b * y = gcd(a, b) 
// require a >= b
    var qr, q, r, x1, x2, y1, y2, x, y;
    if (b.isZero())
        return [biFromNumber(1), biFromNumber(0), a];
    x1 = biFromNumber(0);
    x2 = biFromNumber(1);
    y1 = biFromNumber(1);
    y2 = biFromNumber(0);
    while (!b.isZero()) {
        qr = biDivideModulo(a, b);
        q = qr[0];
        r = qr[1];
        x = biSubtract(x2, biMultiply(q, x1));
        y = biSubtract(y2, biMultiply(q, y1));
        a = b;
        b = r;
        x2 = x1;
        x1 = x;
        y2 = y1;
        y1 = y;
    }
    return [x2, y2, a];
}

function biMontgomeryPowMod(T, EXP, N) {
    var result = biFromNumber(1);
    var m = biModulo(biMultiply(T, N.R), N);
    for (var i = EXP.bin.length - 1; i > -1; i--) {
        if (EXP.bin.charAt(i) == "1") {
            result = biMultiply(result, m);
            result = biMontgomeryModulo(result, N)
        }
        m = biMultiply(m, m);
        m = biMontgomeryModulo(m, N)
    }
    return result;
}

function biMontgomeryModulo(T, N) {
    var m = biModuloByRadixPower(T, N.nN);
    //m = biMultiply(m, N.Ninv);
    //m = biModuloByRadixPower(m, N.nN);
    m = biMultiplyModByRadixPower(m, N.Ninv, N.nN);
    m = biMultiply(m, N);
    m = biAdd(T, m);
    m = biDivideByRadixPower(m, N.nN);
    while (biCompare(m, N) >= 0) {
        m = biSubtract(m, N);
    }
    return m;
}

