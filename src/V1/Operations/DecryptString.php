<?php

declare(strict_types=1);

/**
 * Copyright (c) 2017-present Ganbaro Digital Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   MessagingPipeline/Operations
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2017-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://ganbarodigital.github.io/php-mv-messaging-pipeline
 */

namespace GanbaroDigital\MessagingPipeline\V1\Operations;

use GanbaroDigital\MessagingPipeline\V1\Exceptions\CannotDecryptString;
use GanbaroDigital\MessagingPipeline\V1\Requirements\RequireValidEncryptionCipher;
use GanbaroDigital\MessagingPipeline\V1\Requirements\RequireValidEncryptionIV;

/**
 * decrypt a previously encrypted string
 */
class DecryptString
{
    /**
     * decrypt a previously encrypted string
     *
     * @param  string $item
     *         the string that you want to decrypt
     * @param  string $fieldOrVarName
     *         what do you call $item in your own code?
     * @param  string $encryptionType
     *         what kind of encryption cipher do you want to use?
     *         this must be supported by OpenSSL
     * @param  string $encryptionKey
     *         a password given to you by the person who encrypted the string
     * @param  string $iv
     *         the initialisation vector to used to decrypt the string
     * @return string
     *         the decrypted copy of $item
     */
    public static function from(string $item, string $fieldOrVarName, string $encryptionType, string $encryptionKey, string $iv)
    {
        // robustness!
        RequireValidEncryptionCipher::apply()->to($encryptionType, '$encryptionType');
        RequireValidEncryptionIV::apply($encryptionType)->to($iv, '$iv');

        // deal with decryption problems
        $errorMessage = null;
        set_error_handler(function ($errno, $errstr) use (&$errorMessage) {
            $errorMessage = $errstr;
        });
        $retval = openssl_decrypt(
            $item,
            $encryptionType,
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        restore_error_handler();

        // did we manage to decrypt it?
        if ($errorMessage || !$retval) {
            // something went wrong
            throw CannotDecryptString::newFromInputParameter($item, $fieldOrVarName, ['PHP_error' => $errorMessage]);
        }

        // if we get here, then all is well
        return $retval;
    }
}