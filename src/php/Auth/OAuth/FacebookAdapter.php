<?php

namespace INTERMediator\Auth\OAuth;

use Exception;
use INTERMediator\IMUtil;

/**
 *
 */
class FacebookAdapter extends ProviderAdapter
{
    /**
     *
     */
    function __construct()
    {
        $this->providerName = 'Facebook';
        $this->baseURL = 'https://www.facebook.com/v21.0/dialog/oauth';
        $this->getTokenURL = "https://graph.facebook.com/v21.0/oauth/access_token";
        $this->getInfoURL = "https://graph.facebook.com/me";
    }

    /**
     * @return ProviderAdapter
     */
    public function setTestMode(): ProviderAdapter //MyNumberCardAdapter
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthRequestURL(): string
    {
        $state = IMUtil::randomString(32);
        return $this->baseURL . '?redirect_uri=' . urlencode($this->redirectURL)
            . '&client_id=' . urlencode($this->clientId)
            . '&state=' . urlencode($state);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getUserInfo(): array
    {
        $input_token = $_REQUEST['code'] ?? "";
        if (!$input_token) {
            throw new Exception("This isn't redirected from the providers site.");
        }
        $tokenparams = array(
            'code' => $input_token,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectURL,
        );
        $response = $this->communication($this->getTokenURL, false, $tokenparams);
        $access_token = $response->access_token ?? "";
        if (strlen($access_token) < 1) {
            throw new Exception("Error: Access token couldn't get from: {$this->getTokenURL}.");
        }
        $userInfo = $this->communication($this->getInfoURL, false, ["access_token" => $access_token]);
        return ["username" => "{ $userInfo->id}@{$this->providerName}", "realname" => $userInfo->name];
    }
}