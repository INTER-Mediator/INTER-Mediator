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

namespace INTERMediator\DB;

use INTERMediator\Data_Converter\DataConverter;

/**
 * Handles field data formatting using data converters for database operations in INTER-Mediator.
 */
class Formatters
{
    /** Array of formatter objects for each field.
     * @var array<string, DataConverter>
     */
    private array $formatter = [];

    /** Set formatter objects for fields.
     * @param array<int, array<string, string|bool>>|null $fmt Array of formatter definitions.
     * @return void
     */
    public function setFormatter($fmt): void
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[strval($oneItem['field'])])) {
                    $cvClassName = "INTERMediator\\Data_Converter\\" . $oneItem['converter-class'];
                    if (class_exists($cvClassName) && is_subclass_of($cvClassName, \INTERMediator\Data_Converter\DataConverter::class)) {
                        $this->formatter[strval($oneItem['field'])]
                            = new $cvClassName($oneItem['parameter'] ?? '');
                    } else {
                        Logger::getInstance()->setErrorMessage("The converter class '{$cvClassName}' wasn't instantiated. It requires a subclass of the DataConverter interface.");
                    }
                }
            }
        }
    }

    /** Convert field data from DB format to user format using the field's formatter.
     * @param string $field Field name.
     * @param string|null $data Data from the database.
     * @return string|null Converted data for user display.
     */
    public function formatterFromDB(string $field, ?string $data): ?string
    {
        if (isset($this->formatter[$field])) {
            return $this->formatter[$field]->converterFromDBtoUser($data);
        }
        return $data;
    }

    /** Convert field data from user format to DB format using the field's formatter.
     * @param string $field Field name.
     * @param string|null $data Data from the user.
     * @return string|null Converted data for DB storage.
     */
    public function formatterToDB(string $field, ?string $data): ?string
    {
        if (isset($this->formatter[$field])) {
            return $this->formatter[$field]->converterFromUserToDB($data);
        }
        return $data;
    }
}
