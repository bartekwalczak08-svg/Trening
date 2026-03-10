# Testing Notes

- `tests/unit/models/ContactFormTest.php` contains DB-dependent `testEmailIsSentOnContact` (requires `yii2basic_test` database).
- Quick blacklist-only run:

```powershell
vendor\bin\codecept run unit tests\unit\models\ContactFormTest.php --filter Blacklist
```
