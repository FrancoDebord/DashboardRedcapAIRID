<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccueilProjectController extends Controller
{

    public function __construct()
    {
        // if (!$this->isInternetAvailable()) {
        //     abort(503, 'Internet connection is required.');
        // }
        // elseif (!$this->isRedCapServerAvailable()) {
        //     abort(503, 'REDCap server is currently unavailable.');
        // } 

        $projectId = request()->input('project_id', 38); // Default to project ID 38 if not provided

        global $code_bassila;
        $code_bassila = (($projectId === "38" || $projectId === "40") ? '1' : 'B');

        global $code_zogbodomey;
        $code_zogbodomey = (($projectId === "38" || $projectId === "40") ? '2' : 'Z');
    }

    private function isInternetAvailable()
    {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }
        return false;
    }

    // function to check if the redcap server is available
    private function isRedCapServerAvailable()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://redcap.airid-africa.com/api/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Set a timeout for the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }


    // Function to pull data from REDCap API
    public function pullDataFromRedCap(Request $request)
    {

        $projectId = $request->input('project_id');

        // Set your REDCap API URL and token (replace with your actual values)
        $apiUrl = 'https://redcap.airid-africa.com/api/';
        $apiToken = ''; // You may want to store this securely and retrieve based on $projectId
        $project_title = "";

        // Example: Retrieve API token based on project ID (implement your own logic)
        switch ($projectId) {
            case 31:
                $apiToken = '70D93561C9F0BAE16AE756A2D320A3BD';
                $project_title = "ATSB An. Gambiae Baseline";
                break;
            case 35:
                $apiToken = 'B58508382C6CF09A4CFD97A14643A44D';
                $project_title = "ATSB Other Species Baseline";
                break;
            case 38:
                $apiToken = '4400659EB9A8B164FF3E7D451930BCAC';
                $project_title = "ATSB An. Gambiae FINAL";
                break;
            case 40:
                $apiToken = 'C24E253F1A6E64982873F6E5A3F50D30';
                $project_title = "ATSB ALL MOSQUITOES FINAL";
                break;
            // Add more cases as needed
            default:
                abort(400, 'Invalid project ID');
        }

        $data = array(
            'token' => $apiToken,
            'content' => 'record',
            'format' => 'json',
            'returnFormat' => 'json'
        );




        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
        $output = curl_exec($ch);

        curl_close($ch);
        // Decode JSON response
        $redcapData = json_decode($output, true);




        $total_records = count($redcapData);
        $total_records_bassila = count(array_filter($redcapData, function ($record) {

            global $code_bassila;
            return $record['commune'] === $code_bassila;
        }));
        $total_records_zogbodomey = count(array_filter($redcapData, function ($record) {

            global $code_zogbodomey;
            return $record['commune'] === $code_zogbodomey;
        }));


        $records_per_date_tablet = [];
        $labels_bar_chart = [];
        $data_bar_chart = [];

        foreach ($redcapData as $record) {
            $date = $record['date_collecte'] ?? null;
            $tablet = $record['tablet_id'] ?? null;

            if ($date && !in_array($date, $labels_bar_chart)) {
                $labels_bar_chart[] = $date;
            }


            //prise de l'index de la date dans les labels
            $index = array_search($date, $labels_bar_chart);
            if ($index !== false) {
                if (!isset($data_bar_chart[$index])) {
                    $data_bar_chart[$index] = 0;
                }
                // Increment the count for this date
                $data_bar_chart[$index]++;
            }

            // Count records per date and tablet
            if ($date && $tablet) {
                if (!isset($records_per_date_tablet[$date])) {
                    $records_per_date_tablet[$date] = [];
                }
                if (!isset($records_per_date_tablet[$date][$tablet])) {
                    $records_per_date_tablet[$date][$tablet] = 0;
                }
                $records_per_date_tablet[$date][$tablet]++;
            }
        }
        view()->share('records_per_date_tablet', $records_per_date_tablet);


        $records_bassila = array_filter($redcapData, function ($record) {
            global $code_bassila;
            return isset($record['commune']) && $record['commune'] === $code_bassila;
        });

        $records_zogbodomey = array_filter($redcapData, function ($record) {
            global $code_zogbodomey;
            return isset($record['commune']) && $record['commune'] === $code_zogbodomey;
        });


        $species_mapping = [
            '1'   => 'An. gambiae Sensa Lato',
            '2'   => 'An. Ziemanni',
            '3'   => 'Aedes aegypti',
            '4'   => 'Aedes Albopictus',
            '5'   => 'Culex quinquefasciatos',
            '6'   => 'Mansonia Africanus',
            '7'   => 'Toxosghnchites',
            'UNK' => 'Unknown',
            '99'  => 'Other specie',
        ];

        // Calculate species distribution directly from each commune's data
        $species_per_commune_bassila = [];
        $species_per_commune_zogbodomey = [];

        foreach ($species_mapping as $key => $value) {
            $species_per_commune_bassila[$value] = 0;
            $species_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $species = $record['specie'] ?? null;
            if ($species) {

                $specie_label = $species_mapping[$species] ?? 'Unknown';
                // Use the label for the species
                if (!isset($species_per_commune_bassila[$specie_label])) {
                    $species_per_commune_bassila[$specie_label] = 0;
                }
                $species_per_commune_bassila[$specie_label]++;
            }
        }


        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $species = $record['specie'] ?? null;
            if ($species) {
                $specie_label = $species_mapping[$species] ?? 'Unknown';
                // Use the label for the species
                if (!isset($species_per_commune_zogbodomey[$specie_label])) {
                    $species_per_commune_zogbodomey[$specie_label] = 0;
                }
                $species_per_commune_zogbodomey[$specie_label]++;
            }
        }

        // Prepare species and counts arrays for each commune for Chart.js
        $species_labels_bassila = [];
        $species_counts_bassila = [];

        if (isset($species_per_commune_bassila)) {
            $species_labels_bassila = array_keys($species_per_commune_bassila);
            $species_counts_bassila = array_values($species_per_commune_bassila);
        }

        $species_labels_zogbodomey = [];
        $species_counts_zogbodomey = [];

        if (isset($species_per_commune_zogbodomey)) {
            $species_labels_zogbodomey = array_keys($species_per_commune_zogbodomey);
            $species_counts_zogbodomey = array_values($species_per_commune_zogbodomey);
        }

        view()->share('species_per_commune_bassila', $species_per_commune_bassila);
        view()->share('species_per_commune_zogbodomey', $species_per_commune_zogbodomey);
        view()->share('species_labels_bassila', $species_labels_bassila);
        view()->share('species_counts_bassila', $species_counts_bassila);
        view()->share('species_labels_zogbodomey', $species_labels_zogbodomey);
        view()->share('species_counts_zogbodomey', $species_counts_zogbodomey);

        // Code pour le parametre Location : Indoors et Outdoors

        $locations_mapping = [
            'in' => 'Indoors',
            'out' => 'Outdoors',
            'UNK' => 'Unknown',
        ];
        // Initialize arrays to hold location counts for each commune   
        $location_per_commune_bassila = [];
        $location_per_commune_zogbodomey = [];

        foreach ($locations_mapping as $key => $value) {
            $location_per_commune_bassila[$value] = 0;
            $location_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $location = $locations_mapping[$record['location']] ?? null;
            if ($location) {
                if (!isset($location_per_commune_bassila[$location])) {
                    $location_per_commune_bassila[$location] = 0;
                }
                $location_per_commune_bassila[$location]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $location = $locations_mapping[$record['location']] ?? null;
            if ($location) {
                if (!isset($location_per_commune_zogbodomey[$location])) {
                    $location_per_commune_zogbodomey[$location] = 0;
                }
                $location_per_commune_zogbodomey[$location]++;
            }
        }

        // Prepare locations and counts arrays for each commune for Chart.js
        $location_labels_bassila = [];
        $location_counts_bassila = [];

        if (isset($location_per_commune_bassila)) {
            $location_labels_bassila =  array_keys($location_per_commune_bassila);
            $location_counts_bassila = array_values($location_per_commune_bassila);
        }

        $location_labels_zogbodomey = $locations_mapping;
        $location_counts_zogbodomey = array_fill(0, count($locations_mapping), 0);

        if (isset($location_per_commune_zogbodomey)) {
            $location_labels_zogbodomey = array_keys($location_per_commune_zogbodomey);
            $location_counts_zogbodomey = array_values($location_per_commune_zogbodomey);
        }

        view()->share('location_per_commune_bassila', $location_per_commune_bassila);
        view()->share('location_per_commune_zogbodomey', $location_per_commune_zogbodomey);
        view()->share('location_labels_bassila', $location_labels_bassila);
        view()->share('location_counts_bassila', $location_counts_bassila);
        view()->share('location_labels_zogbodomey', $location_labels_zogbodomey);
        view()->share('location_counts_zogbodomey', $location_counts_zogbodomey);





        //same code for sugar_feeding variable
        $sugar_feeding_mapping = [
            'sugar_fed' => 'Sugar-Fed',
            'sugar_unfed' => 'Sugar-Unfed'
        ];
        // Initialize arrays to hold sugar feeding counts for each commune

        $sugar_feeding_per_commune_bassila = [];
        $sugar_feeding_per_commune_zogbodomey = [];

        foreach ($sugar_feeding_mapping as $key => $value) {
            $sugar_feeding_per_commune_bassila[$value] = 0;
            $sugar_feeding_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $sugar_feeding = $sugar_feeding_mapping[$record['sugar_feeding']] ?? null;

            if ($sugar_feeding) {
                if (!isset($sugar_feeding_per_commune_bassila[$sugar_feeding])) {
                    $sugar_feeding_per_commune_bassila[$sugar_feeding] = 0;
                }
                $sugar_feeding_per_commune_bassila[$sugar_feeding]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $sugar_feeding = $sugar_feeding_mapping[$record['sugar_feeding']] ?? null;

            if ($sugar_feeding) {
                if (!isset($sugar_feeding_per_commune_zogbodomey[$sugar_feeding])) {
                    $sugar_feeding_per_commune_zogbodomey[$sugar_feeding] = 0;
                }
                $sugar_feeding_per_commune_zogbodomey[$sugar_feeding]++;
            }
        }

        // Prepare sugar feeding data for each commune for Chart.js


        // Prepare Sugar Feeding data for Bassila
        $sugar_feeding_labels_bassila = [];
        $sugar_feeding_counts_bassila = [];

        if (isset($sugar_feeding_per_commune_bassila)) {
            $sugar_feeding_labels_bassila = array_keys($sugar_feeding_per_commune_bassila);
            $sugar_feeding_counts_bassila = array_values($sugar_feeding_per_commune_bassila);
        }

        // Prepare sugar feeding data for Zogbodomey
        $sugar_feeding_labels_zogbodomey = [];
        $sugar_feeding_counts_zogbodomey = [];

        if (isset($sugar_feeding_per_commune_zogbodomey)) {
            $sugar_feeding_labels_zogbodomey = array_keys($sugar_feeding_per_commune_zogbodomey);
            $sugar_feeding_counts_zogbodomey = array_values($sugar_feeding_per_commune_zogbodomey);
        }

        view()->share('sugar_feeding_per_commune_bassila', $sugar_feeding_per_commune_bassila);
        view()->share('sugar_feeding_per_commune_zogbodomey', $sugar_feeding_per_commune_zogbodomey);
        view()->share('sugar_feeding_labels_bassila', $sugar_feeding_labels_bassila);
        view()->share('sugar_feeding_counts_bassila', $sugar_feeding_counts_bassila);
        view()->share('sugar_feeding_labels_zogbodomey', $sugar_feeding_labels_zogbodomey);
        view()->share('sugar_feeding_counts_zogbodomey', $sugar_feeding_counts_zogbodomey);



        //same code for colour_fed variable
        $colour_sugar_fed = [
            1 => 'Green',
            2 => 'Blue',
            3 => 'Blue-Green'
        ];

        // Initialize arrays to hold colour counts for each commune
        $colour_per_commune_bassila = [];
        $colour_per_commune_zogbodomey = [];
        foreach ($colour_sugar_fed as $key => $value) {
            $colour_per_commune_bassila[$value] = 0;
            $colour_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $colour = $colour_sugar_fed[$record['colour_fed']] ?? null;
            if ($colour) {
                if (!isset($colour_per_commune_bassila[$colour])) {
                    $colour_per_commune_bassila[$colour] = 0;
                }
                $colour_per_commune_bassila[$colour]++;
            }
        }
        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $colour = $colour_sugar_fed[$record['colour_fed']] ?? null;
            if ($colour) {
                if (!isset($colour_per_commune_zogbodomey[$colour])) {
                    $colour_per_commune_zogbodomey[$colour] = 0;
                }
                $colour_per_commune_zogbodomey[$colour]++;
            }
        }

        // Prepare Colour data for each commune for Chart.js
        // Prepare Colour data for Bassila
        $colour_labels_bassila = [];
        $colour_counts_bassila = [];
        if (isset($colour_per_commune_bassila)) {
            $colour_labels_bassila = array_keys($colour_per_commune_bassila);
            $colour_counts_bassila = array_values($colour_per_commune_bassila);
        }

        // Prepare Colour data for Zogbodomey
        $colour_labels_zogbodomey = [];
        $colour_counts_zogbodomey = [];
        if (isset($colour_per_commune_zogbodomey)) {
            $colour_labels_zogbodomey = array_keys($colour_per_commune_zogbodomey);
            $colour_counts_zogbodomey = array_values($colour_per_commune_zogbodomey);
        }



        view()->share('colour_per_commune_bassila', $colour_per_commune_bassila);
        view()->share('colour_per_commune_zogbodomey', $colour_per_commune_zogbodomey);
        view()->share('colour_labels_bassila', $colour_labels_bassila);
        view()->share('colour_counts_bassila', $colour_counts_bassila);
        view()->share('colour_labels_zogbodomey', $colour_labels_zogbodomey);
        view()->share('colour_counts_zogbodomey', $colour_counts_zogbodomey);



        //same code for the Feeding Status variable
        $feeding_status_mapping = [
            'fed' => 'Blood-Fed',
            'unfed' => 'Unfed',
            'UNK' => 'Unknown'
        ];

        // Initialize arrays to hold feeding status counts for each commune
        $feeding_status_per_commune_bassila = [];
        $feeding_status_per_commune_zogbodomey = [];
        foreach ($feeding_status_mapping as $key => $value) {
            $feeding_status_per_commune_bassila[$value] = 0;
            $feeding_status_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $feeding_status = $feeding_status_mapping[$record['feeding_status']] ?? null;
            if ($feeding_status) {
                if (!isset($feeding_status_per_commune_bassila[$feeding_status])) {
                    $feeding_status_per_commune_bassila[$feeding_status] = 0;
                }
                $feeding_status_per_commune_bassila[$feeding_status]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $feeding_status = $feeding_status_mapping[$record['feeding_status']] ?? null;
            if ($feeding_status) {
                if (!isset($feeding_status_per_commune_zogbodomey[$feeding_status])) {
                    $feeding_status_per_commune_zogbodomey[$feeding_status] = 0;
                }
                $feeding_status_per_commune_zogbodomey[$feeding_status]++;
            }
        }
        // Prepare Feeding Status data for each commune for Chart.js
        $feeding_status_labels_bassila = [];
        $feeding_status_counts_bassila = [];
        if (isset($feeding_status_per_commune_bassila)) {
            $feeding_status_labels_bassila = array_keys($feeding_status_per_commune_bassila);
            $feeding_status_counts_bassila = array_values($feeding_status_per_commune_bassila);
        }
        $feeding_status_labels_zogbodomey = [];
        $feeding_status_counts_zogbodomey = [];
        if (isset($feeding_status_per_commune_zogbodomey)) {
            $feeding_status_labels_zogbodomey = array_keys($feeding_status_per_commune_zogbodomey);
            $feeding_status_counts_zogbodomey = array_values($feeding_status_per_commune_zogbodomey);
        }
        view()->share('feeding_status_per_commune_bassila', $feeding_status_per_commune_bassila);
        view()->share('feeding_status_per_commune_zogbodomey', $feeding_status_per_commune_zogbodomey);
        view()->share('feeding_status_labels_bassila', $feeding_status_labels_bassila);
        view()->share('feeding_status_counts_bassila    ', $feeding_status_counts_bassila);
        view()->share('feeding_status_labels_zogbodomey', $feeding_status_labels_zogbodomey);
        view()->share('feeding_status_counts_zogbodomey', $feeding_status_counts_zogbodomey);



        //same code for the sex_mosquito variable
        $sex_mapping = [
            '1' => 'Male',
            '2' => 'Female',
        ];

        // Initialize arrays to hold sex counts for each commune
        $sex_per_commune_bassila = [];
        $sex_per_commune_zogbodomey = [];
        foreach ($sex_mapping as $key => $value) {
            $sex_per_commune_bassila[$value] = 0;
            $sex_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $sex = $sex_mapping[$record['sex_mosquito']] ?? null;
            if ($sex) {
                if (!isset($sex_per_commune_bassila[$sex])) {
                    $sex_per_commune_bassila[$sex] = 0;
                }
                $sex_per_commune_bassila[$sex]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $sex = $sex_mapping[$record['sex_mosquito']] ?? null;
            if ($sex) {
                if (!isset($sex_per_commune_zogbodomey[$sex])) {
                    $sex_per_commune_zogbodomey[$sex] = 0;
                }
                $sex_per_commune_zogbodomey[$sex]++;
            }
        }
        // Prepare Sex data for each commune for Chart.js
        $sex_labels_bassila = [];
        $sex_counts_bassila = [];
        if (isset($sex_per_commune_bassila)) {
            $sex_labels_bassila = array_keys($sex_per_commune_bassila);
            $sex_counts_bassila = array_values($sex_per_commune_bassila);
        }
        $sex_labels_zogbodomey = [];
        $sex_counts_zogbodomey = [];
        if (isset($sex_per_commune_zogbodomey)) {
            $sex_labels_zogbodomey = array_keys($sex_per_commune_zogbodomey);
            $sex_counts_zogbodomey = array_values($sex_per_commune_zogbodomey);
        }


        view()->share('sex_per_commune_bassila', $sex_per_commune_bassila);
        view()->share('sex_per_commune_zogbodomey', $sex_per_commune_zogbodomey);
        view()->share('sex_labels_bassila', $sex_labels_bassila);
        view()->share('sex_counts_bassila', $sex_counts_bassila);
        view()->share('sex_labels_zogbodomey', $sex_labels_zogbodomey);
        view()->share('sex_counts_zogbodomey', $sex_counts_zogbodomey);




        //same code for gravid_status variable
        $gravid_status_mapping = [
            'not_gravid' => 'Not Gravid',
            'semi_gravid' => 'Semi Gravid',
            'gravid' => 'Gravid',
            'UNK' => 'Unknown'
        ];

        // Initialize arrays to hold gravid status counts for each commune
        $gravid_status_per_commune_bassila = [];
        $gravid_status_per_commune_zogbodomey = [];
        foreach ($gravid_status_mapping as $key => $value) {
            $gravid_status_per_commune_bassila[$value] = 0;
            $gravid_status_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $gravid_status = $gravid_status_mapping[$record['gravid_status']] ?? null;
            if ($gravid_status) {
                if (!isset($gravid_status_per_commune_bassila[$gravid_status])) {
                    $gravid_status_per_commune_bassila[$gravid_status] = 0;
                }
                $gravid_status_per_commune_bassila[$gravid_status]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $gravid_status = $gravid_status_mapping[$record['gravid_status']] ?? null;
            if ($gravid_status) {
                if (!isset($gravid_status_per_commune_zogbodomey[$gravid_status])) {
                    $gravid_status_per_commune_zogbodomey[$gravid_status] = 0;
                }
                $gravid_status_per_commune_zogbodomey[$gravid_status]++;
            }
        }

        // Prepare Gravid Status data for each commune for Chart.js
        $gravid_status_labels_bassila = [];
        $gravid_status_counts_bassila = [];
        if (isset($gravid_status_per_commune_bassila)) {
            $gravid_status_labels_bassila = array_keys($gravid_status_per_commune_bassila);
            $gravid_status_counts_bassila = array_values($gravid_status_per_commune_bassila);
        }
        $gravid_status_labels_zogbodomey = [];
        $gravid_status_counts_zogbodomey = [];
        if (isset($gravid_status_per_commune_zogbodomey)) {
            $gravid_status_labels_zogbodomey = array_keys($gravid_status_per_commune_zogbodomey);
            $gravid_status_counts_zogbodomey = array_values($gravid_status_per_commune_zogbodomey);
        }

        view()->share('gravid_status_per_commune_bassila', $gravid_status_per_commune_bassila);
        view()->share('gravid_status_per_commune_zogbodomey', $gravid_status_per_commune_zogbodomey);
        view()->share('gravid_status_labels_bassila', $gravid_status_labels_bassila);
        view()->share('gravid_status_counts_bassila', $gravid_status_counts_bassila);
        view()->share('gravid_status_labels_zogbodomey', $gravid_status_labels_zogbodomey);
        view()->share('gravid_status_counts_zogbodomey', $gravid_status_counts_zogbodomey);




        //same code for living_status variable
        $living_status_mapping = [
            'live' => 'Live',
            'dead' => 'Dead'
        ];
        // Initialize arrays to hold living status counts for each commune
        $living_status_per_commune_bassila = [];
        $living_status_per_commune_zogbodomey = [];
        foreach ($living_status_mapping as $key => $value) {
            $living_status_per_commune_bassila[$value] = 0;
            $living_status_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $living_status = $living_status_mapping[$record['living_status']] ?? null;
            if ($living_status) {
                if (!isset($living_status_per_commune_bassila[$living_status])) {
                    $living_status_per_commune_bassila[$living_status] = 0;
                }
                $living_status_per_commune_bassila[$living_status]++;
            }
        }
        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $living_status = $living_status_mapping[$record['living_status']] ?? null;
            if ($living_status) {
                if (!isset($living_status_per_commune_zogbodomey[$living_status])) {
                    $living_status_per_commune_zogbodomey[$living_status] = 0;
                }
                $living_status_per_commune_zogbodomey[$living_status]++;
            }
        }

        // Prepare Living Status data for each commune for Chart.js
        $living_status_labels_bassila = [];
        $living_status_counts_bassila = [];
        if (isset($living_status_per_commune_bassila)) {
            $living_status_labels_bassila = array_keys($living_status_per_commune_bassila);
            $living_status_counts_bassila = array_values($living_status_per_commune_bassila);
        }
        $living_status_labels_zogbodomey = [];
        $living_status_counts_zogbodomey = [];
        if (isset($living_status_per_commune_zogbodomey)) {
            $living_status_labels_zogbodomey = array_keys($living_status_per_commune_zogbodomey);
            $living_status_counts_zogbodomey = array_values($living_status_per_commune_zogbodomey);
        }


        view()->share('living_status_per_commune_bassila', $living_status_per_commune_bassila);
        view()->share('living_status_per_commune_zogbodomey', $living_status_per_commune_zogbodomey);
        view()->share('living_status_labels_bassila', $living_status_labels_bassila);
        view()->share('living_status_counts_bassila ', $living_status_counts_bassila);
        view()->share('living_status_labels_zogbodomey', $living_status_labels_zogbodomey);
        view()->share('living_status_counts_zogbodomey', $living_status_counts_zogbodomey);






        if ($request->ajax()) {
            return response()->json([
                'labels_bar_chart' => $labels_bar_chart,
                'data_bar_chart' => $data_bar_chart,
                'project_title' => $project_title,

                "species_labels_bassila" => $species_labels_bassila,
                "species_counts_bassila" => $species_counts_bassila,
                "species_labels_zogbodomey" => $species_labels_zogbodomey,
                "species_counts_zogbodomey" => $species_counts_zogbodomey,

                "location_labels_bassila" => $location_labels_bassila,
                "location_counts_bassila" => $location_counts_bassila,
                "location_labels_zogbodomey" => $location_labels_zogbodomey,
                "location_counts_zogbodomey" => $location_counts_zogbodomey,

                "sugar_feeding_labels_bassila" => $sugar_feeding_labels_bassila,
                "sugar_feeding_counts_bassila" => $sugar_feeding_counts_bassila,
                "sugar_feeding_labels_zogbodomey" => $sugar_feeding_labels_zogbodomey,
                "sugar_feeding_counts_zogbodomey" => $sugar_feeding_counts_zogbodomey,

                "feeding_status_labels_bassila" => $feeding_status_labels_bassila,
                "feeding_status_counts_bassila" => $feeding_status_counts_bassila,
                "feeding_status_labels_zogbodomey" => $feeding_status_labels_zogbodomey,
                "feeding_status_counts_zogbodomey" => $feeding_status_counts_zogbodomey,

                "sex_labels_bassila" => $sex_labels_bassila,
                "sex_counts_bassila" => $sex_counts_bassila,
                "sex_labels_zogbodomey" => $sex_labels_zogbodomey,
                "sex_counts_zogbodomey" => $sex_counts_zogbodomey,

                "colour_labels_bassila" => $colour_labels_bassila,
                "colour_counts_bassila" => $colour_counts_bassila,
                "colour_labels_zogbodomey" => $colour_labels_zogbodomey,
                "colour_counts_zogbodomey" => $colour_counts_zogbodomey,

                "gravid_status_labels_bassila" => $gravid_status_labels_bassila,
                "gravid_status_counts_bassila" => $gravid_status_counts_bassila,
                "gravid_status_labels_zogbodomey" => $gravid_status_labels_zogbodomey,
                "gravid_status_counts_zogbodomey" => $gravid_status_counts_zogbodomey,

                "living_status_labels_bassila" => $living_status_labels_bassila,
                "living_status_counts_bassila" => $living_status_counts_bassila,
                "living_status_labels_zogbodomey" => $living_status_labels_zogbodomey,
                "living_status_counts_zogbodomey" => $living_status_counts_zogbodomey,

            ]);
        } else {

            return view('interface-accueil', compact(
                'redcapData',
                'total_records',
                'total_records_bassila',
                'total_records_zogbodomey',
                'records_per_date_tablet',
                'project_title'
            ));
        }
    }

    // Function to pull data from REDCap API
    public function pullDataFromRedCapAnGambiaeFINAL(Request $request)
    {

        $projectId = $request->input('project_id');

        // Set your REDCap API URL and token (replace with your actual values)
        $apiUrl = 'https://redcap.airid-africa.com/api/';
        $apiToken = ''; // You may want to store this securely and retrieve based on $projectId
        $project_title = "";

        // Example: Retrieve API token based on project ID (implement your own logic)
        switch ($projectId) {
            case 31:
                $apiToken = '70D93561C9F0BAE16AE756A2D320A3BD';
                $project_title = "ATSB An. Gambiae Baseline";
                break;
            case 35:
                $apiToken = 'B58508382C6CF09A4CFD97A14643A44D';
                $project_title = "ATSB Other Species Baseline";
                break;
            case 38:
                $apiToken = '4400659EB9A8B164FF3E7D451930BCAC';
                $project_title = "ATSB An. Gambiae FINAL";
                break;
            case 40:
                $apiToken = 'C24E253F1A6E64982873F6E5A3F50D30';
                $project_title = "ATSB ALL MOSQUITOES FINAL";
                break;
            // Add more cases as needed
            default:
                abort(400, 'Invalid project ID');
        }

        $data = array(
            'token' => $apiToken,
            'content' => 'record',
            'format' => 'json',
            'returnFormat' => 'json'
        );




        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
        $output = curl_exec($ch);

        curl_close($ch);
        // Decode JSON response
        $redcapData = json_decode($output, true);



        $total_records = count($redcapData);
        $total_records_bassila = count(array_filter($redcapData, function ($record) {
            return $record['commune'] === '1';
        }));
        $total_records_zogbodomey = count(array_filter($redcapData, function ($record) {
            return $record['commune'] === '2';
        }));


        $records_per_date_tablet = [];
        $labels_bar_chart = [];
        $data_bar_chart = [];

        foreach ($redcapData as $record) {
            $date = $record['date_collecte'] ?? null;
            $tablet = $record['tablet_id'] ?? null;

            if ($date && !in_array($date, $labels_bar_chart)) {
                $labels_bar_chart[] = $date;
            }


            //prise de l'index de la date dans les labels
            $index = array_search($date, $labels_bar_chart);
            if ($index !== false) {
                if (!isset($data_bar_chart[$index])) {
                    $data_bar_chart[$index] = 0;
                }
                // Increment the count for this date
                $data_bar_chart[$index]++;
            }

            // Count records per date and tablet
            if ($date && $tablet) {
                if (!isset($records_per_date_tablet[$date])) {
                    $records_per_date_tablet[$date] = [];
                }
                if (!isset($records_per_date_tablet[$date][$tablet])) {
                    $records_per_date_tablet[$date][$tablet] = 0;
                }
                $records_per_date_tablet[$date][$tablet]++;
            }
        }
        view()->share('records_per_date_tablet', $records_per_date_tablet);

        $records_bassila = array_filter($redcapData, function ($record) {
            return isset($record['commune']) && $record['commune'] === '1';
        });

        $records_zogbodomey = array_filter($redcapData, function ($record) {
            return isset($record['commune']) && $record['commune'] === '2';
        });


        $species_mapping = [
            '1'   => 'An. gambiae Sensa Lato',
            '2'   => 'An. Ziemanni',
            '3'   => 'Aedes aegypti',
            '4'   => 'Aedes Albopictus',
            '5'   => 'Culex quinquefasciatos',
            '6'   => 'Mansonia Africanus',
            '7'   => 'Toxosghnchites',
            'UNK' => 'Unknown',
            '99'  => 'Other specie',
        ];

        // Calculate species distribution directly from each commune's data
        $species_per_commune_bassila = [];
        $species_per_commune_zogbodomey = [];

        foreach ($species_mapping as $key => $value) {
            $species_per_commune_bassila[$value] = 0;
            $species_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $species = $record['specie'] ?? null;
            if ($species) {

                $specie_label = $species_mapping[$species] ?? 'Unknown';
                // Use the label for the species
                if (!isset($species_per_commune_bassila[$specie_label])) {
                    $species_per_commune_bassila[$specie_label] = 0;
                }
                $species_per_commune_bassila[$specie_label]++;
            }
        }


        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $species = $record['specie'] ?? null;
            if ($species) {
                $specie_label = $species_mapping[$species] ?? 'Unknown';
                // Use the label for the species
                if (!isset($species_per_commune_zogbodomey[$specie_label])) {
                    $species_per_commune_zogbodomey[$specie_label] = 0;
                }
                $species_per_commune_zogbodomey[$specie_label]++;
            }
        }

        // Prepare species and counts arrays for each commune for Chart.js
        $species_labels_bassila = [];
        $species_counts_bassila = [];

        if (isset($species_per_commune_bassila)) {
            $species_labels_bassila = array_keys($species_per_commune_bassila);
            $species_counts_bassila = array_values($species_per_commune_bassila);
        }

        $species_labels_zogbodomey = [];
        $species_counts_zogbodomey = [];

        if (isset($species_per_commune_zogbodomey)) {
            $species_labels_zogbodomey = array_keys($species_per_commune_zogbodomey);
            $species_counts_zogbodomey = array_values($species_per_commune_zogbodomey);
        }

        view()->share('species_per_commune_bassila', $species_per_commune_bassila);
        view()->share('species_per_commune_zogbodomey', $species_per_commune_zogbodomey);
        view()->share('species_labels_bassila', $species_labels_bassila);
        view()->share('species_counts_bassila', $species_counts_bassila);
        view()->share('species_labels_zogbodomey', $species_labels_zogbodomey);
        view()->share('species_counts_zogbodomey', $species_counts_zogbodomey);

        // Code pour le parametre Location : Indoors et Outdoors

        $locations_mapping = [
            'in' => 'Indoors',
            'out' => 'Outdoors',
            'UNK' => 'Unknown',
        ];
        // Initialize arrays to hold location counts for each commune   
        $location_per_commune_bassila = [];
        $location_per_commune_zogbodomey = [];

        foreach ($locations_mapping as $key => $value) {
            $location_per_commune_bassila[$value] = 0;
            $location_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $location = $locations_mapping[$record['location']] ?? null;
            if ($location) {
                if (!isset($location_per_commune_bassila[$location])) {
                    $location_per_commune_bassila[$location] = 0;
                }
                $location_per_commune_bassila[$location]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $location = $locations_mapping[$record['location']] ?? null;
            if ($location) {
                if (!isset($location_per_commune_zogbodomey[$location])) {
                    $location_per_commune_zogbodomey[$location] = 0;
                }
                $location_per_commune_zogbodomey[$location]++;
            }
        }

        // Prepare locations and counts arrays for each commune for Chart.js
        $location_labels_bassila = [];
        $location_counts_bassila = [];

        if (isset($location_per_commune_bassila)) {
            $location_labels_bassila =  array_keys($location_per_commune_bassila);
            $location_counts_bassila = array_values($location_per_commune_bassila);
        }

        $location_labels_zogbodomey = $locations_mapping;
        $location_counts_zogbodomey = array_fill(0, count($locations_mapping), 0);

        if (isset($location_per_commune_zogbodomey)) {
            $location_labels_zogbodomey = array_keys($location_per_commune_zogbodomey);
            $location_counts_zogbodomey = array_values($location_per_commune_zogbodomey);
        }

        view()->share('location_per_commune_bassila', $location_per_commune_bassila);
        view()->share('location_per_commune_zogbodomey', $location_per_commune_zogbodomey);
        view()->share('location_labels_bassila', $location_labels_bassila);
        view()->share('location_counts_bassila', $location_counts_bassila);
        view()->share('location_labels_zogbodomey', $location_labels_zogbodomey);
        view()->share('location_counts_zogbodomey', $location_counts_zogbodomey);





        //same code for sugar_feeding variable
        $sugar_feeding_mapping = [
            'sugar_fed' => 'Sugar-Fed',
            'sugar_unfed' => 'Sugar-Unfed'
        ];
        // Initialize arrays to hold sugar feeding counts for each commune

        $sugar_feeding_per_commune_bassila = [];
        $sugar_feeding_per_commune_zogbodomey = [];

        foreach ($sugar_feeding_mapping as $key => $value) {
            $sugar_feeding_per_commune_bassila[$value] = 0;
            $sugar_feeding_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $sugar_feeding = $sugar_feeding_mapping[$record['sugar_feeding']] ?? null;

            if ($sugar_feeding) {
                if (!isset($sugar_feeding_per_commune_bassila[$sugar_feeding])) {
                    $sugar_feeding_per_commune_bassila[$sugar_feeding] = 0;
                }
                $sugar_feeding_per_commune_bassila[$sugar_feeding]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $sugar_feeding = $sugar_feeding_mapping[$record['sugar_feeding']] ?? null;

            if ($sugar_feeding) {
                if (!isset($sugar_feeding_per_commune_zogbodomey[$sugar_feeding])) {
                    $sugar_feeding_per_commune_zogbodomey[$sugar_feeding] = 0;
                }
                $sugar_feeding_per_commune_zogbodomey[$sugar_feeding]++;
            }
        }

        // Prepare sugar feeding data for each commune for Chart.js


        // Prepare Sugar Feeding data for Bassila
        $sugar_feeding_labels_bassila = [];
        $sugar_feeding_counts_bassila = [];

        if (isset($sugar_feeding_per_commune_bassila)) {
            $sugar_feeding_labels_bassila = array_keys($sugar_feeding_per_commune_bassila);
            $sugar_feeding_counts_bassila = array_values($sugar_feeding_per_commune_bassila);
        }

        // Prepare sugar feeding data for Zogbodomey
        $sugar_feeding_labels_zogbodomey = [];
        $sugar_feeding_counts_zogbodomey = [];

        if (isset($sugar_feeding_per_commune_zogbodomey)) {
            $sugar_feeding_labels_zogbodomey = array_keys($sugar_feeding_per_commune_zogbodomey);
            $sugar_feeding_counts_zogbodomey = array_values($sugar_feeding_per_commune_zogbodomey);
        }

        view()->share('sugar_feeding_per_commune_bassila', $sugar_feeding_per_commune_bassila);
        view()->share('sugar_feeding_per_commune_zogbodomey', $sugar_feeding_per_commune_zogbodomey);
        view()->share('sugar_feeding_labels_bassila', $sugar_feeding_labels_bassila);
        view()->share('sugar_feeding_counts_bassila', $sugar_feeding_counts_bassila);
        view()->share('sugar_feeding_labels_zogbodomey', $sugar_feeding_labels_zogbodomey);
        view()->share('sugar_feeding_counts_zogbodomey', $sugar_feeding_counts_zogbodomey);



        //same code for colour_fed variable
        $colour_sugar_fed = [
            1 => 'Green',
            2 => 'Blue',
            3 => 'Blue-Green'
        ];

        // Initialize arrays to hold colour counts for each commune
        $colour_per_commune_bassila = [];
        $colour_per_commune_zogbodomey = [];
        foreach ($colour_sugar_fed as $key => $value) {
            $colour_per_commune_bassila[$value] = 0;
            $colour_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $colour = $colour_sugar_fed[$record['colour_fed']] ?? null;
            if ($colour) {
                if (!isset($colour_per_commune_bassila[$colour])) {
                    $colour_per_commune_bassila[$colour] = 0;
                }
                $colour_per_commune_bassila[$colour]++;
            }
        }
        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $colour = $colour_sugar_fed[$record['colour_fed']] ?? null;
            if ($colour) {
                if (!isset($colour_per_commune_zogbodomey[$colour])) {
                    $colour_per_commune_zogbodomey[$colour] = 0;
                }
                $colour_per_commune_zogbodomey[$colour]++;
            }
        }

        // Prepare Colour data for each commune for Chart.js
        // Prepare Colour data for Bassila
        $colour_labels_bassila = [];
        $colour_counts_bassila = [];
        if (isset($colour_per_commune_bassila)) {
            $colour_labels_bassila = array_keys($colour_per_commune_bassila);
            $colour_counts_bassila = array_values($colour_per_commune_bassila);
        }

        // Prepare Colour data for Zogbodomey
        $colour_labels_zogbodomey = [];
        $colour_counts_zogbodomey = [];
        if (isset($colour_per_commune_zogbodomey)) {
            $colour_labels_zogbodomey = array_keys($colour_per_commune_zogbodomey);
            $colour_counts_zogbodomey = array_values($colour_per_commune_zogbodomey);
        }



        view()->share('colour_per_commune_bassila', $colour_per_commune_bassila);
        view()->share('colour_per_commune_zogbodomey', $colour_per_commune_zogbodomey);
        view()->share('colour_labels_bassila', $colour_labels_bassila);
        view()->share('colour_counts_bassila', $colour_counts_bassila);
        view()->share('colour_labels_zogbodomey', $colour_labels_zogbodomey);
        view()->share('colour_counts_zogbodomey', $colour_counts_zogbodomey);



        //same code for the Feeding Status variable
        $feeding_status_mapping = [
            'blood_fed' => 'Blood-Fed',
            'unfed' => 'Unfed',
            'UNK' => 'Unknown'
        ];

        // Initialize arrays to hold feeding status counts for each commune
        $feeding_status_per_commune_bassila = [];
        $feeding_status_per_commune_zogbodomey = [];
        foreach ($feeding_status_mapping as $key => $value) {
            $feeding_status_per_commune_bassila[$value] = 0;
            $feeding_status_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $feeding_status = $feeding_status_mapping[$record['feeding_status']] ?? null;
            if ($feeding_status) {
                if (!isset($feeding_status_per_commune_bassila[$feeding_status])) {
                    $feeding_status_per_commune_bassila[$feeding_status] = 0;
                }
                $feeding_status_per_commune_bassila[$feeding_status]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $feeding_status = $feeding_status_mapping[$record['feeding_status']] ?? null;
            if ($feeding_status) {
                if (!isset($feeding_status_per_commune_zogbodomey[$feeding_status])) {
                    $feeding_status_per_commune_zogbodomey[$feeding_status] = 0;
                }
                $feeding_status_per_commune_zogbodomey[$feeding_status]++;
            }
        }
        // Prepare Feeding Status data for each commune for Chart.js
        $feeding_status_labels_bassila = [];
        $feeding_status_counts_bassila = [];
        if (isset($feeding_status_per_commune_bassila)) {
            $feeding_status_labels_bassila = array_keys($feeding_status_per_commune_bassila);
            $feeding_status_counts_bassila = array_values($feeding_status_per_commune_bassila);
        }
        $feeding_status_labels_zogbodomey = [];
        $feeding_status_counts_zogbodomey = [];
        if (isset($feeding_status_per_commune_zogbodomey)) {
            $feeding_status_labels_zogbodomey = array_keys($feeding_status_per_commune_zogbodomey);
            $feeding_status_counts_zogbodomey = array_values($feeding_status_per_commune_zogbodomey);
        }
        view()->share('feeding_status_per_commune_bassila', $feeding_status_per_commune_bassila);
        view()->share('feeding_status_per_commune_zogbodomey', $feeding_status_per_commune_zogbodomey);
        view()->share('feeding_status_labels_bassila', $feeding_status_labels_bassila);
        view()->share('feeding_status_counts_bassila    ', $feeding_status_counts_bassila);
        view()->share('feeding_status_labels_zogbodomey', $feeding_status_labels_zogbodomey);
        view()->share('feeding_status_counts_zogbodomey', $feeding_status_counts_zogbodomey);


        //same code for gravid_status variable
        $gravid_status_mapping = [
            'not_gravid' => 'Not Gravid',
            'semi_gravid' => 'Semi Gravid',
            'gravid' => 'Gravid',
            'UNK' => 'Unknown'
        ];

        // Initialize arrays to hold gravid status counts for each commune
        $gravid_status_per_commune_bassila = [];
        $gravid_status_per_commune_zogbodomey = [];
        foreach ($gravid_status_mapping as $key => $value) {
            $gravid_status_per_commune_bassila[$value] = 0;
            $gravid_status_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {
            $gravid_status = $gravid_status_mapping[$record['gravid_status']] ?? null;
            if ($gravid_status) {
                if (!isset($gravid_status_per_commune_bassila[$gravid_status])) {
                    $gravid_status_per_commune_bassila[$gravid_status] = 0;
                }
                $gravid_status_per_commune_bassila[$gravid_status]++;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $gravid_status = $gravid_status_mapping[$record['gravid_status']] ?? null;
            if ($gravid_status) {
                if (!isset($gravid_status_per_commune_zogbodomey[$gravid_status])) {
                    $gravid_status_per_commune_zogbodomey[$gravid_status] = 0;
                }
                $gravid_status_per_commune_zogbodomey[$gravid_status]++;
            }
        }

        // Prepare Gravid Status data for each commune for Chart.js
        $gravid_status_labels_bassila = [];
        $gravid_status_counts_bassila = [];
        if (isset($gravid_status_per_commune_bassila)) {
            $gravid_status_labels_bassila = array_keys($gravid_status_per_commune_bassila);
            $gravid_status_counts_bassila = array_values($gravid_status_per_commune_bassila);
        }
        $gravid_status_labels_zogbodomey = [];
        $gravid_status_counts_zogbodomey = [];
        if (isset($gravid_status_per_commune_zogbodomey)) {
            $gravid_status_labels_zogbodomey = array_keys($gravid_status_per_commune_zogbodomey);
            $gravid_status_counts_zogbodomey = array_values($gravid_status_per_commune_zogbodomey);
        }

        view()->share('gravid_status_per_commune_bassila', $gravid_status_per_commune_bassila);
        view()->share('gravid_status_per_commune_zogbodomey', $gravid_status_per_commune_zogbodomey);
        view()->share('gravid_status_labels_bassila', $gravid_status_labels_bassila);
        view()->share('gravid_status_counts_bassila', $gravid_status_counts_bassila);
        view()->share('gravid_status_labels_zogbodomey', $gravid_status_labels_zogbodomey);
        view()->share('gravid_status_counts_zogbodomey', $gravid_status_counts_zogbodomey);




        //same code for living_status variable
        $living_status_mapping = [
            'live' => 'Live',
            'dead' => 'Dead'
        ];
        // Initialize arrays to hold living status counts for each commune
        $living_status_per_commune_bassila = [];
        $living_status_per_commune_zogbodomey = [];
        foreach ($living_status_mapping as $key => $value) {
            $living_status_per_commune_bassila[$value] = 0;
            $living_status_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $living_status = $living_status_mapping[$record['living_status']] ?? null;
            if ($living_status) {
                if (!isset($living_status_per_commune_bassila[$living_status])) {
                    $living_status_per_commune_bassila[$living_status] = 0;
                }
                $living_status_per_commune_bassila[$living_status]++;
            }
        }
        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $living_status = $living_status_mapping[$record['living_status']] ?? null;
            if ($living_status) {
                if (!isset($living_status_per_commune_zogbodomey[$living_status])) {
                    $living_status_per_commune_zogbodomey[$living_status] = 0;
                }
                $living_status_per_commune_zogbodomey[$living_status]++;
            }
        }

        // Prepare Living Status data for each commune for Chart.js
        $living_status_labels_bassila = [];
        $living_status_counts_bassila = [];
        if (isset($living_status_per_commune_bassila)) {
            $living_status_labels_bassila = array_keys($living_status_per_commune_bassila);
            $living_status_counts_bassila = array_values($living_status_per_commune_bassila);
        }
        $living_status_labels_zogbodomey = [];
        $living_status_counts_zogbodomey = [];
        if (isset($living_status_per_commune_zogbodomey)) {
            $living_status_labels_zogbodomey = array_keys($living_status_per_commune_zogbodomey);
            $living_status_counts_zogbodomey = array_values($living_status_per_commune_zogbodomey);
        }


        view()->share('living_status_per_commune_bassila', $living_status_per_commune_bassila);
        view()->share('living_status_per_commune_zogbodomey', $living_status_per_commune_zogbodomey);
        view()->share('living_status_labels_bassila', $living_status_labels_bassila);
        view()->share('living_status_counts_bassila ', $living_status_counts_bassila);
        view()->share('living_status_labels_zogbodomey', $living_status_labels_zogbodomey);
        view()->share('living_status_counts_zogbodomey', $living_status_counts_zogbodomey);



        if ($request->ajax()) {
            return response()->json([
                'labels_bar_chart' => $labels_bar_chart,
                'data_bar_chart' => $data_bar_chart,
                'project_title' => $project_title,

                "species_labels_bassila" => $species_labels_bassila,
                "species_counts_bassila" => $species_counts_bassila,
                "species_labels_zogbodomey" => $species_labels_zogbodomey,
                "species_counts_zogbodomey" => $species_counts_zogbodomey,

                "location_labels_bassila" => $location_labels_bassila,
                "location_counts_bassila" => $location_counts_bassila,
                "location_labels_zogbodomey" => $location_labels_zogbodomey,
                "location_counts_zogbodomey" => $location_counts_zogbodomey,

                "sugar_feeding_labels_bassila" => $sugar_feeding_labels_bassila,
                "sugar_feeding_counts_bassila" => $sugar_feeding_counts_bassila,
                "sugar_feeding_labels_zogbodomey" => $sugar_feeding_labels_zogbodomey,
                "sugar_feeding_counts_zogbodomey" => $sugar_feeding_counts_zogbodomey,

                "feeding_status_labels_bassila" => $feeding_status_labels_bassila,
                "feeding_status_counts_bassila" => $feeding_status_counts_bassila,
                "feeding_status_labels_zogbodomey" => $feeding_status_labels_zogbodomey,
                "feeding_status_counts_zogbodomey" => $feeding_status_counts_zogbodomey,

                "colour_labels_bassila" => $colour_labels_bassila,
                "colour_counts_bassila" => $colour_counts_bassila,
                "colour_labels_zogbodomey" => $colour_labels_zogbodomey,
                "colour_counts_zogbodomey" => $colour_counts_zogbodomey,

                "gravid_status_labels_bassila" => $gravid_status_labels_bassila,
                "gravid_status_counts_bassila" => $gravid_status_counts_bassila,
                "gravid_status_labels_zogbodomey" => $gravid_status_labels_zogbodomey,
                "gravid_status_counts_zogbodomey" => $gravid_status_counts_zogbodomey,

                "living_status_labels_bassila" => $living_status_labels_bassila,
                "living_status_counts_bassila" => $living_status_counts_bassila,
                "living_status_labels_zogbodomey" => $living_status_labels_zogbodomey,
                "living_status_counts_zogbodomey" => $living_status_counts_zogbodomey,

            ]);
        } else {

            return view('interface-accueil-an-gambiae-final', compact(
                'redcapData',
                'total_records',
                'total_records_bassila',
                'total_records_zogbodomey',
                'records_per_date_tablet',
                'project_title'
            ));
        }
    }



    // Function to pull data from REDCap API
    public function pullDataFromRedCapAllMosquitoesFINAL(Request $request)
    {

        $projectId = $request->input('project_id');

        // Set your REDCap API URL and token (replace with your actual values)
        $apiUrl = 'https://redcap.airid-africa.com/api/';
        $apiToken = ''; // You may want to store this securely and retrieve based on $projectId
        $project_title = "";

        // Example: Retrieve API token based on project ID (implement your own logic)
        switch ($projectId) {
            case 31:
                $apiToken = '70D93561C9F0BAE16AE756A2D320A3BD';
                $project_title = "ATSB An. Gambiae Baseline";
                break;
            case 35:
                $apiToken = 'B58508382C6CF09A4CFD97A14643A44D';
                $project_title = "ATSB Other Species Baseline";
                break;
            case 38:
                $apiToken = '4400659EB9A8B164FF3E7D451930BCAC';
                $project_title = "ATSB An. Gambiae FINAL";
                break;
            case 40:
                $apiToken = 'C24E253F1A6E64982873F6E5A3F50D30';
                $project_title = "ATSB ALL MOSQUITOES FINAL";
                break;
            // Add more cases as needed
            default:
                abort(400, 'Invalid project ID');
        }

        $data = array(
            'token' => $apiToken,
            'content' => 'record',
            'format' => 'json',
            'returnFormat' => 'json',
            'forms' => 'infos_de_base_menage,summary_detail_mosquitoes', // replace with the repeating instrument's name
            // 'type' => 'flat',
            'rawOrLabelHeaders' => 'raw',
            'exportCheckboxLabel' => 'false',
            'exportDataAccessGroups' => 'false',
            'returnFormat' => 'json'

        );




        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
        $output = curl_exec($ch);

        curl_close($ch);
        // Decode JSON response
        $redcapData = json_decode($output, true);


        // Separate into two datasets
        $infos_base_menage = [];
        $summary_detail_mosquitoes = [];

        foreach ($redcapData as $row) {
            if (empty($row['redcap_repeat_instrument'])) {
                $infos_base_menage[] = $row;
            } elseif ($row['redcap_repeat_instrument'] === "summary_detail_mosquitoes") {
                $summary_detail_mosquitoes[] = $row;
            }
        }


        $total_records = count($infos_base_menage);
        $total_records_bassila = count(array_filter($infos_base_menage, function ($record) {
            return $record['commune'] === '1';
        }));
        $total_records_zogbodomey = count(array_filter($infos_base_menage, function ($record) {
            return $record['commune'] === '2';
        }));


        $records_per_date_tablet = [];
        $labels_bar_chart = [];
        $data_bar_chart = [];

        foreach ($infos_base_menage as $record) {
            $date = $record['date_collecte'] ?? null;
            $tablet = $record['tablet_id'] ?? null;

            if ($date && !in_array($date, $labels_bar_chart)) {
                $labels_bar_chart[] = $date;
            }


            //prise de l'index de la date dans les labels
            $index = array_search($date, $labels_bar_chart);
            if ($index !== false) {
                if (!isset($data_bar_chart[$index])) {
                    $data_bar_chart[$index] = 0;
                }
                // Increment the count for this date
                $data_bar_chart[$index]++;
            }

            // Count records per date and tablet
            if ($date && $tablet) {
                if (!isset($records_per_date_tablet[$date])) {
                    $records_per_date_tablet[$date] = [];
                }
                if (!isset($records_per_date_tablet[$date][$tablet])) {
                    $records_per_date_tablet[$date][$tablet] = 0;
                }
                $records_per_date_tablet[$date][$tablet]++;
            }
        }
        view()->share('records_per_date_tablet', $records_per_date_tablet);

        $records_bassila = array_filter($infos_base_menage, function ($record) {
            return isset($record['commune']) && $record['commune'] === '1';
        });

        $records_zogbodomey = array_filter($infos_base_menage, function ($record) {
            return isset($record['commune']) && $record['commune'] === '2';
        });



        $species_mapping = [
            "1" =>    "An. gambiae Sensu Lato",
            "2" =>    "An. funestus Sensu Lato",
            "3" =>    "An. ziemanni",
            "4" =>    "Aedes aegypti",
            "5" =>    "Aedes albopictus",
            "6" =>    "Culex quinquefasciatos",
            "7" =>    "Mansonia africanus",
            "8" =>    "Mansonia nebulosis",
            "9" =>    "Toxosghnchites",
            "UNK" =>    "Unknown",
            "99" =>    "Other species"
        ];

        // Calculate species distribution directly from each commune's data
        $species_per_commune_bassila = [];
        $species_per_commune_zogbodomey = [];

        foreach ($species_mapping as $key => $value) {
            $species_per_commune_bassila[$value] = 0;
            $species_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $species = $mosquito_specie_detail['specie_summary'] ?? null;

                if ($species) {
                    $specie_label = $species_mapping[$species] ?? 'Unknown';
                    $species_per_commune_bassila[$specie_label] += (int) $mosquito_specie_detail["n_mosquito_specie"] ?? 0;
                }
            }
        }


        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $species = $mosquito_specie_detail['specie_summary'] ?? null;
                if ($species) {
                    $specie_label = $species_mapping[$species] ?? 'Unknown';
                    $species_per_commune_zogbodomey[$specie_label] += (int) $mosquito_specie_detail["n_mosquito_specie"] ?? 0;
                }
            }
        }

        // Prepare species and counts arrays for each commune for Chart.js

        // Prepare species and counts arrays for Bassila
        $species_labels_bassila = [];
        $species_counts_bassila = [];

        if (isset($species_per_commune_bassila)) {
            $species_labels_bassila = array_keys($species_per_commune_bassila);
            $species_counts_bassila = array_values($species_per_commune_bassila);
        }


        // Prepare species and counts arrays for Zogbodomey
        $species_labels_zogbodomey = [];
        $species_counts_zogbodomey = [];

        if (isset($species_per_commune_zogbodomey)) {
            $species_labels_zogbodomey = array_keys($species_per_commune_zogbodomey);
            $species_counts_zogbodomey = array_values($species_per_commune_zogbodomey);
        }

        view()->share('species_per_commune_bassila', $species_per_commune_bassila);
        view()->share('species_per_commune_zogbodomey', $species_per_commune_zogbodomey);
        view()->share('species_labels_bassila', $species_labels_bassila);
        view()->share('species_counts_bassila', $species_counts_bassila);
        view()->share('species_labels_zogbodomey', $species_labels_zogbodomey);
        view()->share('species_counts_zogbodomey', $species_counts_zogbodomey);

        // Code pour le parametre Location : Indoors et Outdoors

        $locations_mapping = [
            'in' => 'Indoors',
            'out' => 'Outdoors'
        ];
        // Initialize arrays to hold location counts for each commune   
        $location_per_commune_bassila = [];
        $location_per_commune_zogbodomey = [];

        foreach ($locations_mapping as $key => $value) {
            $location_per_commune_bassila[$value] = 0;
            $location_per_commune_zogbodomey[$value] = 0;
        }


        // For Bassila
        foreach ($records_bassila as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {

                $location_per_commune_bassila["Indoors"] += (int) $mosquito_specie_detail["number_indoors"] ?? 0;
                $location_per_commune_bassila["Outdoors"] += (int) $mosquito_specie_detail["number_outdoors"] ?? 0;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $location_per_commune_zogbodomey["Indoors"] += (int) $mosquito_specie_detail["number_indoors"] ?? 0;
                $location_per_commune_zogbodomey["Outdoors"] += (int) $mosquito_specie_detail["number_outdoors"] ?? 0;
            }
        }

        // Prepare locations and counts arrays for each commune for Chart.js
        $location_labels_bassila = [];
        $location_counts_bassila = [];

        if (isset($location_per_commune_bassila)) {
            $location_labels_bassila =  array_keys($location_per_commune_bassila);
            $location_counts_bassila = array_values($location_per_commune_bassila);
        }

        $location_labels_zogbodomey = $locations_mapping;
        $location_counts_zogbodomey = array_fill(0, count($locations_mapping), 0);

        if (isset($location_per_commune_zogbodomey)) {
            $location_labels_zogbodomey = array_keys($location_per_commune_zogbodomey);
            $location_counts_zogbodomey = array_values($location_per_commune_zogbodomey);
        }

        view()->share('location_per_commune_bassila', $location_per_commune_bassila);
        view()->share('location_per_commune_zogbodomey', $location_per_commune_zogbodomey);
        view()->share('location_labels_bassila', $location_labels_bassila);
        view()->share('location_counts_bassila', $location_counts_bassila);
        view()->share('location_labels_zogbodomey', $location_labels_zogbodomey);
        view()->share('location_counts_zogbodomey', $location_counts_zogbodomey);





        //same code for sugar_feeding variable
        $sugar_feeding_mapping = [
            'sugar_fed' => 'Sugar-Fed',
            'sugar_unfed' => 'Sugar-Unfed'
        ];
        // Initialize arrays to hold sugar feeding counts for each commune

        $sugar_feeding_per_commune_bassila = [];
        $sugar_feeding_per_commune_zogbodomey = [];

        foreach ($sugar_feeding_mapping as $key => $value) {
            $sugar_feeding_per_commune_bassila[$value] = 0;
            $sugar_feeding_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $sugar_feeding_per_commune_bassila["Sugar-Fed"] += (int) $mosquito_specie_detail["n_sugar_fed"] ?? 0;
                $sugar_feeding_per_commune_bassila["Sugar-Unfed"] += (int) $mosquito_specie_detail["n_sugar_unfed"] ?? 0;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $sugar_feeding_per_commune_zogbodomey["Sugar-Fed"] += (int) $mosquito_specie_detail["n_sugar_fed"] ?? 0;
                $sugar_feeding_per_commune_zogbodomey["Sugar-Unfed"] += (int) $mosquito_specie_detail["n_sugar_unfed"] ?? 0;
            }
        }

        // Prepare sugar feeding data for each commune for Chart.js


        // Prepare Sugar Feeding data for Bassila
        $sugar_feeding_labels_bassila = [];
        $sugar_feeding_counts_bassila = [];

        if (isset($sugar_feeding_per_commune_bassila)) {
            $sugar_feeding_labels_bassila = array_keys($sugar_feeding_per_commune_bassila);
            $sugar_feeding_counts_bassila = array_values($sugar_feeding_per_commune_bassila);
        }

        // Prepare sugar feeding data for Zogbodomey
        $sugar_feeding_labels_zogbodomey = [];
        $sugar_feeding_counts_zogbodomey = [];

        if (isset($sugar_feeding_per_commune_zogbodomey)) {
            $sugar_feeding_labels_zogbodomey = array_keys($sugar_feeding_per_commune_zogbodomey);
            $sugar_feeding_counts_zogbodomey = array_values($sugar_feeding_per_commune_zogbodomey);
        }

        view()->share('sugar_feeding_per_commune_bassila', $sugar_feeding_per_commune_bassila);
        view()->share('sugar_feeding_per_commune_zogbodomey', $sugar_feeding_per_commune_zogbodomey);
        view()->share('sugar_feeding_labels_bassila', $sugar_feeding_labels_bassila);
        view()->share('sugar_feeding_counts_bassila', $sugar_feeding_counts_bassila);
        view()->share('sugar_feeding_labels_zogbodomey', $sugar_feeding_labels_zogbodomey);
        view()->share('sugar_feeding_counts_zogbodomey', $sugar_feeding_counts_zogbodomey);



        //same code for colour_fed variable
        $colour_sugar_fed = [
            "green" => 'Green',
            "blue" => 'Blue',
            "blue-green" => 'Blue-Green'
        ];

        // Initialize arrays to hold colour counts for each commune
        $colour_per_commune_bassila = [];
        $colour_per_commune_zogbodomey = [];
        foreach ($colour_sugar_fed as $key => $value) {
            $colour_per_commune_bassila[$value] = 0;
            $colour_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $colour_per_commune_bassila["Green"] += (int) $mosquito_specie_detail["n_blue"] ?? 0;
                $colour_per_commune_bassila["Blue"] += (int) $mosquito_specie_detail["n_green"] ?? 0;
                $colour_per_commune_bassila["Blue-Green"] += (int) $mosquito_specie_detail["n_green_blue"] ?? 0;
            }
        }
        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $colour_per_commune_zogbodomey["Green"] += (int) $mosquito_specie_detail["n_blue"] ?? 0;
                $colour_per_commune_zogbodomey["Blue"] += (int) $mosquito_specie_detail["n_green"] ?? 0;
                $colour_per_commune_zogbodomey["Blue-Green"] += (int) $mosquito_specie_detail["n_green_blue"] ?? 0;
            }
        }

        // Prepare Colour data for each commune for Chart.js
        // Prepare Colour data for Bassila
        $colour_labels_bassila = [];
        $colour_counts_bassila = [];
        if (isset($colour_per_commune_bassila)) {
            $colour_labels_bassila = array_keys($colour_per_commune_bassila);
            $colour_counts_bassila = array_values($colour_per_commune_bassila);
        }

        // Prepare Colour data for Zogbodomey
        $colour_labels_zogbodomey = [];
        $colour_counts_zogbodomey = [];
        if (isset($colour_per_commune_zogbodomey)) {
            $colour_labels_zogbodomey = array_keys($colour_per_commune_zogbodomey);
            $colour_counts_zogbodomey = array_values($colour_per_commune_zogbodomey);
        }



        view()->share('colour_per_commune_bassila', $colour_per_commune_bassila);
        view()->share('colour_per_commune_zogbodomey', $colour_per_commune_zogbodomey);
        view()->share('colour_labels_bassila', $colour_labels_bassila);
        view()->share('colour_counts_bassila', $colour_counts_bassila);
        view()->share('colour_labels_zogbodomey', $colour_labels_zogbodomey);
        view()->share('colour_counts_zogbodomey', $colour_counts_zogbodomey);



        //same code for the Feeding Status variable
        $feeding_status_mapping = [
            'blood_fed' => 'Blood-Fed',
            'unfed' => 'Unfed',
            'UNK' => 'Unknown'
        ];

        // Initialize arrays to hold feeding status counts for each commune
        $feeding_status_per_commune_bassila = [];
        $feeding_status_per_commune_zogbodomey = [];
        foreach ($feeding_status_mapping as $key => $value) {
            $feeding_status_per_commune_bassila[$value] = 0;
            $feeding_status_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $feeding_status_per_commune_bassila["Blood-Fed"] += (int) $mosquito_specie_detail["n_bfed"] ?? 0;
                $feeding_status_per_commune_bassila["Unfed"] += (int) $mosquito_specie_detail["n_unfed"] ?? 0;
                $feeding_status_per_commune_bassila["Unknown"] += (int) $mosquito_specie_detail["n_unk_feeding_status"] ?? 0;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $feeding_status_per_commune_zogbodomey["Blood-Fed"] += (int) $mosquito_specie_detail["n_bfed"] ?? 0;
                $feeding_status_per_commune_zogbodomey["Unfed"] += (int) $mosquito_specie_detail["n_unfed"] ?? 0;
                $feeding_status_per_commune_zogbodomey["Unknown"] += (int) $mosquito_specie_detail["n_unk_feeding_status"] ?? 0;
            }
        }
        // Prepare Feeding Status data for each commune for Chart.js
        $feeding_status_labels_bassila = [];
        $feeding_status_counts_bassila = [];
        if (isset($feeding_status_per_commune_bassila)) {
            $feeding_status_labels_bassila = array_keys($feeding_status_per_commune_bassila);
            $feeding_status_counts_bassila = array_values($feeding_status_per_commune_bassila);
        }

        // Prepare Feeding Status data for Zogbodomey
        $feeding_status_labels_zogbodomey = [];
        $feeding_status_counts_zogbodomey = [];
        if (isset($feeding_status_per_commune_zogbodomey)) {
            $feeding_status_labels_zogbodomey = array_keys($feeding_status_per_commune_zogbodomey);
            $feeding_status_counts_zogbodomey = array_values($feeding_status_per_commune_zogbodomey);
        }
        view()->share('feeding_status_per_commune_bassila', $feeding_status_per_commune_bassila);
        view()->share('feeding_status_per_commune_zogbodomey', $feeding_status_per_commune_zogbodomey);
        view()->share('feeding_status_labels_bassila', $feeding_status_labels_bassila);
        view()->share('feeding_status_counts_bassila    ', $feeding_status_counts_bassila);
        view()->share('feeding_status_labels_zogbodomey', $feeding_status_labels_zogbodomey);
        view()->share('feeding_status_counts_zogbodomey', $feeding_status_counts_zogbodomey);


        //same code for gravid_status variable
        $gravid_status_mapping = [
            'not_gravid' => 'Not Gravid',
            'semi_gravid' => 'Semi Gravid',
            'gravid' => 'Gravid',
            'UNK' => 'Unknown'
        ];

        // Initialize arrays to hold gravid status counts for each commune
        $gravid_status_per_commune_bassila = [];
        $gravid_status_per_commune_zogbodomey = [];
        foreach ($gravid_status_mapping as $key => $value) {
            $gravid_status_per_commune_bassila[$value] = 0;
            $gravid_status_per_commune_zogbodomey[$value] = 0;
        }

        // For Bassila
        foreach ($records_bassila as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $gravid_status_per_commune_bassila["Not Gravid"] += (int) $mosquito_specie_detail["n_not_gravid"] ?? 0;
                $gravid_status_per_commune_bassila["Semi Gravid"] += (int) $mosquito_specie_detail["n_semi_gravid"] ?? 0;
                $gravid_status_per_commune_bassila["Gravid"] += (int) $mosquito_specie_detail["n_gravid"] ?? 0;
                $gravid_status_per_commune_bassila["Unknown"] += (int) $mosquito_specie_detail["n_unk_gravid_status"] ?? 0;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {

            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $gravid_status_per_commune_zogbodomey["Not Gravid"] += (int) $mosquito_specie_detail["n_not_gravid"] ?? 0;
                $gravid_status_per_commune_zogbodomey["Semi Gravid"] += (int) $mosquito_specie_detail["n_semi_gravid"] ?? 0;
                $gravid_status_per_commune_zogbodomey["Gravid"] += (int) $mosquito_specie_detail["n_gravid"] ?? 0;
                $gravid_status_per_commune_zogbodomey["Unknown"] += (int) $mosquito_specie_detail["n_unk_gravid_status"] ?? 0;
            }
        }

        // Prepare Gravid Status data for each commune for Chart.js
        $gravid_status_labels_bassila = [];
        $gravid_status_counts_bassila = [];
        if (isset($gravid_status_per_commune_bassila)) {
            $gravid_status_labels_bassila = array_keys($gravid_status_per_commune_bassila);
            $gravid_status_counts_bassila = array_values($gravid_status_per_commune_bassila);
        }
        $gravid_status_labels_zogbodomey = [];
        $gravid_status_counts_zogbodomey = [];
        if (isset($gravid_status_per_commune_zogbodomey)) {
            $gravid_status_labels_zogbodomey = array_keys($gravid_status_per_commune_zogbodomey);
            $gravid_status_counts_zogbodomey = array_values($gravid_status_per_commune_zogbodomey);
        }

        view()->share('gravid_status_per_commune_bassila', $gravid_status_per_commune_bassila);
        view()->share('gravid_status_per_commune_zogbodomey', $gravid_status_per_commune_zogbodomey);
        view()->share('gravid_status_labels_bassila', $gravid_status_labels_bassila);
        view()->share('gravid_status_counts_bassila', $gravid_status_counts_bassila);
        view()->share('gravid_status_labels_zogbodomey', $gravid_status_labels_zogbodomey);
        view()->share('gravid_status_counts_zogbodomey', $gravid_status_counts_zogbodomey);




        //same code for living_status variable
        $living_status_mapping = [
            'live' => 'Live',
            'dead' => 'Dead'
        ];
        // Initialize arrays to hold living status counts for each commune
        $living_status_per_commune_bassila = [];
        $living_status_per_commune_zogbodomey = [];
        foreach ($living_status_mapping as $key => $value) {
            $living_status_per_commune_bassila[$value] = 0;
            $living_status_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $living_status_per_commune_bassila["Live"] += (int) $mosquito_specie_detail["n_live"] ?? 0;
                $living_status_per_commune_bassila["Dead"] += (int) $mosquito_specie_detail["n_dead"] ?? 0;
            }
        }
        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $living_status_per_commune_zogbodomey["Live"] += (int) $mosquito_specie_detail["n_live"] ?? 0;
                $living_status_per_commune_zogbodomey["Dead"] += (int) $mosquito_specie_detail["n_dead"] ?? 0;
            }
        }

        // Prepare Living Status data for each commune for Chart.js
        $living_status_labels_bassila = [];
        $living_status_counts_bassila = [];
        if (isset($living_status_per_commune_bassila)) {
            $living_status_labels_bassila = array_keys($living_status_per_commune_bassila);
            $living_status_counts_bassila = array_values($living_status_per_commune_bassila);
        }
        $living_status_labels_zogbodomey = [];
        $living_status_counts_zogbodomey = [];
        if (isset($living_status_per_commune_zogbodomey)) {
            $living_status_labels_zogbodomey = array_keys($living_status_per_commune_zogbodomey);
            $living_status_counts_zogbodomey = array_values($living_status_per_commune_zogbodomey);
        }


        view()->share('living_status_per_commune_bassila', $living_status_per_commune_bassila);
        view()->share('living_status_per_commune_zogbodomey', $living_status_per_commune_zogbodomey);
        view()->share('living_status_labels_bassila', $living_status_labels_bassila);
        view()->share('living_status_counts_bassila ', $living_status_counts_bassila);
        view()->share('living_status_labels_zogbodomey', $living_status_labels_zogbodomey);
        view()->share('living_status_counts_zogbodomey', $living_status_counts_zogbodomey);


        //same code for the sex_mosquito variable
        $sex_mapping = [
            '1' => 'Male',
            '2' => 'Female',
        ];

        // Initialize arrays to hold sex counts for each commune
        $sex_per_commune_bassila = [];
        $sex_per_commune_zogbodomey = [];
        foreach ($sex_mapping as $key => $value) {
            $sex_per_commune_bassila[$value] = 0;
            $sex_per_commune_zogbodomey[$value] = 0;
        }
        // For Bassila
        foreach ($records_bassila as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $sex_per_commune_bassila["Male"] += (int) $mosquito_specie_detail["n_male"] ?? 0;
                $sex_per_commune_bassila["Female"] += (int) $mosquito_specie_detail["n_female"] ?? 0;
            }
        }

        // For Zogbodomey
        foreach ($records_zogbodomey as $record) {
            $repeated_forms_related = collect($summary_detail_mosquitoes)
                ->where('mosquito_code', $record['mosquito_code'])
                ->where('redcap_repeat_instrument', 'summary_detail_mosquitoes')
                ->values()
                ->toArray();

            foreach ($repeated_forms_related as $key => $mosquito_specie_detail) {
                $sex_per_commune_zogbodomey["Male"] += (int) $mosquito_specie_detail["n_male"] ?? 0;
                $sex_per_commune_zogbodomey["Female"] += (int) $mosquito_specie_detail["n_female"] ?? 0;
            }
        }
        // Prepare Sex data for each commune for Chart.js
        $sex_labels_bassila = [];
        $sex_counts_bassila = [];
        if (isset($sex_per_commune_bassila)) {
            $sex_labels_bassila = array_keys($sex_per_commune_bassila);
            $sex_counts_bassila = array_values($sex_per_commune_bassila);
        }
        $sex_labels_zogbodomey = [];
        $sex_counts_zogbodomey = [];
        if (isset($sex_per_commune_zogbodomey)) {
            $sex_labels_zogbodomey = array_keys($sex_per_commune_zogbodomey);
            $sex_counts_zogbodomey = array_values($sex_per_commune_zogbodomey);
        }


        view()->share('sex_per_commune_bassila', $sex_per_commune_bassila);
        view()->share('sex_per_commune_zogbodomey', $sex_per_commune_zogbodomey);
        view()->share('sex_labels_bassila', $sex_labels_bassila);
        view()->share('sex_counts_bassila', $sex_counts_bassila);
        view()->share('sex_labels_zogbodomey', $sex_labels_zogbodomey);
        view()->share('sex_counts_zogbodomey', $sex_counts_zogbodomey);


        if ($request->ajax()) {
            return response()->json([
                'labels_bar_chart' => $labels_bar_chart,
                'data_bar_chart' => $data_bar_chart,
                'project_title' => $project_title,

                "species_labels_bassila" => $species_labels_bassila,
                "species_counts_bassila" => $species_counts_bassila,
                "species_labels_zogbodomey" => $species_labels_zogbodomey,
                "species_counts_zogbodomey" => $species_counts_zogbodomey,

                "location_labels_bassila" => $location_labels_bassila,
                "location_counts_bassila" => $location_counts_bassila,
                "location_labels_zogbodomey" => $location_labels_zogbodomey,
                "location_counts_zogbodomey" => $location_counts_zogbodomey,

                "sugar_feeding_labels_bassila" => $sugar_feeding_labels_bassila,
                "sugar_feeding_counts_bassila" => $sugar_feeding_counts_bassila,
                "sugar_feeding_labels_zogbodomey" => $sugar_feeding_labels_zogbodomey,
                "sugar_feeding_counts_zogbodomey" => $sugar_feeding_counts_zogbodomey,

                "feeding_status_labels_bassila" => $feeding_status_labels_bassila,
                "feeding_status_counts_bassila" => $feeding_status_counts_bassila,
                "feeding_status_labels_zogbodomey" => $feeding_status_labels_zogbodomey,
                "feeding_status_counts_zogbodomey" => $feeding_status_counts_zogbodomey,

                "colour_labels_bassila" => $colour_labels_bassila,
                "colour_counts_bassila" => $colour_counts_bassila,
                "colour_labels_zogbodomey" => $colour_labels_zogbodomey,
                "colour_counts_zogbodomey" => $colour_counts_zogbodomey,

                "gravid_status_labels_bassila" => $gravid_status_labels_bassila,
                "gravid_status_counts_bassila" => $gravid_status_counts_bassila,
                "gravid_status_labels_zogbodomey" => $gravid_status_labels_zogbodomey,
                "gravid_status_counts_zogbodomey" => $gravid_status_counts_zogbodomey,

                "living_status_labels_bassila" => $living_status_labels_bassila,
                "living_status_counts_bassila" => $living_status_counts_bassila,
                "living_status_labels_zogbodomey" => $living_status_labels_zogbodomey,
                "living_status_counts_zogbodomey" => $living_status_counts_zogbodomey,

                "sex_labels_bassila" => $sex_labels_bassila,
                "sex_counts_bassila" => $sex_counts_bassila,
                "sex_labels_zogbodomey" => $sex_labels_zogbodomey,
                "sex_counts_zogbodomey" => $sex_counts_zogbodomey,

            ]);
        } else {

            return view('interface-accueil-all-mosquitoes-final', compact(
                'redcapData',
                'total_records',
                'total_records_bassila',
                'total_records_zogbodomey',
                'records_per_date_tablet',
                'project_title'
            ));
        }
    }



    public function pullQueriesDataREDCapBaseline(Request $request)
    {


        $projectId = $request->input('project_id');

        // Set your REDCap API URL and token (replace with your actual values)
        $apiUrl = 'https://redcap.airid-africa.com/api/';
        $apiToken = ''; // You may want to store this securely and retrieve based on $projectId
        $project_title = "";

        // Example: Retrieve API token based on project ID (implement your own logic)
        switch ($projectId) {
            case 31:
                $apiToken = '70D93561C9F0BAE16AE756A2D320A3BD';
                $project_title = "ATSB An. Gambiae Baseline";
                break;
            case 35:
                $apiToken = 'B58508382C6CF09A4CFD97A14643A44D';
                $project_title = "ATSB Other Species Baseline";
                break;
            case 38:
                $apiToken = '4400659EB9A8B164FF3E7D451930BCAC';
                $project_title = "ATSB An. Gambiae FINAL";
                break;
            case 40:
                $apiToken = 'C24E253F1A6E64982873F6E5A3F50D30';
                $project_title = "ATSB ALL MOSQUITOES FINAL";
                break;
            // Add more cases as needed
            default:
                abort(400, 'Invalid project ID');
        }

        $metadataParams = [
            'token' => $apiToken,
            'content' => 'metadata',
            'format' => 'json',
            'returnFormat' => 'json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($metadataParams, '', '&'));
        $metadataResponse = curl_exec($ch);

        $metadata = json_decode($metadataResponse, true);


        $dataParams = [
            'token' => $apiToken,
            'content' => 'record',
            'format' => 'json',
            'type' => 'flat',
            'rawOrLabel' => 'raw',
            'returnFormat' => 'json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dataParams, '', '&'));
        $output = curl_exec($ch);



        $records = json_decode($output, true);




        $issues = [];

        $issues = [];

        foreach ($records as $record) {
            foreach ($metadata as $field) {
                $fieldName = $field['field_name'];
                $value = $record[$fieldName] ?? '';

                // --- 1️⃣ Evaluate Branching Logic ---
                $branchingPass = true;
                if (!empty($field['branching_logic'])) {
                    $logic = strtolower($field['branching_logic']); // normalize case

                    // Extract conditions
                    preg_match_all(
                        '/\[(.*?)\]\s*(=|!=|>=|<=|>|<)\s*(\'[^\']*\'|\"[^\"]*\"|\d+(\.\d+)?)/i',
                        $logic,
                        $matches,
                        PREG_SET_ORDER
                    );

                    $conditionsResults = [];
                    foreach ($matches as $cond) {
                        $depField = $cond[1];
                        $operator = $cond[2];
                        $rawValue = trim($cond[3], '\'"'); // remove quotes if any
                        $recordValue = $record[$depField] ?? '';

                        // Compare values
                        switch ($operator) {
                            case '=':
                                $conditionsResults[] = ($recordValue == $rawValue);
                                break;
                            case '!=':
                                $conditionsResults[] = ($recordValue != $rawValue);
                                break;
                            case '>':
                                $conditionsResults[] = ($recordValue > $rawValue);
                                break;
                            case '<':
                                $conditionsResults[] = ($recordValue < $rawValue);
                                break;
                            case '>=':
                                $conditionsResults[] = ($recordValue >= $rawValue);
                                break;
                            case '<=':
                                $conditionsResults[] = ($recordValue <= $rawValue);
                                break;
                        }
                    }

                    // Determine overall branchingPass based on logical operators in original string
                    $tokens = preg_split('/\s+(and|or)\s+/i', $logic, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

                    // Replace condition placeholders with actual boolean results
                    $condIndex = 0;
                    foreach ($tokens as &$token) {
                        $trimToken = trim($token);
                        if (!in_array($trimToken, ['and', 'or'])) {
                            $token = $conditionsResults[$condIndex++] ? 'true' : 'false';
                        }
                    }

                    // Evaluate logical expression
                    $expr = implode(' ', $tokens);
                    $expr = str_replace(['and', 'or'], ['&&', '||'], $expr);
                    $branchingPass = eval("return {$expr};");
                }

                // --- 2️⃣ Required Field Check ---
                if ($branchingPass && $field['required_field'] === 'y') {
                    if ($value === '') {
                        $issues[] = "Record {$record['mosquito_code']}: Missing required field '$fieldName' (branching logic met)";
                    } else {
                        // --- 3️⃣ Data Validation Check ---
                        if (!empty($field['text_validation_type_or_show_slider_number'])) {
                            $validationType = $field['text_validation_type_or_show_slider_number'];

                            if ($validationType === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $issues[] = "Record {$record['mosquito_code']}: Invalid email in '$fieldName'";
                            }
                            if ($validationType === 'integer' && !ctype_digit($value)) {
                                $issues[] = "Record {$record['mosquito_code']}: Non-integer value in '$fieldName'";
                            }
                            if ($validationType === 'date_ymd' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                $issues[] = "Record {$record['mosquito_code']}: Invalid date format in '$fieldName'";
                            }
                        }
                    }
                }
            }
        }






        //share the issues with the view
        view()->share('issues', $issues);

        return view('page-queries-baseline', compact(
            'records',
            'metadata',
            'project_title',
            "issues"
        ));
    }
}
