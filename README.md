# Laravel ACID API

## Installation

```bash
composer require anourvalar/laravel-atom
```


## Usage

### Action after transaction commit
```php
Atom::onCommit(function () {
    dispatch(new Job());
});
```


### Action after transaction rollBack
```php
Atom::onRollBack(function () {
    Storage::delete('file.jpg');
});
```


### Pessimistic lock
```php
Atom::lock('user');
```

```php
Atom::lockUser($user->id); // equals to: Atom::lock('user', $user->id);
```


### Optimistic lock
```php
try {
    Atom::strategy('optimistic_transaction')->lock('week_report');
} catch (\AnourValar\LaravelAtom\Exceptions\OptimisticTransactionException $e) {
    // already in progress..
}
```
