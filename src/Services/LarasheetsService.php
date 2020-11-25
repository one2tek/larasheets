<?php

namespace one2tek\larasheets\Services;

use Google_Client;
use Google_Service_Sheets;
use Revolution\Google\Sheets\Sheets;
use Illuminate\Support\Facades\Cache;

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

    public function getAll($except = [])
    {
        if ($this->isCacheEnabled()) {
            if ($this->isFileInCache()) {
                $data = $this->getFileInCache();

                if (!count($except)) {
                    return $data;
                }

                return $this->parseData($data, $except);
            }
        }

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

        if (!$this->isCacheEnabled()) {
            return $allRows;
        }

        return $this->addFileInCache($allRows);
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

    public function getByLine(int $line)
    {
        if ($this->isCacheEnabled()) {
            return $this->getFileInCache()[$line - 1];
        }

        $cell = 'A'. $line. ':'. chr(64 + count($this->headers)). $line;

        $sheetRow = $this->sheets->spreadsheet($this->spreadsheetId)->range($cell)->sheet($this->sheetName)->first();
        $row = [];

        $row['line'] = $line;
        foreach ($this->headers as $key => $header) {
            $row[$header] = $sheetRow[$key];
        }

        return $row;
    }

    public function updateByLine(int $line, $data)
    {
        $cell = $this->sheets->spreadsheet($this->spreadsheetId)->sheet($this->sheetName)->range('A'. $line)->update([$data]);

        $this->removeFileInCache();

        return true;
    }

    public function create($data)
    {
        $cell = $this->sheets->spreadsheet($this->spreadsheetId)->sheet($this->sheetName)->append([$data]);

        $this->removeFileInCache();

        return true;
    }

    public function isCacheEnabled()
    {
        return config('larasheets.laravel_cache.enable');
    }

    public function isFileInCache()
    {
        return Cache::store(config('larasheets.laravel_cache.driver'))->has('larasheets.'. $this->spreadsheetId. $this->sheetName);
    }

    public function getFileInCache()
    {
        return Cache::store(config('larasheets.laravel_cache.driver'))->get('larasheets.'. $this->spreadsheetId. $this->sheetName);
    }

    public function removeFileInCache()
    {
        return Cache::store(config('larasheets.laravel_cache.driver'))->forget('larasheets.'. $this->spreadsheetId. $this->sheetName);
    }

    public function addFileInCache($allRows)
    {
        $key = 'larasheets.'. $this->spreadsheetId. $this->sheetName;

        $closure = function () use ($allRows) {
            return $allRows;
        };

        if (config('larasheets.laravel_cache.remember_forever')) {
            return Cache::store(config('larasheets.laravel_cache.driver'))->rememberForever($key, $closure);
        }

        return Cache::store(config('larasheets.laravel_cache.driver'))->remember($key, config('larasheets.laravel_cache.remember_in_seconds'), $closure);
    }

    public function parseData($data, $except)
    {
        $collection = collect($data);

        $collection = $collection->map(function ($item, $key) use ($except) {
            return collect($item)->except($except);
        });

        return $collection;
    }
}
