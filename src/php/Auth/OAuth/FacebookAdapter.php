<?php

namespace INTERMediator\Auth\OAuth;

use Exception;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;

/**
 * FacebookAdapter - Facebook OAuth Authentication Implementation
 * Facebook OAuth認証の実装
 *
 * This class handles Facebook OAuth authentication process including state management
 * and user information retrieval
 * ステート管理やユーザー情報取得を含むFacebook OAuth認証プロセスを処理するクラス
 *
 * References / 参考文献:
 * @link https://developers.facebook.com/docs/facebook-login Facebook Login Documentation
 * @link https://developers.facebook.com/docs/facebook-login/guides/advanced/manual-flow/#confirm Manual Flow Guide
 * @link https://developers.facebook.com/docs/facebook-login/userexperience User Experience Design
 * @link https://developers.facebook.com/apps/646252287728639/dashboard/ Meta Dashboard
 * @link https://about.meta.com/brand/resources/facebook/logo/ Brand Resources
 *
 * @property string $providerName Name of the OAuth provider (Facebook)
 * @property string $baseURL OAuth authorization dialog URL
 * @property string $getTokenURL Token endpoint URL for access token retrieval
 * @property string $getInfoURL Graph API endpoint for user information
 * @property string $clientId Application's client ID from Facebook
 * @property string $clientSecret Application's client secret from Facebook
 * @property string $redirectURL OAuth callback URL for the application
 */
class FacebookAdapter extends ProviderAdapter
{
    /**
     * Initializes the Facebook OAuth adapter
     * Facebook OAuth アダプターを初期化
     *
     * Sets the provider name and configures endpoint URLs for:
     * - OAuth dialog
     * - Access token retrieval
     * - User information retrieval
     *
     * プロバイダー名と以下のエンドポイントURLを設定:
     * - OAuth ダイアログ
     * - アクセストークン取得
     * - ユーザー情報取得
     */
    function __construct()
    {
//        $this->providerName = 'Facebook';
        $this->baseURL = 'https://www.facebook.com/v21.0/dialog/oauth';
        $this->getTokenURL = "https://graph.facebook.com/v21.0/oauth/access_token";
        $this->getInfoURL = "https://graph.facebook.com/me";
    }

    /**
     * Configures the adapter for testing purposes
     * テスト目的のためのアダプター設定
     *
     * @return ProviderAdapter Returns the current adapter instance for method chaining
     *                        メソッドチェーン用の現在のアダプターインスタンスを返す
     */
    public function setTestMode(): ProviderAdapter //MyNumberCardAdapter
    {
        return $this;
    }

    /**
     * Validates the OAuth authentication process and credentials
     * OAuth認証プロセスと認証情報を検証
     *
     * @return bool True if validation succeeds, false otherwise
     *              検証が成功した場合はtrue、それ以外はfalse
     */
    public function validate(): bool
    {
        return $this->validate_impl(true);
    }

    /**
     * Generates the Facebook OAuth authorization URL with state parameter
     * ステートパラメータを含むFacebook OAuth認証URLを生成
     *
     * @return string Complete authorization URL with state, redirect URI, and client ID
     *                ステート、リダイレクトURI、クライアントIDを含む完全な認証URL
     */
    public function getAuthRequestURL(): string
    {
        $state = IMUtil::randomString(32);
        $this->storeCode($state, "@F:state@");
        $this->storeProviderName($state);
        $this->storeBackURL($_SERVER['HTTP_REFERER'],$state);
        return $this->baseURL . '?redirect_uri=' . urlencode($this->redirectURL)
            . '&client_id=' . urlencode($this->clientId)
            . '&state=' . urlencode($state);
    }

    /**
     * Retrieves user information after successful OAuth authentication
     * OAuth認証成功後にユーザー情報を取得
     *
     * @return array Array containing username and realname of authenticated user
     *               認証されたユーザーのユーザー名と実名を含む配列
     * @throws Exception When state validation fails or token acquisition fails
     *                  ステート検証が失敗またはトークン取得が失敗した場合
     */
    public function getUserInfo(): array
    {
        if (!isset($_GET["state"])) {
            throw new Exception("Failed with security issue. The state parameter doesn't exist in the request.");
        }
        $this->stateValue = $_GET["state"];
        if(!$this->checkCode($this->stateValue, "@F:state@")){
            throw new Exception("Failed with security issue. The state parameter isn't same as the stored one.");
        }
        $input_token = $_GET['code'] ?? "";
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
        return ["username" => "{$userInfo->id}@{$this->providerName}", "realname" => $userInfo->name];
    }
}