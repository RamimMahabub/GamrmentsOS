# GarmentOS — Phase 1

An authenticated owner workspace for **Yarn Inventory → Knitting → Dyeing**, with company contacts, stock movements, lot trace, dashboard and settings. Later manufacturing modules have no routes, screens or placeholders.

## Stack

Laravel 13, PHP **8.4+**, MySQL 8 (InnoDB / utf8mb4), Breeze email/password authentication, Blade, Livewire 4, Alpine, Tailwind 4, Brick Math and Big.js. PHP 8.4 is required by the resolved dependency lock. Node 24 and Composer 2 are used for development.

Required PHP extensions: PDO / pdo_mysql, mbstring, openssl, fileinfo, curl, XML / DOM, tokenizer, ctype, zip. Add pdo_sqlite for the fast test suite.

## Install

1. Create an empty MySQL database named **garmentsos**, using utf8mb4_unicode_ci, and a database user with access to it.
2. Run:

   ~~~sh
   composer install
   cp .env.example .env
   ~~~

3. Set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL and mail delivery settings in .env. The timezone defaults to Asia/Dhaka.
4. Run:

   ~~~sh
   php artisan key:generate
   php artisan migrate --seed
   npm ci
   npm run build
   php artisan serve --host=127.0.0.1 --port=8000
   ~~~

5. Open http://127.0.0.1:8000.

In local and testing environments the default seeder includes the owner's reference demo. It is idempotent: running it twice preserves the existing demo.

**Local demo login:** owner@garmentos.test / ChangeMe2026!

In production, the default seeder adds preferences only. Create the single owner interactively:

~~~sh
php artisan garmentos:owner --email=you@example.com --name="Owner"
~~~

The password prompt is hidden. Public registration is disabled. Profile and password updates are available under Account; deleting the sole owner through the app is disabled.

### This Windows workspace

The portable runtime is at .tools/php/php.exe; the Composer executable is composer.phar. Neither is a source dependency. The existing XAMPP PHP is too old for this application.

~~~powershell
& .\.tools\php\php.exe composer.phar install
& .\.tools\php\php.exe artisan migrate --seed
npm.cmd ci --cache .tools/npm-cache
npm.cmd run build
& .\.tools\php\php.exe artisan serve --host=127.0.0.1 --port=8000
~~~

The local preview uses a separate **garmentsos_development** database on the installed MariaDB 10.4 server. The test database is **garmentsos_testing**. Application tests never use the development database. CI is configured for MySQL 8.4.

## Using the workspace

- **Yarn:** add a material, price and optional bag capacity. Opening stock and Add stock create purchases. The detail page shows signed movements and a running balance.
- **Companies:** select knitting, dyeing or both. Required role contacts appear by type. Referenced companies can be deactivated; they cannot be deleted or lose a type already used in a job.
- **Knitting:** choose the company and yarns, then date, table unit and DIA count. Enter a quantity per yarn per DIA, using weight or bags. Waste is entered; fabric output is calculated. Saving creates the immutable lot and issues yarn stock.
- **Dyeing:** choose a company and an available lot. The fabric summary shows availability per DIA. Choose colors, adjust the given amounts, and record returns now or later. Allocations are checked across every active job for that lot.
- **Lot trace:** inspect yarns, knitting output, dyeing colors and returned balances by DIA.
- **Settings:** change default yarn units, DIA presets, currency label, color names / hex and merge colors. Company creation is also available inline inside each job form.

Search, sort and 25-row pagination are available in the main lists. Tables scroll horizontally on narrower devices and use sticky headers / totals.

## Decimal and reconciliation decisions

Weights in inventory movements and transaction rows use DECIMAL(14,3). Prices per kg use DECIMAL(18,6). All business calculations use decimal strings; PHP floats and JavaScript floating-point arithmetic are excluded from weight, price and percentage calculations.

The exact conversion factor is **1 lb = 0.45359237 kg**. The reciprocal is calculated from that factor rather than a shortened approximation. Canonical kg values stay unchanged during display unit switches.

**Bag capacity is a conversion factor stored at eight decimals** (DECIMAL(18,8)), so a 100 lb bag retains 45.35923700 kg. Prematurely rounding that factor to three decimals would turn 5 bags into 226.795 kg. The saved shipment correctly rounds once to **226.796 kg**.

Six colors split 226.796 kg as five allocations of **37.799 kg** and one of **37.801 kg**. The final color receives the remainder, keeping the full allocation within available fabric. Changing the count only re-splits untouched given fields; manually entered amounts are preserved.

(37.799 − 35.150) / 37.799 × 100 is **7.01%** at two decimal places (approximately 7%). The app shows that calculated value. Waste is — when a return is empty or the given quantity is zero. Subtotal / overall waste stays pending until every included row has a return; known returned quantities are still summed.

A return above the given amount is allowed after checking the confirmation checkbox. Negative waste then represents added dye mass.

Opening 2,500 lb rounds to 1,133.981 kg. Four independently rounded 500 lb issues total 907.184 kg, leaving 226.797 kg. This one-gram difference is the consequence of storing each movement at three decimals.

Saved DIA identifiers cannot be removed. Edits cannot lower output below fabric already allocated. A knitting job with any dyeing history cannot be deleted. Stock reconciliation writes reversals and replacement issues in one transaction; ledger entries cannot be edited.

Color merging combines matching active job rows and recalculates their waste. If one color has a return and the matching other color is still pending, the merge is blocked until both returns are complete, preserving recorded quantities and return status. Archived rows retain their historical links.

## Architecture and extension points

- **app/Modules/Yarn:** unit conversion, stock ledger and inventory writes.
- **app/Modules/Knitting:** transactional lot numbering, allocation and stock reconciliation.
- **app/Modules/Dyeing:** availability, color merging and dyeing writes.
- **DyedFabricBalance::forLot($lot)** exposes returned quantities by color and DIA for later phases.
- **lots.current_stage** is an indexed string, allowing additional stages without changing a database enum.
- All stage relations use lot_id. **config/navigation.php** builds the sidebar.
- Form Requests validate inputs; services enforce stock / availability and perform multi-table writes in transactions.
- Yarn and lot row locks serialize competing allocations. A locked sequence row and the unique lot-number index protect numbering; deadlock transactions retry.
- Master and transaction models have soft deletes, actor IDs and timestamps. **activity_logs** records stock / job creation, updates and deletion, including reconciled job snapshots.
- **/api/lots/preview**, **/api/lots/{lot}/fabric** and **/api/calculations** require authentication. Server calculations are authoritative. PHP and JavaScript run the same decimal reference fixtures.

## Tests

Fast tests (SQLite in memory; the three InnoDB concurrency tests are intentionally skipped):

~~~sh
php artisan test --compact
npm test
npm run build
~~~

Full InnoDB suite, including four-process races: create a **dedicated empty database called garmentsos_testing** and provide its host / credentials through environment variables or .env, then run:

~~~sh
php artisan test --compact --configuration=phpunit.mysql.xml
~~~

The concurrency suite resets that test database. Its safety check requires the name to end in _testing. Never point it at an application database.

On Windows, replace php with **& .\.tools\php\php.exe**. The GitHub Actions workflow runs both database suites, JavaScript tests and the production build against MySQL 8.4.

### Milestone demos

Verified locally on 5 October 2026: **60 PHP tests / 312 assertions pass on InnoDB**, including all three concurrency races; **11 JavaScript tests pass**. The fast SQLite run passes 57 tests with the three InnoDB-only tests skipped. Production assets build successfully, Pint passes, and Composer / npm audits report no known vulnerabilities.

Run the named tests after each checkpoint. The included demo is available after migrate --seed.

| Milestone | Short demo script | Verification |
|---|---|---|
| 1. Setup | Sign in; open the sidebar and Settings; update default units; refresh. | Auth / Profile tests, migrations, production build, CI configuration. |
| 2. Services | In tests, convert 500 lb and 5 bags; create duplicate lots for 10 May 2026. | CalculationsTest, lot and concurrency tests; reference checks pass. |
| 3. Companies | Add a Both company; enter all three contacts; open the inline modal from Knitting and Dyeing. | MasterDataTest and inline Livewire tests in InterfaceTest. |
| 4. Yarn | View Sudan; switch price lb ↔ kg; set 100 lb bags; add stock and inspect the ledger. | Purchase / ledger feature tests and JS form tests. |
| 5. Knitting | Add stock first if repeating the demo; select Sudan; enter 5 bags per DIA; toggle 0% / 1% waste; save and inspect stock. Edit quantity, then delete an unused test shipment and see reversals. | ManufacturingTest: deductions, insufficient stock, edit rollback, lot immutability and reversals. |
| 6. Dyeing | Edit the seeded job; inspect 6 colors and Golden returns; change the count and preserve a manually edited given amount. Try over-allocation, then added dye weight. | Allocation / return feature tests, touched-field JS tests, InnoDB allocation races. |
| 7. Trace / dashboard | Open SU-100526; compare knitting output, dyeing usage and returned Golden fabric. Check dashboard cards and last 10 lots. | InterfaceTest renders all 20 Phase 1 pages and checks the reference trace. |
| 8. Hardening | Search and paginate; inspect audit entries; check tables at desktop / tablet width; run every test and build. Review the backup steps below. | Full suites, static decimal guard, production asset build and dependency audits. |

Automated verification covers page rendering, Livewire actions, form state, arithmetic and transaction races. **Visual browser QA and owner acceptance remain to be performed**: this agent session had no connected browser, so screenshots and tablet layout inspection could not be completed. CI is supplied but has not been executed remotely.

## Production

Use MySQL 8 / InnoDB, PHP 8.4+, APP_ENV=production, APP_DEBUG=false and HTTPS. Set the web server document root to **public/**. Keep .env outside public access. Configure SMTP for password resets.

~~~sh
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan garmentos:owner --email=you@example.com
php artisan optimize
~~~

Give the application process write access to storage and bootstrap/cache. Restart persistent application workers after deployment. Production seeding never creates the demo login.

## MySQL backup and restore

The included **scripts/backup.sh** creates a compressed transaction-consistent dump for InnoDB. It uses a protected client option file for credentials. See the [MySQL mysqldump manual](https://dev.mysql.com/doc/refman/8.4/en/mysqldump.html).

1. Create **/etc/garmentos/backup.cnf** readable only by the backup user (chmod 600):

   ~~~ini
   [client]
   host=127.0.0.1
   user=your_backup_user
   password=your_backup_password
   ~~~

2. Give that database user the dump privileges appropriate to your tables / routines, create /srv/backups/garmentos, and make scripts/backup.sh executable.
3. Install this cron line on a server using **UTC**. It runs daily at **02:00 Asia/Dhaka**:

   ~~~cron
   0 20 * * * GARMENTOS_DB_NAME=garmentsos /srv/garmentos/scripts/backup.sh >> /srv/backups/garmentos/backup.log 2>&1
   ~~~

4. Copy backups to separate storage and set an appropriate retention policy. Avoid schema changes during a single-transaction dump.
5. Restore a selected backup into a separate empty database and verify the application before replacing live data:

   ~~~sh
   gunzip -c /srv/backups/garmentos/garmentos-TIMESTAMP.sql.gz | mysql --defaults-extra-file=/etc/garmentos/backup.cnf garmentsos_restore
   ~~~

On Windows PowerShell, use **mysqldump --result-file=backup.sql** instead of redirecting SQL with > to avoid PowerShell's text encoding conversion. The cron script is a Linux deployment example; it has not been installed or scheduled on this computer.
