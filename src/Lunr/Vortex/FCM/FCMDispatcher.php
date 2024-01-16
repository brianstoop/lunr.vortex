<?php

/**
 * This file contains functionality to dispatch Firebase Cloud Messaging Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Builder;
use Lunr\Vortex\PushNotificationDispatcherInterface;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * Firebase Cloud Messaging Push Notification Dispatcher.
 */
class FCMDispatcher implements PushNotificationDispatcherInterface
{
    /**
     * Push Notification Oauth token.
     * @var string
     */
    protected ?string $oauth_token;

    /**
     * FCM id of the project.
     * @var ?string
     */
    protected ?string $project_id;

    /**
     * Shared instance of the Requests\Session class.
     * @var Session
     */
    protected Session $http;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Url to send the FCM push notification to.
     * @var string
     */
    private const GOOGLE_SEND_URL = 'https://fcm.googleapis.com/v1/projects/';

    /**
     * Url to fetch the OAuth2 token.
     * @var string
     */
    private const GOOGLE_OAUTH_URL = 'https://oauth2.googleapis.com/token';

    /**
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        $this->http        = $http;
        $this->logger      = $logger;
        $this->oauth_token = NULL;
        $this->project_id  = NULL;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->oauth_token);
        unset($this->project_id);
        unset($this->http);
        unset($this->logger);
    }

    /**
     * Getter for FCMResponse.
     *
     * @param Response        $http_response Requests\Response object.
     * @param LoggerInterface $logger        Shared instance of a Logger.
     * @param string          $endpoint      The endpoint the message was sent to.
     * @param string          $payload       Raw payload that was sent to FCM.
     *
     * @return FCMResponse
     */
    public function get_response(Response $http_response, LoggerInterface $logger, string $endpoint, string $payload): FCMResponse
    {
        return new FCMResponse($http_response, $logger, $endpoint, $payload);
    }

    /**
     * Push the notification.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return FCMResponse Response object
     */
    public function push(object $payload, array &$endpoints): FCMResponse
    {
        if (!$payload instanceof FCMPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        if ($endpoints === [])
        {
            throw new InvalidArgumentException('No endpoints provided!');
        }

        if ($this->oauth_token === NULL)
        {
            $this->logger->warning('Tried to push FCM notification to {endpoint} but wasn\'t authenticated.', [ 'endpoint' => $endpoints[0] ]);

            return $this->get_response($this->get_new_response_object_for_failed_request(401), $this->logger, $endpoints[0], $payload->get_payload());
        }

        if ($this->project_id === NULL)
        {
            $this->logger->warning('Tried to push FCM notification to {endpoint} but project id is not provided.', [ 'endpoint' => $endpoints[0] ]);

            return $this->get_response($this->get_new_response_object_for_failed_request(400), $this->logger, $endpoints[0], $payload->get_payload());
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->oauth_token,
        ];

        $tmp_payload = json_decode($payload->get_payload(), TRUE);

        $tmp_payload['to'] = $endpoints[0];

        $json_payload = json_encode($tmp_payload, JSON_UNESCAPED_UNICODE);

        try
        {
            $options = [
                'timeout'         => 15, // timeout in seconds
                'connect_timeout' => 15 // timeout in seconds
            ];

            $http_response = $this->http->post(self::GOOGLE_SEND_URL . $this->project_id . '/messages:send', $headers, $json_payload, $options);
        }
        catch (RequestsException $e)
        {
            $this->logger->warning(
                'Dispatching FCM notification(s) failed: {message}',
                [ 'message' => $e->getMessage() ]
            );

            $http_response = $this->get_new_response_object_for_failed_request();

            if ($e->getType() == 'curlerror' && curl_errno($e->getData()) == 28)
            {
                $http_response->status_code = 500;
            }
        }

        return $this->get_response($http_response, $this->logger, $endpoints[0], $json_payload);
    }

    /**
     * Set the oauth token for the http headers.
     *
     * @param string $iss         The email address of the service account
     * @param string $private_key The private key of the service account
     *
     * @return FCMDispatcher Self reference
     */
    public function set_oauth_token(string $iss, string $private_key): self
    {
        $iat = new DateTimeImmutable();

        $token_builder = new Builder(new JoseEncoder(), ChainedFormatter::default());

        $token = $token_builder->issuedBy($iss)
                               ->permittedFor('https://oauth2.googleapis.com/token')
                               ->issuedAt($iat)
                               ->expiresAt($iat->modify('+1 hour'))
                               ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                               ->withHeader('alg', 'RS2256')
                               ->withHeader('typ', 'JWT')
                               ->getToken(new Sha256(), InMemory::plainText($private_key));

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $token->toString(),
        ];

        try
        {
            $options = [
                'timeout'         => 15, // timeout in seconds
                'connect_timeout' => 15 // timeout in seconds
            ];

            $http_response = $this->http->post(self::GOOGLE_OAUTH_URL, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), $options);
        }
        catch (RequestsException $e)
        {
            $this->logger->warning(
                'Fetching OAuth token for FCM notification(s) failed: {message}',
                [ 'message' => $e->getMessage() ]
            );

            return $this;
        }

        $response_body = json_decode($http_response->body, TRUE);

        if (json_last_error() !== 0)
        {
            $this->logger->warning(
                'Processing json response for fetching OAuth token for FCM notification(s) failed: {message}',
                [ 'message' => json_last_error_msg() ]
            );

            return $this;
        }

        if (!array_key_exists('access_token', $response_body))
        {
            $this->logger->warning(
                'Processing response for fetching OAuth token for FCM notification(s) failed: No access token in the response body'
            );

            return $this;
        }

        $this->oauth_token = $response_body['access_token'];

        return $this;
    }

    /**
     * Set the FCM project id for sending notifications.
     *
     * @param string $project_id The id of the FCM project
     *
     * @return FCMDispatcher Self reference
     */
    public function set_project_id(string $project_id): self
    {
        $this->project_id = $project_id;

        return $this;
    }

    /**
     * Get a Requests\Response object for a failed request.
     *
     * @param int $http_code Set http code for the request.
     *
     * @return Response New instance of a Requests\Response object.
     */
    protected function get_new_response_object_for_failed_request(int $http_code = NULL): Response
    {
        $http_response = new Response();

        $http_response->url = self::GOOGLE_SEND_URL . $this->project_id . '/messages:send';

        if ($http_code !== NULL)
        {
            $http_response->status_code = $http_code;
        }

        return $http_response;
    }

}

?>
