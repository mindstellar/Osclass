php-openssl-cryptor
===================

Description
-----------
Simple to use class for encrypting/decrypting using the PHP Openssl library. 

The Cryptor class supports arbitrary encryption and key hashing algorithms, along
with raw, base64 and hex encoding of the encrypted data. Static convenience methods
are provided for the default of AES, but a cryptor instance can be used for more
flexibility. The default uses aes-256-ctr to avoid the need for padding and the related
issues. Unfortunately GCM cannot be used as the PHP openssl module does not provide 
a way to retrieve the GCM tag. This is proposed to be remedied in PHP 7.1 when 
associated data can be retrieved.
