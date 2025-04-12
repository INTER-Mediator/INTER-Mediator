<?php

namespace INTERMediator\Auth\OAuth;

use Exception;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;

/**
 *
 */
abstract class ProviderAdapter
{
    /**
     * @var bool
     */
    public bool $debugMode = true;
    /**
     * @var string
     */
    protected string $providerName = "";
    /**
     * @var string
     */
    protected string $baseURL = "";
    /**
     * @var string
     */
    protected string $getTokenURL = "";
    /**
     * @var string
     */
    protected string $getInfoURL = "";
    /**
     * @var string|null
     */
    protected ?string $clientId = "";
    /**
     * @var string|null
     */
    protected ?string $clientSecret = "";
    /**
     * @var string|null
     */
    protected ?string $redirectURL = "";
    /**
     * @var string|null
     */
    protected ?string $infoScope = "";

    /**
     * @var string|null
     */
    protected ?string $issuer = "";
    /**
     * @var string|null
     */
    protected ?string $jwksURL = "";

    /**
     * @param string $clientId
     * @return void
     */
    public function setDebugMode(bool $debugMode): void
    {
        $this->debugMode = $debugMode;
    }

    /**
     * @param string $clientId
     * @return void
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @param string $clientId
     * @return void
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @param string $secret
     * @return void
     */
    public function setClientSecret(string $secret): void
    {
        $this->clientSecret = $secret;
    }

    /**
     * @param string $url
     * @return void
     */
    public function setRedirectURL(string $url): void
    {
        $this->redirectURL = $url;
    }

    /**
     * @param string $info
     * @return void
     */
    public function setInfoScope(string $info): void
    {
        $this->infoScope = $info;
    }

    /**
     * @param bool $isRequireSecret
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->baseURL && $this->getTokenURL && $this->getInfoURL && $this->clientId && $this->redirectURL) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public abstract function getAuthRequestURL(): string;

    /**
     * @return array
     */
    public abstract function getUserInfo(): array;

    /**
     * @param string $provider
     * @return ProviderAdapter|null
     */
    public static function createAdapter(string $provider): ProviderAdapter|null
    {
        switch (strtolower($provider)) {
            case "google":
                return new GoogleAdapter();
            case "facebook":
                return new FacebookAdapter();
            case "mynumbercard-sandbox":
                return (new MyNumberCardAdapter()) . setTestMode();
            case "mynumbercard":
                return new MyNumberCardAdapter();
        }
        return null;
    }

    /**
     * @param string $url
     * @param bool $isPost
     * @param array|null $params
     * @param string|null $access_token
     * @return mixed
     * @throws Exception
     */
    protected function communication(string  $url,
                                     bool    $isPost = false,
                                     ?array  $params = null,
                                     ?string $access_token = null): mixed
    {
        $postParam = "";
        if ($params) {
            $isFirstTime = true;
            foreach ($params as $key => $value) {
                if (!$isFirstTime) {
                    $postParam .= "&";
                }
                $postParam .= "{$key}=" . urlencode($value);
                $isFirstTime = false;
            }
            if (!$isPost) {
                $url .= "?{$postParam}";
            }
        }
        if (function_exists('curl_init')) {
            $httpCode = 0;
            $session = curl_init($url);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            if ($access_token) {
                curl_setopt($session, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$access_token}"]);
            }
            if ($isPost) {
                curl_setopt($session, CURLOPT_POST, true);
                curl_setopt($session, CURLOPT_POSTFIELDS, $postParam);
            }
            $content = curl_exec($session);
            if (!curl_errno($session)) {
                $header = curl_getinfo($session);
                $httpCode = $header['http_code'];
            }
            curl_close($session);
        } else {
            throw new Exception("Couldn't get call api (curl is NOT installed).");
        }
        if ($httpCode != 200) {
            throw new Exception("Error[{$httpCode}]: {$url}\nDescription: {$content}");
        }
        $response = json_decode($content);
        if (!$response) {
            throw new Exception("Communication Error: " . var_export($content, true));
        }
        if (isset($response->error)) {
            throw new Exception("Error Response: " . var_export($response, true));
        }
        return $response;
    }

    /**
     * @param string $token
     * @return object
     * @throws Exception
     */
    protected function checkIDToken(string $token, string $access_token): object
    {
        $certficate = $this->communication($this->jwksURL, false, null);
        $jWebToken = explode(".", $token);
        $headerIDToken = json_decode(base64_decode(strtr($jWebToken[0], '-_', '+/')));
        $payloadIDToken = json_decode(base64_decode(strtr($jWebToken[1], '-_', '+/')));
        $jwkSet = JWKSet::createFromJson(json_encode($certficate));
        $key = $jwkSet->get($headerIDToken->kid);
        $algorithmManager = new AlgorithmManager([new ES256(), new RS256(),]);
        $jwsVerifier = new JWSVerifier($algorithmManager);
        $serializerManager = new JWSSerializerManager([new CompactSerializer(),]);
        if (!$jwsVerifier->verifyWithKey($serializerManager->unserialize($token), $key, 0)) {
            throw new Exception("Invalid Signature. {$payloadIDToken->iss}");
        }
        if ($payloadIDToken->iss !== $this->issuer) {
            throw new Exception("Invalid issuer. {$payloadIDToken->iss}");
        }
        if (!str_contains($payloadIDToken->aud, $this->clientId)) {
            throw new Exception("Invalid audience. {$payloadIDToken->aud}");
        }
        if ($payloadIDToken->exp < time()) {
            throw new Exception("Invalid exp. {$payloadIDToken->exp}");
        }
        $atHashNorm = strtr($payloadIDToken->at_hash, '-_', '+/');
        $expectedHash = str_replace("=", "",
            base64_encode(substr(hash('sha256', $access_token, true), 0, 16)));
        if ($atHashNorm !== $expectedHash) {
            throw new Exception("Invalid at_hash. {$payloadIDToken->at_hash} vs {$expectedHash}");
        }
        return $payloadIDToken;
    }
}