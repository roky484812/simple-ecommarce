# E-Commerce Application — Development Plan

> **Companion document: [`DATABASE.md`](./DATABASE.md)** — full schema
> (tables, columns, types, FKs, relationships, migration order). Whenever a
> module below has a **Database** section, cross-check `DATABASE.md` for the
> authoritative column list before writing migrations/models, and keep both
> files in sync if the schema changes.

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


## 5. How to Use This Plan

Each module below is **self-contained**: it lists exactly which migrations,
models, services, controllers, routes, Blade views/components, and tests
belong to it. Build and prompt one module at a time, in the listed order —
each module only depends on modules before it. When prompting an AI agent,
you can paste a single module's section as the task description.

Every module ends with a **Definition of Done** checklist and its own Pest
tests, so it can be verified independently before moving to the next.

---

## Module 0 — Environment & Foundation

**Goal**: Project boots on MySQL + Redis, base UI shell and design system exist.

**Backend**
- `.env`: `DB_CONNECTION=mysql` (+ credentials), `CACHE_STORE=redis`,
  `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `REDIS_*`
- `composer require predis/predis` (approval needed)
- `npm install preline` (approval needed)
- Run `php artisan migrate` against MySQL to confirm connectivity

**Frontend**
- `resources/css/app.css`: Tailwind v4 theme tokens (colors, font) for the store brand
- `resources/js/app.js`: import `daisyui` and initialize on `DOMContentLoaded`
- Blade component library (`resources/views/components/ui/`):
  `button.blade.php`, `input.blade.php`, `select.blade.php`, `textarea.blade.php`,
  `card.blade.php`, `badge.blade.php`, `modal.blade.php`, `alert.blade.php`,
  `pagination-links.blade.php`
- Two shells: `components/layouts/storefront.blade.php` (navbar+footer slot),
  `components/layouts/admin.blade.php` (sidebar+topbar slot) — mobile responsive
  (off-canvas nav/sidebar under `lg:` breakpoint)

**Definition of Done**
- [ ] `php artisan migrate:fresh` succeeds against MySQL
- [ ] `php artisan tinker --execute 'Cache::put("t",1); echo Cache::get("t");'` returns via Redis
- [ ] Both layout shells render with placeholder content, responsive at 375px/768px/1280px
- [ ] `vendor/bin/pint --dirty` clean

---

## Module 1 — Auth & Roles

**Goal**: Users can register/login; `admin` vs `customer` role gating works.

**Database** *(see [`DATABASE.md`](./DATABASE.md) §2.1)*
- Migration: add `role` enum(`customer`,`admin`) default `customer` to `users`
- Migration: `phone` nullable string on `users`
- Migration: `is_blocked` boolean default false on `users` (added here per
  `DATABASE.md` §3 integration note, even though the blocking *behavior* is
  built in Module 9)

**Backend**
- `composer require laravel/breeze --dev` (approval needed) → `php artisan breeze:install blade`
- `app/Models/User.php`: add `role`, `phone` to `$fillable`, `isAdmin(): bool` helper
- Middleware alias `admin` in `bootstrap/app.php` → redirect non-admins with 403
- `database/seeders/DatabaseSeeder.php`: seed one admin + a few customer users

**Frontend**
- Breeze's Blade auth views (`login`, `register`, `forgot-password`) restyled to
  use `<x-ui.input>` / `<x-ui.button>` and the storefront layout
- Post-login redirect: admin → `/admin`, customer → `/`

**Routes**
- Breeze defaults (`/login`, `/register`, `/logout`, ...)
- `/admin/*` group wrapped in `['auth','admin']`

**Definition of Done**
- [ ] Pest: registering creates `role = customer` by default
- [ ] Pest: non-admin hitting `/admin` gets 403
- [ ] Pest: admin hitting `/admin` gets 200
- [ ] Responsive auth forms verified at mobile width

---

## Module 2 — Category Management (Admin)

**Goal**: Admin can CRUD categories (with subcategories).

**Database** *(see [`DATABASE.md`](./DATABASE.md) §2.4)*
- Migration `categories`: `id, name, slug, parent_id (nullable, self-FK), is_active, timestamps, softDeletes`

**Backend**
- `app/Models/Category.php`: `parent()`, `children()` relations, slug auto-generation on save
- `app/Http/Requests/StoreCategoryRequest.php`, `UpdateCategoryRequest.php`
- `app/Http/Controllers/Admin/CategoryController.php`: index/create/store/edit/update/destroy
- `database/factories/CategoryFactory.php`, seeder with a couple of parent + child categories

**Frontend**
- `resources/views/admin/categories/index.blade.php` — `<x-admin.data-table>` listing, nested indent for children
- `resources/views/admin/categories/{create,edit}.blade.php` — form with parent select
- Delete via modal confirm (`<x-ui.modal>`)

**Routes**
- `Route::resource('admin/categories', Admin\CategoryController::class)`

**Definition of Done**
- [x] Pest feature test: admin can create/update/delete a category
- [x] Pest: deleting a parent with children is blocked or cascades (decide + document behavior)
      → **Decision: blocked.** `Admin\CategoryController::destroy()` refuses to delete a
      category that still has children (flash error message); children must be
      deleted/reassigned first.
- [x] Responsive table (stacks to cards on mobile)

---

## Module 3 — Product Management (Admin)

**Goal**: Admin can CRUD products with images and stock.

**Database** *(see [`DATABASE.md`](./DATABASE.md) §2.5–2.6)*
- Migration `products`: `id, category_id (FK), name, slug, description, price, sale_price nullable, sku unique, stock_qty, is_active, timestamps, softDeletes`
- Migration `product_images`: `id, product_id (FK), path, sort_order, timestamps`

**Backend**
- `app/Models/Product.php`: `category()`, `images()`, `isInStock()`, `displayPrice()` accessor
- `app/Models/ProductImage.php`
- `app/Http/Requests/StoreProductRequest.php`, `UpdateProductRequest.php` (image validation: `image|max:2048`)
- `app/Http/Controllers/Admin/ProductController.php`
- `app/Services/StockService.php`: `decrement()`, `increment()`, dispatches `RestockNotification` job when `stock_qty` crosses low-stock threshold
- `database/factories/ProductFactory.php` + `ProductImageFactory.php`, seeder with ~30 demo products across categories

**Frontend**
- `resources/views/admin/products/index.blade.php` — searchable/filterable table, stock badge (`<x-ui.badge>` green/red)
- `resources/views/admin/products/{create,edit}.blade.php` — multi-image upload with preview, category select
- `resources/views/components/admin/stat-card.blade.php` (used later by dashboard, build now since it's product-adjacent)

**Routes**
- `Route::resource('admin/products', Admin\ProductController::class)`

**Definition of Done**
- [ ] Pest: create product with images, verify files stored under `storage/app/public`
- [ ] Pest: `StockService::decrement()` triggers `RestockNotification` job when crossing threshold (use `Queue::fake()`)
- [ ] Responsive form and table

---

## Module 4 — Storefront: Home, Listing, Product Detail

**Goal**: Public-facing browsing experience, no auth required.

**Backend**
- `app/Http/Controllers/Storefront/HomeController.php`: featured + new-arrival products (Redis-cached, `Cache::remember()`)
- `app/Http/Controllers/Storefront/ProductController.php`: `index` (category/price/search filters + pagination), `show`
- Category tree cached in Redis, invalidated via model observer on `Category` save/delete

**Frontend**
- `resources/views/storefront/home.blade.php` — hero banner, `<x-storefront.product-card>` grid
- `resources/views/storefront/products/index.blade.php` — sidebar filters (collapsible on mobile), grid + pagination
- `resources/views/storefront/products/show.blade.php` — image gallery (Preline carousel), `<x-storefront.rating-stars>` (static placeholder), add-to-cart form
- `resources/views/components/storefront/{navbar,footer,product-card,breadcrumbs,rating-stars}.blade.php`

**Routes**
- `GET /`, `GET /products`, `GET /products/{product:slug}`, `GET /categories/{category:slug}`

**Definition of Done**
- [ ] Pest: home page shows only `is_active` products
- [ ] Pest: product listing filters by category/price/search correctly
- [ ] Cache invalidates when a product is updated (assert via `Cache::has()`)
- [ ] Fully responsive at 375/768/1280px, images lazy-loaded

---

## Module 5 — Cart (Guest + Authenticated)

**Goal**: Add/update/remove cart items; guest carts in Redis, user carts in MySQL, merged on login.

**Database** *(see [`DATABASE.md`](./DATABASE.md) §2.7–2.8)*
- Migration `carts`: `id, user_id nullable (FK), session_id nullable, timestamps`
- Migration `cart_items`: `id, cart_id (FK), product_id (FK), qty, price_snapshot, timestamps`

**Backend**
- `app/Models/Cart.php`, `app/Models/CartItem.php`
- `app/Services/CartService.php`: `add()`, `update()`, `remove()`, `getOrCreateCart()`
  (guest → Redis key `cart:{session_id}`, decides Redis vs DB by auth state),
  `mergeGuestCartIntoUser()` called from a login event listener
- `app/Http/Controllers/Storefront/CartController.php`: `index`, `store`, `update`, `destroy`
- Event listener on `Illuminate\Auth\Events\Login` → `CartService::mergeGuestCartIntoUser()`

**Frontend**
- `resources/views/storefront/cart.blade.php` — full cart page, qty steppers, remove buttons
- `resources/views/components/storefront/cart-drawer.blade.php` (class-based component, Livewire-free — reloads via small fetch+DOM update or full page nav, keep it simple with Blade + form submits) — mini-cart icon badge in navbar

**Routes**
- `GET /cart`, `POST /cart`, `PATCH /cart/{cartItem}`, `DELETE /cart/{cartItem}`

**Definition of Done**
- [ ] Pest: guest add-to-cart persists in Redis and survives across requests (same session)
- [ ] Pest: logging in merges guest cart into DB cart, no duplicate items (qty summed)
- [ ] Pest: cannot add more than `stock_qty` to cart
- [ ] Cart drawer/page responsive, works with keyboard/touch

---

## Module 6 — User Profile

**Goal**: Authenticated users manage their info, addresses, password, and see recent orders.

**Database** *(see [`DATABASE.md`](./DATABASE.md) §2.2–2.3)*
- Migration `profiles`: `id, user_id (FK, unique), avatar_path nullable, bio nullable, date_of_birth nullable, gender nullable, timestamps`
- Migration `addresses`: `id, user_id (FK), label, line1, line2 nullable, city, state, postal_code, country, is_default, timestamps`

**Backend**
- `app/Models/Profile.php` (belongsTo User), `app/Models/Address.php`
- `app/Http/Requests/UpdateProfileRequest.php`, `StoreAddressRequest.php`
- `app/Http/Controllers/Storefront/ProfileController.php`: `edit`, `update` (info+avatar),
  `addresses` (index/store/update/destroy/setDefault)
- Reuses Breeze's existing password-update controller/route

**Frontend**
- `resources/views/storefront/profile/edit.blade.php` — tabbed via `<x-ui.tabs>`:
  **Profile** (name/email/phone/avatar/bio), **Addresses** (CRUD list, default badge),
  **Security** (Breeze password partial), **Orders** (recent 5, link to Module 8's order history)
- `app/View/Components/ProfileTabs.php` (class-based) if tab state needs logic beyond markup

**Routes**
- `GET/PATCH /profile`, `GET/POST/PATCH/DELETE /profile/addresses/*`

**Definition of Done**
- [ ] Pest: user can update name/phone/avatar (avatar stored, old one cleaned up on replace)
- [ ] Pest: user can CRUD addresses, only one `is_default` at a time
- [ ] Responsive tabs (dropdown/accordion on mobile if needed)

---

## Module 7 — Checkout & Payment (SSLCommerz)

**Goal**: Convert cart → order, take payment via SSLCommerz, confirm via IPN + Validation API.

**Database** *(see [`DATABASE.md`](./DATABASE.md) §2.9–2.12 — follow the
migration order in §4, not this module numbering, since `orders` depends on
`carts` and `addresses` existing first)*
- Migration `orders`: `id, user_id (FK), order_number unique, status enum(pending,processing,shipped,delivered,cancelled), subtotal, tax, shipping, total, shipping_address json, timestamps, softDeletes`
- Migration `order_items`: `id, order_id (FK), product_id (FK), qty, unit_price, timestamps`
- Migration `payments`: `id, order_id (FK), gateway, transaction_id, amount, status, paid_at nullable, timestamps`
- Migration `order_status_histories`: `id, order_id (FK), status, note nullable, created_at`

**Backend**
- `app/Models/{Order,OrderItem,Payment,OrderStatusHistory}.php`
- `app/Services/OrderService.php`: `createFromCart()` (DB transaction: snapshot cart → order+items, decrement stock via `StockService`, clear cart)
- `app/Services/SslCommerzService.php`: `initiate()`, `validateTransaction()` (per Section 4 flow)
- `app/Http/Controllers/Storefront/CheckoutController.php`: `show` (address selection + order summary), `store` (creates pending order, calls `SslCommerzService::initiate()`, redirects to gateway)
- `app/Http/Controllers/Storefront/PaymentController.php`: `success`, `fail`, `cancel`, `ipn`
  — all call `validateTransaction()` before mutating state (idempotent: check `payments.status` first)
- `app/Jobs/{SendOrderConfirmationEmail,GenerateInvoicePdf}.php` dispatched after validated success
- CSRF exception for `POST /payment/ipn` in `bootstrap/app.php`

**Frontend**
- `resources/views/storefront/checkout.blade.php` — address picker (from Module 6), order summary, "Pay with SSLCommerz" button
- `resources/views/storefront/checkout/{success,fail,cancel}.blade.php` — confirmation pages

**Routes**
- `GET/POST /checkout`
- `GET /payment/success`, `GET /payment/fail`, `GET /payment/cancel`, `POST /payment/ipn`

**Definition of Done**
- [ ] Pest: `OrderService::createFromCart()` creates correct order/items/total inside a transaction, rolls back on failure
- [ ] Pest: `SslCommerzService` calls mocked via `Http::fake()` — initiate returns gateway URL, validate confirms `VALID` status
- [ ] Pest: IPN handler is idempotent (calling twice doesn't double-fire confirmation email)
- [ ] Confirmation email + invoice job dispatched only after validated payment (`Queue::fake()` assertion)
- [ ] Mobile-responsive checkout form

---

## Module 8 — Order History & Tracking (Storefront)

**Goal**: Customers view past orders and track status.

**Backend**
- `app/Http/Controllers/Storefront/OrderController.php`: `index` (paginated, own orders only via policy), `show`
- `app/Policies/OrderPolicy.php`: `view()` — only owner or admin

**Frontend**
- `resources/views/storefront/orders/index.blade.php` — order list with status badges
- `resources/views/storefront/orders/show.blade.php` — line items, totals, `<x-storefront.order-status-timeline>` driven by `order_status_histories`
- `app/View/Components/OrderStatusTimeline.php` (class-based, takes `Order`, renders step progress)

**Routes**
- `GET /orders`, `GET /orders/{order}`

**Definition of Done**
- [ ] Pest: customer cannot view another customer's order (403)
- [ ] Pest: timeline renders all `order_status_histories` entries in order
- [ ] Responsive timeline (vertical stack on mobile)

---

## Module 9 — Admin: Customers & Orders Management

**Goal**: Admin manages customers and order lifecycle.

**Backend**
- `app/Http/Controllers/Admin/CustomerController.php`: `index` (search), `show` (orders+addresses), `toggleBlock`
- `app/Http/Controllers/Admin/OrderController.php`: `index` (filter by status), `show`, `updateStatus`
  (writes `order_status_histories`, dispatches a "status changed" notification job)
- `users.is_blocked` column already exists from Module 1's migration (per
  `DATABASE.md` §3) — this module implements the login-blocking *behavior* only

**Frontend**
- `resources/views/admin/customers/{index,show}.blade.php`
- `resources/views/admin/orders/{index,show}.blade.php` — status dropdown with confirm modal

**Routes**
- `Route::resource('admin/customers', ...)->only(['index','show'])` + `POST admin/customers/{user}/toggle-block`
- `Route::resource('admin/orders', ...)->only(['index','show'])` + `PATCH admin/orders/{order}/status`

**Definition of Done**
- [ ] Pest: admin can change order status, history row created, notification job dispatched
- [ ] Pest: blocked customer cannot log in
- [ ] Responsive tables

---

## Module 10 — Admin: Payment History

**Goal**: Admin views/filters all payment transactions.

**Backend**
- `app/Http/Controllers/Admin/PaymentController.php`: `index` (filter by status/gateway/date range), `show`
- `payments.gateway_response` and `payments.val_id` columns already exist
  from Module 7's migration (per `DATABASE.md` §2.11) — no new migration needed here

**Frontend**
- `resources/views/admin/payments/{index,show}.blade.php` — transaction detail incl. raw gateway response (from `payments.gateway_response` json column)

**Routes**
- `Route::resource('admin/payments', ...)->only(['index','show'])`

**Definition of Done**
- [ ] Pest: filters return correct subset
- [ ] Responsive table

---

## Module 11 — Admin Dashboard (Concurrency + Redis Cache)

**Goal**: At-a-glance sales/stock overview, demonstrating required Concurrency usage.

**Backend**
- `app/Http/Controllers/Admin/DashboardController.php`: uses
  `Concurrency::run([fn () => ..., fn () => ..., ...])` to fetch in parallel:
  total sales (30d), order count by status, top 5 products, low-stock count
- Result cached in Redis (`Cache::remember('admin:dashboard', now()->addMinutes(5), ...)`),
  invalidated on order/product writes via observers

**Frontend**
- `resources/views/admin/dashboard.blade.php` — `<x-admin.stat-card>` grid, simple chart (CSS/SVG or Chart.js if approved)

**Routes**
- `GET /admin` (dashboard as admin home)

**Definition of Done**
- [ ] Pest: dashboard data matches direct DB aggregates
- [ ] Verify (manually or via a timing assertion) that `Concurrency::run` is actually used, not sequential calls
- [ ] Cache invalidates after a new order/product change
- [ ] Responsive stat grid (stacks on mobile)

---

## Module 12 — Scheduled/Background Jobs

**Goal**: Abandoned cart reminders and any remaining async housekeeping.

**Backend**
- `app/Console/Commands/SendAbandonedCartReminders.php` — finds carts inactive > N hours with items, dispatches `SendAbandonedCartReminder` job per cart
- Scheduled in `routes/console.php` (Laravel 13 scheduler) — e.g. hourly
- `app/Jobs/SendAbandonedCartReminder.php`

**Definition of Done**
- [ ] Pest: command dispatches one job per qualifying cart, skips recently-active/empty carts
- [ ] `php artisan schedule:list` shows the job registered

---

## Module 13 — Testing, Polish & Deployment

**Goal**: Everything green, responsive, documented, and live.

**Tasks**
- Full Pest suite run (`php artisan test --compact`), fix any gaps per module's DoD
- `vendor/bin/pint --dirty --format agent` across all touched files
- Manual responsive pass at 375px / 768px / 1024px / 1440px for every screen built
- Queue worker + scheduler set up for production (Supervisor config or Laravel Cloud's managed queue)
- Provision MySQL + Redis on chosen host, run migrations + seeders (demo data) on prod
- Switch SSLCommerz to live credentials only if a live merchant account is approved and ready
- Final smoke test on the live URL: register → browse → cart → checkout → payment → order tracking → admin CRUD

**Definition of Done**
- [ ] All module tests passing
- [ ] App reachable at a live URL, full happy-path flow works end-to-end
- [ ] README or short runbook for restarting queue worker / scheduler in production

---

## 6. Open Decisions Needing Approval

- Adding `predis/predis` to `composer.json`
- Adding `laravel/breeze` (dev dependency) to `composer.json`
- Adding `preline` to `package.json`
- SSLCommerz sandbox merchant credentials (store ID/password) — to be provided by user
- Optional: Chart.js for dashboard charts (Module 11) — CSS/SVG fallback if not approved
- Choice of deployment target (Laravel Cloud vs Forge vs other VPS)
