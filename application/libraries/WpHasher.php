
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WordPress-compatible password hasher for CI3 using PHPass.
 * Produces the same $P$... hashes as wp_hash_password().
 */
class WpHasher
{
    /** @var PasswordHash */
    protected $hasher;

    public function __construct()
    {
        // WordPress uses portable hashes + iteration count ~8
        require_once APPPATH . 'libraries/PasswordHash.php';
        $this->hasher = new PasswordHash(8, true);
    }

    /**
     * Mimics wp_hash_password(): returns $P$... phpass hash.
     */
    public function hash($plain)
    {
        return $this->hasher->HashPassword($plain);
    }

    /**
     * Mimics wp_check_password(): verifies plain against stored hash.
     */
    public function check($plain, $storedHash)
    {
        return $this->hasher->CheckPassword($plain, $storedHash);
    }
}