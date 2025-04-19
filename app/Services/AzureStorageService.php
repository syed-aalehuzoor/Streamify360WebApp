<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class AzureStorageService
{
    protected $baseURL;
    protected $storage_key;
    protected $storage_account;
    protected $container;

    public function __construct()
    {
        $this->storage_key     = config('services.azure.storage_key');
        $this->storage_account = config('services.azure.storage_name');
        $this->container       = config('services.azure.storage_container');
        $this->baseURL         = 'https://' . $this->storage_account . '.blob.core.windows.net/' . $this->container;
    }

    /**
     * Build an authenticated HTTP client using Shared Key authentication.
     *
     * @param string $method         The HTTP method (e.g. "GET", "PUT")
     * @param string $resourcePath   The resource path (e.g. "/myBlob.txt")
     * @param array  $queryParams    Optional query parameters to include in the signature.
     * @param array  $options        Optional signature options (e.g. Content-Type, Content-Length, etc.).
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function buildAuthenticatedRequest(string $method, string $resourcePath = '', array $queryParams = [], array $options = [])
    {
        // Set signature components – default to empty strings if not provided.
        $contentEncoding   = $options['Content-Encoding'] ?? '';
        $contentLanguage   = $options['Content-Language'] ?? '';
        $contentLength     = isset($options['Content-Length']) ? (string)$options['Content-Length'] : '';
        $contentMD5        = $options['Content-MD5'] ?? '';
        $contentType       = $options['Content-Type'] ?? '';
        $ifModifiedSince   = $options['If-Modified-Since'] ?? '';
        $ifMatch           = $options['If-Match'] ?? '';
        $ifNoneMatch       = $options['If-None-Match'] ?? '';
        $ifUnmodifiedSince = $options['If-Unmodified-Since'] ?? '';
        $rangeHeader       = $options['Range'] ?? '';

        // Set x-ms headers.
        $date    = gmdate('D, d M Y H:i:s') . ' GMT';
        $version = '2020-10-02';
        $xMsHeaders = [
            'x-ms-date'    => $date,
            'x-ms-version' => $version,
        ];

        // Merge any additional x-ms- headers passed in options.
        foreach ($options as $key => $value) {
            if (stripos($key, 'x-ms-') === 0) {
                $xMsHeaders[strtolower($key)] = $value;
            }
        }

        // Build the canonicalized headers string.
        ksort($xMsHeaders);
        $canonicalizedHeaders = '';
        foreach ($xMsHeaders as $key => $value) {
            $canonicalizedHeaders .= $key . ':' . $value . "\n";
        }

        // Build the canonicalized resource string.
        // Format: "/{storage_account}/{container}{resourcePath}"
        $canonicalizedResource = '/' . $this->storage_account . '/' . $this->container . $resourcePath;
        if (!empty($queryParams)) {
            ksort($queryParams);
            foreach ($queryParams as $param => $val) {
                $canonicalizedResource .= "\n" . strtolower($param) . ':' . $val;
            }
        }

        // Construct the string to sign.
        // Note: The Date header is empty since we are using x-ms-date.
        $stringToSign = $method . "\n" .
                        $contentEncoding . "\n" .
                        $contentLanguage . "\n" .
                        $contentLength . "\n" .
                        $contentMD5 . "\n" .
                        $contentType . "\n" .
                        "\n" . // Date is empty (using x-ms-date)
                        $ifModifiedSince . "\n" .
                        $ifMatch . "\n" .
                        $ifNoneMatch . "\n" .
                        $ifUnmodifiedSince . "\n" .
                        $rangeHeader . "\n" .
                        $canonicalizedHeaders .
                        $canonicalizedResource;

        // Compute the signature.
        $decodedKey = base64_decode($this->storage_key);
        $signature  = base64_encode(hash_hmac('sha256', $stringToSign, $decodedKey, true));

        // Build the Authorization header.
        $authorizationHeader = "SharedKey " . $this->storage_account . ":" . $signature;

        // Merge the x-ms headers with the Authorization header.
        $finalHeaders = array_merge($xMsHeaders, [
            'Authorization' => $authorizationHeader,
        ]);

        // Return an HTTP client with these headers preset.
        return Http::withHeaders($finalHeaders);
    }

    /**
     * Upload a blob to Azure Storage.
     *
     * @param string $blobName     The name of the blob.
     * @param string $content      The content of the blob.
     * @param string $contentType  The MIME type of the blob.
     *
     * @return \Illuminate\Http\Client\Response
     * @throws Exception
     */
    public function uploadBlob(string $blobName, string $content, string $contentType = 'application/octet-stream')
    {
        // Build the full URL to the blob.
        $url = $this->baseURL . '/' . $blobName;

        // Prepare options that affect the signature.
        $options = [
            'Content-Length'  => strlen($content),
            'Content-Type'    => $contentType,
            // Include the required blob type header for signature purposes.
            'x-ms-blob-type'  => 'BlockBlob',
        ];

        // Build the authenticated HTTP client for a PUT operation.
        // For a blob upload, the resource path is "/" followed by the blob name.
        $client = $this->buildAuthenticatedRequest('PUT', '/' . $blobName, [], $options);

        // Prepare headers for the request.
        $headers = [
            'Content-Length' => strlen($content),
            'Content-Type'   => $contentType,
        ];

        // Send the PUT request with the blob content as the body.
        $response = $client->withHeaders($headers)
                           ->withBody($content, $contentType)
                           ->put($url);

        // Check if the upload was successful (HTTP 201 Created).
        if ($response->status() === 201) {
            return $response;
        }

        // Throw an exception if the upload failed.
        throw new Exception('Blob upload failed: ' . $response->body());
    }
}
