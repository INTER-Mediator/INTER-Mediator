<?php
/**
 * CWPKit
 * Copyright (c) Atsushi Matsuo (http://www.famlog.jp/)
 *
 * @copyright     Atsushi Matsuo (http://www.famlog.jp/)
 * @link          http://www.famlog.jp/cwpkit/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

class CWPKit
{
    protected $config = array();

    function __construct(array $config = array())
    {
        $this->config = $config;
    }

    public function query($queryString)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
            $this->config['urlScheme'] . '://' . $this->config['dataServer'] .
            ':' . $this->config['dataPort'] . '/fmi/xml/fmresultset.xml');
        curl_setopt($ch, CURLOPT_USERPWD,
            $this->config['DBUser'] . ':' . $this->config['DBPassword']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $xml = curl_exec($ch);
        curl_close($ch);
        libxml_use_internal_errors(true);

        file_put_contents("/tmp/1", var_export($xml,true));
        file_put_contents("/tmp/2", var_export(curl_error($ch),true));

        return simplexml_load_string($xml);
    }

    public function getServerVersion()
    {
        $version = '';
        $data = $this->query('-dbnames');
        if ($data !== FALSE) {
            $version = (string) $data->product->attributes()->version;
        }

        return $version;
    }

    public function _removeDuplicatedQuery($queryString)
    {
        $conditions = array();
        $query = explode('&', $queryString);
        foreach ($query as $condition) {
            $val = explode('=', $condition);
            if (!isset($conditions[$val[0]])) {
                $conditions[$val[0]] = $val[1] ?? '';
            }
        }

        $queryString = '';
        $i = 0;
        foreach ($conditions as $key => $val) {
            if ($i > 0 && $queryString !== '&') {
                $queryString .= '&';
            }
            $queryString .= $key . '=' . $val;
            if ($queryString === '='){
                $queryString = '&';
            }
            $i++;
        }

        return $queryString;
    }

    public function _checkDuplicatedFXCondition($queryString, $field, $value)
    {
        $query = $this->_removeDuplicatedQuery(
            $queryString . '&' . $field . '=' . $value
        );
        if ($queryString === $query) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
