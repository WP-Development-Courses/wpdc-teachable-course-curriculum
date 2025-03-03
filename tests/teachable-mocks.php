<?php
/**
 * Mocks for the Teachable namespace.
 */

namespace Teachable;

/**
 * Mock for the Teachable\decrypt function.
 *
 * @param string $encrypted_string The string to decrypt.
 * @return string The decrypted string.
 */
function decrypt( string $encrypted_string ): string {
	return 'decrypted';
}
