<?php defined('SYSPATH') or die('No direct script access');
/**
 * OAuth2 Storage for Sessions
 *
 * License is MIT, to be more compatible with PHP League.
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\OAuth2
 * @copyright  2014 Ushahidi
 * @license    http://mit-license.org/
 * @link       http://github.com/php-loep/oauth2-server
 */

use League\OAuth2\Server\Storage\SessionInterface;

class OAuth2_Storage_Session extends OAuth2_Storage implements SessionInterface
{
	/**
	 * Create a new session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO oauth_sessions (client_id, owner_type,  owner_id)
	 *  VALUE (:clientId, :ownerType, :ownerId)
	 * </code>
	 *
	 * @param  string $clientId  The client ID
	 * @param  string $ownerType The type of the session owner (e.g. "user")
	 * @param  string $ownerId   The ID of the session owner (e.g. "123")
	 * @return int               The session ID
	 */
	public function createSession($clientId, $ownerType, $ownerId)
	{
		$data = array(
			'client_id'  => $clientId,
			'owner_type' => $ownerType,
			'owner_id'   => $ownerId,
			);
		return $this->insert('oauth_sessions', $data);
	}

	/**
	 * Delete a session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * DELETE FROM oauth_sessions WHERE client_id = :clientId AND owner_type = :type AND owner_id = :typeId
	 * </code>
	 *
	 * @param  string $clientId  The client ID
	 * @param  string $ownerType The type of the session owner (e.g. "user")
	 * @param  string $ownerId   The ID of the session owner (e.g. "123")
	 * @return void
	 */
	public function deleteSession($clientId, $ownerType, $ownerId)
	{
		$data = array(
			'client_id'  => $clientId,
			'owner_type' => $ownerType,
			'owner_id'   => $ownerId,
			);
		$this->delete('oauth_sessions', $data);
	}

	/**
	 * Associate a redirect URI with a session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO oauth_session_redirects (session_id, redirect_uri) VALUE (:sessionId, :redirectUri)
	 * </code>
	 *
	 * @param  int    $sessionId   The session ID
	 * @param  string $redirectUri The redirect URI
	 * @return void
	 */
	public function associateRedirectUri($sessionId, $redirectUri)
	{
		$data = array(
			'session_id'   => $sessionId,
			'redirect_uri' => $redirectUri,
			);
		$this->insert('oauth_session_redirects', $data);
	}

	/**
	 * Associate an access token with a session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO oauth_session_access_tokens (session_id, access_token, access_token_expires)
	 *  VALUE (:sessionId, :accessToken, :accessTokenExpire)
	 * </code>
	 *
	 * @param  int    $sessionId   The session ID
	 * @param  string $accessToken The access token
	 * @param  int    $expireTime  Unix timestamp of the access token expiry time
	 * @return int                 The access token ID
	 */
	public function associateAccessToken($sessionId, $accessToken, $expireTime)
	{
		$data = array(
			'session_id'           => $sessionId,
			'access_token'         => $accessToken,
			'access_token_expires' => $expireTime,
			);
		return $this->insert('oauth_session_access_tokens', $data);
	}

	/**
	 * Associate a refresh token with a session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO oauth_session_refresh_tokens (session_access_token_id, refresh_token, refresh_token_expires,
	 *  client_id) VALUE (:accessTokenId, :refreshToken, :expireTime, :clientId)
	 * </code>
	 *
	 * @param  int    $accessTokenId The access token ID
	 * @param  string $refreshToken  The refresh token
	 * @param  int    $expireTime    Unix timestamp of the refresh token expiry time
	 * @param  string $clientId      The client ID
	 * @return void
	 */
	public function associateRefreshToken($accessTokenId, $refreshToken, $expireTime, $clientId)
	{
		$data = array(
			'session_access_token_id' => $accessTokenId,
			'refresh_token'           => $refreshToken,
			'refresh_token_expires'   => $expireTime,
			'client_id'               => $clientId,
			);
		$this->insert('oauth_session_refresh_tokens', $data);
	}

	/**
	 * Assocate an authorization code with a session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO oauth_session_authcodes (session_id, auth_code, auth_code_expires)
	 *  VALUE (:sessionId, :authCode, :authCodeExpires)
	 * </code>
	 *
	 * @param  int    $sessionId  The session ID
	 * @param  string $authCode   The authorization code
	 * @param  int    $expireTime Unix timestamp of the access token expiry time
	 * @return int                The auth code ID
	 */
	public function associateAuthCode($sessionId, $authCode, $expireTime)
	{
		$data = array(
			'session_id'        => $sessionId,
			'auth_code'         => $authCode,
			'auth_code_expires' => $expireTime,
			);
		return $this->insert('oauth_session_authcodes', $data);
	}

	/**
	 * Remove an associated authorization token from a session
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * DELETE FROM oauth_session_authcodes WHERE session_id = :sessionId
	 * </code>
	 *
	 * @param  int    $sessionId   The session ID
	 * @return void
	 */
	public function removeAuthCode($sessionId)
	{
		$where = array(
			'session_id' => $sessionId,
			);
		return $this->delete('oauth_session_authcodes', $where);
	}

	/**
	 * Validate an authorization code
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT oauth_sessions.id AS session_id, oauth_session_authcodes.id AS authcode_id FROM oauth_sessions
	 *  JOIN oauth_session_authcodes ON oauth_session_authcodes.`session_id` = oauth_sessions.id
	 *  JOIN oauth_session_redirects ON oauth_session_redirects.`session_id` = oauth_sessions.id WHERE
	 * oauth_sessions.client_id = :clientId AND oauth_session_authcodes.`auth_code` = :authCode
	 *  AND `oauth_session_authcodes`.`auth_code_expires` >= :time AND
	 *  `oauth_session_redirects`.`redirect_uri` = :redirectUri
	 * </code>
	 *
	 * Expected response:
	 *
	 * <code>
	 * array(
	 *     'session_id' =>  (int)
	 *     'authcode_id'  =>  (int)
	 * )
	 * </code>
	 *
	 * @param  string     $clientId    The client ID
	 * @param  string     $redirectUri The redirect URI
	 * @param  string     $authCode    The authorization code
	 * @return array|bool              False if invalid or array as above
	 */
	public function validateAuthCode($clientId, $redirectUri, $authCode)
	{
		$query = DB::query(Database::SELECT, '
		SELECT oauth_sessions.id AS session_id, oauth_session_authcodes.id AS authcode_id FROM oauth_sessions
		  JOIN oauth_session_authcodes ON oauth_session_authcodes.session_id = oauth_sessions.id
		  JOIN oauth_session_redirects ON oauth_session_redirects.session_id = oauth_sessions.id
		 WHERE oauth_sessions.client_id = :clientId
		   AND oauth_session_authcodes.auth_code = :authCode
		   AND oauth_session_authcodes.auth_code_expires >= :time
		   AND oauth_session_redirects.redirect_uri = :redirectUri')
			->param(':clientId', $clientId)
			->param(':redirectUri', $redirectUri)
			->param(':authCode', $authCode)
			->param(':time', time());
		return $this->select_one_result($query);
	}

	/**
	 * Validate an access token
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT session_id, oauth_sessions.`client_id`, oauth_sessions.`owner_id`, oauth_sessions.`owner_type`
	 *  FROM `oauth_session_access_tokens` JOIN oauth_sessions ON oauth_sessions.`id` = session_id WHERE
	 *  access_token = :accessToken AND access_token_expires >= UNIX_TIMESTAMP(NOW())
	 * </code>
	 *
	 * Expected response:
	 *
	 * <code>
	 * array(
	 *     'session_id' =>  (int),
	 *     'client_id'  =>  (string),
	 *     'owner_id'   =>  (string),
	 *     'owner_type' =>  (string)
	 * )
	 * </code>
	 *
	 * @param  string     $accessToken The access token
	 * @return array|bool              False if invalid or an array as above
	 */
	public function validateAccessToken($accessToken)
	{
		$query = DB::query(Database::SELECT, '
		SELECT oauth_session_access_tokens.session_id, oauth_sessions.client_id, oauth_sessions.owner_id, oauth_sessions.owner_type
		  FROM oauth_session_access_tokens
		  JOIN oauth_sessions ON oauth_sessions.id = session_id
		 WHERE access_token = :accessToken
		   AND access_token_expires >= :time')
			->param(':accessToken', $accessToken)
			->param(':time', time());
		return $this->select_one_result($query);
	}

	/**
	 * Removes a refresh token
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * DELETE FROM `oauth_session_refresh_tokens` WHERE refresh_token = :refreshToken
	 * </code>
	 *
	 * @param  string $refreshToken The refresh token to be removed
	 * @return void
	 */
	public function removeRefreshToken($refreshToken)
	{
		$where = array(
			'refresh_token' => $refreshToken,
			);
		$this->delete('oauth_session_refresh_tokens', $where);
	}

	/**
	 * Validate a refresh token
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT session_access_token_id FROM `oauth_session_refresh_tokens` WHERE refresh_token = :refreshToken
	 *  AND refresh_token_expires >= UNIX_TIMESTAMP(NOW()) AND client_id = :clientId
	 * </code>
	 *
	 * @param  string   $refreshToken The refresh token
	 * @param  string   $clientId     The client ID
	 * @return int|bool               The ID of the access token the refresh token is linked to (or false if invalid)
	 */
	public function validateRefreshToken($refreshToken, $clientId)
	{
		$query = DB::query(Database::SELECT, '
		SELECT session_access_token_id
		  FROM oauth_session_refresh_tokens
		 WHERE refresh_token = :refreshToken
		   AND client_id = :clientId
		   AND refresh_token_expires >= :time')
			->param(':refreshToken', $refreshToken)
			->param(':clientId', $clientId)
			->param(':time', time());
		return $this->select_one_column($query, 'session_access_token_id');
	}

	/**
	 * Get an access token by ID
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT * FROM `oauth_session_access_tokens` WHERE `id` = :accessTokenId
	 * </code>
	 *
	 * Expected response:
	 *
	 * <code>
	 * array(
	 *     'id' =>  (int),
	 *     'session_id' =>  (int),
	 *     'access_token'   =>  (string),
	 *     'access_token_expires'   =>  (int)
	 * )
	 * </code>
	 *
	 * @param  int    $accessTokenId The access token ID
	 * @return array
	 */
	public function getAccessToken($accessTokenId)
	{
		$where = array(
			'id' => $accessTokenId,
			);
		$query = $this->select('oauth_session_access_tokens', $where);
		return $this->select_one_result($query) ?: array();
	}

	/**
	 * Associate scopes with an auth code (bound to the session)
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO `oauth_session_authcode_scopes` (`oauth_session_authcode_id`, `scope_id`) VALUES
	 *  (:authCodeId, :scopeId)
	 * </code>
	 *
	 * @param  int $authCodeId The auth code ID
	 * @param  int $scopeId    The scope ID
	 * @return void
	 */
	public function associateAuthCodeScope($authCodeId, $scopeId)
	{
		$data = array(
			'oauth_session_authcode_id' => $authCodeId,
			'scope_id'                  => $scopeId,
			);
		$this->insert('oauth_session_authcode_scopes', $data);
	}

	/**
	 * Get the scopes associated with an auth code
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT scope_id FROM `oauth_session_authcode_scopes` WHERE oauth_session_authcode_id = :authCodeId
	 * </code>
	 *
	 * Expected response:
	 *
	 * <code>
	 * array(
	 *     array(
	 *         'scope_id' => (int)
	 *     ),
	 *     array(
	 *         'scope_id' => (int)
	 *     ),
	 *     ...
	 * )
	 * </code>
	 *
	 * @param  int   $oauthSessionAuthCodeId The session ID
	 * @return array
	 */
	public function getAuthCodeScopes($oauthSessionAuthCodeId)
	{
		$where = array(
			'oauth_session_authcode_id' => $oauthSessionAuthCodeId,
			);
		$query = $this->select('oauth_session_authcode_scopes', $where)->select('scope_id');
		return $this->select_results($query) ?: array();
	}

	/**
	 * Associate a scope with an access token
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * INSERT INTO `oauth_session_token_scopes` (`session_access_token_id`, `scope_id`) VALUE (:accessTokenId, :scopeId)
	 * </code>
	 *
	 * @param  int    $accessTokenId The ID of the access token
	 * @param  int    $scopeId       The ID of the scope
	 * @return void
	 */
	public function associateScope($accessTokenId, $scopeId)
	{
		$data = array(
			'session_access_token_id' => $accessTokenId,
			'scope_id'                => $scopeId,
			);
		$this->insert('oauth_session_token_scopes', $data);
	}

	/**
	 * Get all associated access tokens for an access token
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT oauth_scopes.* FROM oauth_session_token_scopes JOIN oauth_session_access_tokens
	 *  ON oauth_session_access_tokens.`id` = `oauth_session_token_scopes`.`session_access_token_id`
	 *  JOIN oauth_scopes ON oauth_scopes.id = `oauth_session_token_scopes`.`scope_id`
	 *  WHERE access_token = :accessToken
	 * </code>
	 *
	 * Expected response:
	 *
	 * <code>
	 * array (
	 *     array(
	 *         'id'     =>  (int),
	 *         'scope'  =>  (string),
	 *         'name'   =>  (string),
	 *         'description'    =>  (string)
	 *     ),
	 *     ...
	 *     ...
	 * )
	 * </code>
	 *
	 * @param  string $accessToken The access token
	 * @return array
	 */
	public function getScopes($accessToken)
	{
		$query = DB::query(Database::SELECT, '
		SELECT oauth_scopes.*
		  FROM oauth_session_token_scopes
		  JOIN oauth_session_access_tokens
		    ON oauth_session_access_tokens.id = oauth_session_token_scopes.session_access_token_id
		  JOIN oauth_scopes
		    ON oauth_scopes.id = oauth_session_token_scopes.scope_id
		 WHERE access_token = :accessToken')
			->param(':accessToken', $accessToken);
		return $this->select_results($query) ?: array();
	}
}
