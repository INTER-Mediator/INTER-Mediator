<?php

namespace INTERMediator\Data_Converter;

interface DataConverter
{
    /** @param string|null $str
     * @return string
     */
    function converterFromDBtoUser(string|null $str): string;

    /** @param string $str
     * @return string|null
     */
    function converterFromUserToDB(string $str): string|null;

}