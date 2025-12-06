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
 namespace INTERMediator\Data_Converter;

/**
 * Class AppendPrefix
 * Adds a specified prefix to a string for display, and removes it when saving.
 * This class provides methods to append a prefix to a string when converting from the database to the user,
 *  and to remove the prefix when converting from the user to the database.
 *  Useful for formatting or masking data transparently.*/
class AppendPrefix
{
    /** The prefix string to append or remove during conversion.
     * @var string
     */
    private string $appendStr;

    /** Constructor sets the prefix string to use for conversions.
     * @param string $str The prefix to append or remove. Defaults to an empty string.
     */
    function __construct(string $str = '')
    {
        $this->appendStr = $str;
    }

    /** Converts a value from database format to user format by appending the prefix.
     * @param string|null $str The original value from the database.
     * @return string The value with the prefix appended.
     */
    function converterFromDBtoUser(?string $str): string
    {
        return $this->appendStr . $str;
    }

    /** Converts a value from user format to database format by removing the prefix if present.
     * @param string $str The value from the user.
     * @return string The value with the prefix removed, or the original value if the prefix is not present.
     */
    function converterFromUserToDB(string $str): string
    {
        if (strpos($str, $this->appendStr) === 0) {
            return substr($str, strlen($this->appendStr));
        }
        return $str;
    }
}
