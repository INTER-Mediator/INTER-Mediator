<?php

namespace INTERMediator\Auth\OAuth;

use Exception;
use INTERMediator\DB\Proxy;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
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
     * The name of the OAuth provider (e.g., "google", "facebook").
     * @var string
     */
    protected string $providerName = "";
    /**
     * The base URL for the OAuth provider's endpoints.
     * @var string
     */
    protected string $baseURL = "";
    /**
     * The URL for obtaining access tokens from the provider.
     * @var string
     */
    protected string $getTokenURL = "";
    /**
     * The URL for retrieving user information from the provider.
     * @var string
     */
    protected string $getInfoURL = "";
    /**
     * The client ID issued by the OAuth provider.
     * @var string|null
     */
    protected ?string $clientId = "";
    /**
     * The client secret issued by the OAuth provider.
     * @var string|null
     */
    protected ?string $clientSecret = "";
    /**
     * The URL where the provider will redirect after authentication.
     * @var string|null
     */
    protected ?string $redirectURL = "";
    /**
     * The scope of information requested from the provider.
     * @var string|null
     */
    protected ?string $infoScope = "";

    /**
     * The issuer URL of the authorization server.
     * @var string|null
     */
    protected ?string $issuer = "";
    /**
     * The URL where JSON Web Key Set can be retrieved.
     * @var string|null
     */
    protected ?string $jwksURL = "";
    /**
     * The path to the private key file for signing JWTs.
     * @var string|null
     */
    protected ?string $keyFilePath = "";

    protected ?string $stateValue = null;

    /**
     * Sets the debug mode. If true, debug messages will be printed to the log.
     *
     * @param bool $debugMode The debug mode flag.
     * @return void
     */
    public function setDebugMode(bool $debugMode): void
    {
        $this->debugMode = $debugMode;
    }

    /**
     * @return string The provider name, such as "google", "facebook", "mynumbercard-sandbox", or "mynumbercard".
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * Sets the client ID issued by the authorization server.
     *
     * @param string $clientId Client ID issued by the authorization server.
     * @return void
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * Sets the client secret issued by the authorization server.
     *
     * @param string $secret Client secret issued by the authorization server.
     * @return void
     */
    public function setClientSecret(string $secret): void
    {
        $this->clientSecret = $secret;
    }

    /**
     * Sets the URL that the client will be redirected to after the user has granted
     * access to the client.
     *
     * @param string $url The URL that the client will be redirected to after the user has granted
     * access to the client.
     * @return void
     */
    public function setRedirectURL(string $url): void
    {
        $this->redirectURL = $url;
    }

    /**
     * Sets the scope for the user information to be retrieved.
     *
     * @param string $info The scope for the user information to be retrieved.
     * @return void
     */
    public function setInfoScope(string $info): void
    {
        $this->infoScope = $info;
    }

    /**
     * @param string $path The path to the private key file for the client.
     * @return void
     */
    public function setKeyFilePath(string $path): void
    {
        $this->keyFilePath = $path;
    }

    /**
     * Validates the adapter configuration.
     * @return bool True if the adapter is properly configured, false otherwise.
     */
    public abstract function validate(): bool;

    /**
     * Checks if the adapter is properly configured. This supposed to be called from the validate() method.
     * @param bool $isRequireSecret Whether the client secret is required.
     * @return bool True if the adapter is properly configured, false otherwise.
     */
    protected function validate_impl(bool $isRequireSecret = true): bool
    {
        if ($this->baseURL && $this->getTokenURL && $this->getInfoURL && $this->clientId && $this->redirectURL) {
            if ($isRequireSecret && !$this->clientSecret) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @return string
     *
     * Returns the URL to request the authentication of the user.
     * The returned URL is a string.
     */
    public abstract function getAuthRequestURL(): string;

    /**
     * Stores the provider name associated with the given state.
     * The provider name is stored in the internal database.
     * This method is going to be called from the implementing getAuthRequestURL() method.
     *
     * @param string $state The state value to associate with the provider name.
     * @return void
     */
    protected function storeProviderName(string $state): void
    {
        $this->storeCode($this->providerName, "@provider@", $state);
    }

    /**
     * Stores the back URL associated with the given state.
     * The back URL is the URL that the user was redirected from to the provider's site.
     * The URL is stored in the internal database.
     * This method is going to be called from the implementing getAuthRequestURL() method.
     *
     * @param string $url The URL to store with the given state.
     * @param string $state The state value to associate with the back URL.
     * @return void
     */
    protected function storeBackURL(string $url, string $state): void
    {
        $this->storeCode($url, "@backurl@", $state);
    }

    /**
     * Retrieves the back URL stored with the given state.
     *
     * The back URL is the URL that the user was redirected from to the provider's site.
     * The returned URL is a string.
     *
     * @return string|null The back URL associated with the state, or null if no such state exists.
     */
    public function getBackURL(): ?string
    {
        return $this->retrieveCode($this->stateValue, "@backurl@")[0] ?? null;
    }

    /**
     * Returns a user information array from the provider.
     *
     * The return value is an array with the following keys:
     * - "username": The username of the user. The value is a string.
     * - "realname": The real name of the user. The value is a string.
     * - "email": The E-mail address of the user. The value is a string.
     * - "birthdate": The birthday of the user. The value is a string.
     * - "gender": The gender of the user. The value is a string.
     *
     * @return array User information array
     * @throws Exception Throws an exception if user information couldn't be retrieved
     */
    public abstract function getUserInfo(): array;

    /**
     * @return ProviderAdapter
     *
     * Sets the adapter to test mode and changes some properties of the adapter.
     * The actual behavior depends on the implementation of the adapter.
     */
    public abstract function setTestMode(): ProviderAdapter;

    /**
     * Create an instance of ProviderAdapter based on the provider name.
     *
     * @param string $provider The name of the provider. Supported providers are "google", "facebook", "mynumbercard-sandbox", and "mynumbercard".
     * @return ProviderAdapter|null The instance of ProviderAdapter or null if the provider is not supported
     */
    public static function createAdapter(string $provider): ProviderAdapter|null
    {
        $providerName = $provider;
        if(str_contains($providerName, "_")) {
            $providerName = substr($providerName, 0, strpos($providerName, "_"));
        }
        $adapter = null;
        // Switch based on the provider name
        switch (strtolower($providerName)) {
            case "google":
                // Create an instance of GoogleAdapter
                $adapter= new GoogleAdapter();
                break;
            case "facebook":
                // Create an instance of FacebookAdapter
                $adapter= new FacebookAdapter();
                break;
            case "mynumbercard-sandbox":
                // Create an instance of MyNumberCardAdapter and set it to test mode
                $adapter= (new MyNumberCardAdapter())->setTestMode();
                break;
            case "mynumbercard":
                // Create an instance of MyNumberCardAdapter
                $adapter= new MyNumberCardAdapter();
                break;
        }
        if (!is_null($adapter)) {
            $adapter->providerName = $provider;
        }
        return $adapter;
    }

    /**
     * Create an instance of ProviderAdapter based on the state value.
     *
     * This method is used to create an instance of ProviderAdapter from the state value.
     * The state value is used to determine which provider to use.
     * If the provider name is not found in the state value, the method looks for the provider name in the _im_oauth_provider cookie.
     * If the provider name is not found in the cookie, the method returns null.
     *
     * @param string $state The state value
     * @return ProviderAdapter|null The instance of ProviderAdapter or null if the provider is not supported
     */
    public static function createAdapterFromState(string $state): ProviderAdapter|null
    {
        $candidates = ProviderAdapter::retrieveCodeStatic($state, "@provider@");
        $providerName = $candidates[0] ?? "";
        return self::createAdapter($providerName);
    }

    /**
     * Performs a HTTP request to the specified URL.
     *
     * @param string $url The URL to request
     * @param bool $isPost Whether to use a POST request or a GET request
     * @param array|null $params The parameters to send with the request
     * @param string|null $access_token The access token to include in the request
     * @return mixed The response from the server
     * @throws Exception If there is an error with the request
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
                $postParam .= urlencode($key) . "=" . urlencode($value);
                $isFirstTime = false;
            }
            if (!$isPost) {
                $url .= "?" . $postParam;
            }
        }
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
        $curlError = curl_errno($session);
        $errorMessage = curl_error($session);
        $header = curl_getinfo($session);
        if (!$curlError) {
            $httpCode = $header['http_code'];
        } else {
            throw new Exception("CURL Error[{$curlError}]: {$url}, Message: {$errorMessage}, Headers: " . var_export($header, true));
        }
        if ($httpCode != 200) {
            throw new Exception("HTTP Error[{$httpCode}]: {$url}\nDescription: {$content}");
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
     * Creates a JSON Web Token (JWT) from a payload.
     *
     * @param string $payload The JSON payload to sign
     * @return string The signed JWT
     */
    protected function createJWT(string $payload): string
    {
        // Create a new Algorithm Manager
        $algorithmManager = new AlgorithmManager([new RS256()]);

        // Load the private key file
        $jwk = JWKFactory::createFromKeyFile($this->keyFilePath, null, ['use' => 'sig']);

        // Create a new JWS Builder
        $jwsBuilder = new JWSBuilder($algorithmManager);

        // Create a new JWS
        $jws = $jwsBuilder->create()->withPayload($payload)->addSignature($jwk, ['alg' => 'RS256'])->build();

        // Create a new serializer
        $serializer = new CompactSerializer(); // The serializer

        // Serialize the JWS and return it
        return $serializer->serialize($jws, 0);
    }

    /**
     * Verify the ID token with the JWK set.
     *
     * @param string $token The ID token to verify
     * @param string $access_token The access token to compare with
     * @return object The payload of the ID token
     * @throws Exception If the verification fails
     */
    protected function checkIDToken(string $token, string $access_token): object
    {
        // Get the JWK set from the jwks URL
        $certficate = $this->communication($this->jwksURL, false, null);

        // Split the ID token into its components
        $jWebToken = explode(".", $token);

        // Get the header and payload of the ID token
        $headerIDToken = json_decode($this->base64url_decode($jWebToken[0]));
        $payloadIDToken = json_decode($this->base64url_decode($jWebToken[1]));

        // Get the JWK from the JWK set
        $jwkSet = JWKSet::createFromJson(json_encode($certficate));
        $key = $jwkSet->get($headerIDToken->kid);

        // Verify the signature of the ID token
        $algorithmManager = new AlgorithmManager([new ES256(), new RS256(),]);
        $jwsVerifier = new JWSVerifier($algorithmManager);
        $serializerManager = new JWSSerializerManager([new CompactSerializer(),]);
        if (!$jwsVerifier->verifyWithKey($serializerManager->unserialize($token), $key, 0)) {
            throw new Exception("Invalid Signature. {$payloadIDToken->iss}");
        }

        // Verify the issuer of the ID token
        if ($payloadIDToken->iss !== $this->issuer) {
            throw new Exception("Invalid issuer. {$payloadIDToken->iss}");
        }

        // Verify the audience of the ID token
        if (!str_contains($payloadIDToken->aud, $this->clientId)) {
            throw new Exception("Invalid audience. {$payloadIDToken->aud}");
        }

        // Verify the expiration of the ID token
        if ($payloadIDToken->exp < time()) {
            throw new Exception("Invalid exp. {$payloadIDToken->exp}");
        }

        // Verify the at_hash of the ID token
        $expectedHash = $this->base64url_encode(substr(hash('sha256', $access_token, true), 0, 16));
        if ($payloadIDToken->at_hash !== $expectedHash) {
            throw new Exception("Invalid at_hash. {$payloadIDToken->at_hash} vs {$expectedHash}");
        }

        // Return the payload of the ID token
        return $payloadIDToken;
    }

    /**
     * Encodes a string with base64url, which is a URL-safe variation of Base64.
     * @see https://tools.ietf.org/html/rfc4648#section-5
     *
     * The main difference between base64url and regular base64 is that
     * base64url uses - and _ instead of + and / respectively.
     *
     * @param string $data The string to be encoded
     * @return string The base64url encoded string
     */
    protected function base64url_encode(string $data): string
    {
        // Base64 encode the string
        $encoded = base64_encode($data);
        // Replace the + and / characters with - and _ respectively
        $encoded = strtr($encoded, '+/', '-_');
        // Remove the padding characters (=) from the end of the string
        $encoded = rtrim($encoded, '=');
        // Return the base64url encoded string
        return $encoded;
    }

    /**
     * Decodes a base64url encoded string.
     * @param string $data The base64url encoded string
     * @return string The decoded string
     */
    protected function base64url_decode(string $data): string
    {
        // Pad the string with '=' to make the length a multiple of 4
        $data = str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT);
        // Decode the string with base64
        return base64_decode($data);
    }

    /**
     * Stores the authorization code issued by the authorization server
     * into the internal database.
     *
     * @param string $code Authorization code issued by the authorization server
     * @param string $prefix Prefix for the challenge to be stored
     * @param string|null $key Key to be used for storing the challenge.
     *                         If null, use the client ID.
     *
     * @return void
     */
    protected function storeCode(string $code, string $prefix, ?string $key = null): void
    {
        // Create a new Proxy instance to access the database
        $dbProxy = new Proxy(true);
        // Initialize the Proxy instance
        $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
        // If $key is not set, use the clientID property
        if (!$key) {
            $key = $this->clientId;
        }
        // Store the challenge in the database
        $dbProxy->authDbClass->authHandler->authSupportStoreChallenge(
            0, $code, substr($key, 0, 64), $prefix, true);
    }

    /**
     * Retrieves the stored authorization code from the internal database.
     *
     * @param string $key The key used to store the challenge.
     *                     If null, use the client ID.
     * @param string $prefix The prefix used to store the challenge.
     * @return array The retrieved authorization code.
     */
    protected function retrieveCode($key, $prefix): array
    {
        // Create a new Proxy instance to access the database
        $dbProxy = new Proxy(true);
        // Initialize the Proxy instance
        $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
        // Retrieve the stored challenge from the database
        $challenges = $dbProxy->authDbClass->authHandler->authSupportRetrieveChallenge(
            0, substr($key, 0, 64), true, $prefix, true);
        // Split the retrieved challenge into an array
        return explode("\n", $challenges);
    }

    /**
     * Retrieves the stored authorization code from the internal database.
     * This method is static, so it can be called without an instance of the class.
     *
     * @param string $key The key used to store the challenge.
     *                     If null, use the client ID.
     * @param string $prefix The prefix used to store the challenge.
     * @return array The retrieved authorization code.
     */
    protected static function retrieveCodeStatic($key, $prefix): array
    {
        $tempObject = new GoogleAdapter();
        return $tempObject->retrieveCode($key, $prefix);
    }

    /**
     * Checks if the given code is stored in the internal database.
     *
     * @param string $code Authorization code issued by the authorization server
     * @param string $prefix Prefix for the challenge to be stored
     * @return bool True if the code is stored, false otherwise
     */
    protected function checkCode(string $code, string $prefix): bool
    {
        // Retrieve the stored challenge from the database
        $storedCode = $this->retrieveCode($this->clientId, $prefix);
        // Check if the given code is in the stored challenge
        // If the given code is not in the stored challenge, return false
        if (!in_array($code, $storedCode)) {
            return false;
        }
        // If the given code is in the stored challenge, return true
        return true;
    }
}