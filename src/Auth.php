<?php
class Auth
{
    private int $user_id;
    public function __construct(private UserGateway $user_gateway, private JWTCodec $jWTCodec)
    {
    }
    public function authenticateKey(): bool
    {
        if (empty($_SERVER["HTTP_X_API_KEY"])) {
            http_response_code(400);
            echo json_encode(["message" => "API key is missing"]);
            return false;
        }

        $api_key = $_SERVER["HTTP_X_API_KEY"];

        $user = $this->user_gateway->getByApiKey($api_key);

        if ($user === false) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid Api key"]);
            return false;
        }

        $this->user_id = $user["id"];

        return true;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function authenticationAccessToken(): bool
    {
        if (!preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        try {
            $data = $this->jWTCodec->decode($matches[1]);
        } catch (InvalidArgumentException) {
            http_response_code(401);
            echo json_encode(["message" => "invalid signature"]);
            return false;
        } catch (TokenExpiredException) {
            http_response_code(401);
            echo json_encode(["message" => "token has expired"]);
            return false;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => $e->getMessage()]);
            return false;
        }

        // used for authentication key
        // $plain_text = base64_decode($matches[1], true);
        // if ($plain_text === false) {
        //     http_response_code(400);
        //     echo json_encode(["message" => "invalid authorization header"]);
        //     return false;
        // }

        // $data = json_decode($plain_text, true);

        // if ($data === null) {
        //     http_response_code(400);
        //     echo json_encode(["message" => "invalid json"]);
        //     return false;
        // }

        $this->user_id = $data["sub"];

        return true;
    }
}
