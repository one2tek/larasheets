### Introduction

**Larasheets** is a package thats offers you to connect with Google Sheets via API in Laravel.

### Installation

Follow the steps below to install the package.


**Composer**

```
composer require one2tek/larasheets
```

**Copy Config**

Run `php artisan vendor:publish --provider="one2tek\larasheets\Providers\LaravelServiceProvider"` to publish the `larasheets.php` config file.

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

### Cache

`Larasheets` also support cache system, you just need to to configure at `config/larasheets.php`.

```
laravel_cache' => [
    'enable' => true,
    'driver' => 'file',
    'remember_in_seconds' => 600
]
```

### Usage

Follow the steps below to find how to use the package.

```php
<?php

use one2tek\larasheets\Services\LarasheetsService;

class GoogleSheetService
{
    private $larasheetsService;

    public function __construct()
    {
        $spreadsheetId = 'spreadsheet-id-from-console';
        $sheetName = 'sheet-name-from-console';
        $headers = ['Column1', 'Column2', 'Column3'];

        $this->larasheetsService = new LarasheetsService($spreadsheetId, $sheetName, $headers);
    }

    public function getAll()
    {
        return $this->larasheetsService->getAll();
    }

    public function getByLine($line)
    {
        return $this->larasheetsService->getByLine($line);
    }

    public function update($line, $data)
    {
        $data = [$data['column1'], $data['column2'], $data['column3']];
        
        return $this->larasheetsService->updateByLine($line, $data);
    }

    public function create($data)
    {
        $data = [$data['column1'], $data['column2'], $data['column3']];
       
        return $this->larasheetsService->create($data);
    }
}
```
