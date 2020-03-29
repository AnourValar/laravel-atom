## Usage

### Action after transaction commit
```php
Atom::onCommit(function ()
{
    dispatch(new Job());
});
```


### Action after transaction rollback
```php
Atom::onRollback(function ()
{
    Storage::delete('file.jpg');
});
```


### Pessimistic lock
```php
Atom::lock('user');
```

```php
Atom::lockUser($user->id); // equal to: Atom::lock('user', $user->id);
```
