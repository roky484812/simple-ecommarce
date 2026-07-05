# E-Commerce Application — Development Plan

## 1. Objective

Build a full-stack e-commerce application (Storefront + Admin Panel) in Laravel 13,
satisfying the interview brief in `ecommarce.md`: modern responsive UI, normalized
MySQL schema, secure backend, product/category/customer/order/cart/payment/tracking
features, queue-driven heavy tasks, and a live deployment.

## 2. Tech Stack

| Concern | Choice | Reason |
|---|---|---|
| Framework | Laravel 13 / PHP 8.4 | Already scaffolded |
| Database | **MySQL 8** | Required by brief |
| Cache / Session / Queue driver | **Redis** | Required by brief; fast, supports queues, cache, session store, rate limiting |
| Views | **Blade Components** | Required by brief |
| CSS | Tailwind v4 (installed) | Modern, utility-first, responsive |
| UI Kit | **Preline UI** (Tailwind-native component library) | Free, plain HTML/Tailwind markup (drops straight into Blade components), ships ready-made e-commerce blocks (product cards, carts, pricing, star ratings, breadcrumbs, off-canvas mobile nav). Alternative considered: Flowbite (also Tailwind-native, similar fit) — Preline chosen for slightly richer e-commerce block library. |
| Auth | Laravel Breeze (Blade stack) | Fast login/register/profile scaffolding, Blade-based |
| Queue | Redis + Laravel Queue (`horizon` optional) | Required by brief for heavy/background tasks |
| Concurrency | `Illuminate\Support\Facades\Concurrency` | Required by brief for parallelizable read work (dashboard aggregates) |
| Payments | **SSLCommerz** (sandbox mode) | Required by brief; leading BD payment aggregator (cards, mobile banking, net banking) |
| Testing | Pest v4 | Already installed |
| Deployment | Laravel Cloud (or Forge/VPS) | Needs to be a live URL per brief |

## 3. Redis Usage Map

Redis will back four distinct concerns (separate logical connections/DB indexes):

1. **Queue** (`QUEUE_CONNECTION=redis`) — order confirmation emails, invoice PDF
   generation, stock/restock notifications, abandoned-cart reminders.
2. **Cache** (`CACHE_STORE=redis`) — category tree, product listing pages,
   homepage featured products, admin dashboard aggregates (short TTL + tag-based
   invalidation on product/category writes via `Cache::tags()`).
3. **Session** (`SESSION_DRIVER=redis`) — scalable session storage, needed since
   guest carts are session-bound.
4. **Guest cart storage** — guest cart items stored under a Redis key
   (`cart:{session_id}`) so it survives across requests without hitting MySQL,
   merged into a DB-backed cart on login.

`.env` additions:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```
Requires `predis/predis` (or `phpredis` extension) — needs approval before adding
to `composer.json` per project conventions.

## 4. Payment Integration — SSLCommerz

No official Laravel package is well-maintained (only small single-maintainer
community packages with low adoption — a supply-chain risk for a dependency).
Instead, integrate directly against the SSLCommerz REST API using Laravel's
built-in `Http` client, wrapped in a `SslCommerzService`. This keeps full
control, avoids an unvetted dependency, and is easy to explain/extend live in
the onsite AI-agent round.

**Flow** (sandbox: `https://sandbox.sslcommerz.com`, live: `https://securepay.sslcommerz.com`):

1. **Session Initiate** — on checkout submit, `SslCommerzService::initiate()`
   POSTs order/customer/amount details + `success_url`/`fail_url`/`cancel_url`/
   `ipn_url` to `/gwprocess/v4/api.php`. Response contains a `GatewayPageURL`;
   redirect the customer there. Order is created with `status = pending` and
   `payments` row `status = initiated` before redirecting.
2. **Customer pays** on SSLCommerz-hosted page (card / bKash / Nagad / bank).
3. **Redirect callbacks** — SSLCommerz POSTs back to our `success/fail/cancel`
   routes with a `tran_id` and `val_id`.
4. **IPN listener** (`POST /payment/ipn`, CSRF-excluded) — SSLCommerz also
   independently calls this webhook; must be handled regardless of whether the
   customer's browser redirect completes.
5. **Order Validation API** — on both the callback and the IPN, call
   `/validator/api/validationserverAPI.php` with `val_id` to confirm the
   transaction is genuinely `VALID` before marking the order/payment as paid.
   Never trust the redirect/IPN payload alone (spoofable).
6. On validated success: update `payments.status = paid`, `orders.status =
   processing`, write `order_status_histories`, dispatch
   `SendOrderConfirmationEmail` + `GenerateInvoicePdf` queue jobs.
7. On fail/cancel: mark `payments.status = failed|cancelled`, keep order
   `pending`/cancelled, let customer retry checkout.

**Config** (`config/services.php` + `.env`):
```
SSLCOMMERZ_STORE_ID=
SSLCOMMERZ_STORE_PASSWORD=
SSLCOMMERZ_SANDBOX=true
```

**Structure additions**:
- `app/Services/SslCommerzService.php` — `initiate()`, `validateTransaction()`
- `app/Http/Controllers/Storefront/PaymentController.php` — `success`,
  `fail`, `cancel`, `ipn` actions
- Routes: `POST /payment/ipn` must be added to `bootstrap/app.php` CSRF
  exceptions (SSLCommerz posts without a CSRF token)

## 5. Database Schema (MySQL)

| Table | Key columns |
|---|---|
| `users` | + `role` enum(customer, admin), phone, default address fields |
| `profiles` | user_id (1:1), avatar_path, bio, date_of_birth, gender |
| `addresses` | user_id, label, line1, line2, city, state, postal_code, country, is_default |
| `categories` | name, slug, parent_id (self-referencing) |
| `products` | name, slug, description, price, sale_price, sku, stock_qty, category_id, is_active |
| `product_images` | product_id, path, sort_order |
| `carts` | user_id (nullable for guests), session_id |
| `cart_items` | cart_id, product_id, qty, price_snapshot |
| `orders` | user_id, order_number, status enum(pending/processing/shipped/delivered/cancelled), subtotal, tax, shipping, total, shipping_address (json) |
| `order_items` | order_id, product_id, qty, unit_price |
| `payments` | order_id, gateway, transaction_id, amount, status, paid_at |
| `order_status_histories` | order_id, status, note, created_at — powers order tracking |

Conventions: FKs indexed, `decimal(10,2)` for money, soft deletes on
products/categories/orders, factories + seeders for every model.

## 6. Application Structure

```
app/
  Models/
    User, Profile, Address, Category, Product, ProductImage,
    Cart, CartItem, Order, OrderItem, Payment, OrderStatusHistory
  Http/Controllers/
    Storefront/ (HomeController, ProductController, CartController,
                 CheckoutController, OrderController, ProfileController)
    Admin/ (DashboardController, ProductController, CategoryController,
            OrderController, CustomerController, PaymentController)
  Http/Requests/
    StoreProductRequest, CheckoutRequest, UpdateProfileRequest, ...
  Services/
    CartService, OrderService, PaymentService, StockService
  Jobs/
    SendOrderConfirmationEmail, GenerateInvoicePdf,
    RestockNotification, SendAbandonedCartReminder
  Policies/
    ProductPolicy, OrderPolicy

resources/views/
  components/
    layouts/ (storefront, admin, guest)
    ui/ (button, input, select, badge, modal, card, alert, pagination-links)
    storefront/ (navbar, footer, product-card, cart-drawer, order-status-timeline,
                 breadcrumbs, rating-stars)
    admin/ (sidebar, topbar, stat-card, data-table)
  storefront/
    home.blade.php
    products/{index,show}.blade.php
    cart.blade.php
    checkout.blade.php
    orders/{index,show}.blade.php
    profile/
      edit.blade.php        # profile info + avatar
      addresses.blade.php   # manage saved addresses
      password.blade.php    # change password (Breeze partial)
  admin/
    dashboard.blade.php
    products/*, categories/*, orders/*, customers/*, payments/*
```

## 7. Feature Breakdown

### 7.1 Storefront
- Home: featured/new products via reusable `<x-storefront.product-card>`
- Category & product listing with filters (category, price range, search), pagination
- Product detail: image gallery, stock status, ratings placeholder
- Cart: guest cart in Redis, authenticated cart in MySQL, merged on login,
  rendered via `<x-storefront.cart-drawer>`
- Checkout: address form (reuses saved `addresses`) → SSLCommerz payment
  session → order created (`pending`) inside a DB transaction before redirect
- Order history + tracking page with `<x-storefront.order-status-timeline>`

### 7.2 User Profile Page
- `/profile` (auth-protected, Breeze-based):
  - Edit name/email/phone, avatar upload (stored in `storage/app/public/avatars`)
  - Manage saved addresses (CRUD, mark default)
  - Change password (Breeze's existing partial reused)
  - Recent orders summary block linking to `orders.show`
- Built as tabbed Blade component (`<x-ui.tabs>`) — Profile / Addresses / Security / Orders

### 7.3 Admin Panel (`/admin`, `role:admin` middleware)
- Dashboard: sales stats, order counts, low-stock alerts — aggregates fetched in
  parallel via `Concurrency::run([...])`, cached in Redis for N minutes
- Product & Category CRUD with image upload
- Customer management: list, view customer's orders/addresses, block/unblock
- Order management: update status → writes `order_status_histories` row +
  dispatches notification job
- Payment history: list/filter by status/gateway, view transaction detail

### 7.4 Auth & Roles
- Breeze Blade scaffolding (login/register/reset)
- `role` column, `admin` middleware alias, `ProductPolicy`/`OrderPolicy` for
  fine-grained checks

### 7.5 Queue Jobs (Redis-backed)
- `SendOrderConfirmationEmail` + `GenerateInvoicePdf` — dispatched right after
  successful checkout
- `RestockNotification` — dispatched when `stock_qty` crosses a low threshold
- `SendAbandonedCartReminder` — scheduled command, queued per abandoned cart
- Local dev: `php artisan queue:work redis`; production: Supervisor-managed
  worker (+ Horizon dashboard optional for visibility)

### 7.6 Concurrency Usage
- Admin dashboard: total sales, order count, top products, low-stock count all
  fetched concurrently via `Concurrency::run()` instead of sequential queries
- Result cached in Redis with short TTL, invalidated on relevant writes

## 8. Blade Component & UI Approach

- Base design system (`resources/views/components/ui/`): `<x-ui.button>`,
  `<x-ui.input>`, `<x-ui.select>`, `<x-ui.card>`, `<x-ui.badge>`, `<x-ui.modal>`,
  `<x-ui.alert>` — thin wrappers around Preline UI markup/classes
- Two layouts: `<x-layouts.storefront>` (navbar + footer, mobile off-canvas menu,
  cart drawer) and `<x-layouts.admin>` (collapsible sidebar + topbar, mobile
  hamburger toggle) — both built mobile-first with Tailwind breakpoints
- Anonymous components for markup-only pieces; class-based components
  (`php artisan make:component`) for ones needing logic (`CartDrawer`,
  `OrderStatusTimeline`, `ProfileTabs`)
- Preline's JS (`preline` npm package) loaded once in the storefront/admin
  layouts for interactive bits (dropdowns, off-canvas, modals) without a full JS
  framework

## 9. Build Order (incrementally demoable)

1. Switch `.env` to MySQL + Redis (cache/session/queue); install `predis/predis`
2. Install Breeze (Blade), add `role` column + admin middleware
3. Category, Product, ProductImage models/migrations/factories/seeders
4. Address & Profile models/migrations tied to `users`
5. UI design system components + storefront layout (Preline integration)
6. Storefront: home, listing, product detail
7. Cart: Redis-backed guest cart + DB cart, `CartService`, `<x-cart-drawer>`
8. Profile pages: edit info, addresses CRUD, password (tabs component)
9. Checkout + Order + `SslCommerzService` integration (sandbox) + IPN/validation
   handling + confirmation queue job
10. Admin layout + dashboard (Concurrency + Redis-cached aggregates)
11. Admin CRUD: products, categories, customers, orders, payments
12. Order tracking timeline wired to `order_status_histories`
13. Abandoned cart reminder scheduled job
14. Pest feature tests: auth, profile, cart, checkout (mock SSLCommerz HTTP
    calls), admin CRUD, order tracking
15. Responsive UI polish across breakpoints, `vendor/bin/pint --dirty`
16. Deploy (Laravel Cloud/Forge): MySQL + Redis provisioned, queue worker running,
    `npm run build`, migrations run on prod DB, SSLCommerz sandbox credentials
    set (switch to live credentials only if merchant account is approved)
17. Pre-onsite prep: keep code conventional/documented so a new feature can be
    added live with an AI coding agent

## 10. Open Decisions Needing Approval

- Adding `predis/predis` (or requiring `ext-redis`) to `composer.json`
- Adding `preline` to `package.json` / npm
- SSLCommerz sandbox merchant credentials (store ID/password) — user to
  provide; no third-party SDK will be added, integration is via `Http` client
- Choice of deployment target (Laravel Cloud vs Forge vs other VPS)
