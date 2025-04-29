<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * IMNumberFormatter provides locale-aware number and currency formatting for INTER-Mediator.
 * It allows customization of decimal points, a thousand separators, and currency symbols based on locale settings.
 */
namespace INTERMediator\Locale;

class IMNumberFormatter
{
    /**
     * The character used for the decimal point in the current locale.
     * @var string
     */
    private string $decimalPoint;
    /**
     * The character used for the thousand separators in the current locale.
     * @var string
     */
    private string $thSeparator;
    /**
     * The currency symbol for the current locale.
     * @var string
     */
    private string $currencySymbol;
    /**
     * The number of fraction digits to use in formatting.
     * @var int
     */
    private int $flactionDigit = 0;

    /**
     * Initializes the formatter using the given locale.
     *
     * @param string $locale The locale string (e.g., 'en_US', 'ja_JP').
     */
    public function __construct(string $locale)
    {
        setlocale(LC_ALL, $locale . '.UTF-8');
        $locInfo = localeconv();
        if ($locInfo['currency_symbol'] == '') {
            $locInfo = IMLocaleFormatTable::getLocaleFormat($locale);
        }
        $this->decimalPoint = $locInfo['mon_decimal_point'];
        $this->thSeparator = $locInfo['mon_thousands_sep'];
        $this->currencySymbol = $locInfo['currency_symbol'];
    }

    /**
     * Gets the locale-specific symbol for decimal or a thousand separator.
     *
     * @param int $attr The attribute code (0 for decimal, 1 for a thousand separators).
     * @return string The requested symbol.
     */
    public function getSymbol(int $attr): string
    {
        $s = '';
        switch ($attr) {
            case 0: /*NumberFormatter::DECIMAL_SEPARATOR_SYMBOL*/
                $s = $this->decimalPoint;
                break;
            case 1: /*NumberFormatter::GROUPING_SEPARATOR_SYMBOL*/
                $s = $this->thSeparator;
                break;
        }
        return $s;
    }

    /**
     * Gets the locale-specific currency symbol.
     *
     * @param int $attr The attribute code (5 for currency symbol).
     * @return string The currency symbol.
     */
    public function getTextAttribute(int $attr): string
    {
        $s = '';
        /*NumberFormatter::CURRENCY_CODE*/
        if ($attr == 5) {
            $s = $this->currencySymbol;
        }
        return $s;
    }

    /**
     * Sets the number of fraction digits to use in formatting.
     *
     * @param int $attr The attribute code (8 for fraction digits).
     * @param string $value The number of fraction digits as a string.
     * @return void
     */
    public function setAttribute(int $attr, string $value): void
    {
        /*NumberFormatter::FRACTION_DIGITS*/
        if ($attr == 8) {
            $this->flactionDigit = intval($value);
        }
    }

    /**
     * Formats a value as a currency string using the current locale settings.
     *
     * @param string|null $value The numeric value to format.
     * @param string|null $currency The currency code (unused).
     * @return string The formatted currency string.
     */
    public function formatCurrency(?string $value, ?string $currency): string
    {
        return $this->currencySymbol .
            number_format($value, $this->flactionDigit, $this->decimalPoint, $this->thSeparator);
    }
}