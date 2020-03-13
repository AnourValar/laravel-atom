## Usage

### Action after transaction commit
```php
LaravelAtom::onCommit(function ()
{
    dispatch(new Job());
});
```

### Action after transaction rollback
```php
LaravelAtom::onRollback(function ()
{
    Storage::delete('file.jpg');
});
```
