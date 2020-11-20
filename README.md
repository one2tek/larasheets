### Introduction

**Larasheets** is a package thats offers you to connect with Google Sheets via API in Laravel.

## Installation

Follow the steps below to install the package

**Composer**

```
composer require one2tek/larasheets
```

**Copy Config**

Run `php artisan vendor:publish --provider="one2tek\larasheets\Providers\LaravelServiceProvider" --tag="config"` to publish the google config file.

**Get API Credentials**

Get API Credentials from https://developers.google.com/console
Enable Google Sheets API, Google Drive API.

**Configure .env as needed**

```
GOOGLE_APPLICATION_NAME=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT=
GOOGLE_DEVELOPER_KEY=
GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION=
```


## Usage

Follow the steps below to find how to use the package

```
<?php

use one2tek\larasheets\Services\LarasheetsService;

class GoogleSheetService
{
    private $googleSpreadsheetService;

    public function __construct()
    {
        $spreadsheetId = '..';
        $sheetName = '...';

        $this->googleSpreadsheetService = new GoogleSpreadsheetService($spreadsheetId, $sheetName);
    }

    public function getAll()
    {
        return $this->googleSpreadsheetService->getAll();
    }

    public function update($line, $data)
    {
        $data = [$data['key'], $data['en'], $data['es']];
        
        return $this->googleSpreadsheetService->updateByLine($line, $data);
    }

    public function create($data)
    {
        $data = [$data['key'], $data['en'], $data['es']];
       
        return $this->googleSpreadsheetService->create($data);
    }
}
```
