<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class AzureBatchService
{
    protected $baseURL = 'https://hlsencoder.eastus.batch.azure.com';
    protected $secret;
    protected $tenant;
    protected $clientId;

    public function __construct()
    {
        $this->secret   = config('services.azure.client_secret');
        $this->tenant   = config('services.azure.tenant_id');
        $this->clientId = config('services.azure.client_id');
    }

    /**
     * Test authentication via Microsoft Entra ID to get an access token.
     *
     * @return string Access token if successful.
     * @throws Exception if the request fails or no token is returned.
     */
    public function testAuthentication()
    {
        $url = "https://login.microsoftonline.com/{$this->tenant}/oauth2/token";

        $params = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->secret,
            'resource'      => 'https://batch.core.windows.net/'
        ];
        $response = Http::asForm()->post($url, $params);

        // Check if the response is successful.
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token'])) {
                // Return the access token.
                return $data['access_token'];
            } else {
                throw new Exception('Access token not found in response.');
            }
        } else {
            throw new Exception('Authentication failed with status: ' . $response->status());
        }
    }

    /**
     * Add a Task to a Job with resource files from an Azure Storage container.
     *
     * @param string $jobId The ID of the job to which the task will be added.
     * @param string $taskId A unique identifier for the new task.
     * @param string $commandLine The command line to execute for the task.
     * @param array  $resourceFiles An array of resource file definitions. Each resource file
     *                              should include at least the 'filePath' and 'httpUrl' keys.
     *                              For example:
     *                              [
     *                                  [
     *                                      'filePath' => 'input.txt',
     *                                      'httpUrl'  => 'https://{storage_account}.blob.core.windows.net/{container}/input.txt?{SAS_token}'
     *                                  ]
     *                              ]
     *
     * @return array The JSON response from the Azure Batch service.
     *
     * @throws Exception if the request fails.
     */
    public function addTask(string $jobId, string $taskId, string $commandLine, array $resourceFiles = [])
    {
        // Retrieve the access token using your authentication method.
        $token = $this->testAuthentication();

        // Build the full URL for the Add Task endpoint.
        $url = $this->baseURL . "/jobs/{$jobId}/tasks?api-version=2024-07-01.20.0";

        // Prepare the payload. You can expand this array to include other optional properties.
        $payload = [
            'id'            => $taskId,
            'commandLine'   => $commandLine,
            'constraints'   => [
                'retentionTime' => 'PT5M'
            ],
            'resourceFiles' => $resourceFiles,
        ];

        // Make the POST request with the authorization token and required headers.
        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json; odata=minimalmetadata',
            ])
            ->post($url, $payload);

        // Check if the task was added successfully (HTTP 201 Created)
        if ($response->status() === 201) {
            return $response->json();
        } else {
            throw new Exception('Failed to add task. Status: ' . $response->status() . ' Response: ' . $response->body());
        }
    }

    public function checkTaskStatus(string $jobId, string $taskId)
    {
        $token = $this->testAuthentication();
        $url = $this->baseURL . "/jobs/{$jobId}/tasks/{$taskId}?api-version=2024-07-01.20.0";
        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json; odata=minimalmetadata',
            ])
            ->get($url);
        if ($response->successful()) {
            return $response->json();
        } else {
            throw new Exception('Failed to check task status. Status: ' . $response->status() . ' Response: ' . $response->body());
        }
    }
}
