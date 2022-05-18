# ATT Base Framework
Dokumentasi sementara dari base framework ATT.

# Cara Install
Tambahkan `script` dibawah ini `composer.json`
```javascript
{
  "data": {
    "id": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "johndoe@email.com",
    "created_at": "2021-12-09T08:38:26.000000Z",
    "updated_at": "2021-12-09T08:38:26.000000Z"
  }
}
```

## # Route
Base route yang tersedia diantaranya: `index`, `table`, `show`, `store`, `update`, `delete`, `lov`, `dictionary`.
|Nama|URL|Method|Deskripsi|
|----|----|:----:|----|
|Index|`/`|**GET**|Menampilkan seluruh data
|Table|`/table`|**POST**|Menampilkan seluruh data dengan kemampuan sort dan filter
|Show|`/{id}`|**GET**|Menampilkan data yang dipilih berdasarkan id
|Store|`/`|**POST**|Menyimpan data
|Update|`/{id}`|**PUT**|Mengupdate data yang dipilih berdasarkan id
|Delete|`/{id}`|**DELETE**|Menghapus data yang dipilih berdasarkan id
|LOV|`/lov`|**GET**|Menampilkan seluruh data untuk list of values
|Dictionary|`/detail/dictionary`|**GET**|Menampilkan data dictionary untuk model yang dipilih

## # Menampilkan Data

Secara default, data dari route `index`, `show`, `lov` akan menampilkan semua kolom yang tersedia dari sebuah model. Berikut adalah contoh dari data yang didapatkan dari route `show`:
```javascript
{
  "data": {
    "id": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "johndoe@email.com",
    "created_at": "2021-12-09T08:38:26.000000Z",
    "updated_at": "2021-12-09T08:38:26.000000Z"
  }
}
```
Kolom-kolom yang ingin ditampilkan dapat dipilih dengan mengirimkan kunci `fields` pada saat pengambilan data.\
Request:
```javascript
{
  "fields": ["firstname", "lastname"]
}
```
Response:
```javascript
{
  "data": {
    "firstname": "John",
    "lastname": "Doe"
  }
}
```
> **Catatan:** Untuk menggunakan fitur ini, nama kolom dan tipe data harus dideklarasikan di model.

```php
<?php

namespace App\Models;

use App\Att\AttModel;
use Illuminate\Database\Eloquent\Model;
use App\Att\Interfaces\ModelDictionary;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
  use HasFactory, AttModel;

  // Deklarasi kolom yang tersedia
  protected $modelFields = [
    ['name' => 'id', 'type' => ModelDictionary::COLUMN_TYPE_INTEGER],
    ['name' => 'firstname', 'type' => ModelDictionary::COLUMN_TYPE_STRING],
    ['name' => 'lastname', 'type' => ModelDictionary::COLUMN_TYPE_STRING],
  ];
}
```
> **Catatan:** Untuk route `lov`, data default yang ditampilkan dapat diubah dengan mendeklarasikan `$lovFields` pada model. Kemudian kolom dapat ditambahkan menggunakan kunci `fields` seperti keterangan di atas.

```php
<?php

namespace App\Models;

use App\Att\AttModel;
use Illuminate\Database\Eloquent\Model;
use App\Att\Interfaces\ModelDictionary;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
  use HasFactory, AttModel;

  // Data default yang akan dikirim untuk route lov
  protected $lovFields = [
    'id',
    'firstname'
  ];
}
```
## # Memfilter Data

Data dari route `index`, `lov` dapat difilter dengan kunci `filters`  dan menentukan kolom, operator, dan nilai dari data yang akan dicari. Seperti contoh, untuk mencari user yang memiliki nama `John` kita dapat mengirim data berupa:
```javascript
{
  "filters": [
    {
      "column": "name",   // Nama kolom yang akan dicari
      "operator": "like", // SQL operator
      "value": "%john%"   // Nilai yang akan dicari
    }
  ]
}
```
> **Catatan:** Untuk menggunakan fitur ini, nama kolom dan tipe data harus dideklarasikan di model.

```php
<?php

namespace App\Models;

use App\Att\AttModel;
use Illuminate\Database\Eloquent\Model;
use App\Att\Interfaces\ModelDictionary;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
  use HasFactory, AttModel;

  // Deklarasi kolom yang tersedia
  protected $modelFields = [
    ['name' => 'id', 'type' => ModelDictionary::COLUMN_TYPE_INTEGER],
    ['name' => 'name', 'type' => ModelDictionary::COLUMN_TYPE_STRING]
  ];
}
```

### # Filter Berdasarkan Relasi
Data juga dapat difilter berdasarkan relasi dengan menggunakan simbol titik (`.`). Seperti contoh, untuk mencari user yang memiliki role dengan nama `Admin` kita dapat mengirim data berupa:
```javascript
{
  "filters": [
    {
      "column": "roles.name", // Relasi dan kolom yang akan dicari
      "operator": "=",        // SQL operator
      "value": "Admin"        // Nilai yang akan dicari
    }
  ]
}
```
> **Catatan:** Untuk menggunakan fitur ini, nama relasi harus dideklarasikan pada model, dan nama kolom dan tipe data harus dideklarasikan di model relasi seperti penjelasan di atas.

```php
<?php

namespace App\Models;

use App\Att\AttModel;
use Illuminate\Database\Eloquent\Model;
use App\Att\Interfaces\ModelDictionary;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
  use HasFactory, AttModel;

  // Deklarasi relasi yang tersedia
  protected $modelRelations = [
    'roles'
  ];

  public function roles()
  {
    // Relasi roles
  }
}
```

## # Menampilkan Relasi
Relasi yang ingin ditampilkan dapat dipilih dengan menambahkan field pada saat pengambilan data.\
Request:
```javascript
{
  "fields": ["*", "roles.*"]
}
```
Response:
```javascript
{
  "data": {
    "id": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "johndoe@email.com",
    "created_at": "2021-12-09T08:38:26.000000Z",
    "updated_at": "2021-12-09T08:38:26.000000Z",
    "roles": [
      {
        "id": 1,
        "name": "Admin"
      }
    ]
  }
}
```
