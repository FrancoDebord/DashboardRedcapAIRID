<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccueilProjectController extends Controller
{

    public function __construct()
    {
        if (!$this->isInternetAvailable()) {
            abort(503, 'Internet connection is required.');
        }
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
            return $record['commune'] === 'B';
        }));
        $total_records_zogbodomey = count(array_filter($redcapData, function ($record) {
            return $record['commune'] === 'Z';
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
            return isset($record['commune']) && $record['commune'] === 'B';
        });

        $records_zogbodomey = array_filter($redcapData, function ($record) {
            return isset($record['commune']) && $record['commune'] === 'Z';
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



        if ($request->ajax()) {
            return response()->json([
                'labels_bar_chart' => $labels_bar_chart,
                'data_bar_chart' => $data_bar_chart,
                'project_title' => $project_title,
                "species_labels_bassila" => $species_labels_bassila,
                "species_counts_bassila" => $species_counts_bassila,
                "species_labels_zogbodomey" => $species_labels_zogbodomey,
                "species_counts_zogbodomey" => $species_counts_zogbodomey
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
}
