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

/**
 * Handles field data formatting using data converters for database operations in INTER-Mediator.
 */
class Formatters
{
    /** Array of formatter objects for each field.
     * @var array
     */
    private array $formatter = [];

    /** Set formatter objects for fields.
     * @param mixed $fmt Array of formatter definitions.
     * @return void
     */
    public function setFormatter($fmt)
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[$oneItem['field']])) {
                    $cvClassName = "INTERMediator\\Data_Converter\\" . $oneItem['converter-class'];
                    $this->formatter[$oneItem['field']]
                        = new $cvClassName($oneItem['parameter'] ?? '');
                }
            }
        }
    }

    /** Convert field data from DB format to user format using the field's formatter.
     * @param mixed $field Field name.
     * @param mixed $data Data from the database.
     * @return mixed Converted data for user display.
     */
    public function formatterFromDB($field, $data)
    {
        if (isset($this->formatter[$field])) {
            return $this->formatter[$field]->converterFromDBtoUser($data);
        }
        return $data;
    }

    /** Convert field data from user format to DB format using the field's formatter.
     * @param mixed $field Field name.
     * @param mixed $data Data from the user.
     * @return mixed Converted data for DB storage.
     */
    public function formatterToDB($field, $data)
    {
        if (isset($this->formatter[$field])) {
            return $this->formatter[$field]->converterFromUserToDB($data);
        }
        return $data;
    }
}
