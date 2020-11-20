
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
    private $headers;

    public function __construct($spreadsheetId, $sheetName, $headers)
    {
        $this->sheets = new Sheets;
        $this->client = new Google_Client(config('larasheets'));

        $this->client->setAuthConfig(base_path(config('larasheets.service.file')));
        $this->client->setScopes([Google_Service_Sheets::DRIVE, Google_Service_Sheets::SPREADSHEETS]);

        $service = new Google_Service_Sheets($this->client);
        $this->sheets->setService($service);

        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
        $this->headers = $headers;
    }

    public function getAll()
    {
        $sheetRows = $this->sheets->spreadsheet($this->spreadsheetId)->sheet($this->sheetName)->get();

        $allRows = [];

        if (count($sheetRows) == 0) {
            return [];
        }

        foreach ($sheetRows as $key => $value) {
            $values['line'] = ($key + 1);
            foreach ($value as $key => $val) {
                $values[$this->headers[$key] ?? '(No Header)'] = $val;
            }
            
            $allRows[] = $values;
        }

        return $allRows;
    }

    public function getByRange($range)
    {
        $sheetRows = $this->sheets->spreadsheet($this->spreadsheetId)->range($range)->sheet($this->sheetName)->get();
        $allRows = [];

        if (count($sheetRows) == 0) {
            return [];
        }

        foreach ($sheetRows as $value) {
            foreach ($value as $key => $val) {
                $values[$this->headers[$key] ?? '(No Header)'] = $val;
            }
            
            $allRows[] = $values;
        }

        return $allRows;
    }

    public function getByLine($line)
    {
        $cell = 'A'. $line. ':'. chr(64 + count($this->headers)). $line;

        $sheetRow = $this->sheets->spreadsheet($this->spreadsheetId)->range($cell)->sheet($this->sheetName)->first();
        $row = [];

        $row['line'] = $line;
        foreach ($this->headers as $key => $header) {
            $row[$header] = $sheetRow[$key];
        }

        return $row;
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
