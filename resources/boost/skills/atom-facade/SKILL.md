---
name: laravel-atom
description: Use this skill whenever you encounter the static \Atom class (facade) in the codebase.
---

# Laravel Atom: Atom class


## When to use this skill

Use this skill whenever you encounter the static \Atom class (facade) in the codebase.


## Features

- Atom::lock() and Atom::lock<pattern>() implement a pessimistic lock by key to prevent race conditions.

- Atom::beforeCommit() registers a hook executed before a DB transaction commit.

- Atom::onCommit() registers a hook executed after a DB transaction is successfully committed.

- Atom::onRollBack() registers a hook executed after a DB transaction rollback.
