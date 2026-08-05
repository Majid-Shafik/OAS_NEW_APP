<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MinistryApiService
{
    /**
     * Fetch high school student data from the Ministry API.
     *
     * @param string|int $year
     * @param string|int $seatno
     * @param string|int|null $total
     * @return array|null Returns the student data array if found and valid, or null.
     */
    public function fetchStudentData($year, $seatno, $total = 0)
    {
        $secret = config('services.ministry_api.secret', env('MINISTRY_API_SECRET'));
        $apiUrl = config('services.ministry_api.url', env('MINISTRY_API_URL', 'https://portal.test.oasyemen.net/api/high-school-api'));
        $useLocalDb = config('services.ministry_api.use_local_db', env('USE_LOCAL_MINISTRY_DB', false));

        // Check if local fallback is explicitly forced
        if ($useLocalDb) {
            Log::info("MinistryApiService: USE_LOCAL_MINISTRY_DB is enabled. Fetching directly from local DB.");
            return $this->fetchFromLocalDB($year, $seatno, $total);
        }

        if (!$secret) {
            Log::error('MinistryApiService: MINISTRY_API_SECRET is missing in config / .env');
            return null;
        }

        // According to user's specification
        $data = [
            "year" => $year,
            "seat_number" => $seatno,
            "total" => $total, // If empty, we pass 0 or null depending on what $total received
            "timestamp" => time(),
            "nonce" => bin2hex(random_bytes(16))
        ];

        // The exact payload that is signed MUST be what is sent.
        $payload = json_encode($data);

        // Create signature
        $signature = hash_hmac('sha256', $payload, $secret);

        Log::info("MinistryApiService: Sending request to Ministry API", [
            'url' => $apiUrl,
            'year' => $year,
            'seat_number' => $seatno,
            'total' => $total,
        ]);

        $timeout = config('services.ministry_api.timeout', 20);
        $connectTimeout = config('services.ministry_api.connect_timeout', 10);
        $verifySsl = config('services.ministry_api.verify_ssl', true);

        try {
            $httpClient = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Signature' => $signature
            ])
            ->timeout($timeout)
            ->connectTimeout($connectTimeout);

            if (!$verifySsl) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient
                ->withBody($payload, 'application/json')
                ->post($apiUrl);

            Log::info("MinistryApiService: Received API response", [
                'status' => $response->status(),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Fallback to local DB if API returned empty
                if (empty($responseData) || (is_array($responseData) && count($responseData) === 0)) {
                    Log::info("MinistryApiService: API returned empty data, falling back to local DB.");
                    return $this->fetchFromLocalDB($year, $seatno, $total);
                }
                
                return $responseData;
            } elseif ($response->status() === 403 || $response->serverError()) {
                Log::warning("MinistryApiService: API returned status {$response->status()}, falling back to local DB.", [
                    'body' => $response->body()
                ]);
                return $this->fetchFromLocalDB($year, $seatno, $total);
            } else {
                Log::error("MinistryApiService: API request failed", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("MinistryApiService: Exception occurred during API request", [
                'message' => $e->getMessage()
            ]);
            // Fallback to local DB on connection exception
            return $this->fetchFromLocalDB($year, $seatno, $total);
        }

        return null;
    }

    /**
     * Fetch student data from local database as fallback
     */
    private function fetchFromLocalDB($year, $seatno, $total = 0)
    {
        try {
            $tableName = 'high_school_degrees_' . $year;
            
            $student = \Illuminate\Support\Facades\DB::connection('ministry_db')
                ->table($tableName)
                ->where('SEC_SCHOOL_SEATNO', $seatno)
                ->first();

            if ($student) {
                // Determine section ID from type
                $type = trim($student->SEC_SCHOOL_TYPE ?? '');
                $sectionId = 1; // Default
                if ($type === 'علمي') {
                    $sectionId = 1;
                } elseif ($type === 'أدبي' || $type === 'ادبي') {
                    $sectionId = 2;
                } elseif (str_contains($type, 'شرعي')) {
                    $sectionId = 3;
                }

                // Return in the same array format the API used to return
                return [
                    'seat_number' => $student->SEC_SCHOOL_SEATNO,
                    'year' => $year,
                    'total' => $student->SEC_SCHOOL_MARK,
                    'name' => $student->STUDENT_NAME,
                    'school_name' => $student->SEC_SCHOOL_NAME ?? '',
                    'rate' => $student->SEC_SCHOOL_RATE ?? '',
                    'type' => $type,
                    'section_id' => $sectionId,
                    'gender' => $student->GENDER ?? '',
                    'city_birth' => $student->PLACE_OF_BIRTH ?? '',
                    'city_study' => $student->SEC_SCHOOL_PLACE ?? '',
                    'nationality' => $student->COUNTRY_NAME ?? 'يمني',
                    'bod' => $student->DATE_OF_BIRTH ?? null,
                    'date_of_brith' => $student->DATE_OF_BIRTH ?? null, // Providing both just in case
                    'governorate' => $student->PROVINCE ?? '',
                    'school_governorate' => $student->SEC_SCHOOL_PROVINCE ?? '',
                    'territory' => $student->TERRITORY ?? '',
                    'school_territory' => $student->SEC_SCHOOL_TERRITORY ?? '',
                ];
            }
        } catch (\Exception $e) {
            Log::error("MinistryApiService: Local DB Fallback failed", [
                'message' => $e->getMessage()
            ]);
        }
        return null;
    }
}
