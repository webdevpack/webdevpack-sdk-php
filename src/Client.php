<?php

/*
 * WEBDEVPACK SDK for PHP
 * https://github.com/webdevpack/webdevpack-sdk-php
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace WebDevPack;

/**
 * WEBDEVPACK SDK for PHP
 */
class Client
{

    /**
     * 
     * @var array
     */
    private array $options = [];

    /**
     * 
     * @param array $options Available values: apiKey
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * 
     * @param string $path
     * @param array $data
     * @param string $method
     * @param boolean $isJSON
     * @return array
     */
    private function sendRequest(string $path, array $data, string $method = 'POST', bool $isJSON = true): array
    {
        $url = 'https://api.webdevpack.com' . $path;
        $headers = [];
        if (isset($this->options['apiKey'])) {
            $headers[] = 'WDP-API-Key:' . $this->options['apiKey'];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $isJSON ? json_encode($data) : $data);
        }
        if ($isJSON) {
            $headers[] = 'Content-Type:application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        if ($error !== '') {
            throw new \Exception('Еrror: ' . $error);
        }
        if ($method === 'GET') {
            return ['result' => $body];
        }
        $data = json_decode($body, true);
        if (is_array($data) && isset($data['status'])) {
            if ($data['status'] === 'ok') {
                return $data;
            } else if ($data['status'] === 'error') {
                $codeParts = explode(':', $data['code']);
                if ($codeParts[0] === 'missingArgument') {
                    throw new \Exception('Missing argument: ' . $codeParts[1]);
                }
                if ($codeParts[0] === 'invalidArgument') {
                    throw new \Exception('Invalid argument: ' . $codeParts[1]);
                }
                if (isset($data['message'])) {
                    throw new \Exception('Error: ' . $data['message']);
                }
            }
        }
        throw new \Exception('Unknown error: ' . $body . "\n" . $header);
    }

    /**
     * 
     * @param string $filename
     * @return string
     */
    private function uploadFile(string $filename): string
    {
        $response = $this->sendRequest('/v0/upload', ['file' => curl_file_create($filename)], 'POST', false);
        return $response['file'];
    }

    /**
     * 
     * @param string $fileID
     * @param string $targetFilename
     * @return void
     */
    private function downloadFile(string $fileID, string $targetFilename): void
    {
        $response = $this->sendRequest('/v0/download/' . $fileID, [], 'GET', false);
        if (strlen($response['result']) > 0) {
            $this->makeDir(pathinfo($targetFilename, PATHINFO_DIRNAME));
            file_put_contents($targetFilename, $response['result']);
        } else {
            throw new \Exception('Download error: file empty');
        }
    }

    /**
     * 
     * @param string $dir
     * @return void
     */
    private function makeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    /**
     * 
     * @param string $sourceFilename
     * @return void
     */
    private function checkSourceFilename(string $sourceFilename): void
    {
        if (!is_file($sourceFilename)) {
            throw new \Exception('The source file (' . $sourceFilename . ') does not exists!');
        }
        if (!is_readable($sourceFilename)) {
            throw new \Exception('The source file (' . $sourceFilename . ') is not readable!');
        }
    }

    /**
     * 
     * @param string $targetFilename
     * @return void
     */
    private function checkTargetFilename(string $targetFilename): void
    {
        if (is_file($targetFilename)) {
            if (!is_writable($targetFilename)) {
                throw new \Exception('The target file (' . $targetFilename . ') is not writable!');
            }
        } else {
            if (!is_writable(pathinfo($targetFilename, PATHINFO_DIRNAME))) {
                throw new \Exception('The target file (' . $targetFilename . ') is not writable!');
            }
        }
    }

    /**
     * 
     * @param string $text
     * @param string $transform Available values: uppercase and lowercase.
     * @return string
     */
    public function transformText(string $text, string $transform): string
    {
        $response = $this->sendRequest('/v0/text-transform', ['text' => $text, 'transform' => $transform]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $text
     * @param string $transform
     * @return string
     */
    private function base64EncodeDecode(string $text, string $transform): string
    {
        $response = $this->sendRequest('/v0/base64-encode-decode', ['text' => $text, 'transform' => $transform]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public function base64Encode(string $text): string
    {
        return $this->base64EncodeDecode($text, 'encode');
    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public function base64Decode(string $text): string
    {
        return $this->base64EncodeDecode($text, 'decode');
    }

    /**
     * 
     * @param string $text
     * @param string $algorithm Available values: md5, crc32, sha-1, sha-256, sha-512, sha3-256 and sha3-512
     * @return string
     */
    public function hashText(string $text, string $algorithm): string
    {
        $response = $this->sendRequest('/v0/text-hash', ['text' => $text, 'algorithm' => $algorithm]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $url
     * @param string $transform
     * @return string
     */
    private function encodeDecodeURL(string $url, string $transform): string
    {
        $response = $this->sendRequest('/v0/url-encode-decode', ['text' => $url, 'transform' => $transform]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $url
     * @return string
     */
    public function encodeURL(string $url): string
    {
        return $this->encodeDecodeURL($url, 'encode');
    }

    /**
     * 
     * @param string $url
     * @return string
     */
    public function decodeURL(string $url): string
    {
        return $this->encodeDecodeURL($url, 'decode');
    }

    /**
     * 
     * @param string $domain
     * @return string
     */
    public function domainWhois(string $domain): string
    {
        $response = $this->sendRequest('/v0/domain-whois', ['domain' => $domain]);
        return $response['result']['raw'];
    }

    /**
     * 
     * @param string $sourceFilename
     * @param string $targetFilename
     * @param integer $quality
     * @return void
     */
    public function optimizeImage(string $sourceFilename, string $targetFilename, int $quality = 100): void
    {
        $this->checkSourceFilename($sourceFilename);
        $this->checkTargetFilename($targetFilename);
        $fileID = $this->uploadFile($sourceFilename);
        $response = $this->sendRequest('/v0/image-optimize', ['file' => $fileID, 'quality' => $quality]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }

    /**
     * 
     * @param string $sourceFilename
     * @param string $targetFilename
     * @param string $format
     * @param integer $quality
     * @return void
     */
    public function convertImage(string $sourceFilename, string $targetFilename, string $format, int $quality = 100): void
    {
        $this->checkSourceFilename($sourceFilename);
        $this->checkTargetFilename($targetFilename);
        $fileID = $this->uploadFile($sourceFilename);
        $response = $this->sendRequest('/v0/image-convert', ['file' => $fileID, 'format' => $format, 'quality' => $quality]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }

    /**
     * 
     * @param string $sourceFilename
     * @param string $language Available values: afr, ara, aze, bel, ben, bul, cat, ces, chi_sim, chi_tra, dan, deu, eng, epo, est, eus, fin, fra, glg, grc, heb, hin, hrv, hun, ind, isl, ita, jpn, kan, kat, khm, kor, lav, lit, mal, mkd, mlt, msa, nld, nor, pol, por, ron, rus, slk, slv, spa, sqi, srp, swa, swe, tam, tel, tgl, tha, tur, ukr, vie
     * @return string
     */
    public function getTextFromImage(string $sourceFilename, string $language = 'eng'): string
    {
        $this->checkSourceFilename($sourceFilename);
        $fileID = $this->uploadFile($sourceFilename);
        $response = $this->sendRequest('/v0/text-from-image', ['file' => $fileID, 'language' => $language]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $text
     * @param string $targetFilename
     * @param integer $size
     * @param string $format
     * @return void
     */
    public function generateQRCode(string $text, string $targetFilename, int $size, string $format): void
    {
        $this->checkTargetFilename($targetFilename);
        $response = $this->sendRequest('/v0/qrcode', ['text' => $text, 'size' => $size, 'format' => $format]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }

    /**
     * 
     * @param string $text
     * @param string $targetFilename
     * @param integer $width
     * @param integer $height
     * @param string $format
     * @return void
     */
    public function generateBarcode(string $text, string $targetFilename, int $width, int $height, string $format): void
    {
        $this->checkTargetFilename($targetFilename);
        $response = $this->sendRequest('/v0/barcode', ['text' => $text, 'width' => $width, 'height' => $height, 'format' => $format]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }

    /**
     * 
     * @param string $code
     * @return string
     */
    public function minifyJavaScript(string $code): string
    {
        $response = $this->sendRequest('/v0/js-minify-text', ['text' => $code]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $sourceFilename
     * @param string $targetFilename
     * @return void
     */
    public function minifyJavaScriptFile(string $sourceFilename, string $targetFilename): void
    {
        $this->checkSourceFilename($sourceFilename);
        $this->checkTargetFilename($targetFilename);
        $fileID = $this->uploadFile($sourceFilename);
        $response = $this->sendRequest('/v0/js-minify-file', ['file' => $fileID]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }

    /**
     * 
     * @param string $code
     * @return string
     */
    public function minifyCSS(string $code): string
    {
        $response = $this->sendRequest('/v0/css-minify-text', ['text' => $code]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $sourceFilename
     * @param string $targetFilename
     * @return void
     */
    public function minifyCSSFile(string $sourceFilename, string $targetFilename): void
    {
        $this->checkSourceFilename($sourceFilename);
        $this->checkTargetFilename($targetFilename);
        $fileID = $this->uploadFile($sourceFilename);
        $response = $this->sendRequest('/v0/css-minify-file', ['file' => $fileID]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }


    /**
     * 
     * @param string $text
     * @param string $transform
     * @return string
     */
    private function encodeDecodeJSON(string $text, string $transform): string
    {
        $response = $this->sendRequest('/v0/json-encode-decode', ['text' => $text, 'transform' => $transform]);
        return $response['result']['text'];
    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public function encodeJSON(string $text): string
    {
        return $this->encodeDecodeJSON($text, 'encode');
    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public function decodeJSON(string $text): string
    {
        return $this->encodeDecodeJSON($text, 'decode');
    }

    /**
     * 
     * @param int $length
     * @param boolean $includeUpperCase
     * @param boolean $includeSymbols
     * @param boolean $includeNumbers
     * @return string
     */
    public function generatePassword(int $length, bool $includeUpperCase, bool $includeSymbols, bool $includeNumbers): string
    {
        $response = $this->sendRequest('/v0/password', ['length' => $length, 'uppercase' => $includeUpperCase, 'symbols' => $includeSymbols, 'numbers' => $includeNumbers]);
        return $response['result']['password'];
    }

    /**
     * 
     * @param integer $bits
     * @return array
     */
    public function generateKeyPair(int $bits): array
    {
        $response = $this->sendRequest('/v0/keypair', ['bits' => $bits]);
        return [
            'privateKey' => $response['result']['privateKey'],
            'publicKey' => $response['result']['publicKey']
        ];
    }

    /**
     * 
     * @param string $code
     * @param string $targetFilename
     * @return void
     */
    public function convertHTMLToPDF(string $code, string $targetFilename): void
    {
        $this->checkTargetFilename($targetFilename);
        $response = $this->sendRequest('/v0/html-to-pdf', ['text' => $code]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }

    /**
     * 
     * @param string $sourceFilename
     * @param string $targetFilename
     * @return void
     */
    public function convertHTMLFileToPDF(string $sourceFilename, string $targetFilename): void
    {
        $this->checkSourceFilename($sourceFilename);
        $this->checkTargetFilename($targetFilename);
        $fileID = $this->uploadFile($sourceFilename);
        $response = $this->sendRequest('/v0/html-file-to-pdf', ['file' => $fileID]);
        $this->downloadFile($response['result']['file'], $targetFilename);
    }
}
