<?php

namespace App\Service;

use Monolog\Logger;

class GSheets
{

    private $logger;
    private $service;
    private $spreadsheetId;

    public function __construct( Logger $logger,string $doc_id,string $credentials)
    {
        $this->logger = $logger;

        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets and PHP');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig(__DIR__ . '/../../'.$credentials);
        $this->service = new \Google_Service_Sheets($client);

        $this->spreadsheetId = $doc_id;
    }

    public function update($class_name,$data){
        $entity_name = $this->cleanPageName($class_name);
        $update_range = ucwords(strtolower($entity_name."s"));
        $body = new \Google_Service_Sheets_ValueRange(['values' => $data]);
        $params = ['valueInputOption' => 'RAW'];
        $this->createPageIfMissing($update_range);
        $update_sheet = $this->service->spreadsheets_values->update($this->spreadsheetId, $update_range, $body, $params);
    }

    private function createPageIfMissing($page){
        $sheetInfo = $this->service->spreadsheets->get($this->spreadsheetId);
        $all_sheet_info = $sheetInfo['sheets'];
        $idCats = array_column($all_sheet_info, 'properties');
        if (!$this->myArrayContainsWord($idCats, $page)) {
            $bodyBatchUpdate = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
                'requests' => array(
                    'addSheet' => array(
                        'properties' => array(
                            'title' => $page
                        )
                    )
                )
            ));
            $result1 = $this->service->spreadsheets->batchUpdate($this->spreadsheetId,$bodyBatchUpdate);
        }
    }

    private function myArrayContainsWord($myArray, $word){
        foreach ($myArray as $element) {
            if ($element->title == $word) {
                return true;
            }
        }
        return false;
    }

    protected function cleanPageName($className){
        $exploded = explode('\\',$className);
        if (is_array($exploded))
            return array_pop($exploded);
        return $exploded;
    }

}