<?php
/**
 * This class provides methods to append a prefix to a string when converting from the database to the user,
 * and to remove the prefix when converting from the user to the database.
 * Useful for formatting or masking data transparently.
 */
namespace INTERMediator\Data_Converter;

/**
 * Class AppendPrefix
 * Adds a specified prefix to a string for display, and removes it when saving.
 */
class AppendPrefix
{
    /**
     * The prefix string to append or remove during conversion.
     * @var string
     */
    private string $appendStr;

    /**
     * Constructor sets the prefix string to use for conversions.
     *
     * @param string $str The prefix to append or remove. Defaults to an empty string.
     */
    function __construct(string $str = '')
    {
        $this->appendStr = $str;
    }

    /**
     * Converts a value from database format to user format by appending the prefix.
     *
     * @param string|null $str The original value from the database.
     * @return string The value with the prefix appended.
     */
    function converterFromDBtoUser(?string $str): string
    {
        return $this->appendStr . $str;
    }

    /**
     * Converts a value from user format to database format by removing the prefix if present.
     *
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
