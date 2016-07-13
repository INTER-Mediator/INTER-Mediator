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
        $data = simplexml_load_string($xml);

        return $data;
    }

    public function getServerVersion()
    {
        $data = $this->query('-dbnames');
        $version = $data->product->attributes()->version;
        
        if (isset($version[1])) {
            return $version[1];
        } else {
            return '';
        }
    }
}
