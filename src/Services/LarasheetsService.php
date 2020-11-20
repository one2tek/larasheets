<?php

namespace one2tek\larasheets\Services;

use Google_Client;
use Google_Service_Sheets;
use Revolution\Google\Sheets\Sheets;

class LarasheetsService
{
    private $sheets;
    private $client;
    private $spreadsheetId;
    private $sheetName;

    public function __construct($spreadsheetId, $sheetName)
    {
        $this->sheets = new Sheets;
        $this->client = new Google_Client(config('larasheets'));

        $this->client->setAuthConfig(base_path(config('larasheets.service.file')));
        $this->client->setScopes([Google_Service_Sheets::DRIVE, Google_Service_Sheets::SPREADSHEETS]);

        $service = new Google_Service_Sheets($this->client);
        $this->sheets->setService($service);

        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
    }

    public function getAll()
    {
        $sheetRows = $this->sheets->spreadsheet($this->spreadsheetId)->sheet($this->sheetName)->get();
        $headers = $sheetRows->pull(0);
        $allRows = [];

        foreach ($sheetRows as $key => $value) {
            $values['line'] = ($key + 1);
            foreach ($headers as $f => $header) {
                $values[$header] = $value[$f];
            }
            
            $allRows[] = $values;
        }

        return $allRows;
    }

    public function updateByLine($line, $data)
    {
        $cell = $this->sheets->spreadsheet($this->spreadsheetId)->sheet($this->sheetName)->range('A'. $line)->update([$data]);

        return true;
    }

    public function create($data)
    {
        $cell = $this->sheets->spreadsheet($this->spreadsheetId)->sheet($this->sheetName)->append([$data]);

        return true;
    }
}
