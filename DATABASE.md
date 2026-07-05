# Database Design — E-Commerce Application

> **Companion document to [`PLAN.md`](./PLAN.md).**
> Every module in `PLAN.md` references tables defined here. Before
> implementing any module's "Database" section, check this file for the
> authoritative column list, types, constraints, and relationships so
> migrations/models stay consistent across modules. If a module needs a
> column not listed here, add it here first, then implement — this file is
> the single source of truth for schema, `PLAN.md` is the source of truth for
> build order and feature scope.

Engine: **MySQL 8**, `InnoDB`, `utf8mb4_unicode_ci`.
Conventions: every table has `id` (unsigned bigint, PK), `created_at`/
`updated_at`; soft-deletable tables also get `deleted_at`. Money columns are
`decimal(10,2)`, storing plain **BDT (Bangladeshi Taka, ৳)** amounts — see
`PLAN.md` §3 Currency; there is no multi-currency support or minor-unit
conversion. All foreign keys are indexed and `ON DELETE` behavior is
specified per relationship below.

## 1. Entity Relationship Overview

```
users ──1:1── profiles
users ──1:N── addresses
users ──1:N── carts
users ──1:N── orders

categories ──1:N── categories (self, parent_id)
categories ──1:N── products

products ──1:N── product_images
products ──1:N── cart_items
products ──1:N── order_items

carts ──1:N── cart_items

orders ──1:N── order_items
orders ──1:N── payments
orders ──1:N── order_status_histories
```

## 2. Tables

### 2.1 `users` *(Module 1, extends Laravel default)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| name | varchar(255) | not null | |
| email | varchar(255) | unique, not null | |
| email_verified_at | timestamp | nullable | |
| password | varchar(255) | not null | |
| role | enum('customer','admin') | not null, default 'customer' | Module 1 |
| phone | varchar(20) | nullable | Module 1 |
| is_blocked | boolean | not null, default false | Module 9 |
| remember_token | varchar(100) | nullable | Breeze default |
| created_at / updated_at | timestamp | | |

### 2.2 `profiles` *(Module 6)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK → users.id, unique, cascade on delete | 1:1 |
| avatar_path | varchar(255) | nullable | storage disk path |
| bio | text | nullable | |
| date_of_birth | date | nullable | |
| gender | enum('male','female','other') | nullable | |
| created_at / updated_at | timestamp | | |

### 2.3 `addresses` *(Module 6)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK → users.id, cascade on delete | |
| label | varchar(50) | not null | e.g. "Home", "Office" |
| line1 | varchar(255) | not null | |
| line2 | varchar(255) | nullable | |
| city | varchar(100) | not null | |
| state | varchar(100) | not null | |
| postal_code | varchar(20) | not null | |
| country | varchar(100) | not null | |
| is_default | boolean | not null, default false | only one true per user (enforced in `AddressService`/controller, not DB) |
| created_at / updated_at | timestamp | | |

### 2.4 `categories` *(Module 2)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| parent_id | bigint unsigned | FK → categories.id, nullable, null on delete | self-referencing |
| name | varchar(150) | not null | |
| slug | varchar(170) | unique, not null | auto-generated from name |
| is_active | boolean | not null, default true | |
| created_at / updated_at / deleted_at | timestamp | soft deletes | |

### 2.5 `products` *(Module 3)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| category_id | bigint unsigned | FK → categories.id, restrict on delete | |
| name | varchar(255) | not null | |
| slug | varchar(275) | unique, not null | |
| description | text | nullable | |
| price | decimal(10,2) | not null | |
| sale_price | decimal(10,2) | nullable | if set and < price, shown as discount |
| sku | varchar(64) | unique, not null | |
| stock_qty | int unsigned | not null, default 0 | |
| low_stock_threshold | int unsigned | not null, default 5 | Module 3 restock trigger |
| is_active | boolean | not null, default true | |
| created_at / updated_at / deleted_at | timestamp | soft deletes | |

### 2.6 `product_images` *(Module 3)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| product_id | bigint unsigned | FK → products.id, cascade on delete | |
| path | varchar(255) | not null | storage disk path |
| sort_order | smallint unsigned | not null, default 0 | |
| created_at / updated_at | timestamp | | |

### 2.7 `carts` *(Module 5)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK → users.id, nullable, cascade on delete | null = guest cart (Redis-backed instead; this row is only for authenticated carts) |
| session_id | varchar(100) | nullable, index | used only if a DB-backed guest fallback is needed |
| created_at / updated_at | timestamp | | |

### 2.8 `cart_items` *(Module 5)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| cart_id | bigint unsigned | FK → carts.id, cascade on delete | |
| product_id | bigint unsigned | FK → products.id, cascade on delete | |
| qty | int unsigned | not null, default 1 | |
| price_snapshot | decimal(10,2) | not null | price at time of adding |
| created_at / updated_at | timestamp | | |

> Guest carts live in **Redis** as `cart:{session_id}` (JSON-encoded item
> list), not in these tables — see `PLAN.md` §4 Redis Usage Map. These tables
> are only populated once a cart belongs to an authenticated `user_id`.

### 2.9 `orders` *(Module 7)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK → users.id, restrict on delete | |
| order_number | varchar(32) | unique, not null | human-readable, e.g. `ORD-20260705-0001` |
| status | enum('pending','processing','shipped','delivered','cancelled') | not null, default 'pending' | |
| subtotal | decimal(10,2) | not null | |
| tax | decimal(10,2) | not null, default 0 | |
| shipping | decimal(10,2) | not null, default 0 | |
| total | decimal(10,2) | not null | |
| shipping_address | json | not null | snapshot of `addresses` row at order time |
| created_at / updated_at / deleted_at | timestamp | soft deletes | |

### 2.10 `order_items` *(Module 7)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| order_id | bigint unsigned | FK → orders.id, cascade on delete | |
| product_id | bigint unsigned | FK → products.id, restrict on delete | keep history even if product later deleted (soft delete handles this) |
| qty | int unsigned | not null | |
| unit_price | decimal(10,2) | not null | price snapshot at purchase time |
| created_at / updated_at | timestamp | | |

### 2.11 `payments` *(Module 7, extended Module 10)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| order_id | bigint unsigned | FK → orders.id, cascade on delete | |
| gateway | varchar(50) | not null, default 'sslcommerz' | |
| transaction_id | varchar(100) | unique, not null | SSLCommerz `tran_id` |
| val_id | varchar(100) | nullable | SSLCommerz `val_id`, used for Validation API |
| amount | decimal(10,2) | not null | |
| status | enum('initiated','paid','failed','cancelled') | not null, default 'initiated' | |
| gateway_response | json | nullable | raw IPN/validation payload, Module 10 admin detail view |
| paid_at | timestamp | nullable | |
| created_at / updated_at | timestamp | | |

### 2.12 `order_status_histories` *(Module 7)*

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK | |
| order_id | bigint unsigned | FK → orders.id, cascade on delete | |
| status | enum('pending','processing','shipped','delivered','cancelled') | not null | |
| note | varchar(255) | nullable | e.g. "Payment confirmed via SSLCommerz" |
| created_at | timestamp | no `updated_at` (append-only log) | |

## 3. Cross-Module Integration Notes

- **Module 1 → 9**: `users.is_blocked` is added by Module 9's migration but
  gates login logic that Module 1's auth flow must respect — when
  implementing Module 1, leave a hook point (e.g. check in the login
  `FormRequest` or a listener) even though the column doesn't exist until
  Module 9, OR add `is_blocked` to the Module 1 migration directly instead of
  a later one. **Decision: add `is_blocked` in Module 1's migration** to
  avoid a second ALTER TABLE later. `PLAN.md` Module 9 should be read as "add
  the blocking *behavior*", not the column.
- **Module 5 → 7**: `cart_items.price_snapshot` becomes `order_items.unit_price`
  when `OrderService::createFromCart()` runs — always copy, never
  re-look-up `products.price` at order time (price may have changed).
- **Module 6 → 7**: `orders.shipping_address` is a JSON snapshot of the
  selected `addresses` row, not a foreign key — so later edits/deletes of an
  address never alter historical orders.
- **Module 3 → 7**: `OrderService::createFromCart()` must decrement
  `products.stock_qty` via `StockService::decrement()` (Module 3) inside the
  same DB transaction that creates the order — never adjust stock outside a
  transaction boundary.
- **Module 7 → 10**: `payments.gateway_response` (json) is written by the
  IPN/validation handlers in Module 7 and only *read* by Module 10's admin
  view — don't duplicate storage elsewhere.

## 4. Migration File Order

Migrations must be created in this order (respects FK dependencies):

1. `users` (extend default migration — Module 1)
2. `categories` (Module 2)
3. `products`, `product_images` (Module 3)
4. `profiles`, `addresses` (Module 6)
5. `carts`, `cart_items` (Module 5)
6. `orders`, `order_items`, `payments`, `order_status_histories` (Module 7)

> Note: this migration order differs slightly from the module build order in
> `PLAN.md` (Module 5/Cart and Module 6/Profile) because `orders` needs both
> `carts` and `addresses` to exist first. When actually running migrations,
> follow this section's order, not the module numbering.
