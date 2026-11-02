<?php
/**
 * Manages different kinds of tokens in the database.
 * 
 * E.g. Auth can use this to generate & verify a one-time token to achieve
 * things like email address verification or password resets.
 */
class TokenRepository {
    private $db;
    private $config;

    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
    }

    private function generate_token($token_type) {
        return match ($token_type) {
            TokenType::EMAIL_VERIFICATION   => UUID::v7(),
            TokenType::PASSWORD_RESET       => UUID::v7(),
        };
    }

    private function hash_token($token) {
        $hash_algo = $this->config['hash_algo'];
        $pepper = $this->config['pepper'];
        return hash_hmac($hash_algo, $token, $pepper);
    }

    private function get_token_lifetime($token_type) {
        return match ($token_type) {
            TokenType::EMAIL_VERIFICATION   => new DateInterval('P1D'),
            TokenType::PASSWORD_RESET       => new DateInterval('PT1H'),
        };
    }

    private function get_recent_duration($token_type) {
        return match ($token_type) {
            TokenType::EMAIL_VERIFICATION   => new DateInterval('PT5M'),
            TokenType::PASSWORD_RESET       => new DateInterval('PT5M'),
        };
    }

    public function create_token($user_uuid, $token_type) {
        $token = $this->generate_token($token_type);
        $now = new DateTimeImmutable('now');
        $row = [
            'user_uuid' => $user_uuid,
            'token_type' => $token_type->value,
            'token_hash' => $this->hash_token($token),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->add($this->get_token_lifetime($token_type))
                                ->format('Y-m-d H:i:s'),
        ];
        $sql = 'DELETE FROM verification_tokens
                WHERE user_uuid = :user_uuid AND token_type = :token_type';
        $this->db->execute($sql, [
            'user_uuid' => $row['user_uuid'],
            'token_type' => $row['token_type'],
        ]);
        $this->db->insert_row('verification_tokens', $row);
        return $token;
    }

    public function has_recent_token($user_uuid, $token_type) {
        $now = new DateTimeImmutable('now');
        $data = [
            'user_uuid' => $user_uuid,
            'token_type' => $token_type->value,
            'threshold' => $now->sub($this->get_recent_duration($token_type))
                               ->format('Y-m-d H:i:s'),
        ];
        $sql = 'SELECT COUNT(*) FROM verification_tokens
                WHERE user_uuid = :user_uuid
                  AND token_type = :token_type
                  AND created_at > :threshold';
        return $this->db->value($sql, $data) == 1;
    }

    public function use_token($user_uuid, $token_type, $token) {
        $data = [
            'user_uuid' => $user_uuid,
            'token_type' => $token_type->value,
            'token_hash' => $this->hash_token($token),
            'now' => (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
        ];
        $sql = 'DELETE FROM verification_tokens
                WHERE user_uuid = :user_uuid
                  AND token_type = :token_type
                  AND token_hash = :token_hash
                  AND expires_at > :now';
        return $this->db->execute($sql, $data) == 1;
    }
}

enum TokenType: string {
    case EMAIL_VERIFICATION = 'email_verification';
    case PASSWORD_RESET     = 'password_reset';
}
?>
